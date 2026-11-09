<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExecutiveAnalyticsAndReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;

    protected User $staff;

    protected User $hrd;

    protected Menu $espresso;

    protected Menu $croissant;

    protected Ingredient $beans;

    protected function setUp(): void
    {
        parent::setUp();

        $password = Hash::make('password123');

        $this->manager = User::create([
            'full_name' => 'Elena Vance',
            'email' => 'manager@cafesync.local',
            'password_hash' => $password,
            'role' => 'MANAGER',
            'hourly_rate' => 35.00,
        ]);

        $this->staff = User::create([
            'full_name' => 'Alex Rivera',
            'email' => 'cashier@cafesync.local',
            'password_hash' => $password,
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
        ]);

        $this->hrd = User::create([
            'full_name' => 'David Miller',
            'email' => 'hrd@cafesync.local',
            'password_hash' => $password,
            'role' => 'HRD',
            'hourly_rate' => 25.00,
        ]);

        $this->beans = Ingredient::create([
            'item_name' => 'Arabica Coffee Beans',
            'category' => 'Specialty Beans',
            'sku' => 'RAW-BN-01',
            'current_stock' => 5000.00,
            'max_capacity' => 10000.00,
            'unit' => 'grams',
            'minimum_threshold' => 1000.00,
            'unit_cost' => 0.05,
        ]);

        $this->espresso = Menu::create([
            'item_name' => 'Espresso Supreme',
            'price' => 3.50,
            'category' => 'Espresso',
            'is_available' => true,
        ]);

        $this->croissant = Menu::create([
            'item_name' => 'Artisan Butter Croissant',
            'price' => 4.00,
            'category' => 'Bakery',
            'is_available' => true,
        ]);
    }

    /**
     * Test 1: Manager can view executive analytics dashboard.
     */
    public function test_manager_can_access_executive_dashboard(): void
    {
        $response = $this->actingAs($this->manager)->get('/manager/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Executive Dashboard &amp; Sales Intelligence', false);
        $response->assertSee('Executive Intelligence');
        $response->assertSee("Today's Revenue");
        $response->assertSee('Total Paid Transactions');
        $response->assertSee('Labor Cost Ratio (LCR)');
        $response->assertSee('Hourly Sales Velocity Trends');
    }

    /**
     * Test 2: Staff is forbidden from accessing executive dashboard.
     */
    public function test_staff_cannot_access_executive_dashboard(): void
    {
        $response = $this->actingAs($this->staff)->get('/manager/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test 3: HRD is forbidden from accessing executive dashboard.
     */
    public function test_hrd_cannot_access_executive_dashboard(): void
    {
        $response = $this->actingAs($this->hrd)->get('/manager/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test 4: Unauthenticated guest is redirected to login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/manager/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test 5: Executive dashboard correctly computes today revenue and paid orders.
     */
    public function test_dashboard_computes_accurate_revenue_and_orders(): void
    {
        // Order 1 (Today, Paid, Cash)
        $order1 = Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 15.00,
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'cash_tendered' => 20.00,
            'change_due' => 5.00,
            'created_at' => Carbon::now()->setTime(9, 15),
        ]);

        OrderDetail::create([
            'order_id' => $order1->order_id,
            'menu_id' => $this->espresso->menu_id,
            'quantity' => 2,
            'subtotal' => 7.00,
        ]);

        // Order 2 (Today, Paid, QRIS)
        $order2 = Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 25.50,
            'payment_method' => 'QRIS',
            'payment_status' => 'PAID',
            'qris_reference' => 'QRIS-SYNC-TEST1',
            'created_at' => Carbon::now()->setTime(14, 30),
        ]);

        OrderDetail::create([
            'order_id' => $order2->order_id,
            'menu_id' => $this->croissant->menu_id,
            'quantity' => 5,
            'subtotal' => 20.00,
        ]);

        // Order 3 (Today, Failed, should not be included in revenue)
        Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 50.00,
            'payment_method' => 'QRIS',
            'payment_status' => 'FAILED',
            'created_at' => Carbon::now()->setTime(11, 0),
        ]);

        $response = $this->actingAs($this->manager)->get('/manager/dashboard');

        $response->assertStatus(200);
        // Revenue should be 15.00 + 25.50 = 40.50
        $response->assertSee('$40.50');
        // Total orders should be 2
        $response->assertSee('2');
        $response->assertSee('Register Tickets');
    }

    /**
     * Test 6: Labor cost ratio calculation correctly computes based on shift attendances.
     */
    public function test_dashboard_computes_labor_cost_and_ratio(): void
    {
        // Order today: $200.00
        Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 200.00,
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'cash_tendered' => 200.00,
            'change_due' => 0.00,
            'created_at' => Carbon::now()->setTime(10, 0),
        ]);

        // Staff attendance today: 2 hours * $15.00 = $30.00 labor cost
        // Labor cost ratio = ($30 / $200) * 100 = 15.0% (< 20% Healthy)
        Attendance::create([
            'user_id' => $this->staff->user_id,
            'clock_in' => Carbon::now()->setTime(8, 0),
            'clock_out' => Carbon::now()->setTime(10, 0),
            'total_hours' => 2.00,
            'overtime_hours' => 0.00,
            'status' => 'ON_TIME',
        ]);

        $response = $this->actingAs($this->manager)->get('/manager/dashboard');

        $response->assertStatus(200);
        $response->assertSee('15.0%');
        $response->assertSee('Healthy');
        $response->assertSee('$30.00');
    }

    /**
     * Test 7: Hourly sales velocity tracks morning & afternoon surge periods.
     */
    public function test_hourly_sales_velocity_captures_morning_and_afternoon_surges(): void
    {
        // Morning Surge Order (09:00 AM)
        Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 45.00,
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'created_at' => Carbon::now()->setTime(9, 0),
        ]);

        // Afternoon Surge Order (14:30 PM)
        Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 60.00,
            'payment_method' => 'QRIS',
            'payment_status' => 'PAID',
            'created_at' => Carbon::now()->setTime(14, 30),
        ]);

        $response = $this->actingAs($this->manager)->get('/manager/dashboard');

        $response->assertStatus(200);
        $response->assertSee('08:00 AM – 10:30 AM');
        $response->assertSee('Morning Surge');
        $response->assertSee('$45.00');
        $response->assertSee('02:00 PM – 03:30 PM');
        $response->assertSee('Afternoon Rush');
        $response->assertSee('$60.00');
    }

    /**
     * Test 8: Custom date filtering works as expected.
     */
    public function test_dashboard_supports_custom_date_filtering(): void
    {
        // Past order 5 days ago
        Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 120.00,
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'created_at' => Carbon::now()->subDays(5)->setTime(12, 0),
        ]);

        $startDate = Carbon::now()->subDays(7)->format('Y-m-d');
        $endDate = Carbon::now()->subDays(3)->format('Y-m-d');

        $response = $this->actingAs($this->manager)->get("/manager/dashboard?period=custom&custom_start={$startDate}&custom_end={$endDate}");

        $response->assertStatus(200);
        $response->assertSee('$120.00');
        $response->assertSee('Showing custom date range:');
    }

    /**
     * Test 9: Manager can export sales audit to CSV.
     */
    public function test_manager_can_export_sales_audit_to_csv(): void
    {
        $order = Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 33.00,
            'payment_method' => 'QRIS',
            'payment_status' => 'PAID',
            'qris_reference' => 'QRIS-AUDIT-999',
            'created_at' => Carbon::now()->setTime(10, 15),
        ]);

        OrderDetail::create([
            'order_id' => $order->order_id,
            'menu_id' => $this->espresso->menu_id,
            'quantity' => 2,
            'subtotal' => 7.00,
        ]);

        $response = $this->actingAs($this->manager)->get('/manager/export-sales?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertTrue($response->headers->has('Content-Disposition'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        $this->assertStringContainsString('No. Order', $content);
        $this->assertStringContainsString('Tanggal & Waktu Transaksi', $content);
        $this->assertStringContainsString('Metode Pembayaran', $content);
        $this->assertStringContainsString('QRIS-AUDIT-999', $content);
        $this->assertStringContainsString('33.00', $content);
        $this->assertStringContainsString('TOTAL AUDIT', $content);
    }

    /**
     * Test 10: Manager can export sales audit to PDF.
     */
    public function test_manager_can_export_sales_audit_to_pdf(): void
    {
        $order = Order::create([
            'user_id' => $this->staff->user_id,
            'total_amount' => 55.00,
            'payment_method' => 'CASH',
            'payment_status' => 'PAID',
            'cash_tendered' => 60.00,
            'change_due' => 5.00,
            'created_at' => Carbon::now()->setTime(11, 0),
        ]);

        OrderDetail::create([
            'order_id' => $order->order_id,
            'menu_id' => $this->croissant->menu_id,
            'quantity' => 3,
            'subtotal' => 12.00,
        ]);

        $response = $this->actingAs($this->manager)->get('/manager/export-sales?format=pdf');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertTrue($response->headers->has('Content-Disposition'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        // PDF magic bytes %PDF-
        $this->assertStringStartsWith('%PDF-', $content);
    }

    /**
     * Test 11: Non-managers are forbidden from exporting sales audit.
     */
    public function test_non_manager_cannot_export_sales_audit(): void
    {
        // Staff attempt
        $staffResponse = $this->actingAs($this->staff)->get('/manager/export-sales?format=csv');
        $staffResponse->assertStatus(403);

        // HRD attempt
        $hrdResponse = $this->actingAs($this->hrd)->get('/manager/export-sales?format=pdf');
        $hrdResponse->assertStatus(403);
    }
}
