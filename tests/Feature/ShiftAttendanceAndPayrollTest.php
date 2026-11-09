<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\HrAlert;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShiftAttendanceAndPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected User $hrd;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::create([
            'full_name' => 'Alex Cashier',
            'email' => 'alex_test@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'STAFF',
            'hourly_rate' => 15.00,
        ]);

        $this->hrd = User::create([
            'full_name' => 'David HRD',
            'email' => 'david_test@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'HRD',
            'hourly_rate' => 25.00,
        ]);

        $this->manager = User::create([
            'full_name' => 'Elena Manager',
            'email' => 'elena_test@cafesync.local',
            'password_hash' => Hash::make('password123'),
            'role' => 'MANAGER',
            'hourly_rate' => 35.00,
        ]);
    }

    public function test_staff_can_clock_in_on_time(): void
    {
        // Scheduled shift: 08:00 - 16:00
        ShiftSchedule::create([
            'user_id' => $this->staff->user_id,
            'shift_name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'day_of_week' => 'ALL',
        ]);

        // Clock in at 08:10 (within 15 minutes grace period)
        $clockInTime = Carbon::today()->setTime(8, 10);

        $response = $this->actingAs($this->staff)->post('/attendance/clock-in', [
            'clock_in_time' => $clockInTime->toDateTimeString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('attendance', [
            'user_id' => $this->staff->user_id,
            'status' => 'ON_TIME',
        ]);

        $active = $this->staff->fresh()->activeAttendance();
        $this->assertNotNull($active);
        $this->assertEquals('ON_TIME', $active->status);
        $this->assertEquals(0, HrAlert::where('user_id', $this->staff->user_id)->count());
    }

    public function test_staff_clock_in_late_triggers_hr_alert(): void
    {
        // Scheduled shift: 08:00 - 16:00
        ShiftSchedule::create([
            'user_id' => $this->staff->user_id,
            'shift_name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'day_of_week' => 'ALL',
        ]);

        // Clock in at 08:25 (25 minutes late > 15 minutes tolerance)
        $clockInTime = Carbon::today()->setTime(8, 25);

        $response = $this->actingAs($this->staff)->post('/attendance/clock-in', [
            'clock_in_time' => $clockInTime->toDateTimeString(),
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('attendance', [
            'user_id' => $this->staff->user_id,
            'status' => 'LATE',
        ]);

        // Verify HR alert was created
        $this->assertDatabaseHas('hr_alerts', [
            'user_id' => $this->staff->user_id,
            'type' => 'LATE_CLOCK_IN',
            'is_acknowledged' => false,
        ]);

        $alert = HrAlert::where('user_id', $this->staff->user_id)->first();
        $this->assertStringContainsString('25 menit', $alert->message);
    }

    public function test_unscheduled_shift_without_override_pin_is_rejected_or_prompted(): void
    {
        // No ShiftSchedule created for staff (unscheduled)
        $response = $this->actingAs($this->staff)->post('/attendance/clock-in', []);

        // Should return redirect with requires_override session flag
        $response->assertSessionHas('requires_override', true);
        $response->assertSessionHas('error');

        // Attendance must NOT be created
        $this->assertDatabaseMissing('attendance', [
            'user_id' => $this->staff->user_id,
        ]);
    }

    public function test_unscheduled_shift_with_manager_override_pin_succeeds(): void
    {
        // No ShiftSchedule created for staff (unscheduled)
        // Clock in with Manager PIN '1234'
        $response = $this->actingAs($this->staff)->post('/attendance/clock-in', [
            'override_pin' => '1234',
            'override_notes' => 'Disetujui pengganti shift barista sakit',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('attendance', [
            'user_id' => $this->staff->user_id,
            'status' => 'ON_TIME',
            'manager_override_notes' => 'Disetujui pengganti shift barista sakit',
        ]);

        // Audit alert for unscheduled override
        $this->assertDatabaseHas('hr_alerts', [
            'user_id' => $this->staff->user_id,
            'type' => 'UNSCHEDULED_SHIFT',
        ]);
    }

    public function test_duplicate_active_shift_clock_in_is_prevented(): void
    {
        // First clock in
        Attendance::create([
            'user_id' => $this->staff->user_id,
            'clock_in' => Carbon::now()->subHour(),
            'clock_out' => null,
            'total_hours' => 0.00,
            'overtime_hours' => 0.00,
            'status' => 'ON_TIME',
        ]);

        // Attempt second clock in while first is active
        $response = $this->actingAs($this->staff)->post('/attendance/clock-in', []);

        $response->assertSessionHas('error');
        $this->assertEquals(1, Attendance::where('user_id', $this->staff->user_id)->count());
    }

    public function test_clock_out_calculates_total_hours_and_overtime(): void
    {
        // 8 hour scheduled shift
        ShiftSchedule::create([
            'user_id' => $this->staff->user_id,
            'shift_name' => 'Regular Shift',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'day_of_week' => 'ALL',
        ]);

        $clockIn = Carbon::today()->setTime(8, 0);
        // Worked 9 hours 30 mins = 9.50 hours (1.50 hours overtime)
        $clockOut = Carbon::today()->setTime(17, 30);

        $attendance = Attendance::create([
            'user_id' => $this->staff->user_id,
            'clock_in' => $clockIn,
            'clock_out' => null,
            'total_hours' => 0.00,
            'overtime_hours' => 0.00,
            'status' => 'ON_TIME',
        ]);

        $response = $this->actingAs($this->staff)->post('/attendance/clock-out', [
            'attendance_id' => $attendance->attendance_id,
            'clock_out_time' => $clockOut->toDateTimeString(),
        ]);

        $response->assertSessionHas('success');
        $updated = $attendance->fresh();
        $this->assertNotNull($updated->clock_out);
        $this->assertEquals(9.50, (float) $updated->total_hours);
        $this->assertEquals(1.50, (float) $updated->overtime_hours);
        $this->assertEquals('OVERTIME', $updated->status);

        // Verify estimated earnings: 9.50 * 15.00 = 142.50
        $this->assertEquals(142.50, $updated->estimatedEarnings());
    }

    public function test_hr_roster_renders_attendance_and_monthly_payroll(): void
    {
        // Create 2 attendances for staff this month
        Attendance::create([
            'user_id' => $this->staff->user_id,
            'clock_in' => Carbon::now()->subDays(2)->setTime(8, 0),
            'clock_out' => Carbon::now()->subDays(2)->setTime(16, 0),
            'total_hours' => 8.00,
            'overtime_hours' => 0.00,
            'status' => 'ON_TIME',
        ]);

        Attendance::create([
            'user_id' => $this->staff->user_id,
            'clock_in' => Carbon::now()->subDays(1)->setTime(8, 0),
            'clock_out' => Carbon::now()->subDays(1)->setTime(18, 0),
            'total_hours' => 10.00,
            'overtime_hours' => 2.00,
            'status' => 'OVERTIME',
        ]);

        // Total hours = 18.00. Wage = 18.00 * $15.00 = $270.00
        $response = $this->actingAs($this->hrd)->get('/hr/roster');

        $response->assertStatus(200);
        $response->assertSee('Staff Roster &amp; Attendance Management', false);
        $response->assertSee('Alex Cashier');
        $response->assertSee('$15.00'); // hourly rate
        $response->assertSee('18.00'); // total hours
        $response->assertSee('$270.00'); // estimated wage
    }

    public function test_hr_can_acknowledge_late_alert(): void
    {
        $alert = HrAlert::create([
            'user_id' => $this->staff->user_id,
            'type' => 'LATE_CLOCK_IN',
            'title' => 'Late Alert Test',
            'message' => 'Staff late test',
            'is_acknowledged' => false,
        ]);

        $response = $this->actingAs($this->hrd)->post("/hr/alerts/{$alert->alert_id}/acknowledge");

        $response->assertSessionHas('success');
        $this->assertTrue($alert->fresh()->is_acknowledged);
        $this->assertEquals($this->hrd->user_id, $alert->fresh()->acknowledged_by);
    }

    public function test_hr_can_create_and_delete_shift_schedule(): void
    {
        $response = $this->actingAs($this->hrd)->post('/hr/shifts', [
            'user_id' => $this->staff->user_id,
            'shift_name' => 'Weekend Special Shift',
            'start_time' => '10:00',
            'end_time' => '18:00',
            'day_of_week' => 'Saturday',
            'notes' => 'Weekend Barista Prep',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('shift_schedules', [
            'user_id' => $this->staff->user_id,
            'shift_name' => 'Weekend Special Shift',
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
        ]);

        $schedule = ShiftSchedule::where('shift_name', 'Weekend Special Shift')->first();
        $this->assertNotNull($schedule);

        // Delete schedule
        $delResponse = $this->actingAs($this->hrd)->delete("/hr/shifts/{$schedule->schedule_id}");
        $delResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('shift_schedules', [
            'schedule_id' => $schedule->schedule_id,
        ]);
    }
}
