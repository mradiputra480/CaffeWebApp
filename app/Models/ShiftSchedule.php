<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSchedule extends Model
{
    use HasFactory;

    protected $table = 'shift_schedules';

    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'user_id',
        'shift_name',
        'start_time',
        'end_time',
        'scheduled_date',
        'day_of_week',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get carbon instance of today's start time.
     */
    public function getTodayStartDateTime(?Carbon $date = null): Carbon
    {
        $baseDate = $date ? $date->copy() : Carbon::today();
        [$hour, $minute, $second] = array_pad(explode(':', $this->start_time), 3, '00');

        return $baseDate->setTime((int) $hour, (int) $minute, (int) $second);
    }

    /**
     * Get carbon instance of today's end time.
     */
    public function getTodayEndDateTime(?Carbon $date = null): Carbon
    {
        $baseDate = $date ? $date->copy() : Carbon::today();
        [$hour, $minute, $second] = array_pad(explode(':', $this->end_time), 3, '00');
        $end = $baseDate->copy()->setTime((int) $hour, (int) $minute, (int) $second);

        // If end time is earlier than start time (overnight shift), add 1 day
        if ($end->lessThan($this->getTodayStartDateTime($baseDate))) {
            $end->addDay();
        }

        return $end;
    }

    /**
     * Get scheduled shift duration in hours.
     */
    public function getDurationHours(): float
    {
        $start = $this->getTodayStartDateTime();
        $end = $this->getTodayEndDateTime();

        return round($start->diffInMinutes($end) / 60, 2);
    }
}
