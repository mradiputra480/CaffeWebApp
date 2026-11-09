<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\HrAlert;
use App\Models\User;
use App\Services\AttendancePayrollService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendancePayrollService $payrollService
    ) {}

    /**
     * Display HR Staff Roster and Attendance Management.
     */
    public function roster(Request $request): View
    {
        $currentUser = Auth::user();
        $month = $request->has('month') ? (int) $request->input('month') : Carbon::now()->month;
        $year = $request->has('year') ? (int) $request->input('year') : Carbon::now()->year;

        $overview = $this->payrollService->getRosterOverview($month, $year);

        return view('hr.roster', array_merge([
            'currentUser' => $currentUser,
            'activeTab' => $request->input('tab', 'roster'),
        ], $overview));
    }

    /**
     * Handle Clock-In for staff (via POS or HR Portal).
     */
    public function clockIn(Request $request): RedirectResponse|JsonResponse
    {
        $currentUser = Auth::user();

        $request->validate([
            'user_id' => 'nullable|exists:users,user_id',
            'override_pin' => 'nullable|string',
            'override_notes' => 'nullable|string|max:255',
            'clock_in_time' => 'nullable|date',
        ]);

        // Target user: default to currently authenticated user, or specified if HR/Manager
        $targetUserId = $request->filled('user_id') ? (int) $request->input('user_id') : $currentUser->user_id;

        // If trying to clock in for another staff, only HRD or MANAGER is allowed
        if ($targetUserId !== $currentUser->user_id && ! $currentUser->isHrd() && ! $currentUser->isManager()) {
            return $this->respondError(
                $request,
                'Anda tidak memiliki wewenang untuk mencatatkan presensi karyawan lain.',
                403
            );
        }

        $targetUser = User::findOrFail($targetUserId);
        $clockInTime = $request->filled('clock_in_time') ? Carbon::parse($request->input('clock_in_time')) : null;

        $result = $this->payrollService->clockIn(
            user: $targetUser,
            clockInTime: $clockInTime,
            overridePin: $request->input('override_pin'),
            overrideNotes: $request->input('override_notes'),
            actor: $currentUser
        );

        if (! $result['success']) {
            if (! empty($result['requires_override'])) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'requires_override' => true,
                        'message' => $result['message'],
                    ], 422);
                }

                return redirect()->back()
                    ->withInput()
                    ->with('requires_override', true)
                    ->with('target_user_id', $targetUserId)
                    ->with('error', $result['message']);
            }

            return $this->respondError($request, $result['message'], 422);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['attendance'],
                'status' => $result['status'],
            ]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Handle Clock-Out for staff.
     */
    public function clockOut(Request $request): RedirectResponse|JsonResponse
    {
        $currentUser = Auth::user();

        $request->validate([
            'attendance_id' => 'nullable|exists:attendance,attendance_id',
            'user_id' => 'nullable|exists:users,user_id',
            'clock_out_time' => 'nullable|date',
        ]);

        $attendance = null;

        if ($request->filled('attendance_id')) {
            $attendance = Attendance::with('user')->findOrFail($request->input('attendance_id'));
        } elseif ($request->filled('user_id')) {
            $targetUser = User::findOrFail($request->input('user_id'));
            $attendance = $targetUser->activeAttendance();
        } else {
            $attendance = $currentUser->activeAttendance();
        }

        if (! $attendance) {
            return $this->respondError($request, 'Tidak ditemukan shift aktif yang dapat di-clock out.', 404);
        }

        // Authorization check: User can clock out own shift, HR/Manager can clock out any
        if ($attendance->user_id !== $currentUser->user_id && ! $currentUser->isHrd() && ! $currentUser->isManager()) {
            return $this->respondError($request, 'Anda tidak memiliki akses untuk clock-out shift karyawan lain.', 403);
        }

        $clockOutTime = $request->filled('clock_out_time') ? Carbon::parse($request->input('clock_out_time')) : null;

        $result = $this->payrollService->clockOut($attendance, $clockOutTime);

        if (! $result['success']) {
            return $this->respondError($request, $result['message'], 422);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'attendance' => $result['attendance'],
                    'total_hours' => $result['total_hours'],
                    'overtime_hours' => $result['overtime_hours'],
                    'earnings' => $result['earnings'],
                ],
            ]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Acknowledge HR Late/Overtime Alert.
     */
    public function acknowledgeAlert(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $alert = HrAlert::findOrFail($id);
        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_by' => Auth::id(),
            'acknowledged_at' => Carbon::now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Peringatan telah ditandai selesai ditinjau.',
            ]);
        }

        return redirect()->back()->with('success', 'Peringatan HR berhasil ditandai selesai ditinjau.');
    }

    /**
     * Telemetry API to fetch current active shift status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = Auth::user();
        $activeShift = $user->activeAttendance();
        $schedule = $user->todaySchedule();

        return response()->json([
            'user' => [
                'user_id' => $user->user_id,
                'full_name' => $user->full_name,
                'role' => $user->role,
                'hourly_rate' => (float) $user->hourly_rate,
            ],
            'is_clocked_in' => $activeShift !== null,
            'active_shift' => $activeShift ? [
                'attendance_id' => $activeShift->attendance_id,
                'clock_in' => $activeShift->clock_in->toIso8601String(),
                'status' => $activeShift->status,
                'elapsed_seconds' => Carbon::now()->diffInSeconds($activeShift->clock_in),
            ] : null,
            'today_schedule' => $schedule ? [
                'schedule_id' => $schedule->schedule_id,
                'shift_name' => $schedule->shift_name,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'duration_hours' => $schedule->getDurationHours(),
            ] : null,
        ]);
    }

    /**
     * Helper to return unified error response.
     */
    protected function respondError(Request $request, string $message, int $status = 400): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return redirect()->back()->with('error', $message);
    }
}
