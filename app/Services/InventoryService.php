<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\RestockRequest;
use App\Models\StockIntake;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Check if a menu item can be fulfilled for a given quantity.
     * Returns array with 'available' bool and 'shortages' array of insufficient ingredients.
     */
    public function checkStockAvailability(Menu $menu, int $quantity = 1): array
    {
        $menu->loadMissing('recipes.ingredient');
        $shortages = [];

        foreach ($menu->recipes as $recipe) {
            $required = $recipe->amount_required * $quantity;
            $ingredient = $recipe->ingredient;

            if (! $ingredient || $ingredient->current_stock < $required) {
                $shortages[] = [
                    'ingredient_id' => $ingredient->ingredient_id ?? null,
                    'item_name' => $ingredient->item_name ?? 'Unknown',
                    'required' => round($required, 2),
                    'available' => round($ingredient->current_stock ?? 0, 2),
                    'unit' => $ingredient->unit ?? '',
                    'deficit' => round($required - ($ingredient->current_stock ?? 0), 2),
                ];
            }
        }

        return [
            'available' => empty($shortages),
            'shortages' => $shortages,
        ];
    }

    /**
     * Check stock availability for multiple cart items at once.
     * Aggregates ingredient requirements across all items to detect combined shortages.
     */
    public function checkCartStockAvailability(array $cartItems): array
    {
        $aggregatedRequirements = [];

        foreach ($cartItems as $item) {
            $menu = Menu::with('recipes.ingredient')->find($item['menu_id']);
            if (! $menu) {
                continue;
            }

            foreach ($menu->recipes as $recipe) {
                $ingId = $recipe->ingredient_id;
                $required = $recipe->amount_required * $item['quantity'];

                if (! isset($aggregatedRequirements[$ingId])) {
                    $aggregatedRequirements[$ingId] = [
                        'ingredient' => $recipe->ingredient,
                        'total_required' => 0,
                    ];
                }
                $aggregatedRequirements[$ingId]['total_required'] += $required;
            }
        }

        $shortages = [];
        foreach ($aggregatedRequirements as $ingId => $data) {
            $ingredient = $data['ingredient'];
            $totalRequired = $data['total_required'];

            if (! $ingredient || $ingredient->current_stock < $totalRequired) {
                $shortages[] = [
                    'ingredient_id' => $ingredient->ingredient_id ?? null,
                    'item_name' => $ingredient->item_name ?? 'Unknown',
                    'required' => round($totalRequired, 2),
                    'available' => round($ingredient->current_stock ?? 0, 2),
                    'unit' => $ingredient->unit ?? '',
                    'deficit' => round($totalRequired - ($ingredient->current_stock ?? 0), 2),
                ];
            }
        }

        return [
            'available' => empty($shortages),
            'shortages' => $shortages,
        ];
    }

    /**
     * Atomically deduct ingredient stocks for a list of cart items.
     * Uses lockForUpdate() to prevent race conditions.
     * Creates restock requests if stock falls below minimum_threshold.
     *
     * IMPORTANT: Must be called within a DB::transaction() context.
     *
     * @throws \Exception if stock insufficient (will rollback the transaction)
     */
    public function deductStockForOrder(array $cartItems, int $cashierId): void
    {
        foreach ($cartItems as $item) {
            $menu = Menu::with('recipes.ingredient')->findOrFail($item['menu_id']);

            foreach ($menu->recipes as $recipe) {
                $requiredQty = $recipe->amount_required * $item['quantity'];

                // Lock the row to prevent concurrent modification (pessimistic locking)
                $ingredient = Ingredient::where('ingredient_id', $recipe->ingredient_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($ingredient->current_stock < $requiredQty) {
                    throw new \Exception(
                        "Stok {$ingredient->item_name} tidak mencukupi. ".
                        "Dibutuhkan: {$requiredQty} {$ingredient->unit}, ".
                        "Tersedia: {$ingredient->current_stock} {$ingredient->unit}."
                    );
                }

                // Atomically decrement stock
                $ingredient->decrement('current_stock', $requiredQty);

                // Refresh to get updated stock value
                $ingredient->refresh();

                // Auto-create restock request if stock falls below threshold
                if ($ingredient->current_stock <= $ingredient->minimum_threshold) {
                    RestockRequest::firstOrCreate(
                        [
                            'ingredient_id' => $ingredient->ingredient_id,
                            'status' => 'PENDING',
                        ],
                        [
                            'requested_by_user' => $cashierId,
                            'quantity_requested' => $ingredient->minimum_threshold * 2,
                            'notes' => "Auto-generated: Stok {$ingredient->item_name} turun di bawah ambang batas minimum ".
                                       "({$ingredient->current_stock} {$ingredient->unit} <= {$ingredient->minimum_threshold} {$ingredient->unit}) ".
                                       'setelah transaksi POS.',
                        ]
                    );
                }
            }
        }
    }

    /**
     * Calculate the maximum number of servings possible for a menu item
     * based on current ingredient stock levels.
     */
    public function calculateMaxServings(Menu $menu): int
    {
        $menu->loadMissing('recipes.ingredient');
        $maxServings = PHP_INT_MAX;

        foreach ($menu->recipes as $recipe) {
            if (! $recipe->ingredient || $recipe->amount_required <= 0) {
                return 0;
            }
            $possible = (int) floor($recipe->ingredient->current_stock / $recipe->amount_required);
            $maxServings = min($maxServings, $possible);
        }

        return $maxServings === PHP_INT_MAX ? 0 : $maxServings;
    }

    /**
     * Get aggregate KPI metrics for inventory dashboard.
     */
    public function getInventoryKPIs(): array
    {
        $ingredients = Ingredient::all();
        $totalItems = $ingredients->count();

        $healthyItems = $ingredients->filter(fn ($i) => (float) $i->current_stock > (float) $i->minimum_threshold);
        $lowStockItems = $ingredients->filter(fn ($i) => (float) $i->current_stock <= (float) $i->minimum_threshold && (float) $i->current_stock > 0);
        $outOfStockItems = $ingredients->filter(fn ($i) => (float) $i->current_stock <= 0);

        $totalValuation = $ingredients->sum(fn ($i) => (float) $i->current_stock * (float) $i->unit_cost);

        $healthyPercent = $totalItems > 0 ? round(($healthyItems->count() / $totalItems) * 100) : 0;
        $lowStockPercent = $totalItems > 0 ? round(($lowStockItems->count() / $totalItems) * 100) : 0;
        $outOfStockPercent = $totalItems > 0 ? round(($outOfStockItems->count() / $totalItems) * 100) : 0;

        return [
            'total_items' => $totalItems,
            'healthy_count' => $healthyItems->count(),
            'healthy_percent' => $healthyPercent,
            'low_stock_count' => $lowStockItems->count(),
            'low_stock_percent' => $lowStockPercent,
            'out_of_stock_count' => $outOfStockItems->count(),
            'out_of_stock_percent' => $outOfStockPercent,
            'total_valuation' => round($totalValuation, 2),
        ];
    }

    /**
     * Record physical stock intake from a supplier and increment ingredient stock balance.
     * Atomically executes in DB transaction with row locking.
     */
    public function recordStockIntake(array $data, int $userId): StockIntake
    {
        return DB::transaction(function () use ($data, $userId) {
            $ingredient = Ingredient::where('ingredient_id', $data['ingredient_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $qtyReceived = (float) $data['quantity_received'];
            if ($qtyReceived <= 0) {
                throw new \InvalidArgumentException('Jumlah barang masuk harus lebih besar dari 0.');
            }

            // 1. Increment current stock
            $ingredient->increment('current_stock', $qtyReceived);
            $ingredient->refresh();

            // 2. Create stock intake record
            $intake = StockIntake::create([
                'ingredient_id' => $ingredient->ingredient_id,
                'user_id' => $userId,
                'restock_request_id' => $data['restock_request_id'] ?? null,
                'quantity_received' => $qtyReceived,
                'unit' => $data['unit'] ?? $ingredient->unit,
                'supplier_name' => $data['supplier_name'] ?? $ingredient->preferred_supplier,
                'invoice_number' => $data['invoice_number'] ?? null,
                'storage_location' => $data['storage_location'] ?? $ingredient->storage_location,
                'batch_number' => $data['batch_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // 3. If tied to a specific restock request or if an approved request exists for this ingredient, mark COMPLETED
            if (! empty($data['restock_request_id'])) {
                $restock = RestockRequest::find($data['restock_request_id']);
                if ($restock && in_array($restock->status, ['PENDING', 'APPROVED'])) {
                    $restock->update([
                        'status' => 'COMPLETED',
                        'notes' => ($restock->notes ? $restock->notes.' | ' : '').
                                   "Stock Intake recorded: {$qtyReceived} {$intake->unit} on ".now()->format('Y-m-d H:i'),
                    ]);
                }
            } else {
                // Auto-complete any APPROVED request for this ingredient
                $matchingRequest = RestockRequest::where('ingredient_id', $ingredient->ingredient_id)
                    ->where('status', 'APPROVED')
                    ->latest()
                    ->first();

                if ($matchingRequest) {
                    $matchingRequest->update([
                        'status' => 'COMPLETED',
                        'notes' => ($matchingRequest->notes ? $matchingRequest->notes.' | ' : '').
                                   "Auto-completed by Stock Intake: {$qtyReceived} {$intake->unit} on ".now()->format('Y-m-d H:i'),
                    ]);
                    $intake->update(['restock_request_id' => $matchingRequest->request_id]);
                }
            }

            return $intake;
        });
    }

    /**
     * Create a manual restock requisition.
     */
    public function createRestockRequest(array $data, int $userId): RestockRequest
    {
        $ingredient = Ingredient::findOrFail($data['ingredient_id']);
        $quantity = (float) $data['quantity_requested'];

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Jumlah pengajuan restock harus lebih besar dari 0.');
        }

        return RestockRequest::create([
            'ingredient_id' => $ingredient->ingredient_id,
            'requested_by_user' => $userId,
            'quantity_requested' => $quantity,
            'status' => 'PENDING',
            'notes' => $data['notes'] ?? "Manual request by staff for {$ingredient->item_name}.",
        ]);
    }

    /**
     * Manager approves a pending restock requisition.
     */
    public function approveRestockRequest(int $requestId, int $managerId, ?string $notes = null): RestockRequest
    {
        $request = RestockRequest::findOrFail($requestId);

        if ($request->status !== 'PENDING') {
            throw new \InvalidArgumentException("Hanya pengajuan berstatus PENDING yang dapat disetujui. Status saat ini: {$request->status}");
        }

        $notesUpdate = $request->notes;
        if ($notes) {
            $notesUpdate = ($notesUpdate ? $notesUpdate.' | ' : '').'Manager Approval: '.$notes;
        }

        $request->update([
            'status' => 'APPROVED',
            'approved_by_user' => $managerId,
            'notes' => $notesUpdate,
        ]);

        return $request->fresh(['ingredient', 'requestedBy', 'approvedBy']);
    }

    /**
     * Manager rejects a pending restock requisition.
     */
    public function rejectRestockRequest(int $requestId, int $managerId, ?string $notes = null): RestockRequest
    {
        $request = RestockRequest::findOrFail($requestId);

        if ($request->status !== 'PENDING') {
            throw new \InvalidArgumentException("Hanya pengajuan berstatus PENDING yang dapat ditolak. Status saat ini: {$request->status}");
        }

        $notesUpdate = $request->notes;
        if ($notes) {
            $notesUpdate = ($notesUpdate ? $notesUpdate.' | ' : '').'Manager Rejection: '.$notes;
        }

        $request->update([
            'status' => 'REJECTED',
            'approved_by_user' => $managerId,
            'notes' => $notesUpdate,
        ]);

        return $request->fresh(['ingredient', 'requestedBy', 'approvedBy']);
    }
}
