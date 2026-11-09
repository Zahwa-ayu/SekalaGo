<?php

namespace Tests\Feature\Security;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    private function attempt(string $email, string $password = 'password')
    {
        return $this->post('/login', ['email' => $email, 'password' => $password]);
    }

    public static function dashboards(): array
    {
        return [
            'pelanggan' => ['customer', 'home'],
            'admin'     => ['admin', 'admin.dashboard'],
            'mitra'     => ['partner', 'partner.dashboard'],
            'corporate' => ['corporate', 'corporate.dashboard'],
        ];
    }

    #[DataProvider('dashboards')]
    public function test_each_role_lands_on_its_own_dashboard(string $type, string $route): void
    {
        $user = $this->userOfType($type);

        $this->attempt($user->email)->assertRedirect(route($route));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_is_refused(): void
    {
        $user = $this->makeCustomer();

        $this->attempt($user->email, 'salah')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_unknown_emails_and_wrong_passwords_get_the_same_message(): void
    {
        $user = $this->makeCustomer();

        $this->attempt($user->email, 'salah')->assertSessionHasErrors('email');
        $wrongPassword = session('errors')->first('email');

        $this->attempt('tidak-ada@sekalago.test')->assertSessionHasErrors('email');
        $unknownEmail = session('errors')->first('email');

        $this->assertSame($wrongPassword, $unknownEmail);
    }

    public function test_a_deactivated_account_cannot_log_in(): void
    {
        $user = $this->makeCustomer();
        $user->forceFill(['is_active' => false])->save();

        $this->attempt($user->email)->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // ------------------------------------------------------ batas percobaan

    public function test_the_sixth_attempt_within_a_minute_is_blocked_even_with_the_right_password(): void
    {
        $user = $this->makeCustomer();

        foreach (range(1, 5) as $i) {
            $this->attempt($user->email, 'salah-' . $i)->assertSessionHasErrors('email');
        }

        $this->attempt($user->email, 'password')->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertMatchesRegularExpression('/\d/', session('errors')->first('email'));   // pesan memuat jumlah detik
    }

    public function test_the_block_lifts_after_a_minute(): void
    {
        $user = $this->makeCustomer();

        foreach (range(1, 5) as $i) {
            $this->attempt($user->email, 'salah-' . $i);
        }

        $this->travel(61)->seconds();

        $this->attempt($user->email, 'password')->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_blocking_one_email_does_not_block_another_account(): void
    {
        $victim = $this->makeCustomer();
        $other  = $this->makeCustomer();

        foreach (range(1, 5) as $i) {
            $this->attempt($victim->email, 'salah-' . $i);
        }

        $this->attempt($other->email)->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($other);
    }

        public function test_upper_and_lower_case_emails_share_the_same_counter(): void
    {
        $user = $this->makeCustomer();

        foreach (range(1, 3) as $i) {
            $this->attempt(strtoupper($user->email), 'salah-' . $i);
        }
        foreach (range(1, 2) as $i) {
            $this->attempt(strtolower($user->email), 'salah-' . $i);
        }

        // Percobaan ke-6 dengan password yang benar tetap ditolak: kelima percobaan tadi dihitung satu
        $this->attempt($user->email, 'password');
        $this->assertGuest();

        // ...dan terbuka lagi setelah semenit
        $this->travel(61)->seconds();
        $this->attempt($user->email, 'password')->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    // ------------------------------------------------------- keluar dan daftar

    public function test_logging_out_ends_the_session(): void
    {
        $user = $this->makeCustomer();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_the_login_page_offers_a_way_to_reset_the_password(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('password.request'), false);
    }

    public function test_public_registration_creates_a_customer_and_logs_in(): void
    {
        $this->post('/register', [
            'full_name'             => 'Siti Baru',
            'email'                 => 'siti@baru.test',
            'phone_number'          => '08123456789',
            'password'              => 'RahasiaKu123',
            'password_confirmation' => 'RahasiaKu123',
        ]);

        $user = User::where('email', 'siti@baru.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('customer', $user->user_type);
        $this->assertSame('Siti Baru', $user->customer->full_name);
    }

    public function test_registration_cannot_be_used_to_create_an_admin(): void
    {
        $this->post('/register', [
            'full_name'             => 'Penyusup',
            'email'                 => 'penyusup@baru.test',
            'password'              => 'RahasiaKu123',
            'password_confirmation' => 'RahasiaKu123',
            'user_type'             => 'admin',
        ]);

        $this->assertSame('customer', User::where('email', 'penyusup@baru.test')->firstOrFail()->user_type);
    }
}