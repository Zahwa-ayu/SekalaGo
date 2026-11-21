<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_agreements', function (Blueprint $table) {
            $table->unsignedTinyInteger('late_cancel_fee_percent')->default(50);
        });
    }

    public function down(): void
    {
        Schema::table('partner_agreements', fn (Blueprint $table) => $table->dropColumn('late_cancel_fee_percent'));
    }
};