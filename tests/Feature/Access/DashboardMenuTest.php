<?php

namespace Tests\Feature\Access;

use App\Support\DashboardMenu;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DashboardMenuTest extends TestCase
{
    private function adminMenu(): array
    {
        return DashboardMenu::for($this->makeUser('admin'));
    }

    public function test_the_admin_menu_groups_products_content_and_account(): void
    {
        $groups = collect($this->adminMenu())->where('type', 'group')->keyBy('label');

        $this->assertSame(
            ['admin.hotels.index', 'admin.vehicles.index'],
            collect($groups['ui.review_products']['children'])->pluck('route')->all()
        );
        $this->assertContains('admin.social-media.index', collect($groups['ui.cms']['children'])->pluck('route')->all());
        $this->assertContains('admin.users.index', collect($groups['ui.admin_account']['children'])->pluck('route')->all());
        $this->assertContains('account.password.edit', collect($groups['ui.admin_account']['children'])->pluck('route')->all());
    }

    public function test_top_level_admin_items_keep_the_agreed_order(): void
    {
        $labels = collect($this->adminMenu())->pluck('label')->all();

        $this->assertSame('ui.dashboard', $labels[0]);
        $this->assertSame('ui.verify_partners', $labels[1]);
        $this->assertSame('ui.review_products', $labels[2]);
        $this->assertSame('ui.all_bookings', $labels[3]);
        $this->assertSame('ui.corporate_management', $labels[4]);
    }

    public function test_only_pages_that_exist_are_listed(): void
    {
        foreach ($this->adminMenu() as $entry) {
            if ($entry['type'] === 'group') {
                $this->assertNotEmpty($entry['children']);

                foreach ($entry['children'] as $child) {
                    $this->assertTrue(Route::has($child['route']), "Rute tidak ada: {$child['route']}");
                }
            } else {
                $this->assertTrue(Route::has($entry['route']), "Rute tidak ada: {$entry['route']}");
            }
        }
    }

    public function test_a_group_is_active_when_any_of_its_pages_is(): void
    {
        $group = collect($this->adminMenu())->firstWhere('label', 'ui.review_products');

        $this->assertContains('admin.hotels.*', $group['active']);
        $this->assertContains('admin.vehicles.*', $group['active']);
    }

    public function test_partner_and_finance_menus_stay_flat(): void
    {
        $partner = $this->makePartner()->user;
        [$finance] = $this->makeEmployee($this->makeCompany(), 'admin_finance');

        foreach ([$partner, $finance] as $user) {
            $menu = DashboardMenu::for($user);

            $this->assertNotEmpty($menu);
            $this->assertSame(['item'], collect($menu)->pluck('type')->unique()->values()->all());
        }
    }

    public function test_ordinary_employees_get_no_sidebar_menu(): void
    {
        [$employee] = $this->makeEmployee($this->makeCompany());

        $this->assertSame([], DashboardMenu::for($employee));
    }

    public function test_the_sidebar_renders_groups_and_opens_the_one_you_are_in(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get(route('admin.hotels.index'))
            ->assertOk()
            ->assertSee('data-sb-group', false)
            ->assertSee('data-sb-open="1"', false)       // grup Tinjau Produk terbuka karena halaman ini ada di dalamnya
            ->assertSee(route('admin.vehicles.index'), false);
    }

    public function test_a_group_stays_closed_when_you_are_elsewhere(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get(route('admin.bookings.index'))
            ->assertOk()
            ->assertSee('data-sb-group', false)
            ->assertDontSee('data-sb-open="1"', false);
    }
}