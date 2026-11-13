<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('partner_id')->constrained();
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('total_amount');                  // yang ditransfer ke mitra (hak mitra)
            $table->unsignedBigInteger('platform_fee_total')->default(0); // komisi pesanan-pesanan ini, hanya informasi
            $table->unsignedInteger('booking_count');
            $table->string('bank_account')->nullable();                  // salinan rekening saat dibuat
            $table->string('schedule', 20)->nullable();                  // salinan jadwal MOU
            $table->string('transfer_reference')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 500)->nullable();                     // catatan / alasan pembatalan
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['partner_id', 'status']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('payout_id')->nullable()->after('vendor_revenue')->constrained('payouts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', fn (Blueprint $table) => $table->dropConstrainedForeignId('payout_id'));
        Schema::dropIfExists('payouts');
    }
};