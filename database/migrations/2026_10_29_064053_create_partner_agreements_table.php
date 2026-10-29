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
        Schema::create('partner_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('version')->default(1);
            $table->decimal('commission_rate', 5, 4);          // 0.1500 = 15%
            $table->enum('payout_schedule', ['weekly', 'biweekly', 'monthly'])->default('monthly');
            $table->unsignedTinyInteger('free_cancel_days')->default(3);
            $table->longText('content');                        // salinan isi MOU saat dikirim
            $table->enum('status', ['sent', 'signed', 'void'])->default('sent');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_name')->nullable();
            $table->string('signed_ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_agreements');
    }
};
