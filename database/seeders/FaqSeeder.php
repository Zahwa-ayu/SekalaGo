<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->data() as $category => $items) {
            foreach ($items as $i => $item) {
                // Kunci: category + sort_order, jadi aman dijalankan berulang
                $faq = Faq::firstOrCreate(
                    ['category' => $category, 'sort_order' => $i + 1],
                    ['is_active' => true]
                );

                foreach ($item as $locale => [$question, $answer]) {
                    FaqTranslation::updateOrCreate(
                        ['faq_id' => $faq->id, 'locale' => $locale],
                        ['question' => $question, 'answer' => $answer]
                    );
                }
            }
        }
    }

    private function data(): array
    {
        return [

            // ===================== PARTNER =====================
            'partner' => [
                [
                    'id' => ['Siapa saja yang bisa mendaftar sebagai mitra?',
                        'Pemilik atau pengelola hotel, villa, guest house, serta penyedia sewa kendaraan (perorangan maupun badan usaha) yang memiliki legalitas usaha yang sah.'],
                    'en' => ['Who can register as a partner?',
                        'Owners or managers of hotels, villas and guest houses, as well as vehicle rental providers (individuals or companies) with valid business legality.'],
                ],
                [
                    'id' => ['Dokumen apa saja yang perlu disiapkan?',
                        'Siapkan nama usaha, NIB atau nomor izin usaha, serta nomor rekening bank untuk pencairan dana. Data lain seperti foto properti atau armada dapat dilengkapi setelah akun aktif.'],
                    'en' => ['What documents do I need to prepare?',
                        'Prepare your business name, business license number (NIB), and a bank account number for payouts. Other details such as property or fleet photos can be added after your account is active.'],
                ],
                [
                    'id' => ['Berapa lama proses verifikasi akun mitra?',
                        'Tim operasional kami memverifikasi data Anda dalam waktu 1x24 jam pada hari kerja. Anda akan dihubungi melalui email jika ada data yang perlu dilengkapi.'],
                    'en' => ['How long does partner verification take?',
                        'Our operations team verifies your data within 1x24 hours on business days. We will contact you by email if anything needs to be completed.'],
                ],
                [
                    'id' => ['Apakah saya bisa mengatur tarif sendiri?',
                        'Bisa. Anda bebas menentukan dan menyesuaikan tarif kamar maupun sewa kendaraan kapan saja melalui dashboard mitra, termasuk tarif berbeda untuk musim ramai.'],
                    'en' => ['Can I set my own rates?',
                        'Yes. You can set and adjust room or vehicle rental rates at any time from the partner dashboard, including different rates for peak seasons.'],
                ],
                [
                    'id' => ['Apakah ada biaya untuk bergabung?',
                        'Pendaftaran mitra tidak dipungut biaya. Ketentuan komisi atas pemesanan yang berhasil dapat Anda lihat pada dashboard setelah akun aktif.'],
                    'en' => ['Is there a fee to join?',
                        'Partner registration is free. Commission terms for successful bookings can be viewed in your dashboard once your account is active.'],
                ],
            ],

            // ===================== CUSTOMER =====================
            'customer' => [
                [
                    'id' => ['Bagaimana cara memesan hotel atau mobil di SekalaGo?',
                        'Pilih hotel atau rental mobil, tentukan tanggal dan jumlah tamu atau unit, lalu lanjutkan ke pembayaran. Bukti pemesanan akan dikirim ke email Anda setelah pembayaran berhasil.'],
                    'en' => ['How do I book a hotel or car on SekalaGo?',
                        'Choose a hotel or car rental, select your dates and number of guests or units, then proceed to payment. Your booking confirmation will be emailed once payment is successful.'],
                ],
                [
                    'id' => ['Metode pembayaran apa saja yang tersedia?',
                        'Anda dapat membayar melalui metode yang tersedia pada halaman pembayaran. Pilihan yang tampil dapat berbeda tergantung jenis pesanan.'],
                    'en' => ['Which payment methods are available?',
                        'You can pay using the methods shown on the payment page. Available options may vary depending on the type of booking.'],
                ],
                [
                    'id' => ['Apakah saya bisa membatalkan atau mengubah pesanan?',
                        'Bisa, selama masih sesuai kebijakan pembatalan yang tertera pada pesanan tersebut. Cek detail kebijakan sebelum membayar karena setiap hotel dan penyedia kendaraan dapat berbeda.'],
                    'en' => ['Can I cancel or change my booking?',
                        'Yes, as long as it is within the cancellation policy shown on the booking. Check the policy before paying, as each hotel and vehicle provider may differ.'],
                ],
                [
                    'id' => ['Di mana saya bisa melihat bukti pemesanan?',
                        'Bukti pemesanan dikirim ke email Anda dan juga tersedia di akun Anda pada menu riwayat pesanan.'],
                    'en' => ['Where can I find my booking confirmation?',
                        'Your confirmation is sent to your email and is also available in your account under booking history.'],
                ],
                [
                    'id' => ['Apa syarat menyewa mobil?',
                        'Siapkan identitas diri dan SIM yang masih berlaku untuk sewa lepas kunci. Syarat tambahan dapat berbeda tergantung penyedia kendaraan dan akan ditampilkan sebelum pembayaran.'],
                    'en' => ['What are the requirements to rent a car?',
                        'Prepare a valid ID and driving license for self-drive rentals. Additional requirements may vary by provider and are shown before payment.'],
                ],
            ],

            // ===================== CORPORATE =====================
            'corporate' => [
                [
                    'id' => ['Apa itu layanan perusahaan (corporate) SekalaGo?',
                        'Layanan untuk perusahaan yang membutuhkan akomodasi dan transportasi bagi karyawan atau tamu bisnis, dengan pengelolaan pemesanan yang lebih terpusat.'],
                    'en' => ['What is SekalaGo corporate service?',
                        'A service for companies that need accommodation and transport for employees or business guests, with more centralized booking management.'],
                ],
                [
                    'id' => ['Bagaimana cara mendaftarkan akun perusahaan?',
                        'Daftar melalui halaman pendaftaran perusahaan dan lengkapi data usaha. Tim kami akan memverifikasi data Anda sebelum akun aktif.'],
                    'en' => ['How do I register a corporate account?',
                        'Sign up through the corporate registration page and complete your company details. Our team will verify your data before the account is activated.'],
                ],
                [
                    'id' => ['Apakah tersedia invoice untuk keperluan perusahaan?',
                        'Ya, invoice dapat diterbitkan atas nama perusahaan untuk setiap pesanan sehingga memudahkan pembukuan dan reimburse.'],
                    'en' => ['Are invoices available for company use?',
                        'Yes, invoices can be issued in the company name for each booking, making accounting and reimbursement easier.'],
                ],
                [
                    'id' => ['Bisakah memesan untuk banyak karyawan sekaligus?',
                        'Bisa. Anda dapat memesan beberapa kamar atau kendaraan untuk rombongan dalam satu pesanan, dan hubungi tim kami untuk kebutuhan jumlah besar.'],
                    'en' => ['Can I book for many employees at once?',
                        'Yes. You can book multiple rooms or vehicles for a group in one booking, and contact our team for large-volume needs.'],
                ],
                [
                    'id' => ['Apakah ada dukungan khusus untuk akun perusahaan?',
                        'Tim operasional kami siap membantu kebutuhan pemesanan perusahaan, mulai dari perubahan jadwal hingga penanganan kendaraan selama perjalanan.'],
                    'en' => ['Is there dedicated support for corporate accounts?',
                        'Our operations team is ready to help with corporate booking needs, from schedule changes to handling issues during the trip.'],
                ],
            ],

        ];
    }
}