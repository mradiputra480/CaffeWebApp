<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id('ingredient_id');
            $table->string('item_name', 100);
            $table->decimal('current_stock', 10, 2)->default(0.00);
            $table->string('unit', 20); // e.g. 'grams', 'ml', 'pcs'
            $table->decimal('minimum_threshold', 10, 2)->default(10.00);
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->timestamps();

            $table->index(['current_stock', 'minimum_threshold'], 'idx_stock_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
