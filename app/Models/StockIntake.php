<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIntake extends Model
{
    use HasFactory;

    protected $table = 'stock_intakes';

    protected $primaryKey = 'intake_id';

    protected $fillable = [
        'ingredient_id',
        'user_id',
        'restock_request_id',
        'quantity_received',
        'unit',
        'supplier_name',
        'invoice_number',
        'storage_location',
        'batch_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_received' => 'decimal:2',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'ingredient_id', 'ingredient_id');
    }

    public function receivedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function restockRequest(): BelongsTo
    {
        return $this->belongsTo(RestockRequest::class, 'restock_request_id', 'request_id');
    }
}
