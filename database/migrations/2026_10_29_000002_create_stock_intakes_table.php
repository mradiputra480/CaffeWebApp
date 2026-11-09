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
        Schema::create('stock_intakes', function (Blueprint $table) {
            $table->id('intake_id');
            $table->unsignedBigInteger('ingredient_id');
            $table->unsignedBigInteger('user_id'); // Cashier or Manager who logged the receipt
            $table->unsignedBigInteger('restock_request_id')->nullable();
            $table->decimal('quantity_received', 10, 2);
            $table->string('unit', 20);
            $table->string('supplier_name', 100)->nullable();
            $table->string('invoice_number', 50)->nullable();
            $table->string('storage_location', 100)->nullable();
            $table->string('batch_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('ingredient_id')->references('ingredient_id')->on('ingredients')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict');
            $table->foreign('restock_request_id')->references('request_id')->on('restock_requests')->onDelete('set null');
            $table->index(['ingredient_id', 'created_at'], 'idx_intake_ingredient_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_intakes');
    }
};
