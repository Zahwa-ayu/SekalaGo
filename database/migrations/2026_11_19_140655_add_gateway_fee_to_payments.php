<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('gateway_fee')->default(0);      // biaya total (sudah termasuk PPN) yang ditanggung platform
            $table->string('gateway_fee_source', 10)->nullable();       // estimate atau actual
        });
    }

    public function down(): void
    {
        Schema::table('payments', fn (Blueprint $table) => $table->dropColumn(['gateway_fee', 'gateway_fee_source']));
    }
};