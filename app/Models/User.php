<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'full_name',
        'email',
        'password_hash',
        'role',
        'hourly_rate',
        'failed_logins',
        'locked_until',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'locked_until' => 'datetime',
            'hourly_rate' => 'decimal:2',
            'failed_logins' => 'integer',
        ];
    }

    /**
     * Specify password column name for Laravel Authentication.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Relationships
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    public function restockRequestsMade(): HasMany
    {
        return $this->hasMany(RestockRequest::class, 'requested_by_user', 'user_id');
    }

    public function restockRequestsApproved(): HasMany
    {
        return $this->hasMany(RestockRequest::class, 'approved_by_user', 'user_id');
    }

    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class, 'user_id', 'user_id');
    }

    public function hrAlerts(): HasMany
    {
        return $this->hasMany(HrAlert::class, 'user_id', 'user_id');
    }

    /**
     * Get active attendance record (clocked-in and not yet clocked-out).
     */
    public function activeAttendance(): ?Attendance
    {
        return $this->attendances()
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();
    }

    /**
     * Get today's scheduled shift for this user.
     */
    public function todaySchedule(?Carbon $date = null): ?ShiftSchedule
    {
        $targetDate = $date ? $date->copy() : Carbon::today();
        $dayName = $targetDate->format('l');

        // 1. Look for specific date schedule
        $schedule = $this->shiftSchedules()
            ->where('is_active', true)
            ->whereDate('scheduled_date', $targetDate)
            ->first();

        if ($schedule) {
            return $schedule;
        }

        // 2. Look for day of week or 'ALL' recurring schedule
        return $this->shiftSchedules()
            ->where('is_active', true)
            ->whereNull('scheduled_date')
            ->where(function ($query) use ($dayName) {
                $query->where('day_of_week', $dayName)
                    ->orWhere('day_of_week', 'ALL')
                    ->orWhereNull('day_of_week');
            })
            ->first();
    }

    /**
     * Role & Lockout Helper Checks
     */
    public function isStaff(): bool
    {
        return $this->role === 'STAFF';
    }

    public function isHrd(): bool
    {
        return $this->role === 'HRD';
    }

    public function isManager(): bool
    {
        return $this->role === 'MANAGER';
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }
}
