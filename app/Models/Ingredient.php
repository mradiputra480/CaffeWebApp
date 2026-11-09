<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $table = 'ingredients';

    protected $primaryKey = 'ingredient_id';

    protected $fillable = [
        'item_name',
        'category',
        'sku',
        'current_stock',
        'max_capacity',
        'unit',
        'minimum_threshold',
        'unit_cost',
        'storage_location',
        'preferred_supplier',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'max_capacity' => 'decimal:2',
            'minimum_threshold' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'ingredient_id', 'ingredient_id');
    }

    public function restockRequests(): HasMany
    {
        return $this->hasMany(RestockRequest::class, 'ingredient_id', 'ingredient_id');
    }

    public function stockIntakes(): HasMany
    {
        return $this->hasMany(StockIntake::class, 'ingredient_id', 'ingredient_id');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'recipes', 'ingredient_id', 'menu_id')
            ->withPivot('amount_required');
    }

    /**
     * Stock status checks
     */
    public function isHealthy(): bool
    {
        return (float) $this->current_stock > (float) $this->minimum_threshold;
    }

    public function isLowStock(): bool
    {
        return (float) $this->current_stock <= (float) $this->minimum_threshold && (float) $this->current_stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return (float) $this->current_stock <= 0;
    }

    /**
     * Human-readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }
        if ($this->isLowStock()) {
            return 'Low Stock';
        }

        return 'Optimal';
    }

    /**
     * Capacity saturation percentage (0-100)
     */
    public function getCapacityPercentageAttribute(): int
    {
        $max = (float) $this->max_capacity;
        if ($max <= 0) {
            $max = max(1, (float) $this->minimum_threshold * 4);
        }

        $percentage = round(((float) $this->current_stock / $max) * 100);

        return (int) min(100, max(0, $percentage));
    }

    /**
     * Effective SKU fallback
     */
    public function getDisplaySkuAttribute(): string
    {
        if (! empty($this->sku)) {
            return $this->sku;
        }

        return 'RAW-'.strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $this->category ?? 'GEN'), 0, 2)).'-'.str_pad($this->ingredient_id, 2, '0', STR_PAD_LEFT);
    }
}
