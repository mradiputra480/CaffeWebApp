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
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id('schedule_id');
            $table->unsignedBigInteger('user_id');
            $table->string('shift_name', 50);
            $table->time('start_time');
            $table->time('end_time');
            $table->date('scheduled_date')->nullable();
            $table->string('day_of_week', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'scheduled_date'], 'idx_user_scheduled_date');
            $table->index('is_active', 'idx_schedule_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
    }
};
