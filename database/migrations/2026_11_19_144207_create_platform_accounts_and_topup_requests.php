<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 60);
            $table->string('account_number', 40);
            $table->string('account_name', 100);
            $table->string('note', 300)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('topup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('platform_bank_account_id')->nullable()->constrained('platform_bank_accounts')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('approved_amount')->nullable();
            $table->date('transfer_date');
            $table->string('sender_name', 100)->nullable();
            $table->string('note', 300)->nullable();
            $table->string('proof_path');
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('decided_at')->nullable();
            $table->string('decision_note', 500)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topup_requests');
        Schema::dropIfExists('platform_bank_accounts');
    }
};