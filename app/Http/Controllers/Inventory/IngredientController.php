<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\RestockRequest;
use App\Models\StockIntake;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IngredientController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display the raw inventory portal with KPI cards, raw materials table,
     * and manager restock requisitions deck.
     */
    public function index(Request $request): View
    {
        $query = Ingredient::query();

        // 1. Text Search Filter (name, sku, category)
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // 2. Category Filter
        if ($category = $request->query('category')) {
            if ($category !== 'All') {
                $query->where('category', $category);
            }
        }

        // 3. Status Filter (Healthy, Low Stock, Out of Stock)
        if ($status = $request->query('status')) {
            if ($status === 'Optimal' || $status === 'Healthy') {
                $query->whereColumn('current_stock', '>', 'minimum_threshold');
            } elseif ($status === 'Low Stock') {
                $query->whereColumn('current_stock', '<=', 'minimum_threshold')
                    ->where('current_stock', '>', 0);
            } elseif ($status === 'Critical' || $status === 'Out of Stock') {
                $query->where('current_stock', '<=', 0);
            }
        }

        // 4. Supplier Filter
        if ($supplier = $request->query('supplier')) {
            if ($supplier !== 'All') {
                $query->where('preferred_supplier', $supplier);
            }
        }

        $ingredients = $query->orderBy('item_name', 'asc')->get();

        // Aggregate KPI metrics
        $kpis = $this->inventoryService->getInventoryKPIs();

        // Categories & Suppliers list for filter dropdowns
        $categories = Ingredient::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->values();

        $suppliers = Ingredient::whereNotNull('preferred_supplier')
            ->distinct()
            ->pluck('preferred_supplier')
            ->values();

        // Requisition requests (Pending for manager approval deck)
        $pendingRestocks = RestockRequest::with(['ingredient', 'requestedBy'])
            ->where('status', 'PENDING')
            ->latest()
            ->get();

        // All restock requests for tab/audit review
        $allRestocks = RestockRequest::with(['ingredient', 'requestedBy', 'approvedBy'])
            ->latest()
            ->take(20)
            ->get();

        // Recent Stock Intakes
        $recentIntakes = StockIntake::with(['ingredient', 'receivedByUser', 'restockRequest'])
            ->latest()
            ->take(10)
            ->get();

        // All ingredients for intake dropdown selection
        $allIngredients = Ingredient::orderBy('item_name', 'asc')->get();

        return view('inventory.index', compact(
            'ingredients',
            'kpis',
            'categories',
            'suppliers',
            'pendingRestocks',
            'allRestocks',
            'recentIntakes',
            'allIngredients'
        ));
    }

    /**
     * Record a new physical stock intake from a supplier.
     */
    public function recordIntake(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,ingredient_id',
            'quantity_received' => 'required|numeric|min:0.01',
            'unit' => 'nullable|string|max:20',
            'supplier_name' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:50',
            'storage_location' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
            'restock_request_id' => 'nullable|exists:restock_requests,request_id',
        ], [
            'ingredient_id.required' => 'Pilih bahan baku mentah yang diterima.',
            'quantity_received.required' => 'Masukkan jumlah fisik barang masuk.',
            'quantity_received.min' => 'Jumlah barang masuk minimal 0.01.',
        ]);

        try {
            $intake = $this->inventoryService->recordStockIntake($validated, auth()->id());

            $msg = "Penerimaan stok {$intake->ingredient->item_name} berhasil dicatat: +{$intake->quantity_received} {$intake->unit}. ".
                   "Stok terkini: {$intake->ingredient->current_stock} {$intake->unit}.";

            if ($intake->restock_request_id) {
                $msg .= " (Pengajuan Restock #{$intake->restock_request_id} ditandai COMPLETED)";
            }

            return redirect()->route('inventory.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Gagal mencatat penerimaan stok: '.$e->getMessage());
        }
    }

    /**
     * Quick stock threshold or audit adjustment for Manager.
     */
    public function updateThreshold(Request $request, int $id): RedirectResponse
    {
        // Only manager can adjust thresholds
        if (! auth()->user()->isManager()) {
            abort(403, 'Hanya Cafe Manager yang berwenang memperbarui ambang batas persediaan.');
        }

        $validated = $request->validate([
            'minimum_threshold' => 'required|numeric|min:0',
            'max_capacity' => 'nullable|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        $ingredient = Ingredient::findOrFail($id);
        $ingredient->update($validated);

        return redirect()->route('inventory.index')->with('success', "Parameter stok untuk {$ingredient->item_name} berhasil diperbarui.");
    }
}
