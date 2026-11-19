<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 20)->default('midtrans');
            $table->string('order_id', 50)->unique();              // SG-XXXXXXXX-1
            $table->unsignedBigInteger('amount');                  // IDR
            $table->string('status', 20)->default('pending');      // pending, paid, failed, expired, refunded
            $table->string('payment_type', 30)->nullable();        // bank_transfer, gopay, qris, credit_card, ...
            $table->string('transaction_id', 60)->nullable();
            $table->string('snap_token')->nullable();
            $table->text('redirect_url')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('refund_status', 20)->nullable();       // done, manual_pending, manual_done
            $table->unsignedBigInteger('refund_amount')->nullable();
            $table->string('refund_reference')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->json('last_payload')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index('refund_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};