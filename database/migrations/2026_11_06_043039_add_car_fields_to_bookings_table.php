<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->after('room_type_id')
                ->constrained()->nullOnDelete();
            $table->boolean('with_driver')->default(false)->after('vehicle_id');
            $table->unsignedBigInteger('driver_fee_per_day')->nullable()->after('with_driver'); // salinan estimasi, dibayar langsung ke vendor
            $table->string('pickup_location')->nullable()->after('driver_fee_per_day');

            $table->index(['vehicle_id', 'status', 'check_in'], 'bookings_vehicle_period_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_vehicle_period_index');
            $table->dropConstrainedForeignId('vehicle_id');
            $table->dropColumn(['with_driver', 'driver_fee_per_day', 'pickup_location']);
        });
    }
};