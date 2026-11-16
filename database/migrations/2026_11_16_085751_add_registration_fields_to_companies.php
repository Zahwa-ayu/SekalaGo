<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('legal_form', 30)->nullable();
            $table->string('tax_id', 20)->nullable()->unique();            // NPWP, hanya angka
            $table->string('business_license_no', 30)->nullable();         // NIB
            $table->string('address')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('phone', 30)->nullable();
            $table->string('industry', 60)->nullable();
            $table->string('tax_document_path')->nullable();
            $table->string('license_document_path')->nullable();
            $table->string('source', 10)->default('admin');               // admin atau self
            // Perusahaan yang sudah ada otomatis terverifikasi
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('verified');
            $table->string('verification_note', 500)->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
        });

        DB::table('companies')->whereNull('verified_at')->update(['verified_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropConstrainedForeignId('city_id');
            $table->dropUnique(['tax_id']);
            $table->dropColumn([
                'legal_form', 'tax_id', 'business_license_no', 'address', 'phone', 'industry',
                'tax_document_path', 'license_document_path', 'source',
                'verification_status', 'verification_note', 'verified_at',
            ]);
        });
    }
};