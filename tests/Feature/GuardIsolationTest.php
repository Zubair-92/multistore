<?php

use App\Models\Role;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreCategoryTranslation;
use App\Models\StoreTranslation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Role::ensureDefaults();
});

function createRoleUser(string $roleName): User
{
    $attributes = [
        'role_id' => Role::query()->where('name', $roleName)->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ];

    if ($roleName === 'store') {
        $category = StoreCategory::create();

        StoreCategoryTranslation::create([
            'store_category_id' => $category->id,
            'locale' => 'en',
            'store_category' => 'General',
        ]);

        $store = Store::create([
            'store_category_id' => $category->id,
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0500000000',
            'logo' => 'stores/test.png',
            'approved' => true,
        ]);

        StoreTranslation::create([
            'store_id' => $store->id,
            'locale' => 'en',
            'name' => 'Test Store',
            'description' => 'Store description',
            'address' => 'Riyadh',
        ]);

        $attributes['store_id'] = $store->id;
    }

    $user = User::create($attributes);
    $user->syncTranslation(['name' => ucfirst($roleName).' User'], 'en');

    return $user;
}

test('admin login does not authenticate frontend guard', function () {
    $admin = createRoleUser('admin');

    $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.home'));

    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest('web');
});

test('frontend login does not authenticate admin guard', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user, 'web');
    $this->assertGuest('admin');
});

test('store login does not authenticate frontend or admin guards', function () {
    $storeUser = createRoleUser('store');

    $this->post('/store/login', [
        'email' => $storeUser->email,
        'password' => 'password',
    ])->assertRedirect(route('store.profile'));

    $this->assertAuthenticatedAs($storeUser, 'store');
    $this->assertGuest('web');
    $this->assertGuest('admin');
});

test('logging out store guard does not log out admin guard', function () {
    $admin = createRoleUser('admin');
    $storeUser = createRoleUser('store');

    $this->actingAs($admin, 'admin');
    $this->actingAs($storeUser, 'store');

    $this->post('/store/logout')->assertRedirect(route('store.login'));

    $this->assertGuest('store');
    $this->assertAuthenticatedAs($admin, 'admin');
});
