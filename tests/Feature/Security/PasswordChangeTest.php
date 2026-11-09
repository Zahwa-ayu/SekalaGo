<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    private function change(User $user, array $data)
    {
        return $this->actingAs($user)->put(route('account.password.update'), $data);
    }

    private function valid(array $overrides = []): array
    {
        return array_merge([
            'current_password'      => 'password',
            'password'              => 'BaruAman123',
            'password_confirmation' => 'BaruAman123',
        ], $overrides);
    }

    // ------------------------------------------------------------ ganti

    public function test_a_user_changes_their_own_password(): void
    {
        $user = $this->makeCustomer();

        $this->change($user, $this->valid())->assertRedirect(route('home'));

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'BaruAman123']));
        $this->assertFalse(Auth::validate(['email' => $user->email, 'password' => 'password']));
    }

    public function test_the_current_password_must_be_right(): void
    {
        $user = $this->makeCustomer();

        $this->change($user, $this->valid(['current_password' => 'salah']))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'password']));
    }

    // public function test_weak_new_passwords_are_refused(): void
    // {
    //     $user = $this->makeCustomer();

    //     foreach (['abc1' => 'terlalu pendek', 'hanyahuruf' => 'tanpa angka', '12345678' => 'tanpa huruf'] as $weak => $why) {
    //         $this->change($user, $this->valid(['password' => $weak, 'password_confirmation' => $weak]))
    //             ->assertSessionHasErrors('password', null, "Seharusnya ditolak ({$why}): {$weak}");
    //     }

    //     $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'password']));
    // }

        public function test_weak_new_passwords_are_refused(): void
    {
        $user = $this->makeCustomer();

        // terlalu pendek, tanpa angka, tanpa huruf
        foreach (['abc1', 'hanyahuruf', '12345678'] as $weak) {
            $this->change($user, $this->valid(['password' => $weak, 'password_confirmation' => $weak]))
                ->assertSessionHasErrors('password');
        }

        $this->assertTrue(Auth::validate(['email' => $user->email, 'password' => 'password']));
    }

    public function test_the_confirmation_must_match(): void
    {
        $this->change($this->makeCustomer(), $this->valid(['password_confirmation' => 'Berbeda123']))
            ->assertSessionHasErrors('password');
    }

    public function test_the_new_password_must_differ_from_the_current_one(): void
    {
        $user = $this->makeCustomer();
        $user->forceFill(['password' => 'Rahasia123'])->save();

        $this->change($user, $this->valid([
            'current_password'      => 'Rahasia123',
            'password'              => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ]))->assertSessionHasErrors('password');
    }

    public function test_guests_cannot_change_a_password(): void
    {
        $this->put(route('account.password.update'), $this->valid())->assertRedirect(route('login'));
    }

    // ------------------------------------------------ password sementara

    private function temporaryUser(): User
    {
        $user = $this->makeCustomer();
        $user->forceFill(['must_change_password' => true])->save();

        return $user;
    }

    public function test_a_temporary_password_sends_every_page_to_the_change_form(): void
    {
        $user = $this->temporaryUser();

        $this->actingAs($user)
            ->get(route('bookings.index'))
            ->assertRedirect(route('account.password.edit'))
            ->assertSessionHas('url.intended', route('bookings.index'));
    }

    public function test_the_change_form_and_logout_stay_reachable(): void
    {
        $user = $this->temporaryUser();

        $this->actingAs($user)->get(route('account.password.edit'))->assertOk();
        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));
    }

    public function test_after_changing_the_temporary_password_the_user_returns_where_they_were_going(): void
    {
        $user = $this->temporaryUser();

        $this->withSession(['url.intended' => route('bookings.index')]);
        $this->change($user, $this->valid())->assertRedirect(route('bookings.index'));

        $this->assertFalse((bool) $user->fresh()->must_change_password);
        $this->actingAs($user->fresh())->get(route('bookings.index'))->assertOk();
    }

    // -------------------------------------------- akun buatan orang lain

    private function financeWithEmployee(): array
    {
        $company               = $this->makeCompany();
        [$finance, $financeRow] = $this->makeEmployee($company, 'admin_finance');
        [$employee, $employeeRow] = $this->makeEmployee($company);

        return [$finance, $financeRow, $employee, $employeeRow];
    }

    public function test_finance_creates_an_employee_with_a_temporary_password(): void
    {
        $company   = $this->makeCompany();
        [$finance] = $this->makeEmployee($company, 'admin_finance');

        $this->actingAs($finance)->post(route('corporate.employees.store'), [
            'full_name'       => 'Karyawan Baru',
            'email'           => 'baru@perusahaan.test',
            'password'        => 'SandiAwal123',
            'department'      => 'Marketing',
            'role_in_company' => 'employee',
        ])->assertRedirect(route('corporate.employees.index'));

        $created = User::where('email', 'baru@perusahaan.test')->firstOrFail();

        $this->assertTrue((bool) $created->must_change_password);
    }

    public function test_resetting_an_employees_password_makes_it_temporary_again(): void
    {
        [$finance, , $employee, $employeeRow] = $this->financeWithEmployee();

        $this->actingAs($finance)->put(route('corporate.employees.update', $employeeRow), [
            'full_name'       => 'Karyawan Tes',
            'email'           => $employee->email,
            'password'        => 'SandiReset123',
            'department'      => 'Marketing',
            'role_in_company' => 'employee',
            'is_active'       => 1,
        ])->assertRedirect(route('corporate.employees.index'));

        $employee->refresh();
        $this->assertTrue((bool) $employee->must_change_password);
        $this->assertTrue(Auth::validate(['email' => $employee->email, 'password' => 'SandiReset123']));
    }

    public function test_finance_changing_its_own_password_there_is_not_forced_to_change_it_again(): void
    {
        [$finance, $financeRow] = $this->financeWithEmployee();

        $this->actingAs($finance)->put(route('corporate.employees.update', $financeRow), [
            'full_name'       => 'Finance Tes',
            'email'           => $finance->email,
            'password'        => 'SandiSendiri123',
            'role_in_company' => 'admin_finance',
        ]);

        $finance->refresh();
        $this->assertFalse((bool) $finance->must_change_password);
        $this->assertTrue(Auth::validate(['email' => $finance->email, 'password' => 'SandiSendiri123']));
    }

    public function test_editing_an_employee_without_a_new_password_keeps_the_flag_unchanged(): void
    {
        [$finance, , $employee, $employeeRow] = $this->financeWithEmployee();

        $this->actingAs($finance)->put(route('corporate.employees.update', $employeeRow), [
            'full_name'       => 'Nama Diganti',
            'email'           => $employee->email,
            'role_in_company' => 'employee',
            'is_active'       => 1,
        ]);

        $this->assertFalse((bool) $employee->fresh()->must_change_password);
    }
}