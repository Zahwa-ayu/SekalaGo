<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris hanya ada jika admin sudah mengganti gambarnya; tanpa baris = gambar bawaan
        Schema::create('site_banners', function (Blueprint $table) {
            $table->id();
            $table->string('key', 30)->unique();          // home, hotels, cars
            $table->string('image_path');                 // di disk public
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_banners');
    }
};