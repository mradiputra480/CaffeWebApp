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
        Schema::create('hr_alerts', function (Blueprint $table) {
            $table->id('alert_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->enum('type', ['LATE_CLOCK_IN', 'OVERTIME_ALERT', 'UNSCHEDULED_SHIFT'])->default('LATE_CLOCK_IN');
            $table->string('title', 150);
            $table->text('message');
            $table->boolean('is_acknowledged')->default(false);
            $table->unsignedBigInteger('acknowledged_by')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('attendance_id')->references('attendance_id')->on('attendance')->onDelete('cascade');
            $table->foreign('acknowledged_by')->references('user_id')->on('users')->onDelete('set null');
            $table->index(['is_acknowledged', 'type'], 'idx_alert_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_alerts');
    }
};
