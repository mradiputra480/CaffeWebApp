<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'total_hours',
        'overtime_hours',
        'status',
        'manager_override_notes',
    ];

    protected function casts(): array
    {
        return [
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'total_hours' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function hrAlert(): HasOne
    {
        return $this->hasOne(HrAlert::class, 'attendance_id', 'attendance_id');
    }

    public function isLate(): bool
    {
        return $this->status === 'LATE';
    }

    public function hasOvertime(): bool
    {
        return (float) $this->overtime_hours > 0;
    }

    public function estimatedEarnings(): float
    {
        $rate = $this->user ? (float) $this->user->hourly_rate : 0.00;

        return round((float) $this->total_hours * $rate, 2);
    }

    public function getDurationFormatted(): string
    {
        $hours = floor((float) $this->total_hours);
        $minutes = round(((float) $this->total_hours - $hours) * 60);

        return sprintf('%02dh %02dm', $hours, $minutes);
    }
}
