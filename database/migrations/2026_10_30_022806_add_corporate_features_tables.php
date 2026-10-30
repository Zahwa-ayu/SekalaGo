<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedBigInteger('booking_limit');   // IDR per pemesanan
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::table('company_users', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('user_id');
            $table->foreignId('position_id')->nullable()->after('department')
                ->constrained('company_positions')->nullOnDelete();
        });

        Schema::create('deposit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->enum('type', ['topup', 'charge', 'refund', 'adjustment']);
            $table->bigInteger('amount');                  // + masuk, - keluar (IDR)
            $table->unsignedBigInteger('balance_after');
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Saldo perusahaan yang sudah ada dicatat sebagai baris pembuka di buku besar
        DB::table('companies')->where('deposit_balance', '>', 0)->get()->each(function ($company) {
            DB::table('deposit_transactions')->insert([
                'company_id'    => $company->id,
                'type'          => 'adjustment',
                'amount'        => $company->deposit_balance,
                'balance_after' => $company->deposit_balance,
                'note'          => 'Saldo awal',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposit_transactions');

        Schema::table('company_users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('position_id');
            $table->dropColumn('full_name');
        });

        Schema::dropIfExists('company_positions');
    }
};