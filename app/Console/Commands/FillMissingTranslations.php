<?php

namespace App\Console\Commands;

use App\Models\Hotel;
use App\Models\RoomType;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class FillMissingTranslations extends Command
{
    protected $signature = 'translations:fill';

    protected $description = 'Lengkapi terjemahan otomatis untuk bahasa yang belum ada';

    public function handle(TranslationService $service): int
    {
        $count = 0;

        foreach (Hotel::with('translations')->get() as $hotel) {
            $count += $service->fillMissing($hotel);
        }

        foreach (RoomType::with('translations')->get() as $roomType) {
            $count += $service->fillMissing($roomType);
        }

        $this->info("Selesai. {$count} terjemahan baru dibuat.");

        return self::SUCCESS;
    }
}