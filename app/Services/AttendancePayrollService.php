<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\HrAlert;
use App\Models\ShiftSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AttendancePayrollService
{
    /**
     * Standard Manager Override PIN fallback (default '1234').
     */
    public const DEFAULT_OVERRIDE_PIN = '1234';

    /**
     * Standard fallback shift duration in hours when unscheduled.
     */
    public const STANDARD_SHIFT_HOURS = 8.00;

    /**
     * Maximum grace period in minutes to be counted as On Time.
     */
    public const ON_TIME_TOLERANCE_MINUTES = 15;

    /**
     * Verify whether a manager override authorization is valid.
     */
    public function verifyManagerOverride(?string $pin, ?User $actor = null): bool
    {
        // 1. If currently authenticated actor is already a manager, auto-authorized
        if ($actor && $actor->isManager()) {
            return true;
        }

        if (empty($pin)) {
            return false;
        }

        // 2. Check static or environment override PIN
        $configuredPin = env('MANAGER_OVERRIDE_PIN', self::DEFAULT_OVERRIDE_PIN);
        if ($pin === $configuredPin || $pin === self::DEFAULT_OVERRIDE_PIN) {
            return true;
        }

        // 3. Verify against actual manager password hashes in the database
        $managers = User::where('role', 'MANAGER')->get();
        foreach ($managers as $manager) {
            if (Hash::check($pin, $manager->password_hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Execute clock-in with schedule matching, late threshold, and manager override validation.
     *
     * @return array{success: bool, requires_override?: bool, message: string, attendance?: Attendance, status?: string}
     */
    public function clockIn(
        User $user,
        ?Carbon $clockInTime = null,
        ?string $overridePin = null,
        ?string $overrideNotes = null,
        ?User $actor = null
    ): array {
        // Prevent duplicate concurrent active shift
        $activeShift = $user->activeAttendance();
        if ($activeShift) {
            return [
                'success' => false,
                'message' => "Staf {$user->full_name} masih memiliki shift aktif yang belum di-clock out (Clock-in: {$activeShift->clock_in->format('H:i')} WIB).",
            ];
        }

        $now = $clockInTime ? $clockInTime->copy() : Carbon::now();
        $schedule = $user->todaySchedule($now);

        // CASE A: Unscheduled Shift (No shift schedule for today)
        if (! $schedule) {
            $isOverrideValid = $this->verifyManagerOverride($overridePin, $actor);

            if (! $isOverrideValid) {
                return [
                    'success' => false,
                    'requires_override' => true,
                    'message' => "Shift tidak terjadwal untuk {$user->full_name} hari ini. Masukkan PIN Otorisasi Manager untuk melanjutkan.",
                ];
            }

            // Manager Override Authorized
            $notes = $overrideNotes ?: 'Manager Override: Shift tidak terjadwal disetujui Manager';

            $attendance = Attendance::create([
                'user_id' => $user->user_id,
                'clock_in' => $now,
                'clock_out' => null,
                'total_hours' => 0.00,
                'overtime_hours' => 0.00,
                'status' => 'ON_TIME',
                'manager_override_notes' => $notes,
            ]);

            // Create audit alert for unscheduled shift override
            HrAlert::create([
                'user_id' => $user->user_id,
                'attendance_id' => $attendance->attendance_id,
                'type' => 'UNSCHEDULED_SHIFT',
                'title' => "Presensi Shift Tak Terjadwal: {$user->full_name}",
                'message' => "Staf {$user->full_name} masuk shift tak terjadwal pada {$now->format('d M Y, H:i')} WIB dengan otorisasi Manager Override.",
                'is_acknowledged' => false,
            ]);

            return [
                'success' => true,
                'attendance' => $attendance,
                'status' => 'ON_TIME',
                'is_override' => true,
                'message' => 'Clock-in berhasil dengan otorisasi Manager Override untuk shift tak terjadwal.',
            ];
        }

        // CASE B: Scheduled Shift Exists
        $scheduledStart = $schedule->getTodayStartDateTime($now);
        // Positive diffInMinutes means clock-in is AFTER scheduled start
        $diffMinutes = $now->greaterThan($scheduledStart)
            ? (int) $scheduledStart->diffInMinutes($now)
            : 0;

        $isLate = $diffMinutes > self::ON_TIME_TOLERANCE_MINUTES;
        $status = $isLate ? 'LATE' : 'ON_TIME';
        $notes = $isLate ? "Terlambat {$diffMinutes} menit dari jadwal shift ({$schedule->shift_name})" : null;

        $attendance = Attendance::create([
            'user_id' => $user->user_id,
            'clock_in' => $now,
            'clock_out' => null,
            'total_hours' => 0.00,
            'overtime_hours' => 0.00,
            'status' => $status,
            'manager_override_notes' => $notes,
        ]);

        // If Late (> 15 mins), send high-priority alert to HR
        if ($isLate) {
            HrAlert::create([
                'user_id' => $user->user_id,
                'attendance_id' => $attendance->attendance_id,
                'type' => 'LATE_CLOCK_IN',
                'title' => "Alert Keterlambatan: {$user->full_name}",
                'message' => "Staf {$user->full_name} ({$user->role}) clock-in pukul {$now->format('H:i')} WIB, terlambat {$diffMinutes} menit dari jadwal {$schedule->shift_name} ({$schedule->start_time}).",
                'is_acknowledged' => false,
            ]);
        }

        return [
            'success' => true,
            'attendance' => $attendance,
            'status' => $status,
            'minutes_late' => $diffMinutes,
            'message' => $isLate
                ? "Clock-in tercatat TERLAMBAT ({$diffMinutes} menit). Notifikasi peringatan telah dikirimkan ke portal HRD."
                : 'Clock-in tepat waktu (On Time) berhasil dicatat. Selamat bertugas!',
        ];
    }

    /**
     * Execute clock-out with active working hours & overtime calculation.
     *
     * @return array{success: bool, message: string, attendance?: Attendance, total_hours?: float, overtime_hours?: float, earnings?: float}
     */
    public function clockOut(Attendance $attendance, ?Carbon $clockOutTime = null): array
    {
        if ($attendance->clock_out !== null) {
            return [
                'success' => false,
                'message' => "Shift ini sudah selesai di-clock out pada {$attendance->clock_out->format('H:i')} WIB.",
            ];
        }

        $now = $clockOutTime ? $clockOutTime->copy() : Carbon::now();

        if ($now->lessThan($attendance->clock_in)) {
            $now = $attendance->clock_in->copy();
        }

        // Calculate total hours
        $diffMinutes = $attendance->clock_in->diffInMinutes($now);
        $totalHours = round($diffMinutes / 60, 2);

        // Determine shift scheduled standard duration
        $schedule = $attendance->user->todaySchedule($attendance->clock_in);
        $standardDuration = $schedule ? $schedule->getDurationHours() : self::STANDARD_SHIFT_HOURS;

        // Calculate overtime hours
        $overtimeHours = 0.00;
        if ($totalHours > $standardDuration) {
            $overtimeHours = round($totalHours - $standardDuration, 2);
        }

        // Update status if overtime accumulated
        $status = $attendance->status;
        if ($overtimeHours > 0) {
            $status = 'OVERTIME';

            // Send Overtime alert to HR
            HrAlert::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->attendance_id,
                'type' => 'OVERTIME_ALERT',
                'title' => "Akumulasi Lembur: {$attendance->user->full_name}",
                'message' => "Staf {$attendance->user->full_name} menyelesaikan shift dengan durasi {$totalHours} jam (lembur {$overtimeHours} jam melebihi target {$standardDuration} jam).",
                'is_acknowledged' => false,
            ]);
        }

        $attendance->update([
            'clock_out' => $now,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'status' => $status,
        ]);

        $earnings = round($totalHours * (float) $attendance->user->hourly_rate, 2);

        return [
            'success' => true,
            'attendance' => $attendance->fresh(),
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'earnings' => $earnings,
            'message' => "Clock-out berhasil! Total jam kerja: {$totalHours} jam (Lembur: {$overtimeHours} jam). Estimasi upah shift: \${$earnings}.",
        ];
    }

    /**
     * Calculate monthly attendance and wage metrics for an individual staff member.
     */
    public function calculateMonthlyPayroll(User $user, ?int $month = null, ?int $year = null): array
    {
        $targetMonth = $month ?: Carbon::now()->month;
        $targetYear = $year ?: Carbon::now()->year;

        $records = Attendance::where('user_id', $user->user_id)
            ->whereMonth('clock_in', $targetMonth)
            ->whereYear('clock_in', $targetYear)
            ->get();

        $totalHours = round((float) $records->sum('total_hours'), 2);
        $overtimeHours = round((float) $records->sum('overtime_hours'), 2);
        $regularHours = max(0.00, round($totalHours - $overtimeHours, 2));
        $hourlyRate = (float) $user->hourly_rate;

        // Formula according to spec: total_hours * hourly_rate
        $estimatedWage = round($totalHours * $hourlyRate, 2);

        return [
            'user' => $user,
            'month' => $targetMonth,
            'year' => $targetYear,
            'shift_count' => $records->count(),
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'regular_hours' => $regularHours,
            'hourly_rate' => $hourlyRate,
            'estimated_wage' => $estimatedWage,
        ];
    }

    /**
     * Get aggregate roster data for HR Dashboard.
     */
    public function getRosterOverview(?int $month = null, ?int $year = null): array
    {
        $targetMonth = $month ?: Carbon::now()->month;
        $targetYear = $year ?: Carbon::now()->year;

        $staffMembers = User::orderBy('full_name')->get();
        $rosterList = [];
        $totalMonthlyPayroll = 0.00;
        $totalMonthlyHours = 0.00;
        $activeStaffCount = 0;

        foreach ($staffMembers as $staff) {
            $payroll = $this->calculateMonthlyPayroll($staff, $targetMonth, $targetYear);
            $activeShift = $staff->activeAttendance();
            $todaySchedule = $staff->todaySchedule();

            if ($activeShift) {
                $activeStaffCount++;
            }

            $totalMonthlyPayroll += $payroll['estimated_wage'];
            $totalMonthlyHours += $payroll['total_hours'];

            $rosterList[] = [
                'user' => $staff,
                'payroll' => $payroll,
                'active_shift' => $activeShift,
                'today_schedule' => $todaySchedule,
            ];
        }

        // Active unacknowledged HR alerts
        $unacknowledgedAlerts = HrAlert::with(['user', 'attendance'])
            ->where('is_acknowledged', false)
            ->latest()
            ->get();

        // Recent attendances with pagination/limit
        $recentAttendances = Attendance::with('user')
            ->latest('clock_in')
            ->limit(20)
            ->get();

        // Today's shift schedules
        $shiftSchedules = ShiftSchedule::with('user')
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        return [
            'staffMembers' => $staffMembers,
            'month' => $targetMonth,
            'year' => $targetYear,
            'month_name' => Carbon::createFromDate($targetYear, $targetMonth, 1)->translatedFormat('F Y'),
            'staff_roster' => $rosterList,
            'total_staff' => $staffMembers->count(),
            'active_staff_count' => $activeStaffCount,
            'total_monthly_hours' => round($totalMonthlyHours, 2),
            'total_monthly_payroll' => round($totalMonthlyPayroll, 2),
            'unacknowledged_alerts' => $unacknowledgedAlerts,
            'recent_attendances' => $recentAttendances,
            'recentAttendances' => $recentAttendances,
            'shift_schedules' => $shiftSchedules,
        ];
    }
}
