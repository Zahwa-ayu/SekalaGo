<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedTinyInteger('late_cancel_fee_percent')->default(50);   // salinan aturan saat memesan
            $table->unsignedBigInteger('cancel_fee')->default(0);                  // denda yang ditahan (IDR)
        });
    }

    public function down(): void
    {
        Schema::table('bookings', fn (Blueprint $table) => $table->dropColumn(['late_cancel_fee_percent', 'cancel_fee']));
    }
};