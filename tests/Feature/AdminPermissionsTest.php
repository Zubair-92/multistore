<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductTranslation;
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

function createAdminUserWithPermissions(array $permissions = [], string $roleName = 'sub_admin'): User
{
    $user = User::create([
        'role_id' => Role::query()->where('name', $roleName)->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'admin_permissions' => $permissions,
        'email_verified_at' => now(),
    ]);

    $user->syncTranslation(['name' => 'Back Office User'], 'en');

    return $user;
}

function createOrderFixtureForAdminPermissions(): Order
{
    $storeCategory = StoreCategory::create();
    StoreCategoryTranslation::create([
        'store_category_id' => $storeCategory->id,
        'locale' => 'en',
        'store_category' => 'General',
    ]);

    $store = Store::create([
        'store_category_id' => $storeCategory->id,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '0500000000',
        'logo' => 'stores/test.png',
        'approved' => true,
    ]);

    StoreTranslation::create([
        'store_id' => $store->id,
        'locale' => 'en',
        'name' => 'Permission Store',
        'description' => 'Store description',
        'address' => 'Riyadh',
    ]);

    $customer = User::factory()->create();
    $category = \App\Models\Category::create();
    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 20,
        'offer_price' => null,
        'stock' => 10,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Permission Product',
        'description' => 'Description',
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'total_amount' => 20,
        'status' => 'pending',
        'payment_method' => 'cod',
        'payment_status' => 'unpaid',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'product_name' => $product->name,
        'price' => 20,
        'quantity' => 1,
    ]);

    return $order;
}

test('subadmin can access allowed admin section', function () {
    $subadmin = createAdminUserWithPermissions(['orders']);
    $order = createOrderFixtureForAdminPermissions();

    $this->actingAs($subadmin, 'admin')
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee((string) $order->id);
});

test('subadmin cannot access disallowed admin section', function () {
    $subadmin = createAdminUserWithPermissions(['orders']);

    $this->actingAs($subadmin, 'admin')
        ->get(route('admin.stores.index'))
        ->assertForbidden();
});

test('admin can access subadmin management', function () {
    $admin = createAdminUserWithPermissions([], 'admin');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.subadmins.index'))
        ->assertOk()
        ->assertSee('Subadmins');
});
