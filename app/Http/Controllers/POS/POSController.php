<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class POSController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display POS Web Terminal for Staff & Manager.
     */
    public function index(): View
    {
        $user = Auth::user();
        $menuItems = Menu::with('recipes.ingredient')->where('is_available', true)->get();
        $activeShift = $user->activeAttendance();
        $todaySchedule = $user->todaySchedule();

        // Calculate max servings for each menu item
        $menuData = $menuItems->map(function ($menu) {
            return [
                'menu_id' => $menu->menu_id,
                'item_name' => $menu->item_name,
                'price' => (float) $menu->price,
                'category' => $menu->category,
                'image_url' => $menu->image_url,
                'is_available' => $menu->is_available,
                'max_servings' => $this->inventoryService->calculateMaxServings($menu),
                'recipes_count' => $menu->recipes->count(),
            ];
        });

        // Get unique categories for filter chips
        $categories = $menuItems->pluck('category')->unique()->sort()->values();

        // Get the next order number
        $nextOrderNumber = (Order::max('order_id') ?? 0) + 1;

        return view('pos.index', [
            'user' => $user,
            'menuItems' => $menuItems,
            'menuData' => $menuData,
            'categories' => $categories,
            'activeShift' => $activeShift,
            'todaySchedule' => $todaySchedule,
            'nextOrderNumber' => $nextOrderNumber,
        ]);
    }

    /**
     * API: Check stock availability for a menu item.
     */
    public function checkStock(Request $request): JsonResponse
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:menu,menu_id',
            'quantity' => 'required|integer|min:1',
        ]);

        $menu = Menu::with('recipes.ingredient')->findOrFail($request->menu_id);
        $result = $this->inventoryService->checkStockAvailability($menu, $request->quantity);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API: Process order checkout (Cash & QRIS).
     */
    public function processOrder(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer|exists:menu,menu_id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:CASH,QRIS',
            'cash_tendered' => 'nullable|numeric|min:0',
            'qris_reference' => 'nullable|string',
        ]);

        $user = Auth::user();
        $cartItems = $request->items;
        $paymentMethod = $request->payment_method;

        // Pre-validate full cart stock availability (aggregated check)
        $stockCheck = $this->inventoryService->checkCartStockAvailability($cartItems);
        if (! $stockCheck['available']) {
            return response()->json([
                'success' => false,
                'message' => 'Stok bahan baku tidak mencukupi untuk pesanan ini.',
                'shortages' => $stockCheck['shortages'],
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($user, $cartItems, $paymentMethod, $request) {
                $subtotal = 0;
                $orderDetails = [];

                // Calculate subtotal and prepare order details
                foreach ($cartItems as $item) {
                    $menu = Menu::findOrFail($item['menu_id']);
                    $itemSubtotal = $menu->price * $item['quantity'];
                    $subtotal += $itemSubtotal;

                    $orderDetails[] = [
                        'menu_id' => $menu->menu_id,
                        'quantity' => $item['quantity'],
                        'subtotal' => $itemSubtotal,
                        'item_name' => $menu->item_name,
                        'unit_price' => $menu->price,
                    ];
                }

                // Calculate tax (10%) and grand total
                $taxAmount = round($subtotal * 0.10, 2);
                $grandTotal = round($subtotal + $taxAmount, 2);

                // Validate cash payment
                $cashTendered = null;
                $changeDue = null;
                $qrisReference = null;

                if ($paymentMethod === 'CASH') {
                    $cashTendered = (float) $request->cash_tendered;
                    if ($cashTendered < $grandTotal) {
                        throw new \Exception("Uang diterima (\${$cashTendered}) kurang dari total tagihan (\${$grandTotal}).");
                    }
                    $changeDue = round($cashTendered - $grandTotal, 2);
                } elseif ($paymentMethod === 'QRIS') {
                    $qrisReference = $request->qris_reference;
                    if (empty($qrisReference)) {
                        throw new \Exception('Referensi transaksi QRIS diperlukan.');
                    }
                }

                // Create order header
                $order = Order::create([
                    'user_id' => $user->user_id,
                    'total_amount' => $grandTotal,
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'PAID',
                    'cash_tendered' => $cashTendered,
                    'change_due' => $changeDue,
                    'qris_reference' => $qrisReference,
                ]);

                // Create order detail lines
                foreach ($orderDetails as $detail) {
                    OrderDetail::create([
                        'order_id' => $order->order_id,
                        'menu_id' => $detail['menu_id'],
                        'quantity' => $detail['quantity'],
                        'subtotal' => $detail['subtotal'],
                    ]);
                }

                // Atomically deduct ingredient stocks with lockForUpdate
                $this->inventoryService->deductStockForOrder($cartItems, $user->user_id);

                return $order;
            });

            // Reload with relations
            $order->load('orderDetails.menu', 'user');

            // Calculate display values
            $subtotal = $order->orderDetails->sum('subtotal');
            $taxAmount = round($subtotal * 0.10, 2);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses!',
                'data' => [
                    'order_id' => $order->order_id,
                    'total_amount' => (float) $order->total_amount,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'cash_tendered' => $order->cash_tendered ? (float) $order->cash_tendered : null,
                    'change_due' => $order->change_due ? (float) $order->change_due : null,
                    'qris_reference' => $order->qris_reference,
                    'created_at' => $order->created_at,
                    'receipt_url' => route('pos.receipt', $order->order_id),
                    'updated_menu' => $this->getFormattedMenuData(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API: Get available menu items with live calculated max servings.
     */
    public function getAvailableMenu(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getFormattedMenuData(),
        ]);
    }

    /**
     * Helper to get formatted menu data with max servings.
     */
    protected function getFormattedMenuData()
    {
        $menuItems = Menu::with('recipes.ingredient')->where('is_available', true)->get();

        return $menuItems->map(function ($menu) {
            return [
                'menu_id' => $menu->menu_id,
                'item_name' => $menu->item_name,
                'price' => (float) $menu->price,
                'category' => $menu->category,
                'image_url' => $menu->image_url,
                'is_available' => $menu->is_available,
                'max_servings' => $this->inventoryService->calculateMaxServings($menu),
                'recipes_count' => $menu->recipes->count(),
            ];
        });
    }

    /**
     * Display printable thermal receipt for an order.
     */
    public function receipt(int $id): View
    {
        $order = Order::with(['orderDetails.menu', 'user'])->findOrFail($id);

        $subtotal = $order->orderDetails->sum('subtotal');
        $taxAmount = round($subtotal * 0.10, 2);

        return view('pos.receipt', [
            'order' => $order,
            'subtotal' => $subtotal,
            'taxAmount' => $taxAmount,
        ]);
    }
}
