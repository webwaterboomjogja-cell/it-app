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
        Schema::create('export_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('monthly_it_report_id')
                ->nullable()
                ->constrained('monthlyitreports')
                ->nullOnDelete();

            $table->string('document_number')
                ->unique();

            $table->enum('report_type', [
                'assets',
                'daily_reports',
                'monthly_reports',
            ]);

            $table->enum('format', [
                'xlsx',
                'pdf',
            ]);

            $table->enum('generation_status', [
                'processing',
                'completed',
                'failed',
            ])->default('processing');


            $table->enum('document_status', [
                'draft',
                'final',
            ])->default('draft');

            $table->json('filters')
                ->nullable();


            $table->json('signatories')
                ->nullable();

            $table->string('disk')
                ->default('reports');

            $table->string('file_path')
                ->nullable();

            $table->string('original_filename')
                ->nullable();

            $table->unsignedBigInteger('file_size')
                ->nullable();

            $table->string('checksum', 64)
                ->nullable();

            $table->text('error_message')
                ->nullable();

            $table->foreignId('finalized_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('finalized_at')
                ->nullable();

           
            $table->unsignedInteger('download_count')
                ->default(0);

            $table->timestamp('last_downloaded_at')
                ->nullable();

            $table->timestamp('generated_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'report_type',
                'document_status',
            ]);

            $table->index([
                'generation_status',
                'generated_at',
            ]);

        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('export_histories');
    }
};
