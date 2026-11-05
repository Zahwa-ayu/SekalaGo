<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();

            // Lokasi ambil dan kembali
            $table->string('city', 100);
            $table->string('area', 100)->nullable();
            $table->string('address')->nullable();

            // Spesifikasi
            $table->string('type', 20)->default('mpv');
            $table->string('transmission', 20)->default('manual');
            $table->string('fuel_type', 20)->default('petrol');
            $table->unsignedTinyInteger('seats')->default(4);
            $table->unsignedTinyInteger('luggage_capacity')->nullable();
            $table->unsignedSmallInteger('model_year')->nullable();
            $table->json('features')->nullable();

            // Ketentuan sewa
            $table->unsignedSmallInteger('units')->default(1);
            $table->unsignedBigInteger('price_per_day');            // IDR, harga dasar sewa (dasar komisi)
            $table->unsignedTinyInteger('min_days')->default(1);
            $table->string('driver_mode', 20)->default('both');     // both | with_driver | self_drive
            $table->unsignedBigInteger('driver_fee_per_day')->nullable(); // estimasi, dibayar langsung ke vendor
            $table->string('extras_note', 500)->nullable();         // catatan BBM, tol, parkir

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejection_reason', 500)->nullable();
            $table->string('source_locale', 5)->default('id');
            $table->timestamps();

            $table->index(['status', 'city']);
        });

        Schema::create('vehicle_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_auto_translated')->default(false);
            $table->timestamps();

            $table->unique(['vehicle_id', 'locale']);
        });

        Schema::create('vehicle_blackouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_blackouts');
        Schema::dropIfExists('vehicle_translations');
        Schema::dropIfExists('vehicles');
    }
};