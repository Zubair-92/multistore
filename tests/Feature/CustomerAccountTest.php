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

beforeEach(function () {
    Role::ensureDefaults();
});

function createCustomerOrderFixture(): array
{
    $customer = User::factory()->create();
    $customer->syncTranslation([
        'name' => 'Customer Name',
        'address' => 'Old Address',
    ], 'en');

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
        'name' => 'Fixture Store',
        'description' => 'Store description',
        'address' => 'Riyadh',
    ]);

    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => \App\Models\Category::create()->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 15,
        'offer_price' => null,
        'stock' => 10,
        'image' => 'products/test.png',
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Fixture Product',
        'description' => 'Product description',
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'total_amount' => 30,
        'status' => 'pending',
        'payment_method' => 'COD',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'product_name' => 'Fixture Product',
        'price' => 15,
        'quantity' => 2,
    ]);

    return compact('customer', 'order');
}

test('customer can update profile including address', function () {
    ['customer' => $customer] = createCustomerOrderFixture();

    $response = $this->actingAs($customer, 'web')->patch('/profile', [
        'name' => 'Updated Customer',
        'email' => 'updated@example.com',
        'address' => 'New Address',
    ]);

    $response->assertRedirect('/profile');

    expect($customer->fresh()->email)->toBe('updated@example.com');
    expect($customer->fresh()->name)->toBe('Updated Customer');
    expect($customer->fresh()->address)->toBe('New Address');
});

test('customer can view own order details', function () {
    ['customer' => $customer, 'order' => $order] = createCustomerOrderFixture();

    $this->actingAs($customer, 'web')
        ->get(route('profile.orders.show', $order))
        ->assertOk()
        ->assertSee('Order #'.$order->id)
        ->assertSee('Fixture Product');
});

test('customer can not view another customers order details', function () {
    ['order' => $order] = createCustomerOrderFixture();
    $otherCustomer = User::factory()->create();

    $this->actingAs($otherCustomer, 'web')
        ->get(route('profile.orders.show', $order))
        ->assertNotFound();
});

test('customer can save and remove wishlist products', function () {
    ['customer' => $customer] = createCustomerOrderFixture();

    $store = Store::first();
    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => \App\Models\Category::create()->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 22,
        'offer_price' => null,
        'stock' => 7,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Wishlist Product',
        'description' => 'Wishlist description',
    ]);

    $this->actingAs($customer, 'web')
        ->post(route('wishlist.store', $product))
        ->assertRedirect();

    expect($customer->fresh()->wishlistProducts()->where('products.id', $product->id)->exists())->toBeTrue();

    $this->actingAs($customer, 'web')
        ->delete(route('wishlist.destroy', $product))
        ->assertRedirect();

    expect($customer->fresh()->wishlistProducts()->where('products.id', $product->id)->exists())->toBeFalse();
});

test('customer can view wishlist page', function () {
    ['customer' => $customer] = createCustomerOrderFixture();
    $product = Product::first();

    $customer->wishlistItems()->create([
        'product_id' => $product->id,
    ]);

    $this->actingAs($customer, 'web')
        ->get(route('wishlist.index'))
        ->assertOk()
        ->assertSee('My Wishlist')
        ->assertSee('Fixture Product');
});

test('customer profile paginates orders and wishlist items', function () {
    ['customer' => $customer] = createCustomerOrderFixture();

    $store = Store::first();
    $categoryId = Product::first()->category_id;

    foreach (range(1, 7) as $index) {
        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $categoryId,
            'subcategory_id' => null,
            'product_category_id' => null,
            'price' => 10 + $index,
            'offer_price' => null,
            'stock' => 4,
            'image' => null,
            'status' => true,
        ]);

        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => 'en',
            'name' => 'Wishlist Extra '.$index,
            'description' => 'Wishlist product '.$index,
        ]);

        $customer->wishlistItems()->create([
            'product_id' => $product->id,
        ]);
    }

    foreach (range(1, 8) as $index) {
        Order::create([
            'user_id' => $customer->id,
            'total_amount' => 20 + $index,
            'status' => 'pending',
            'payment_method' => 'COD',
        ]);
    }

    $this->actingAs($customer, 'web')
        ->get('/profile?orders_page=2&wishlist_page=2')
        ->assertOk()
        ->assertSee('orders_page=1', false)
        ->assertSee('wishlist_page=1', false);
});
