<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menu';

    protected $primaryKey = 'menu_id';

    protected $fillable = [
        'item_name',
        'price',
        'category',
        'image_url',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class, 'menu_id', 'menu_id');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipes', 'menu_id', 'ingredient_id')
            ->withPivot('amount_required');
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'menu_id', 'menu_id');
    }

    /**
     * Check if recipe ingredients are sufficient for a given quantity.
     */
    public function canFulfill(int $quantity = 1): bool
    {
        if (! $this->is_available) {
            return false;
        }

        foreach ($this->recipes as $recipe) {
            $required = $recipe->amount_required * $quantity;
            if (! $recipe->ingredient || $recipe->ingredient->current_stock < $required) {
                return false;
            }
        }

        return true;
    }
}
