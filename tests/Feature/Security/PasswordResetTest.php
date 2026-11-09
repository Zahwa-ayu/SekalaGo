<?php

namespace Tests\Feature\Security;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    private function reset(array $data)
    {
        return $this->post(route('password.store'), $data);
    }

    // ----------------------------------------------------------- permintaan

    public function test_a_registered_email_receives_a_reset_link(): void
    {
        Notification::fake();
        $user = $this->makeCustomer();

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertTrue(DB::table('password_reset_tokens')->where('email', $user->email)->exists());
    }

    public function test_an_unknown_email_gets_the_same_answer_and_nothing_is_sent(): void
    {
        Notification::fake();
        $user = $this->makeCustomer();

        $this->post(route('password.email'), ['email' => $user->email]);
        $known = session('success');

        $this->post(route('password.email'), ['email' => 'tidak-ada@sekalago.test']);
        $unknown = session('success');

        $this->assertNotNull($known);
        $this->assertSame($known, $unknown);
        Notification::assertSentToTimes($user, ResetPassword::class, 1);
        $this->assertSame(0, DB::table('password_reset_tokens')->where('email', 'tidak-ada@sekalago.test')->count());
    }

    public function test_the_demo_link_is_shown_only_in_demo_mode_and_only_for_real_accounts(): void
    {
        Notification::fake();
        $user = $this->makeCustomer();

        config(['sekalago.show_reset_link' => false]);
        $this->post(route('password.email'), ['email' => $user->email]);
        $this->assertNull(session('demo_link'));

        config(['sekalago.show_reset_link' => true]);
        $this->post(route('password.email'), ['email' => $user->email]);
        $this->assertStringContainsString('/reset-password/', (string) session('demo_link'));

        $this->post(route('password.email'), ['email' => 'tidak-ada@sekalago.test']);
        $this->assertNull(session('demo_link'));
    }

    public function test_the_request_form_is_rate_limited(): void
    {
        Notification::fake();
        $user = $this->makeCustomer();

        foreach (range(1, 5) as $i) {
            $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('success');
        }

        $this->post(route('password.email'), ['email' => $user->email])->assertStatus(429);
    }

    public function test_a_malformed_email_is_refused(): void
    {
        $this->post(route('password.email'), ['email' => 'bukan-email'])->assertSessionHasErrors('email');
    }

    public function test_the_reset_page_opens_with_the_email_filled_in(): void
    {
        $user = $this->makeCustomer();

        $this->get(route('password.reset', ['token' => 'apa-saja', 'email' => $user->email]))
            ->assertOk()
            ->assertSee($user->email);
    }

    // ---------------------------------------------------------------- reset

    private function newPassword(string $token, string $email, string $password = 'BaruAman123'): array
    {
        return [
            'token'                 => $token,
            'email'                 => $email,
            'password'              => $password,
            'password_confirmation' => $password,
        ];
    }

    public function test_a_valid_link_sets_the_new_password(): void
    {
        $user  = $this->makeCustomer();
        $token = Password::broker()->createToken($user);

        $this->reset($this->newPassword($token, $user->email))->assertRedirect(route('login'));

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'BaruAman123']));
        $this->assertFalse(Auth::validate(['email' => $user->email, 'password' => 'password']));
    }

    public function test_resetting_clears_the_temporary_password_flag(): void
    {
        $user = $this->makeCustomer();
        $user->forceFill(['must_change_password' => true])->save();
        $token = Password::broker()->createToken($user);

        $this->reset($this->newPassword($token, $user->email));

        $this->assertFalse((bool) $user->fresh()->must_change_password);
    }

    public function test_a_link_works_only_once(): void
    {
        $user  = $this->makeCustomer();
        $token = Password::broker()->createToken($user);

        $this->reset($this->newPassword($token, $user->email))->assertRedirect(route('login'));
        $this->reset($this->newPassword($token, $user->email, 'LainLagi123'))->assertSessionHasErrors('email');

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'BaruAman123']));
    }

    public function test_a_wrong_token_changes_nothing(): void
    {
        $user = $this->makeCustomer();
        Password::broker()->createToken($user);

        $this->reset($this->newPassword('token-palsu', $user->email))->assertSessionHasErrors('email');

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'password']));
    }

    public function test_a_link_expires_after_an_hour(): void
    {
        $user  = $this->makeCustomer();
        $token = Password::broker()->createToken($user);

        $this->travel(61)->minutes();

        $this->reset($this->newPassword($token, $user->email))->assertSessionHasErrors('email');
        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'password']));
    }

    public function test_a_weak_or_unconfirmed_new_password_is_refused_and_the_link_stays_usable(): void
    {
        $user  = $this->makeCustomer();
        $token = Password::broker()->createToken($user);

        $this->reset($this->newPassword($token, $user->email, 'lemah'))->assertSessionHasErrors('password');
        $this->reset(['password_confirmation' => 'Berbeda123'] + $this->newPassword($token, $user->email))
            ->assertSessionHasErrors('password');

        $this->reset($this->newPassword($token, $user->email))->assertRedirect(route('login'));
    }
}