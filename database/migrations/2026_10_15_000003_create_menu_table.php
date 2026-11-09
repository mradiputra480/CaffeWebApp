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
        Schema::create('menu', function (Blueprint $table) {
            $table->id('menu_id');
            $table->string('item_name', 100);
            $table->decimal('price', 10, 2);
            $table->string('category', 50); // e.g., 'Espresso', 'Cold Brew', 'Bakery'
            $table->string('image_url', 255)->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index('category', 'idx_menu_category');
            $table->index('is_available', 'idx_menu_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
