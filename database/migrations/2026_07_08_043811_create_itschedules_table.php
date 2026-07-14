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
        Schema::create('itschedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('schedule_date');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('type');
            $table->string('location')->nullable();
            $table->string('status')->default('planned');

            $table->text('notes')->nullable();

            $table->index(['user_id', 'schedule_date']);
            $table->index(['type', 'status']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itschedules');
    }
};
