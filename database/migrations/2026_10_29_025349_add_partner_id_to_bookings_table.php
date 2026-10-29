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
            $table->foreignId('partner_id')->nullable()->after('user_id')
                ->constrained()->nullOnDelete();
        });

        // Isi untuk pesanan yang sudah ada
        DB::table('bookings')->update([
            'partner_id' => DB::raw('(select partner_id from hotels where hotels.id = bookings.hotel_id)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('partner_id');
        });
    }
};