<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->time('return_time')->default('12:00:00')->after('min_days');
            $table->unsignedSmallInteger('late_grace_minutes')->default(60)->after('return_time');
            $table->unsignedTinyInteger('late_fee_percent')->default(10)->after('late_grace_minutes');
        });

        Schema::table('bookings', function (Blueprint $table) {
            // Salinan kebijakan saat dipesan
            $table->dateTime('due_at')->nullable()->after('check_out');
            $table->unsignedSmallInteger('late_grace_minutes')->default(60)->after('due_at');
            $table->unsignedTinyInteger('late_fee_percent')->default(10)->after('late_grace_minutes');

            // Kejadian nyata, dicatat mitra
            $table->dateTime('picked_up_at')->nullable()->after('late_fee_percent');
            $table->dateTime('returned_at')->nullable()->after('picked_up_at');
            $table->unsignedInteger('late_minutes')->default(0)->after('returned_at');
            $table->unsignedBigInteger('late_fee_computed')->default(0)->after('late_minutes');   // hitungan sistem
            $table->unsignedBigInteger('late_fee')->default(0)->after('late_fee_computed');       // angka akhir (boleh diubah mitra)

            $table->index(['vehicle_id', 'picked_up_at', 'returned_at'], 'bookings_rental_state_index');
        });

        // Pesanan mobil yang sudah ada: batas kembali bawaan pukul 12.00 pada tanggal kembali
        DB::table('bookings')
            ->where('product_type', 'car')
            ->whereNull('due_at')
            ->update(['due_at' => DB::raw("CONCAT(check_out, ' 12:00:00')")]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_rental_state_index');
            $table->dropColumn([
                'due_at', 'late_grace_minutes', 'late_fee_percent',
                'picked_up_at', 'returned_at', 'late_minutes', 'late_fee_computed', 'late_fee',
            ]);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['return_time', 'late_grace_minutes', 'late_fee_percent']);
        });
    }
};