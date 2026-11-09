<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test root route renders landing page for unauthenticated guest.
     */
    public function test_guest_can_view_landing_page_from_root(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('CafeSync');
        $response->assertSee('Seamless Operations for Modern Specialty Coffee');
    }

    /**
     * Test authenticated user on root route is redirected to their role portal.
     */
    public function test_authenticated_user_is_redirected_to_portal_from_root(): void
    {
        $staff = User::create([
            'full_name' => 'Alex Rivera',
            'email' => 'cashier@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
        ]);

        $response = $this->actingAs($staff)->get('/');

        $response->assertRedirect(route('pos.index'));
    }

    /**
     * Test 1-click demo login authenticates the requested role.
     */
    public function test_demo_login_authenticates_staff_and_redirects(): void
    {
        User::create([
            'full_name' => 'Alex Rivera',
            'email' => 'cashier@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
        ]);

        $response = $this->get(route('demo.login', 'staff'));

        $response->assertRedirect(route('pos.index'));
        $this->assertAuthenticated();
    }
}
