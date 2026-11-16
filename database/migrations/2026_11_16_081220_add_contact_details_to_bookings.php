<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->time('arrival_time')->nullable();            // perkiraan jam tiba (hotel) / jam ambil (mobil)
            $table->text('special_requests')->nullable();
            $table->string('destination')->nullable();           // tujuan atau rute (mobil)
            $table->boolean('license_confirmed')->default(false); // penyewa lepas kunci membawa SIM dan KTP
            $table->string('trip_purpose', 100)->nullable();     // corporate
            $table->string('cost_center', 60)->nullable();       // corporate: nomor PO atau cost center
        });

        // Pesanan lama: kontak diambil dari akun pemiliknya
        DB::statement('
            UPDATE bookings b
            JOIN users u ON u.id = b.user_id
            SET b.contact_name = b.guest_name, b.contact_email = u.email, b.contact_phone = u.phone_number
            WHERE b.contact_email IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'contact_name', 'contact_email', 'contact_phone', 'arrival_time', 'special_requests',
                'destination', 'license_confirmed', 'trip_purpose', 'cost_center',
            ]);
        });
    }
};