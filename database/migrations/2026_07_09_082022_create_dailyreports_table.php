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
        Schema::create('dailyreports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('work_category_id')
                ->nullable()
                ->constrained('workcategories')
                ->nullOnDelete();

            $table->date('report_date');

            $table->string('title');
            $table->text('description');

            $table->text('obstacle')->nullable();
            $table->text('solution')->nullable();

            $table->enum('work_status', [
                'selesai',
                'proses',
                'tertunda',
            ])->default('proses');

            $table->enum('review_status', [
                'draft',
                'dikirim',
                'direview',
            ])->default('draft');

            $table->json('attachments')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'report_date']);
            $table->index(['work_status', 'review_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dailyreports');
    }
};
