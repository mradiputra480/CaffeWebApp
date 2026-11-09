<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestockRequest extends Model
{
    use HasFactory;

    protected $table = 'restock_requests';

    protected $primaryKey = 'request_id';

    protected $fillable = [
        'ingredient_id',
        'requested_by_user',
        'approved_by_user',
        'quantity_requested',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_requested' => 'decimal:2',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id', 'ingredient_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user', 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user', 'user_id');
    }

    public function stockIntakes(): HasMany
    {
        return $this->hasMany(StockIntake::class, 'restock_request_id', 'request_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isApproved(): bool
    {
        return $this->status === 'APPROVED';
    }

    public function isRejected(): bool
    {
        return $this->status === 'REJECTED';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    public function getTotalEstimatedCostAttribute(): float
    {
        $unitCost = $this->ingredient ? (float) $this->ingredient->unit_cost : 0.0;

        return round((float) $this->quantity_requested * $unitCost, 2);
    }
}
