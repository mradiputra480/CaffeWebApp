<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\RestockRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RawInventoryAndRestockApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected User $hrd;

    protected User $manager;

    protected Ingredient $coffeeBeans;

    protected Ingredient $milk;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Users
        $this->staff = User::create([
            'full_name' => 'Alex Rivera',
            'email' => 'staff@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
        ]);

        $this->hrd = User::create([
            'full_name' => 'David Miller',
            'email' => 'hrd@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'HRD',
            'hourly_rate' => 25.00,
        ]);

        $this->manager = User::create([
            'full_name' => 'Elena Vance',
            'email' => 'manager@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'MANAGER',
            'hourly_rate' => 35.00,
        ]);

        // 2. Setup Ingredients
        $this->coffeeBeans = Ingredient::create([
            'item_name' => 'Arabica Specialty Coffee Beans',
            'category' => 'Specialty Beans',
            'sku' => 'RAW-BN-01',
            'current_stock' => 5000.00,
            'max_capacity' => 10000.00,
            'unit' => 'grams',
            'minimum_threshold' => 1000.00,
            'unit_cost' => 0.05,
            'storage_location' => 'Bean Degas Silo Room',
            'preferred_supplier' => 'OriginDirect Coffee Labs',
        ]);

        $this->milk = Ingredient::create([
            'item_name' => 'Fresh Whole Milk',
            'category' => 'Dairy & Alternatives',
            'sku' => 'RAW-DY-01',
            'current_stock' => 2500.00, // Below threshold -> Low Stock
            'max_capacity' => 25000.00,
            'unit' => 'ml',
            'minimum_threshold' => 3000.00,
            'unit_cost' => 0.003,
            'storage_location' => 'Walk-In Cold Vault',
            'preferred_supplier' => 'Clover Valley Dairy',
        ]);
    }

    /**
     * Scenario 1: RBAC Access Control for /inventory
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('inventory.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_hrd_cannot_access_inventory(): void
    {
        $response = $this->actingAs($this->hrd)->get(route('inventory.index'));
        $response->assertStatus(403);
    }

    public function test_staff_and_manager_can_access_inventory_screen(): void
    {
        // 1. Staff Access
        $staffResponse = $this->actingAs($this->staff)->get(route('inventory.index'));
        $staffResponse->assertStatus(200);
        $staffResponse->assertSee('Live Raw Stock Ledger');
        $staffResponse->assertSee('Arabica Specialty Coffee Beans');

        // 2. Manager Access
        $managerResponse = $this->actingAs($this->manager)->get(route('inventory.index'));
        $managerResponse->assertStatus(200);
        $managerResponse->assertSee('Live Raw Stock Ledger');
        $managerResponse->assertSee('Arabica Specialty Coffee Beans');
    }

    /**
     * Scenario 2: UI Elements (KPI Cards, Table Data, Badges)
     */
    public function test_inventory_screen_renders_kpi_cards_and_badges(): void
    {
        $response = $this->actingAs($this->manager)->get(route('inventory.index'));

        $response->assertStatus(200);

        // KPI Cards Check
        $response->assertSee('Total Tracked Raw Items');
        $response->assertSee('Healthy Stock');
        $response->assertSee('Low Stock');
        $response->assertSee('Out of Stock');

        // Table Columns Check
        $response->assertSee('Raw Ingredient');
        $response->assertSee('SKU');
        $response->assertSee('Category');
        $response->assertSee('Current Stock vs Capacity');
        $response->assertSee('Unit');
        $response->assertSee('Threshold');
        $response->assertSee('Status');

        // Items and Badges Check
        $response->assertSee('RAW-BN-01');
        $response->assertSee('Specialty Beans');
        $response->assertSee('Optimal'); // Coffee Beans is > 1000 -> Optimal

        $response->assertSee('RAW-DY-01');
        $response->assertSee('Dairy');
        $response->assertSee('Low Stock'); // Milk is 2500 <= 3000 -> Low Stock
    }

    /**
     * Scenario 3: Record Stock Intake increments physical stock balance
     */
    public function test_record_stock_intake_increments_ingredient_stock(): void
    {
        $initialStock = (float) $this->coffeeBeans->current_stock;
        $qtyReceived = 3000.00;

        $response = $this->actingAs($this->manager)->post(route('inventory.intake'), [
            'ingredient_id' => $this->coffeeBeans->ingredient_id,
            'quantity_received' => $qtyReceived,
            'unit' => 'grams',
            'supplier_name' => 'OriginDirect Coffee Labs',
            'invoice_number' => 'INV-2026-1088',
            'storage_location' => 'Bean Degas Silo Room',
            'batch_number' => 'LOT-ROAST-441',
            'notes' => 'Penerimaan biji kopi Arabica batch 441 dalam karung goni.',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $response->assertSessionHas('success');

        // Verify stock incremented
        $this->coffeeBeans->refresh();
        $this->assertEquals($initialStock + $qtyReceived, (float) $this->coffeeBeans->current_stock);

        // Verify stock_intakes record created
        $this->assertDatabaseHas('stock_intakes', [
            'ingredient_id' => $this->coffeeBeans->ingredient_id,
            'user_id' => $this->manager->user_id,
            'quantity_received' => 3000.00,
            'invoice_number' => 'INV-2026-1088',
            'supplier_name' => 'OriginDirect Coffee Labs',
        ]);
    }

    /**
     * Scenario 4: Stock Intake fulfills an approved restock request to COMPLETED
     */
    public function test_stock_intake_completes_approved_restock_request(): void
    {
        // Create an approved restock request for milk
        $restock = RestockRequest::create([
            'ingredient_id' => $this->milk->ingredient_id,
            'requested_by_user' => $this->staff->user_id,
            'approved_by_user' => $this->manager->user_id,
            'quantity_requested' => 5000.00,
            'status' => 'APPROVED',
            'notes' => 'Restock susu segar untuk operasional.',
        ]);

        // Record stock intake specifying this restock_request_id
        $response = $this->actingAs($this->manager)->post(route('inventory.intake'), [
            'ingredient_id' => $this->milk->ingredient_id,
            'quantity_received' => 5000.00,
            'unit' => 'ml',
            'supplier_name' => 'Clover Valley Dairy',
            'invoice_number' => 'INV-MILK-771',
            'restock_request_id' => $restock->request_id,
        ]);

        $response->assertRedirect(route('inventory.index'));

        // Verify restock status transitioned to COMPLETED
        $restock->refresh();
        $this->assertEquals('COMPLETED', $restock->status);

        // Verify stock increased
        $this->milk->refresh();
        $this->assertEquals(7500.00, (float) $this->milk->current_stock);
    }

    /**
     * Scenario 5: Staff can submit restock requisition with status PENDING
     */
    public function test_staff_can_create_restock_request(): void
    {
        $response = $this->actingAs($this->staff)->post(route('inventory.restock.store'), [
            'ingredient_id' => $this->milk->ingredient_id,
            'quantity_requested' => 6000.00,
            'notes' => 'Susu menipis, mohon segera diorder.',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('restock_requests', [
            'ingredient_id' => $this->milk->ingredient_id,
            'requested_by_user' => $this->staff->user_id,
            'quantity_requested' => 6000.00,
            'status' => 'PENDING',
        ]);
    }

    /**
     * Scenario 6: Manager can review and Approve PENDING restock request
     */
    public function test_manager_can_approve_pending_restock_request(): void
    {
        $req = RestockRequest::create([
            'ingredient_id' => $this->coffeeBeans->ingredient_id,
            'requested_by_user' => $this->staff->user_id,
            'quantity_requested' => 10000.00,
            'status' => 'PENDING',
            'notes' => 'Permintaan biji kopi Arabica.',
        ]);

        $response = $this->actingAs($this->manager)->post(route('inventory.restock.approve', $req->request_id), [
            'notes' => 'Disetujui. PO telah dikirim ke supplier.',
        ]);

        $response->assertRedirect(route('inventory.index'));
        $response->assertSessionHas('success');

        $req->refresh();
        $this->assertEquals('APPROVED', $req->status);
        $this->assertEquals($this->manager->user_id, $req->approved_by_user);
    }

    /**
     * Scenario 7: Manager can review and Reject PENDING restock request
     */
    public function test_manager_can_reject_pending_restock_request(): void
    {
        $req = RestockRequest::create([
            'ingredient_id' => $this->coffeeBeans->ingredient_id,
            'requested_by_user' => $this->staff->user_id,
            'quantity_requested' => 5000.00,
            'status' => 'PENDING',
            'notes' => 'Pengajuan tambahan biji kopi.',
        ]);

        $response = $this->actingAs($this->manager)->post(route('inventory.restock.reject', $req->request_id), [
            'notes' => 'Ditolak, stok pengganti masih mencukupi.',
        ]);

        $response->assertRedirect(route('inventory.index'));

        $req->refresh();
        $this->assertEquals('REJECTED', $req->status);
        $this->assertEquals($this->manager->user_id, $req->approved_by_user);
    }

    /**
     * Scenario 8: Non-Manager cannot Approve or Reject Restock Request
     */
    public function test_staff_cannot_approve_or_reject_restock_request(): void
    {
        $req = RestockRequest::create([
            'ingredient_id' => $this->coffeeBeans->ingredient_id,
            'requested_by_user' => $this->staff->user_id,
            'quantity_requested' => 5000.00,
            'status' => 'PENDING',
        ]);

        // Attempt approve by staff
        $approveResponse = $this->actingAs($this->staff)->post(route('inventory.restock.approve', $req->request_id));
        $approveResponse->assertStatus(403);

        // Attempt reject by staff
        $rejectResponse = $this->actingAs($this->staff)->post(route('inventory.restock.reject', $req->request_id));
        $rejectResponse->assertStatus(403);

        $req->refresh();
        $this->assertEquals('PENDING', $req->status);
    }

    /**
     * Scenario 9: Manager can bulk approve all pending restock requisitions
     */
    public function test_manager_can_bulk_approve_all_pending_requests(): void
    {
        $req1 = RestockRequest::create([
            'ingredient_id' => $this->coffeeBeans->ingredient_id,
            'requested_by_user' => $this->staff->user_id,
            'quantity_requested' => 2000.00,
            'status' => 'PENDING',
        ]);

        $req2 = RestockRequest::create([
            'ingredient_id' => $this->milk->ingredient_id,
            'requested_by_user' => $this->staff->user_id,
            'quantity_requested' => 5000.00,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->manager)->post(route('inventory.restock.approve-all'));
        $response->assertRedirect(route('inventory.index'));
        $response->assertSessionHas('success');

        $req1->refresh();
        $req2->refresh();
        $this->assertEquals('APPROVED', $req1->status);
        $this->assertEquals('APPROVED', $req2->status);
    }

    /**
     * Scenario 10: Stock adjustment audit by Manager
     */
    public function test_manager_can_update_threshold_parameters(): void
    {
        $response = $this->actingAs($this->manager)->put(route('inventory.ingredients.update', $this->coffeeBeans->ingredient_id), [
            'minimum_threshold' => 1500.00,
            'max_capacity' => 12000.00,
            'unit_cost' => 0.055,
        ]);

        $response->assertRedirect(route('inventory.index'));
        $this->coffeeBeans->refresh();

        $this->assertEquals(1500.00, (float) $this->coffeeBeans->minimum_threshold);
        $this->assertEquals(12000.00, (float) $this->coffeeBeans->max_capacity);
    }
}
