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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');
            $table->unsignedBigInteger('user_id'); // Cashier who processed order
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['CASH', 'QRIS']);
            $table->enum('payment_status', ['PENDING', 'PAID', 'FAILED'])->default('PAID');
            $table->decimal('cash_tendered', 10, 2)->nullable();
            $table->decimal('change_due', 10, 2)->nullable();
            $table->string('qris_reference', 100)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('restrict');
            $table->index('created_at', 'idx_orders_created');
            $table->index('payment_status', 'idx_orders_status');
            $table->index('user_id', 'idx_orders_cashier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
