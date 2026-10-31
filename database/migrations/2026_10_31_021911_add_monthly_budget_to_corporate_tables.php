<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_positions', function (Blueprint $table) {
            $table->unsignedBigInteger('monthly_budget')->nullable()->after('booking_limit');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('default_monthly_budget')->nullable()->after('travel_policy_limit');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('approval_reason', 20)->nullable()->after('decision_note');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('approval_reason');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('default_monthly_budget');
        });

        Schema::table('company_positions', function (Blueprint $table) {
            $table->dropColumn('monthly_budget');
        });
    }
};