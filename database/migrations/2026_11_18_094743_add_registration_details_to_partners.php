<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->string('legal_form', 30)->nullable();
            $table->string('business_type', 10)->nullable();             // hotel, car, both
            $table->string('tax_id', 20)->nullable()->unique();          // NPWP, hanya angka
            $table->string('address')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('phone', 30)->nullable();
            $table->string('website')->nullable();
            $table->string('contact_name')->nullable();                  // penanggung jawab
            $table->string('tax_document_path')->nullable();
            $table->string('license_document_path')->nullable();
            $table->string('supporting_document_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
            $table->dropUnique(['tax_id']);
            $table->dropColumn([
                'legal_form', 'business_type', 'tax_id', 'address', 'phone', 'website', 'contact_name',
                'tax_document_path', 'license_document_path', 'supporting_document_path',
            ]);
        });
    }
};