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
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedTinyInteger('free_cancel_days')->default(3)->after('expires_at');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            $table->string('cancelled_by', 20)->nullable()->after('cancelled_at');
            $table->string('cancellation_reason', 500)->nullable()->after('cancelled_by');
            $table->unsignedBigInteger('refund_amount')->default(0)->after('cancellation_reason');
            $table->timestamp('refunded_at')->nullable()->after('refund_amount');
            $table->string('refund_reference')->nullable()->after('refunded_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'free_cancel_days', 'cancelled_at', 'cancelled_by', 'cancellation_reason',
                'refund_amount', 'refunded_at', 'refund_reference',
            ]);
        });
    }
};
