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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('item_name');
            $table->string('sku', 30)->nullable()->after('category');
            $table->decimal('max_capacity', 10, 2)->default(0.00)->after('current_stock');
            $table->string('storage_location', 100)->nullable()->after('unit_cost');
            $table->string('preferred_supplier', 100)->nullable()->after('storage_location');

            $table->index('category', 'idx_ingredient_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropIndex('idx_ingredient_category');
            $table->dropColumn([
                'category',
                'sku',
                'max_capacity',
                'storage_location',
                'preferred_supplier',
            ]);
        });
    }
};
