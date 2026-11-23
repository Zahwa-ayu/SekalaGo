<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_staffs', function (Blueprint $table) {
            // Staf yang sudah ada otomatis menjadi super admin supaya tidak terkunci
            $table->string('sub_role', 20)->default('super_admin');
        });
    }

    public function down(): void
    {
        Schema::table('admin_staffs', fn (Blueprint $table) => $table->dropColumn('sub_role'));
    }
};