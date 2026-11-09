<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Recipe;
use App\Models\RestockRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class POSTerminalAndStockDeductionTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected User $manager;

    protected User $hrd;

    protected Ingredient $coffeeBeans;

    protected Ingredient $wholeMilk;

    protected Ingredient $paperCup;

    protected Menu $espresso;

    protected Menu $flatWhite;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Users
        $this->staff = User::create([
            'full_name' => 'Alex Rivera',
            'email' => 'cashier@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
        ]);

        $this->manager = User::create([
            'full_name' => 'Elena Vance',
            'email' => 'manager@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'MANAGER',
            'hourly_rate' => 35.00,
        ]);

        $this->hrd = User::create([
            'full_name' => 'David Miller',
            'email' => 'hrd@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'HRD',
            'hourly_rate' => 25.00,
        ]);

        // 2. Setup Ingredients
        $this->coffeeBeans = Ingredient::create([
            'item_name' => 'Arabica Coffee Beans',
            'current_stock' => 1000.00,
            'unit' => 'grams',
            'minimum_threshold' => 200.00,
            'unit_cost' => 0.05,
        ]);

        $this->wholeMilk = Ingredient::create([
            'item_name' => 'Fresh Whole Milk',
            'current_stock' => 5000.00,
            'unit' => 'ml',
            'minimum_threshold' => 1000.00,
            'unit_cost' => 0.003,
        ]);

        $this->paperCup = Ingredient::create([
            'item_name' => 'Takeaway Paper Cup 8oz',
            'current_stock' => 100.00,
            'unit' => 'pcs',
            'minimum_threshold' => 20.00,
            'unit_cost' => 0.15,
        ]);

        // 3. Setup Menu
        $this->espresso = Menu::create([
            'item_name' => 'Espresso Supreme',
            'price' => 3.50,
            'category' => 'Espresso',
            'image_url' => 'https://example.com/espresso.jpg',
            'is_available' => true,
        ]);

        $this->flatWhite = Menu::create([
            'item_name' => 'Classic Flat White',
            'price' => 4.50,
            'category' => 'Espresso',
            'image_url' => 'https://example.com/flatwhite.jpg',
            'is_available' => true,
        ]);

        // 4. Setup Recipes
        // Espresso: 18g Beans + 1 Paper Cup
        Recipe::create([
            'menu_id' => $this->espresso->menu_id,
            'ingredient_id' => $this->coffeeBeans->ingredient_id,
            'amount_required' => 18.00,
        ]);
        Recipe::create([
            'menu_id' => $this->espresso->menu_id,
            'ingredient_id' => $this->paperCup->ingredient_id,
            'amount_required' => 1.00,
        ]);

        // Flat White: 18g Beans + 180ml Milk + 1 Paper Cup
        Recipe::create([
            'menu_id' => $this->flatWhite->menu_id,
            'ingredient_id' => $this->coffeeBeans->ingredient_id,
            'amount_required' => 18.00,
        ]);
        Recipe::create([
            'menu_id' => $this->flatWhite->menu_id,
            'ingredient_id' => $this->wholeMilk->ingredient_id,
            'amount_required' => 180.00,
        ]);
        Recipe::create([
            'menu_id' => $this->flatWhite->menu_id,
            'ingredient_id' => $this->paperCup->ingredient_id,
            'amount_required' => 1.00,
        ]);
    }

    /**
     * Test 1: POS Terminal Screen Access by RBAC Role
     */
    public function test_pos_terminal_screen_access_rbac(): void
    {
        // STAFF can access POS
        $responseStaff = $this->actingAs($this->staff)->get(route('pos.index'));
        $responseStaff->assertStatus(200);
        $responseStaff->assertSee('Terminal POS Kasir #01');
        $responseStaff->assertSee('Espresso Supreme');
        $responseStaff->assertSee('Classic Flat White');

        // MANAGER can access POS
        $responseManager = $this->actingAs($this->manager)->get(route('pos.index'));
        $responseManager->assertStatus(200);

        // HRD cannot access POS (403)
        $responseHrd = $this->actingAs($this->hrd)->get(route('pos.index'));
        $responseHrd->assertStatus(403);
    }

    /**
     * Test 2: Early Stock Check API (Item Availability & Deficit Calculation)
     */
    public function test_early_stock_check_api_verifies_availability_and_deficits(): void
    {
        // Limited stock scenario: 30g beans
        $this->coffeeBeans->update(['current_stock' => 30.00]);

        // 1 serving Espresso requires 18g -> available
        $responseAvailable = $this->actingAs($this->staff)->postJson(route('api.pos.check-stock'), [
            'menu_id' => $this->espresso->menu_id,
            'quantity' => 1,
        ]);
        $responseAvailable->assertStatus(200);
        $responseAvailable->assertJson([
            'success' => true,
            'data' => [
                'available' => true,
                'shortages' => [],
            ],
        ]);

        // 2 servings Espresso require 36g -> shortages detected!
        $responseInsufficient = $this->actingAs($this->staff)->postJson(route('api.pos.check-stock'), [
            'menu_id' => $this->espresso->menu_id,
            'quantity' => 2,
        ]);
        $responseInsufficient->assertStatus(200);
        $responseInsufficient->assertJson([
            'success' => true,
            'data' => [
                'available' => false,
            ],
        ]);
        $this->assertCount(1, $responseInsufficient->json('data.shortages'));
        $this->assertEquals('Arabica Coffee Beans', $responseInsufficient->json('data.shortages.0.item_name'));
        $this->assertEquals(6.00, $responseInsufficient->json('data.shortages.0.deficit'));
    }

    /**
     * Test 3: Cash Payment flow with exact cash_tendered and change_due calculation
     */
    public function test_cash_payment_processes_order_and_calculates_change(): void
    {
        // 2x Espresso @ $3.50 = $7.00. Subtotal: $7.00. Tax 10%: $0.70. Grand Total: $7.70
        $response = $this->actingAs($this->staff)->postJson(route('api.pos.orders'), [
            'items' => [
                ['menu_id' => $this->espresso->menu_id, 'quantity' => 2],
            ],
            'payment_method' => 'CASH',
            'cash_tendered' => 10.00,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'payment_method' => 'CASH',
                'payment_status' => 'PAID',
                'total_amount' => 7.70,
                'cash_tendered' => 10.00,
                'change_due' => 2.30,
            ],
        ]);

        // Verify order saved in database
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->staff->user_id,
            'total_amount' => 7.70,
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'cash_tendered' => 10.00,
            'change_due' => 2.30,
        ]);

        // Verify order_details saved
        $orderId = $response->json('data.order_id');
        $this->assertDatabaseHas('order_details', [
            'order_id' => $orderId,
            'menu_id' => $this->espresso->menu_id,
            'quantity' => 2,
            'subtotal' => 7.00,
        ]);
    }

    /**
     * Test 4: Cash Payment rejected when tendered amount is less than grand total
     */
    public function test_cash_payment_rejected_if_tendered_is_insufficient(): void
    {
        // 2x Espresso Grand Total: $7.70. Tendered only $5.00
        $response = $this->actingAs($this->staff)->postJson(route('api.pos.orders'), [
            'items' => [
                ['menu_id' => $this->espresso->menu_id, 'quantity' => 2],
            ],
            'payment_method' => 'CASH',
            'cash_tendered' => 5.00,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);

        // Verify NO order created
        $this->assertEquals(0, Order::count());
    }

    /**
     * Test 5: Dynamic QRIS Generation, Polling, and Payment Simulation
     */
    public function test_dynamic_qris_lifecycle_generation_polling_and_confirmation(): void
    {
        // 1. Generate QRIS dynamic payload
        $genResponse = $this->actingAs($this->staff)->postJson(route('api.qris.generate'), [
            'amount' => 15.40,
            'order_reference' => 'TEST-ORD-001',
        ]);

        $genResponse->assertStatus(200);
        $genResponse->assertJsonStructure([
            'success',
            'data' => [
                'transaction_id',
                'qr_payload',
                'qr_image_url',
                'expires_at',
            ],
        ]);

        $transId = $genResponse->json('data.transaction_id');

        // 2. Poll QRIS status (Initially PENDING)
        $pollResponse1 = $this->actingAs($this->staff)->getJson(route('api.qris.status', $transId));
        $pollResponse1->assertStatus(200);
        $this->assertEquals('PENDING', $pollResponse1->json('data.status'));

        // 3. Simulate customer scan & pay success
        $simResponse = $this->actingAs($this->staff)->postJson(route('api.qris.simulate'), [
            'transaction_id' => $transId,
        ]);
        $simResponse->assertStatus(200);
        $this->assertTrue($simResponse->json('success'));

        // 4. Poll again -> status should now be SUCCESS
        $pollResponse2 = $this->actingAs($this->staff)->getJson(route('api.qris.status', $transId));
        $pollResponse2->assertStatus(200);
        $this->assertEquals('SUCCESS', $pollResponse2->json('data.status'));

        // 5. Finalize order with QRIS payment method
        $orderResponse = $this->actingAs($this->staff)->postJson(route('api.pos.orders'), [
            'items' => [
                ['menu_id' => $this->flatWhite->menu_id, 'quantity' => 1],
            ],
            'payment_method' => 'QRIS',
            'qris_reference' => $transId,
        ]);

        $orderResponse->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'payment_method' => 'QRIS',
            'payment_status' => 'PAID',
            'qris_reference' => $transId,
        ]);
    }

    /**
     * Test 6: Atomic DB Transaction - Ingredients stock deducted according to recipe compositions
     */
    public function test_atomic_stock_deduction_for_recipes(): void
    {
        // Initial stock:
        // Beans: 1000g, Milk: 5000ml, Paper Cup: 100pcs
        // Order: 2x Flat White (each requires 18g beans, 180ml milk, 1 paper cup)
        // Expected consumption: 36g beans, 360ml milk, 2 paper cups

        $response = $this->actingAs($this->staff)->postJson(route('api.pos.orders'), [
            'items' => [
                ['menu_id' => $this->flatWhite->menu_id, 'quantity' => 2],
            ],
            'payment_method' => 'CASH',
            'cash_tendered' => 15.00,
        ]);

        $response->assertStatus(200);

        // Check deducted ingredient stocks
        $this->assertEquals(964.00, $this->coffeeBeans->fresh()->current_stock);
        $this->assertEquals(4640.00, $this->wholeMilk->fresh()->current_stock);
        $this->assertEquals(98.00, $this->paperCup->fresh()->current_stock);
    }

    /**
     * Test 7: Auto-creates Restock Request draft when stock drops <= minimum_threshold
     */
    public function test_auto_creates_restock_request_when_stock_falls_below_threshold(): void
    {
        // Set paper cup stock to 21 pcs (threshold is 20 pcs)
        $this->paperCup->update(['current_stock' => 21.00]);

        // Order 2 Espressos -> consumes 2 cups -> leaves 19 pcs (19 <= 20 threshold)
        $response = $this->actingAs($this->staff)->postJson(route('api.pos.orders'), [
            'items' => [
                ['menu_id' => $this->espresso->menu_id, 'quantity' => 2],
            ],
            'payment_method' => 'CASH',
            'cash_tendered' => 10.00,
        ]);

        $response->assertStatus(200);

        // Verify stock is now 19 pcs
        $this->assertEquals(19.00, $this->paperCup->fresh()->current_stock);

        // Verify automatic RestockRequest was created
        $this->assertDatabaseHas('restock_requests', [
            'ingredient_id' => $this->paperCup->ingredient_id,
            'status' => 'PENDING',
            'requested_by_user' => $this->staff->user_id,
            'quantity_requested' => 40.00, // minimum_threshold * 2 (20 * 2)
        ]);
    }

    /**
     * Test 8: ACID Transaction Rollback - Order fails and no stocks are deducted if shortage occurs
     */
    public function test_acid_transaction_rolls_back_if_any_ingredient_insufficient(): void
    {
        // Initial stocks:
        // Beans: 1000g, Milk: only 100ml (Flat White needs 180ml per cup)
        $this->wholeMilk->update(['current_stock' => 100.00]);

        $response = $this->actingAs($this->staff)->postJson(route('api.pos.orders'), [
            'items' => [
                ['menu_id' => $this->flatWhite->menu_id, 'quantity' => 1],
            ],
            'payment_method' => 'CASH',
            'cash_tendered' => 10.00,
        ]);

        $response->assertStatus(422);

        // Verify stocks remain untouched (ACID rollback)
        $this->assertEquals(1000.00, $this->coffeeBeans->fresh()->current_stock);
        $this->assertEquals(100.00, $this->wholeMilk->fresh()->current_stock);
        $this->assertEquals(100.00, $this->paperCup->fresh()->current_stock);

        // Verify no order was saved
        $this->assertEquals(0, Order::count());
    }

    /**
     * Test 9: Printable Thermal Receipt View Rendering
     */
    public function test_thermal_receipt_view_renders_order_details_and_tax(): void
    {
        // Create an order
        $order = Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 7.70,
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'cash_tendered' => 10.00,
            'change_due' => 2.30,
        ]);

        OrderDetail::create([
            'order_id' => $order->order_id,
            'menu_id' => $this->espresso->menu_id,
            'quantity' => 2,
            'subtotal' => 7.00,
        ]);

        $response = $this->actingAs($this->staff)->get(route('pos.receipt', $order->order_id));
        $response->assertStatus(200);
        $response->assertSee('CafeSync');
        $response->assertSee('Espresso Supreme');
        $response->assertSee('Subtotal');
        $response->assertSee('Pajak (10%)');
        $response->assertSee('GRAND TOTAL');
        $response->assertSee('$7.70');
        $response->assertSee('Tunai Diterima');
        $response->assertSee('$10.00');
        $response->assertSee('Kembalian');
        $response->assertSee('$2.30');
    }
}
