<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidationMessagesTest extends TestCase
{
    private function message(array $data, array $rules, string $field): string
    {
        return Validator::make($data, $rules)->errors()->first($field);
    }

    public function test_indonesian_messages_use_readable_field_names(): void
    {
        app()->setLocale('id');

        $this->assertSame('email wajib diisi.', $this->message(['email' => ''], ['email' => 'required'], 'email'));
        $this->assertSame('tanggal check-in wajib diisi.', $this->message([], ['check_in' => 'required'], 'check_in'));
        $this->assertSame(
            'password harus terdiri dari minimal 8 karakter.',
            $this->message(['password' => 'abc'], ['password' => 'min:8'], 'password')
        );
        $this->assertSame(
            'email harus berupa alamat email yang valid.',
            $this->message(['email' => 'bukan-email'], ['email' => 'email'], 'email')
        );
    }

    public function test_confirmation_and_date_messages_read_naturally(): void
    {
        app()->setLocale('id');

        $this->assertSame(
            'Konfirmasi password tidak cocok.',
            $this->message(['password' => 'abc12345', 'password_confirmation' => 'x'], ['password' => 'confirmed'], 'password')
        );

        $this->assertSame(
            'tanggal kembali harus berupa tanggal setelah tanggal ambil.',
            $this->message(
                ['pickup_date' => '2026-02-01', 'return_date' => '2026-01-01'],
                ['return_date' => 'after:pickup_date'],
                'return_date'
            )
        );
    }

    public function test_the_password_rules_have_indonesian_messages(): void
    {
        app()->setLocale('id');

        $rule = \Illuminate\Validation\Rules\Password::min(8)->letters()->numbers();

        $this->assertSame(
            'password harus mengandung setidaknya satu angka.',
            $this->message(['password' => 'hanyahuruf'], ['password' => [$rule]], 'password')
        );
        $this->assertSame(
            'password harus mengandung setidaknya satu huruf.',
            $this->message(['password' => '12345678'], ['password' => [$rule]], 'password')
        );
    }

    public function test_english_keeps_the_standard_messages(): void
    {
        app()->setLocale('en');

        $this->assertSame('The email field is required.', $this->message([], ['email' => 'required'], 'email'));
    }

    public function test_korean_falls_back_to_english_instead_of_showing_raw_keys(): void
    {
        app()->setLocale('ko');

        $this->assertSame('The email field is required.', $this->message([], ['email' => 'required'], 'email'));
    }
}