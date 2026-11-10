<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['id', 'en'] as $locale) { // tambah locale lain kalau file lang-nya ada
            $items = trans('ui.partner_faq', [], $locale);
            if (!is_array($items)) continue;

            foreach (array_values($items) as $i => $item) {
                $faq = Faq::firstOrCreate(
                    ['category' => 'partner', 'sort_order' => $i + 1],
                    ['is_active' => true]
                );

                FaqTranslation::updateOrCreate(
                    ['faq_id' => $faq->id, 'locale' => $locale],
                    ['question' => $item['q'], 'answer' => $item['a']]
                );
            }
        }
    }
}