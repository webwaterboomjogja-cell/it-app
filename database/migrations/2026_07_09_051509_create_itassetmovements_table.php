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
        Schema::create('itassetmovements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('itasset_id')
                ->constrained('itassets')
                ->cascadeOnDelete();

            $table->foreignId('from_location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();

            $table->foreignId('to_location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();

            $table->foreignId('from_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('to_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('moved_at');

            $table->enum('type', [
                'perpindahan_lokasi',
                'pergantian_pic',
                'serah_terima',
                'penarikan',
            ])->default('perpindahan_lokasi');

            $table->enum('condition_when_moved', [
                'baik',
                'cukup',
                'rusak_ringan',
                'rusak_berat',
            ])->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itassetmovements');
    }
};
