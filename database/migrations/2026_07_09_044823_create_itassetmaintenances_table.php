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
        Schema::create('itassetmaintenances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('itasset_id')
                ->constrained('itassets')
                ->cascadeOnDelete();

            $table->date('maintenance_date');

            $table->text('problem');

            $table->text('action_taken')->nullable();

            $table->foreignId('handled_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->decimal('cost', 15, 2)->default(0);

            $table->enum('status', [
                'proses',
                'selesai',
                'gagal',
                'perlu_penggantian',
            ])->default('proses');

            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itassetmaintenances');
    }
};
