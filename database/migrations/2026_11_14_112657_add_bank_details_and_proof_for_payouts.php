<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('bank_name', 60)->nullable()->after('bank_account_no');
            $table->string('bank_account_name', 100)->nullable()->after('bank_name');
        });

        // Salinan rekening di pencairan: kolom lama diganti nama, isinya tetap
        Schema::table('payouts', function (Blueprint $table) {
            $table->renameColumn('bank_account', 'bank_account_no');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->string('bank_name', 60)->nullable()->after('bank_account_no');
            $table->string('bank_account_name', 100)->nullable()->after('bank_name');
            $table->string('proof_path')->nullable()->after('transfer_reference');
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_name', 'proof_path']);
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->renameColumn('bank_account_no', 'bank_account');
        });

        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_name']);
        });
    }
};