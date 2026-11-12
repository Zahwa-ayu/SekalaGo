<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('province', 100)->nullable();
            $table->string('aliases')->nullable();            // nama lain, dipisah koma: "Jogja, Yogya, DIY"
            $table->string('image_path')->nullable();         // foto sampul (disk public)
            $table->boolean('is_featured')->default(false);   // tampil di Kota Favorit
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('hotels', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('partner_id')->constrained('cities')->nullOnDelete();
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('partner_id')->constrained('cities')->nullOnDelete();
        });

        $this->backfill();
    }

    /** Buat kota dari nama yang sudah dipakai hotel dan armada, lalu tautkan. */
    private function backfill(): void
    {
        $names = DB::table('hotels')->pluck('city')
            ->merge(DB::table('vehicles')->pluck('city'))
            ->map(fn ($name) => trim((string) $name))
            ->filter();

        foreach ($names->groupBy(fn ($name) => mb_strtolower($name)) as $group) {
            $name = $group->countBy()->sortDesc()->keys()->first();   // penulisan yang paling sering dipakai

            $id = DB::table('cities')->where('name', $name)->value('id')
                ?? DB::table('cities')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);

            foreach (['hotels', 'vehicles'] as $table) {
                DB::table($table)
                    ->whereRaw('LOWER(TRIM(city)) = ?', [mb_strtolower($name)])
                    ->update(['city_id' => $id, 'city' => $name]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', fn (Blueprint $table) => $table->dropConstrainedForeignId('city_id'));
        Schema::table('hotels', fn (Blueprint $table) => $table->dropConstrainedForeignId('city_id'));
        Schema::dropIfExists('cities');
    }
};