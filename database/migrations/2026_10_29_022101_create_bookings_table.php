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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->foreignId('user_id')->constrained();
            $table->string('product_type', 20)->default('hotel');

            $table->foreignId('hotel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('hotel_name');   // salinan nama saat pesan
            $table->string('room_name');

            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('nights');
            $table->unsignedTinyInteger('guests');
            $table->string('guest_name');

            $table->unsignedBigInteger('price_per_night');   // IDR
            $table->unsignedBigInteger('total_price');       // IDR
            $table->unsignedBigInteger('platform_fee');      // IDR
            $table->unsignedBigInteger('vendor_revenue');    // IDR

            $table->string('currency_code', 3);
            $table->decimal('exchange_rate', 18, 6);
            $table->decimal('total_price_display', 18, 2);

            $table->enum('status', ['pending_payment', 'confirmed', 'cancelled', 'expired'])->default('pending_payment');
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'refunded'])->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
