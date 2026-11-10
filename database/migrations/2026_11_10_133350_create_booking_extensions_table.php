<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('days');
            $table->date('old_check_out');
            $table->date('new_check_out');
            $table->unsignedBigInteger('amount');             // biaya tambahan (IDR)
            $table->unsignedBigInteger('platform_fee');
            $table->unsignedBigInteger('vendor_revenue');
            $table->string('payment_method', 20);             // gateway atau deposit
            $table->string('payment_reference')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_extensions');
    }
};