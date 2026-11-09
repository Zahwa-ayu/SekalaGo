<?php

namespace Tests\Feature\Money;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCompanyDepositTest extends TestCase
{
    private function validCompany(array $overrides = []): array
    {
        return array_merge([
            'company_name'        => 'PT Contoh Abadi',
            'corporate_code'      => 'abc01',
            'travel_policy_limit' => 2000000,
            'initial_deposit'     => 5000000,
            'finance_name'        => 'Sari Finance',
            'finance_email'       => 'sari@contoh.test',
            'finance_password'    => 'RahasiaKu123',
        ], $overrides);
    }

    public function test_admin_tops_up_a_company(): void
    {
        $admin   = $this->makeUser('admin');
        $company = $this->makeCompany(deposit: 1000000);

        $this->actingAs($admin)
            ->post(route('admin.companies.topup', $company), ['amount' => 2500000, 'note' => 'Transfer 9 Nov'])
            ->assertRedirect(route('admin.companies.show', $company));

        $this->assertSame(3500000, $company->fresh()->deposit_balance);

        $tx = $company->transactions()->latest('id')->firstOrFail();
        $this->assertSame('topup', $tx->type);
        $this->assertSame(2500000, $tx->amount);
        $this->assertSame($admin->id, $tx->created_by);
    }

    public function test_invalid_topup_amounts_are_refused(): void
    {
        $admin   = $this->makeUser('admin');
        $company = $this->makeCompany(deposit: 1000000);

        foreach (['0', '-5000', 'abc', ''] as $amount) {
            $this->actingAs($admin)
                ->post(route('admin.companies.topup', $company), ['amount' => $amount])
                ->assertSessionHasErrors('amount');
        }

        $this->assertSame(1000000, $company->fresh()->deposit_balance);
    }

    public function test_only_admins_can_top_up(): void
    {
        $company         = $this->makeCompany();
        [$finance, $row] = $this->makeEmployee($company, 'admin_finance');

        $this->actingAs($this->makeCustomer())
            ->post(route('admin.companies.topup', $company), ['amount' => 1000])
            ->assertForbidden();

        $this->actingAs($finance)
            ->post(route('admin.companies.topup', $company), ['amount' => 1000])
            ->assertForbidden();

        auth()->logout();
        $this->post(route('admin.companies.topup', $company), ['amount' => 1000])
            ->assertRedirect(route('login'));

        $this->assertSame(0, $company->fresh()->deposit_balance);
    }

    public function test_admin_creates_a_company_with_a_finance_account_and_an_opening_deposit(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin)->post(route('admin.companies.store'), $this->validCompany());

        $company = Company::where('company_name', 'PT Contoh Abadi')->firstOrFail();
        $response->assertRedirect(route('admin.companies.show', $company));

        $this->assertSame('ABC01', $company->corporate_code);
        $this->assertSame(5000000, $company->deposit_balance);

        $user = User::where('email', 'sari@contoh.test')->firstOrFail();
        $this->assertSame('corporate', $user->user_type);
        $this->assertTrue(Hash::check('RahasiaKu123', $user->password));
        $this->assertTrue((bool) $user->must_change_password);   // password sementara dari admin

        $member = CompanyUser::where('user_id', $user->id)->firstOrFail();
        $this->assertSame($company->id, $member->company_id);
        $this->assertSame('admin_finance', $member->role_in_company);

        $tx = $company->transactions()->firstOrFail();
        $this->assertSame('topup', $tx->type);
        $this->assertSame(5000000, $tx->amount);
        $this->assertSame($admin->id, $tx->created_by);
    }

    public function test_a_company_without_an_opening_deposit_starts_at_zero(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.companies.store'), $this->validCompany(['initial_deposit' => 0]));

        $company = Company::where('company_name', 'PT Contoh Abadi')->firstOrFail();

        $this->assertSame(0, $company->deposit_balance);
        $this->assertSame(0, $company->transactions()->count());
    }

    public function test_a_duplicate_finance_email_creates_nothing(): void
    {
        $this->makeUser('customer');
        $existing = User::query()->firstOrFail();

        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.companies.store'), $this->validCompany(['finance_email' => $existing->email]))
            ->assertSessionHasErrors('finance_email');

        $this->assertSame(0, Company::count());
    }

    public function test_a_duplicate_corporate_code_creates_nothing(): void
    {
        $this->makeCompany(attributes: ['corporate_code' => 'ABC01']);

        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.companies.store'), $this->validCompany(['corporate_code' => 'ABC01']))
            ->assertSessionHasErrors('corporate_code');

        $this->assertSame(1, Company::count());
    }

    public function test_a_short_temporary_password_is_refused(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post(route('admin.companies.store'), $this->validCompany(['finance_password' => 'pendek']))
            ->assertSessionHasErrors('finance_password');

        $this->assertSame(0, Company::count());
    }
}