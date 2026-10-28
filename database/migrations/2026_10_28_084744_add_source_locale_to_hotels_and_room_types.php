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
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('source_locale', 5)->default('id')->after('star_rating');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->string('source_locale', 5)->default('id')->after('total_rooms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', fn (Blueprint $table) => $table->dropColumn('source_locale'));
        Schema::table('room_types', fn (Blueprint $table) => $table->dropColumn('source_locale'));
    }
};
