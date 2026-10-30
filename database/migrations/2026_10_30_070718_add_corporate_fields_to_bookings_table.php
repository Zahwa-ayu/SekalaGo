<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('partner_id')
                ->constrained()->nullOnDelete();
            $table->string('department', 100)->nullable()->after('guest_name');
            $table->unsignedBigInteger('policy_limit')->nullable()->after('vendor_revenue');
            $table->string('payment_method', 20)->default('gateway')->after('payment_status');
            $table->foreignId('decided_by')->nullable()->after('refund_reference')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable()->after('decided_by');
            $table->string('decision_note', 500)->nullable()->after('decided_at');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', [
                'pending_payment', 'pending_approval', 'confirmed', 'cancelled', 'expired', 'rejected',
            ])->default('pending_payment')->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('decided_by');
            $table->dropColumn(['department', 'policy_limit', 'payment_method', 'decided_at', 'decision_note']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('status', ['pending_payment', 'confirmed', 'cancelled', 'expired'])
                ->default('pending_payment')->change();
        });
    }
};