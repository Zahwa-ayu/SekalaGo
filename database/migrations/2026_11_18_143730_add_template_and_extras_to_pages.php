<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            // 'default' = teks biasa, 'about' = tampilan Tentang Kami berbagian
            $table->string('template', 20)->default('default')->after('slug');
        });

        Schema::table('page_translations', function (Blueprint $table) {
            // Bagian tambahan per bahasa (kartu angka dan poin keunggulan), hanya dipakai template 'about'
            $table->json('extras')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropColumn('extras');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('template');
        });
    }
};