<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->pages() as $data) {
                $page = Page::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'template'       => $data['template'],
                        'sort_order'     => $data['sort_order'],
                        'is_active'      => true,
                        'show_in_footer' => true,
                    ]
                );

                foreach ($data['translations'] as $locale => $tr) {
                    PageTranslation::updateOrCreate(
                        ['page_id' => $page->id, 'locale' => $locale],
                        [
                            'title'  => $tr['title'],
                            'body'   => $tr['body'],
                            'extras' => $tr['extras'] ?? null,
                        ]
                    );
                }
            }
        });
    }

    private function pages(): array
    {
        return [
            // ---------------------------------------------------------
            // TENTANG KAMI (template: about)
            // ---------------------------------------------------------
            [
                'slug'       => 'about-us',
                'template'   => 'about',
                'sort_order' => 1,
                'translations' => [
                    'id' => [
                        'title' => 'Tentang Kami',
                        'body'  => "Kami adalah perusahaan perjalanan yang membantu Anda menemukan akomodasi dan transportasi terbaik di seluruh Indonesia.\n\n"
                                 . "Sejak berdiri, kami berkomitmen memberikan layanan yang cepat, transparan, dan mudah digunakan oleh pelanggan individu maupun korporasi.\n\n"
                                 . "Tim kami siap membantu Anda setiap hari, mulai dari pemesanan hingga perjalanan selesai.",
                        'extras' => [
                            'stats_title' => 'Sekilas Tentang Kami',
                            'stats' => [
                                ['value' => '100+',   'label' => 'Mitra hotel'],
                                ['value' => '5.000+', 'label' => 'Pelanggan puas'],
                                ['value' => '10',     'label' => 'Tahun pengalaman'],
                                ['value' => '24/7',   'label' => 'Layanan pelanggan'],
                            ],
                            'highlights_title' => 'Mengapa Memilih Kami',
                            'highlights' => [
                                ['title' => 'Harga Transparan',    'text' => 'Tidak ada biaya tersembunyi, semua rincian harga terlihat sejak awal.'],
                                ['title' => 'Proses Cepat',        'text' => 'Pemesanan selesai dalam beberapa langkah sederhana.'],
                                ['title' => 'Dukungan Responsif',  'text' => 'Tim kami siap membantu kapan pun Anda membutuhkan.'],
                            ],
                        ],
                    ],
                    'en' => [
                        'title' => 'About Us',
                        'body'  => "We are a travel company that helps you find the best accommodation and transportation across Indonesia.\n\n"
                                 . "Since our founding, we have been committed to providing fast, transparent, and easy-to-use services for both individual and corporate customers.\n\n"
                                 . "Our team is ready to help you every day, from booking to the end of your trip.",
                        'extras' => [
                            'stats_title' => 'At a Glance',
                            'stats' => [
                                ['value' => '100+',   'label' => 'Partner hotels'],
                                ['value' => '5,000+', 'label' => 'Happy customers'],
                                ['value' => '10',     'label' => 'Years of experience'],
                                ['value' => '24/7',   'label' => 'Customer support'],
                            ],
                            'highlights_title' => 'Why Choose Us',
                            'highlights' => [
                                ['title' => 'Transparent Pricing',  'text' => 'No hidden fees, every detail is visible from the start.'],
                                ['title' => 'Fast Process',         'text' => 'Complete your booking in just a few simple steps.'],
                                ['title' => 'Responsive Support',   'text' => 'Our team is ready to help whenever you need us.'],
                            ],
                        ],
                    ],
                ],
            ],

            // ---------------------------------------------------------
            // SYARAT & KETENTUAN (template: default)
            // ---------------------------------------------------------
            [
                'slug'       => 'terms-and-conditions',
                'template'   => 'default',
                'sort_order' => 2,
                'translations' => [
                    'id' => [
                        'title' => 'Syarat dan Ketentuan',
                        'body'  => "Dengan menggunakan layanan kami, Anda menyetujui syarat dan ketentuan berikut.\n\n"
                                 . "1. Pemesanan dianggap sah setelah pembayaran dikonfirmasi oleh sistem.\n\n"
                                 . "2. Pembatalan dapat dilakukan paling lambat 24 jam sebelum tanggal check-in, sesuai kebijakan masing-masing penyedia.\n\n"
                                 . "3. Kami berhak mengubah syarat dan ketentuan sewaktu-waktu tanpa pemberitahuan sebelumnya.",
                    ],
                    'en' => [
                        'title' => 'Terms and Conditions',
                        'body'  => "By using our services, you agree to the following terms and conditions.\n\n"
                                 . "1. A booking is considered valid once payment is confirmed by the system.\n\n"
                                 . "2. Cancellations may be made at least 24 hours before the check-in date, subject to each provider's policy.\n\n"
                                 . "3. We reserve the right to change these terms and conditions at any time without prior notice.",
                    ],
                ],
            ],
        ];
    }
}