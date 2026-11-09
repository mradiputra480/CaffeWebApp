<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\HrAlert;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Recipe;
use App\Models\RestockRequest;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // 1. Users Seeder
        $alex = User::create([
            'full_name' => 'Alex Rivera',
            'email' => 'cashier@cafesync.local',
            'password_hash' => $defaultPassword,
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        $sarah = User::create([
            'full_name' => 'Sarah Jenkins',
            'email' => 'barista@cafesync.local',
            'password_hash' => $defaultPassword,
            'role' => 'STAFF',
            'hourly_rate' => 16.50,
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        $david = User::create([
            'full_name' => 'David Miller',
            'email' => 'hrd@cafesync.local',
            'password_hash' => $defaultPassword,
            'role' => 'HRD',
            'hourly_rate' => 25.00,
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        $elena = User::create([
            'full_name' => 'Elena Vance',
            'email' => 'manager@cafesync.local',
            'password_hash' => $defaultPassword,
            'role' => 'MANAGER',
            'hourly_rate' => 35.00,
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        // 2. Ingredients Seeder
        $beans = Ingredient::create([
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

        $milk = Ingredient::create([
            'item_name' => 'Fresh Whole Milk',
            'category' => 'Dairy & Alternatives',
            'sku' => 'RAW-DY-01',
            'current_stock' => 15000.00,
            'max_capacity' => 25000.00,
            'unit' => 'ml',
            'minimum_threshold' => 3000.00,
            'unit_cost' => 0.003,
            'storage_location' => 'Walk-In Cold Vault',
            'preferred_supplier' => 'Clover Valley Dairy',
        ]);

        $oatMilk = Ingredient::create([
            'item_name' => 'Oat Milk Barista Edition',
            'category' => 'Dairy & Alternatives',
            'sku' => 'RAW-DY-04',
            'current_stock' => 8000.00,
            'max_capacity' => 15000.00,
            'unit' => 'ml',
            'minimum_threshold' => 2000.00,
            'unit_cost' => 0.006,
            'storage_location' => 'Walk-In Cold Vault',
            'preferred_supplier' => 'Oatly Distribution',
        ]);

        $matcha = Ingredient::create([
            'item_name' => 'Artisanal Matcha Powder',
            'category' => 'Botanicals & Powders',
            'sku' => 'RAW-BT-01',
            'current_stock' => 1500.00,
            'max_capacity' => 3000.00,
            'unit' => 'grams',
            'minimum_threshold' => 300.00,
            'unit_cost' => 0.12,
            'storage_location' => 'Main Bar Secondary Caddy',
            'preferred_supplier' => 'Kyoto Craft Botanicals',
        ]);

        $croissantDough = Ingredient::create([
            'item_name' => 'French Butter Croissant Dough',
            'category' => 'Bakery & Pastry',
            'sku' => 'RAW-BK-01',
            'current_stock' => 45.00,
            'max_capacity' => 100.00,
            'unit' => 'pcs',
            'minimum_threshold' => 10.00,
            'unit_cost' => 1.20,
            'storage_location' => 'Walk-In Cold Vault',
            'preferred_supplier' => 'La Parisian Bakery Supplies',
        ]);

        $vanillaSyrup = Ingredient::create([
            'item_name' => 'Vanilla Bean Syrup',
            'category' => 'Syrups & Botanicals',
            'sku' => 'RAW-SY-02',
            'current_stock' => 2500.00,
            'max_capacity' => 5000.00,
            'unit' => 'ml',
            'minimum_threshold' => 500.00,
            'unit_cost' => 0.02,
            'storage_location' => 'Main Bar Secondary Caddy',
            'preferred_supplier' => 'Maison Monin USA',
        ]);

        $paperCup = Ingredient::create([
            'item_name' => 'Takeaway Paper Cup 8oz',
            'category' => 'Packaging & Ware',
            'sku' => 'RAW-PK-08',
            'current_stock' => 200.00,
            'max_capacity' => 500.00,
            'unit' => 'pcs',
            'minimum_threshold' => 50.00,
            'unit_cost' => 0.15,
            'storage_location' => 'Dry Goods Mezzanine',
            'preferred_supplier' => 'GreenPack Corp',
        ]);

        $coldCup = Ingredient::create([
            'item_name' => 'Takeaway Cold Cup 16oz',
            'category' => 'Packaging & Ware',
            'sku' => 'RAW-PK-16',
            'current_stock' => 180.00,
            'max_capacity' => 500.00,
            'unit' => 'pcs',
            'minimum_threshold' => 50.00,
            'unit_cost' => 0.18,
            'storage_location' => 'Dry Goods Mezzanine',
            'preferred_supplier' => 'GreenPack Corp',
        ]);

        // 3. Menu Seeder
        $espresso = Menu::create([
            'item_name' => 'Espresso Supreme',
            'price' => 3.50,
            'category' => 'Espresso',
            'image_url' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=500&auto=format&fit=crop&q=60',
            'is_available' => true,
        ]);

        $flatWhite = Menu::create([
            'item_name' => 'Classic Flat White',
            'price' => 4.50,
            'category' => 'Espresso',
            'image_url' => 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?w=500&auto=format&fit=crop&q=60',
            'is_available' => true,
        ]);

        $icedMatcha = Menu::create([
            'item_name' => 'Iced Oat Matcha Latte',
            'price' => 5.50,
            'category' => 'Cold Brew',
            'image_url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?w=500&auto=format&fit=crop&q=60',
            'is_available' => true,
        ]);

        $croissant = Menu::create([
            'item_name' => 'Artisan Butter Croissant',
            'price' => 4.00,
            'category' => 'Bakery',
            'image_url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=500&auto=format&fit=crop&q=60',
            'is_available' => true,
        ]);

        $nitroColdBrew = Menu::create([
            'item_name' => 'Nitro Cold Brew',
            'price' => 4.80,
            'category' => 'Cold Brew',
            'image_url' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?w=500&auto=format&fit=crop&q=60',
            'is_available' => true,
        ]);

        // 4. Recipes Mapping Seeder
        // Espresso Supreme: 18g Arabica + 1 Paper Cup 8oz
        Recipe::create(['menu_id' => $espresso->menu_id, 'ingredient_id' => $beans->ingredient_id, 'amount_required' => 18.00]);
        Recipe::create(['menu_id' => $espresso->menu_id, 'ingredient_id' => $paperCup->ingredient_id, 'amount_required' => 1.00]);

        // Classic Flat White: 18g Arabica + 180ml Whole Milk + 1 Paper Cup 8oz
        Recipe::create(['menu_id' => $flatWhite->menu_id, 'ingredient_id' => $beans->ingredient_id, 'amount_required' => 18.00]);
        Recipe::create(['menu_id' => $flatWhite->menu_id, 'ingredient_id' => $milk->ingredient_id, 'amount_required' => 180.00]);
        Recipe::create(['menu_id' => $flatWhite->menu_id, 'ingredient_id' => $paperCup->ingredient_id, 'amount_required' => 1.00]);

        // Iced Oat Matcha Latte: 12g Matcha + 200ml Oat Milk + 1 Cold Cup 16oz
        Recipe::create(['menu_id' => $icedMatcha->menu_id, 'ingredient_id' => $matcha->ingredient_id, 'amount_required' => 12.00]);
        Recipe::create(['menu_id' => $icedMatcha->menu_id, 'ingredient_id' => $oatMilk->ingredient_id, 'amount_required' => 200.00]);
        Recipe::create(['menu_id' => $icedMatcha->menu_id, 'ingredient_id' => $coldCup->ingredient_id, 'amount_required' => 1.00]);

        // Artisan Butter Croissant: 1 pcs Croissant Dough
        Recipe::create(['menu_id' => $croissant->menu_id, 'ingredient_id' => $croissantDough->ingredient_id, 'amount_required' => 1.00]);

        // Nitro Cold Brew: 25g Arabica + 1 Cold Cup 16oz
        Recipe::create(['menu_id' => $nitroColdBrew->menu_id, 'ingredient_id' => $beans->ingredient_id, 'amount_required' => 25.00]);
        Recipe::create(['menu_id' => $nitroColdBrew->menu_id, 'ingredient_id' => $coldCup->ingredient_id, 'amount_required' => 1.00]);

        // 5. Shift Schedules Seeder
        ShiftSchedule::create([
            'user_id' => $alex->user_id,
            'shift_name' => 'Morning Cashier Shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'day_of_week' => 'ALL',
            'notes' => 'Shift pagi kasir utama counter',
        ]);

        ShiftSchedule::create([
            'user_id' => $sarah->user_id,
            'shift_name' => 'Morning Barista Shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'day_of_week' => 'ALL',
            'notes' => 'Shift pagi head barista bar espresso',
        ]);

        ShiftSchedule::create([
            'user_id' => $david->user_id,
            'shift_name' => 'Afternoon Operations Shift',
            'start_time' => '12:00:00',
            'end_time' => '20:00:00',
            'day_of_week' => 'ALL',
            'notes' => 'Shift siang HR & floor coordinator',
        ]);

        ShiftSchedule::create([
            'user_id' => $elena->user_id,
            'shift_name' => 'General Manager Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'day_of_week' => 'ALL',
            'notes' => 'Supervisi manajemen operasional kafe',
        ]);

        // 6. Initial Attendance Seeder (Sarah & Alex active today)
        $attSarah = Attendance::create([
            'user_id' => $sarah->user_id,
            'clock_in' => Carbon::now()->subHours(3)->subMinutes(42),
            'clock_out' => null,
            'total_hours' => 3.70,
            'overtime_hours' => 0.00,
            'status' => 'ON_TIME',
            'manager_override_notes' => null,
        ]);

        $attAlex = Attendance::create([
            'user_id' => $alex->user_id,
            'clock_in' => Carbon::now()->subHours(2)->subMinutes(15),
            'clock_out' => null,
            'total_hours' => 2.25,
            'overtime_hours' => 0.00,
            'status' => 'ON_TIME',
            'manager_override_notes' => null,
        ]);

        // Past Completed Attendances for Monthly Payroll Calculation
        Attendance::create([
            'user_id' => $alex->user_id,
            'clock_in' => Carbon::now()->subDays(1)->setTime(8, 0),
            'clock_out' => Carbon::now()->subDays(1)->setTime(16, 30),
            'total_hours' => 8.50,
            'overtime_hours' => 0.50,
            'status' => 'OVERTIME',
            'manager_override_notes' => null,
        ]);

        Attendance::create([
            'user_id' => $alex->user_id,
            'clock_in' => Carbon::now()->subDays(2)->setTime(8, 25), // Late
            'clock_out' => Carbon::now()->subDays(2)->setTime(16, 0),
            'total_hours' => 7.58,
            'overtime_hours' => 0.00,
            'status' => 'LATE',
            'manager_override_notes' => 'Terlambat 25 menit dari jadwal shift (Morning Cashier Shift)',
        ]);

        Attendance::create([
            'user_id' => $sarah->user_id,
            'clock_in' => Carbon::now()->subDays(1)->setTime(8, 0),
            'clock_out' => Carbon::now()->subDays(1)->setTime(17, 30),
            'total_hours' => 9.50,
            'overtime_hours' => 1.50,
            'status' => 'OVERTIME',
            'manager_override_notes' => null,
        ]);

        Attendance::create([
            'user_id' => $david->user_id,
            'clock_in' => Carbon::now()->subDays(1)->setTime(12, 0),
            'clock_out' => Carbon::now()->subDays(1)->setTime(20, 0),
            'total_hours' => 8.00,
            'overtime_hours' => 0.00,
            'status' => 'ON_TIME',
            'manager_override_notes' => null,
        ]);

        // Sample HR Alert for Late Clock-In
        HrAlert::create([
            'user_id' => $alex->user_id,
            'attendance_id' => null,
            'type' => 'LATE_CLOCK_IN',
            'title' => 'Alert Keterlambatan: Alex Rivera',
            'message' => 'Staf Alex Rivera (STAFF) terlambat clock-in 25 menit dari jadwal shift Morning Cashier Shift (Jadwal: 08:00 WIB, Masuk: 08:25 WIB).',
            'is_acknowledged' => false,
        ]);

        // 7. Sample Initial Order Seeder (Order #1042)
        $order = Order::create([
            'user_id' => $alex->user_id,
            'total_amount' => 17.60,
            'payment_method' => 'QRIS',
            'payment_status' => 'PAID',
            'cash_tendered' => null,
            'change_due' => null,
            'qris_reference' => 'QRIS-SYNC-'.strtoupper(bin2hex(random_bytes(4))),
        ]);

        OrderDetail::create([
            'order_id' => $order->order_id,
            'menu_id' => $espresso->menu_id,
            'quantity' => 1,
            'subtotal' => 3.50,
        ]);

        OrderDetail::create([
            'order_id' => $order->order_id,
            'menu_id' => $croissant->menu_id,
            'quantity' => 2,
            'subtotal' => 8.00,
        ]);

        OrderDetail::create([
            'order_id' => $order->order_id,
            'menu_id' => $icedMatcha->menu_id,
            'quantity' => 1,
            'subtotal' => 5.50,
        ]);

        // 7. Sample Restock Request Seeder
        RestockRequest::create([
            'ingredient_id' => $beans->ingredient_id,
            'requested_by_user' => $sarah->user_id,
            'approved_by_user' => null,
            'quantity_requested' => 5000.00,
            'status' => 'PENDING',
            'notes' => 'Persiapan lonjakan akhir pekan (weekend rush prep).',
        ]);
    }
}
