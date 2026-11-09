<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    const UPDATED_AT = null;

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'user_id',
        'total_amount',
        'payment_method',
        'payment_status',
        'cash_tendered',
        'change_due',
        'qris_reference',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'cash_tendered' => 'decimal:2',
            'change_due' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->user();
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'order_details', 'order_id', 'menu_id')
            ->withPivot('quantity', 'subtotal');
    }
}
