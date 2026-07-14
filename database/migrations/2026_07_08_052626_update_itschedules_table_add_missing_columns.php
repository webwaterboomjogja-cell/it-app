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
        Schema::table('itschedules', function (Blueprint $table) {
            if (! Schema::hasColumn('itschedules', 'location')) {
                $table->string('location')->nullable()->after('type');
            }

            if (! Schema::hasColumn('itschedules', 'status')) {
                $table->string('status')->default('planned')->after('location');
            }

            if (! Schema::hasColumn('itschedules', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('itschedules', function (Blueprint $table) {
            if (Schema::hasColumn('itschedules', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('itschedules', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('itschedules', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};
