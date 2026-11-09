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
        Schema::create('restock_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->unsignedBigInteger('ingredient_id');
            $table->unsignedBigInteger('requested_by_user');
            $table->unsignedBigInteger('approved_by_user')->nullable();
            $table->decimal('quantity_requested', 10, 2);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'COMPLETED'])->default('PENDING');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('ingredient_id')->references('ingredient_id')->on('ingredients')->onDelete('restrict');
            $table->foreign('requested_by_user')->references('user_id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by_user')->references('user_id')->on('users')->onDelete('restrict');
            $table->index('status', 'idx_restock_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restock_requests');
    }
};
