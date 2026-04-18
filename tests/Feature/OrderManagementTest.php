<?php

use App\Models\Category;
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

function createStoreWithProduct(string $storeName = 'Store Alpha'): array
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
        'name' => $storeName,
        'description' => 'Store description',
        'address' => 'Riyadh',
    ]);

    $owner = User::create([
        'role_id' => Role::query()->where('name', 'store')->value('id'),
        'store_id' => $store->id,
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $owner->syncTranslation(['name' => $storeName.' Owner'], 'en');

    $category = Category::create();

    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 30,
        'offer_price' => null,
        'stock' => 5,
        'image' => 'products/test.png',
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => $storeName.' Product',
        'description' => 'Product description',
    ]);

    return compact('store', 'owner', 'product');
}

function createCustomerUser(): User
{
    $user = User::factory()->create();
    $user->syncTranslation(['name' => 'Customer User'], 'en');

    return $user;
}

function createOrderForStore(Store $store, Product $product, User $customer, string $status = 'pending'): Order
{
    $order = Order::create([
        'user_id' => $customer->id,
        'total_amount' => 60,
        'status' => $status,
        'payment_method' => 'COD',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'product_name' => $product->name,
        'price' => 30,
        'quantity' => 2,
    ]);

    return $order;
}

test('admin dashboard shows core platform metrics', function () {
    $admin = User::create([
        'role_id' => Role::query()->where('name', 'admin')->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $admin->syncTranslation(['name' => 'Admin User'], 'en');

    ['store' => $store, 'product' => $product] = createStoreWithProduct();
    $customer = createCustomerUser();
    $order = createOrderForStore($store, $product, $customer);

    $response = $this->actingAs($admin, 'admin')->get('/admin');

    $response->assertOk()
        ->assertSee('Dashboard')
        ->assertSee((string) $order->id)
        ->assertSee('Total Stores')
        ->assertSee('Pending Orders')
        ->assertSee('Low Stock Watchlist')
        ->assertSee('Top Stores By Revenue');
});

test('admin can export filtered order report', function () {
    $admin = User::create([
        'role_id' => Role::query()->where('name', 'admin')->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $admin->syncTranslation(['name' => 'Admin User'], 'en');

    ['store' => $store, 'product' => $product] = createStoreWithProduct();
    $customer = createCustomerUser();
    $order = createOrderForStore($store, $product, $customer, 'confirmed');
    $order->update(['payment_status' => 'paid']);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.orders.export', [
        'status' => 'confirmed',
        'payment_status' => 'paid',
    ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertStreamed();
    expect($response->streamedContent())->toContain((string) $order->id);
    expect($response->streamedContent())->toContain('paid');
});

test('admin can update order status', function () {
    $admin = User::create([
        'role_id' => Role::query()->where('name', 'admin')->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $admin->syncTranslation(['name' => 'Admin User'], 'en');

    ['store' => $store, 'product' => $product] = createStoreWithProduct();
    $customer = createCustomerUser();
    $order = createOrderForStore($store, $product, $customer);

    $response = $this->actingAs($admin, 'admin')->patch("/admin/orders/{$order->id}", [
        'status' => 'confirmed',
        'payment_status' => 'pending',
        'transaction_id' => 'BANK-123',
    ]);

    $response->assertRedirect(route('admin.orders.show', $order));
    expect($order->fresh()->status)->toBe('confirmed');
    expect($order->fresh()->payment_status)->toBe('pending');
    expect($order->fresh()->transaction_id)->toBe('BANK-123');
});

test('store can view and update its own order', function () {
    ['store' => $store, 'owner' => $storeOwner, 'product' => $product] = createStoreWithProduct();
    $customer = createCustomerUser();
    $order = createOrderForStore($store, $product, $customer);

    $this->actingAs($storeOwner, 'store')
        ->get(route('store.orders.show', $order))
        ->assertOk()
        ->assertSee('Order #'.$order->id)
        ->assertSee($product->name);

    $response = $this->actingAs($storeOwner, 'store')->patch(route('store.orders.update', $order), [
        'status' => 'delivered',
    ]);

    $response->assertRedirect(route('store.orders.show', $order));
    expect($order->fresh()->status)->toBe('delivered');
});

test('store can export its filtered order report', function () {
    ['store' => $store, 'owner' => $storeOwner, 'product' => $product] = createStoreWithProduct();
    $customer = createCustomerUser();
    $order = createOrderForStore($store, $product, $customer, 'pending');
    $order->update(['payment_status' => 'pending']);

    $response = $this->actingAs($storeOwner, 'store')->get(route('store.orders.export', [
        'status' => 'pending',
        'payment_status' => 'pending',
    ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertStreamed();
    expect($response->streamedContent())->toContain((string) $order->id);
    expect($response->streamedContent())->toContain('pending');
});

test('store orders page paginates long result sets', function () {
    ['store' => $store, 'owner' => $storeOwner, 'product' => $product] = createStoreWithProduct();
    $customer = createCustomerUser();

    foreach (range(1, 13) as $index) {
        createOrderForStore($store, $product, $customer, $index % 2 === 0 ? 'confirmed' : 'pending');
    }

    $this->actingAs($storeOwner, 'store')
        ->get(route('store.orders', ['page' => 2]))
        ->assertOk()
        ->assertSee('Orders Received')
        ->assertSee('?page=1', false);
});

test('store dashboard revenue only counts the current stores order items', function () {
    ['store' => $store, 'owner' => $storeOwner, 'product' => $product] = createStoreWithProduct('Store One');
    ['store' => $otherStore, 'product' => $otherProduct] = createStoreWithProduct('Store Two');
    $customer = createCustomerUser();

    $order = Order::create([
        'user_id' => $customer->id,
        'total_amount' => 130,
        'status' => 'confirmed',
        'payment_method' => 'COD',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'product_name' => $product->name,
        'price' => 30,
        'quantity' => 2,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $otherProduct->id,
        'store_id' => $otherStore->id,
        'product_name' => $otherProduct->name,
        'price' => 35,
        'quantity' => 2,
    ]);

    $this->actingAs($storeOwner, 'store')
        ->get(route('store.profile'))
        ->assertOk()
        ->assertSee('$60.00')
        ->assertDontSee('$130.00');
});

test('cancelling an order restores product stock', function () {
    $admin = User::create([
        'role_id' => Role::query()->where('name', 'admin')->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $admin->syncTranslation(['name' => 'Admin User'], 'en');

    ['store' => $store, 'product' => $product] = createStoreWithProduct();
    $customer = createCustomerUser();
    $order = createOrderForStore($store, $product, $customer, 'confirmed');

    $product->update(['stock' => 3]);

    $response = $this->actingAs($admin, 'admin')->patch("/admin/orders/{$order->id}", [
        'status' => 'cancelled',
        'payment_status' => 'failed',
    ]);

    $response->assertRedirect(route('admin.orders.show', $order));
    expect($order->fresh()->status)->toBe('cancelled');
    expect($product->fresh()->stock)->toBe(5);
});
