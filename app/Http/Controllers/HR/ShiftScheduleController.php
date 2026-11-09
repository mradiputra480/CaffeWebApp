<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\ShiftSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShiftScheduleController extends Controller
{
    /**
     * Store a newly created shift schedule.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'shift_name' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'scheduled_date' => 'nullable|date',
            'day_of_week' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:255',
        ]);

        $schedule = ShiftSchedule::create([
            'user_id' => $validated['user_id'],
            'shift_name' => $validated['shift_name'],
            'start_time' => $validated['start_time'].':00',
            'end_time' => $validated['end_time'].':00',
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'day_of_week' => $validated['day_of_week'] ?? 'ALL',
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal shift berhasil didaftarkan.',
                'data' => $schedule,
            ], 201);
        }

        return redirect()->back()->with('success', "Jadwal shift {$schedule->shift_name} berhasil didaftarkan.");
    }

    /**
     * Update an existing shift schedule.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $schedule = ShiftSchedule::findOrFail($id);

        $validated = $request->validate([
            'shift_name' => 'sometimes|required|string|max:50',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i',
            'scheduled_date' => 'nullable|date',
            'day_of_week' => 'nullable|string|max:20',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string|max:255',
        ]);

        if (isset($validated['start_time'])) {
            $validated['start_time'] = $validated['start_time'].':00';
        }
        if (isset($validated['end_time'])) {
            $validated['end_time'] = $validated['end_time'].':00';
        }

        $schedule->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal shift berhasil diperbarui.',
                'data' => $schedule,
            ]);
        }

        return redirect()->back()->with('success', "Jadwal shift {$schedule->shift_name} berhasil diperbarui.");
    }

    /**
     * Remove the specified shift schedule.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $schedule = ShiftSchedule::findOrFail($id);
        $name = $schedule->shift_name;
        $schedule->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Jadwal shift {$name} telah dihapus.",
            ]);
        }

        return redirect()->back()->with('success', "Jadwal shift {$name} telah dihapus.");
    }
}
