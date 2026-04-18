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

function createStoreUser(bool $approved = false): User
{
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
        'logo' => 'stores/test-logo.png',
        'approved' => $approved,
    ]);

    StoreTranslation::create([
        'store_id' => $store->id,
        'locale' => 'en',
        'name' => 'Test Store',
        'description' => 'Store description',
        'address' => 'Riyadh',
    ]);

    $user = User::create([
        'role_id' => Role::query()->where('name', 'store')->value('id'),
        'store_id' => $store->id,
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $user->syncTranslation(['name' => 'Store Owner'], 'en');

    return $user;
}

test('approved store users can log in', function () {
    $user = createStoreUser(approved: true);

    $response = $this->post('/store/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user, 'store');
    $response->assertRedirect(route('store.profile'));
});

test('unapproved store users can not log in', function () {
    $user = createStoreUser(approved: false);

    $response = $this->from('/store/login')->post('/store/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest('store');
    $response->assertRedirect('/store/login');
    $response->assertSessionHas('error', 'Your store account is pending approval.');
});

test('store approval middleware blocks pending stores from dashboard access', function () {
    $user = createStoreUser(approved: false);

    $response = $this->actingAs($user, 'store')->get('/store/profile');

    $this->assertGuest('store');
    $response->assertRedirect(route('store.login'));
});
