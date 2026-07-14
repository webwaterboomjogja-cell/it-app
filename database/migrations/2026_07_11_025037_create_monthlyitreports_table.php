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
        Schema::create('monthlyitreports', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');

            $table->date('period_start');
            $table->date('period_end');

            
            $table->string('status', 20)->default('draft');

            
            $table->unsignedInteger('total_daily_reports')->default(0);
            $table->unsignedInteger('total_completed')->default(0);
            $table->unsignedInteger('total_pending')->default(0);
            $table->unsignedInteger('total_urgent')->default(0);

            $table->unsignedInteger('total_assets')->default(0);
            $table->unsignedInteger('total_problem_assets')->default(0);
            $table->unsignedInteger('total_maintenance_assets')->default(0);

            $table->unsignedInteger('total_schedules')->default(0);

           
            $table->json('daily_report_summary')->nullable();
            $table->json('staff_summary')->nullable();
            $table->json('category_summary')->nullable();
            $table->json('work_status_summary')->nullable();
            $table->json('priority_summary')->nullable();
            $table->json('asset_summary')->nullable();
            $table->json('schedule_summary')->nullable();

           
            $table->longText('evaluation')->nullable();
            $table->longText('recommendation')->nullable();
            $table->longText('notes')->nullable();

            
            $table->foreignId('generated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('generated_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['month', 'year'],
                'monthly_it_reports_month_year_unique'
            );

            $table->index(['year', 'month']);
            $table->index('status');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthlyitreports');
    }
};
