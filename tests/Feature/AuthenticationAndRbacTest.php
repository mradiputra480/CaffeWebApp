<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure clean test users
        User::whereIn('email', [
            'test_cashier@cafesync.local',
            'test_hrd@cafesync.local',
            'test_manager@cafesync.local',
        ])->delete();
    }

    public function test_login_portal_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('CafeSync');
        $response->assertSee('Staff Portal Sign-In');
        $response->assertSee('Staff / Cashier');
        $response->assertSee('HR &amp; Shift', false);
        $response->assertSee('Manager');
    }

    public function test_staff_user_can_login_and_redirect_to_pos(): void
    {
        $staff = User::create([
            'full_name' => 'Test Cashier',
            'email' => 'test_cashier@cafesync.local',
            'password_hash' => Hash::make('secret123'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'test_cashier@cafesync.local',
            'password' => 'secret123',
            'role' => 'STAFF',
        ]);

        $response->assertRedirect('/pos');
        $this->assertAuthenticatedAs($staff);

        // Access POS terminal
        $posResponse = $this->get('/pos');
        $posResponse->assertStatus(200);
        $posResponse->assertSee('POS Cashier Terminal');

        $staff->delete();
    }

    public function test_hrd_user_can_login_and_redirect_to_hr_roster(): void
    {
        $hrd = User::create([
            'full_name' => 'Test HRD',
            'email' => 'test_hrd@cafesync.local',
            'password_hash' => Hash::make('secret123'),
            'role' => 'HRD',
            'hourly_rate' => 25.00,
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'test_hrd@cafesync.local',
            'password' => 'secret123',
            'role' => 'HRD',
        ]);

        $response->assertRedirect('/hr/roster');
        $this->assertAuthenticatedAs($hrd);

        // Access HR roster
        $rosterResponse = $this->get('/hr/roster');
        $rosterResponse->assertStatus(200);
        $rosterResponse->assertSee('Staff Roster &amp; Attendance Management', false);

        $hrd->delete();
    }

    public function test_manager_user_can_login_and_redirect_to_manager_dashboard(): void
    {
        $manager = User::create([
            'full_name' => 'Test Manager',
            'email' => 'test_manager@cafesync.local',
            'password_hash' => Hash::make('secret123'),
            'role' => 'MANAGER',
            'hourly_rate' => 35.00,
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'test_manager@cafesync.local',
            'password' => 'secret123',
            'role' => 'MANAGER',
        ]);

        $response->assertRedirect('/manager/dashboard');
        $this->assertAuthenticatedAs($manager);

        // Access Manager dashboard
        $dashResponse = $this->get('/manager/dashboard');
        $dashResponse->assertStatus(200);
        $dashResponse->assertSee('Executive Analytics &amp; Operational Intelligence', false);

        $manager->delete();
    }

    public function test_user_lockout_after_3_failed_attempts(): void
    {
        $user = User::create([
            'full_name' => 'Brute Force Target',
            'email' => 'test_cashier@cafesync.local',
            'password_hash' => Hash::make('correct_pass'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
            'failed_logins' => 0,
            'locked_until' => null,
        ]);

        // Attempt 1: Wrong password
        $res1 = $this->from('/login')->post('/login', [
            'email' => 'test_cashier@cafesync.local',
            'password' => 'wrong_1',
        ]);
        $res1->assertSessionHasErrors(['email']);
        $this->assertEquals(1, $user->fresh()->failed_logins);
        $this->assertNull($user->fresh()->locked_until);

        // Attempt 2: Wrong password
        $res2 = $this->from('/login')->post('/login', [
            'email' => 'test_cashier@cafesync.local',
            'password' => 'wrong_2',
        ]);
        $res2->assertSessionHasErrors(['email']);
        $this->assertEquals(2, $user->fresh()->failed_logins);
        $this->assertNull($user->fresh()->locked_until);

        // Attempt 3: Wrong password -> Lockout triggered!
        $res3 = $this->from('/login')->post('/login', [
            'email' => 'test_cashier@cafesync.local',
            'password' => 'wrong_3',
        ]);
        $res3->assertSessionHasErrors(['email']);
        $updatedUser = $user->fresh();
        $this->assertEquals(3, $updatedUser->failed_logins);
        $this->assertNotNull($updatedUser->locked_until);
        $this->assertTrue($updatedUser->isLocked());

        // Attempt 4: Even with correct password, login is blocked due to 15m lockout!
        $res4 = $this->from('/login')->post('/login', [
            'email' => 'test_cashier@cafesync.local',
            'password' => 'correct_pass',
        ]);
        $res4->assertSessionHasErrors(['email']);
        $this->assertGuest();

        $user->delete();
    }

    public function test_rbac_restrictions(): void
    {
        $staff = User::create([
            'full_name' => 'Staff Role Test',
            'email' => 'test_cashier@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
        ]);

        // Staff can access POS
        $this->actingAs($staff)->get('/pos')->assertStatus(200);

        // Staff CANNOT access HR Roster -> 403 Forbidden
        $this->actingAs($staff)->get('/hr/roster')->assertStatus(403);

        // Staff CANNOT access Manager Dashboard -> 403 Forbidden
        $this->actingAs($staff)->get('/manager/dashboard')->assertStatus(403);

        $staff->delete();
    }

    public function test_manager_has_full_access(): void
    {
        $manager = User::create([
            'full_name' => 'Manager Super Test',
            'email' => 'test_manager@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'MANAGER',
            'hourly_rate' => 35.00,
        ]);

        // Manager can access all 3 areas
        $this->actingAs($manager)->get('/pos')->assertStatus(200);
        $this->actingAs($manager)->get('/hr/roster')->assertStatus(200);
        $this->actingAs($manager)->get('/manager/dashboard')->assertStatus(200);

        $manager->delete();
    }

    public function test_user_can_logout_successfully(): void
    {
        $user = User::create([
            'full_name' => 'Logout User',
            'email' => 'test_cashier@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
        ]);

        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();

        $user->delete();
    }
}
