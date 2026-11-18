<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->json('names')->nullable();                      // {"id": "...", "en": "...", "ko": "..."}
            $table->string('icon_class', 60)->nullable();           // misalnya fa-wifi
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->dropColumn(['names', 'icon_class', 'is_active', 'sort_order']);
        });
    }
};