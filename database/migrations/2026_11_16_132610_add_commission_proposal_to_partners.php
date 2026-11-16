<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->decimal('proposed_commission_rate', 5, 4)->nullable();   // 0,1250 = 12,5%
            $table->string('commission_proposal_note', 300)->nullable();
            $table->dateTime('commission_proposed_at')->nullable();
        });

        Schema::table('partner_agreements', function (Blueprint $table) {
            $table->decimal('proposed_rate', 5, 4)->nullable();              // salinan usulan mitra saat MOU dikirim
            $table->string('admin_note', 300)->nullable();                   // catatan admin untuk mitra
        });
    }

    public function down(): void
    {
        Schema::table('partner_agreements', fn (Blueprint $table) => $table->dropColumn(['proposed_rate', 'admin_note']));
        Schema::table('partners', fn (Blueprint $table) => $table->dropColumn(['proposed_commission_rate', 'commission_proposal_note', 'commission_proposed_at']));
    }
};