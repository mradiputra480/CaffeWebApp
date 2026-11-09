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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id('recipe_id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('ingredient_id');
            $table->decimal('amount_required', 10, 2);
            $table->timestamps();

            $table->foreign('menu_id')->references('menu_id')->on('menu')->onDelete('cascade');
            $table->foreign('ingredient_id')->references('ingredient_id')->on('ingredients')->onDelete('restrict');
            $table->unique(['menu_id', 'ingredient_id'], 'uq_menu_ingredient');
            $table->index('menu_id', 'idx_recipe_menu');
            $table->index('ingredient_id', 'idx_recipe_ingredient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
