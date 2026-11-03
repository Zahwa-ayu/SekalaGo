<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('area', 100)->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->time('check_in_time')->default('14:00:00')->after('star_rating');
            $table->time('check_out_time')->default('12:00:00')->after('check_in_time');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->string('bed_type', 20)->default('double')->after('capacity');
            $table->unsignedTinyInteger('bed_count')->default(1)->after('bed_type');
            $table->boolean('smoking_allowed')->default(false)->after('bed_count');
            $table->string('meal_plan', 20)->default('room_only')->after('smoking_allowed');
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->enum('scope', ['hotel', 'room', 'both'])->default('hotel')->after('code');
        });

        DB::table('facilities')->where('code', 'wifi')->update(['scope' => 'both']);
        DB::table('facilities')->where('code', 'ac')->update(['scope' => 'room']);

        Schema::create('facility_room_type', function (Blueprint $table) {
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->primary(['facility_id', 'room_type_id']);
        });

        Schema::create('hotel_nearby_places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('type', 20)->default('other');
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->timestamps();
        });

        // Foto untuk hotel dan tipe kamar (relasi polimorfik)
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->morphs('photoable');
            $table->string('path');
            $table->string('caption')->nullable();
            $table->boolean('is_cover')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos');
        Schema::dropIfExists('hotel_nearby_places');
        Schema::dropIfExists('facility_room_type');

        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn('scope');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['bed_type', 'bed_count', 'smoking_allowed', 'meal_plan']);
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['area', 'latitude', 'longitude', 'check_in_time', 'check_out_time']);
        });
    }
};