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
        Schema::table('partner_agreements', function (Blueprint $table) {
            $table->string('signature_hash', 64)->nullable()->after('signed_ip');
            $table->string('pdf_path')->nullable()->after('signature_hash');
        });
    }

    public function down(): void
    {
        Schema::table('partner_agreements', function (Blueprint $table) {
            $table->dropColumn(['signature_hash', 'pdf_path']);
        });
    }
};
