<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('rate_to_base');
        });

        Schema::create('currency_rate_logs', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3)->index();
            $table->decimal('old_rate', 18, 6)->nullable();
            $table->decimal('new_rate', 18, 6);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_rate_logs');

        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};