<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('subject');            // yang berubah (hotel, tipe kamar, ...)
            $table->string('subject_label')->nullable();  // salinan nama saat kejadian
            $table->nullableMorphs('scope');              // induknya, misalnya hotel untuk perubahan tipe kamar
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('causer_label')->nullable();   // salinan email pelaku saat kejadian
            $table->string('causer_role', 20)->nullable();
            $table->string('event', 40);
            $table->json('properties')->nullable();       // {"old": {...}, "new": {...}}
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};