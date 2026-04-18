<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
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

function createApprovedStore(): Store
{
    $storeCategory = StoreCategory::create();

    StoreCategoryTranslation::create([
        'store_category_id' => $storeCategory->id,
        'locale' => 'en',
        'store_category' => 'Groceries',
    ]);

    $store = Store::create([
        'store_category_id' => $storeCategory->id,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '0500000000',
        'logo' => 'stores/store.png',
        'approved' => true,
    ]);

    StoreTranslation::create([
        'store_id' => $store->id,
        'locale' => 'en',
        'name' => 'Fresh Store',
        'description' => 'Everyday goods',
        'address' => 'Riyadh',
    ]);

    return $store;
}

test('customer can place order and order item stores product snapshot name', function () {
    $store = createApprovedStore();
    $category = Category::create();

    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 25,
        'offer_price' => null,
        'stock' => 10,
        'image' => 'products/test.png',
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Test Product',
        'description' => 'Nice item',
    ]);

    $user = User::factory()->create();

    $cart = Cart::create([
        'user_id' => $user->id,
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 25,
    ]);

    $response = $this->actingAs($user)->post('/checkout/place-order', [
        'delivery_name' => 'Customer Name',
        'delivery_email' => 'customer@example.com',
        'delivery_phone' => '0555555555',
        'delivery_address' => 'Riyadh Test Address',
        'payment_method' => 'cod',
        'customer_note' => 'Leave at the front desk',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $order = Order::first();

    expect($order)->not->toBeNull();
    expect($order->total_amount)->toBe('50.00');
    expect($order->payment_method)->toBe('cod');
    expect($order->payment_status)->toBe('unpaid');
    expect($order->delivery_name)->toBe('Customer Name');
    expect($order->delivery_address)->toBe('Riyadh Test Address');
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->product_name)->toBe('Test Product');
    expect($product->fresh()->stock)->toBe(8);
    expect($cart->fresh()->items)->toHaveCount(0);
});

test('customer can place order with demo card payment', function () {
    $store = createApprovedStore();
    $category = Category::create();

    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 40,
        'offer_price' => null,
        'stock' => 10,
        'image' => 'products/test.png',
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Demo Paid Product',
        'description' => 'Sandbox payment product',
    ]);

    $user = User::factory()->create();

    $cart = Cart::create([
        'user_id' => $user->id,
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 40,
    ]);

    $response = $this->actingAs($user)->post('/checkout/place-order', [
        'delivery_name' => 'Demo Customer',
        'delivery_email' => 'demo@example.com',
        'delivery_phone' => '0555555555',
        'delivery_address' => 'Doha Demo Address',
        'payment_method' => 'demo_card',
        'demo_card_name' => 'Demo Customer',
        'demo_card_number' => '4242 4242 4242 4242',
        'demo_card_expiry' => '12/30',
        'demo_card_cvv' => '123',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Demo payment approved and order placed successfully.',
        ]);

    $order = Order::latest('id')->first();

    expect($order->payment_method)->toBe('demo_card');
    expect($order->payment_status)->toBe('paid');
    expect($order->transaction_id)->toStartWith('DEMO-');
});

test('demo card payment can simulate a declined transaction', function () {
    $store = createApprovedStore();
    $category = Category::create();

    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 40,
        'offer_price' => null,
        'stock' => 10,
        'image' => 'products/test.png',
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Declined Product',
        'description' => 'Sandbox decline product',
    ]);

    $user = User::factory()->create();

    $cart = Cart::create([
        'user_id' => $user->id,
    ]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 40,
    ]);

    $response = $this->actingAs($user)->post('/checkout/place-order', [
        'delivery_name' => 'Demo Customer',
        'delivery_email' => 'demo@example.com',
        'delivery_phone' => '0555555555',
        'delivery_address' => 'Doha Demo Address',
        'payment_method' => 'demo_card',
        'demo_card_name' => 'Demo Customer',
        'demo_card_number' => '4000 0000 0000 0002',
        'demo_card_expiry' => '12/30',
        'demo_card_cvv' => '123',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
            'message' => 'Demo payment was declined. Use 4242 4242 4242 4242 to complete the order successfully.',
        ]);

    expect(Order::count())->toBe(0);
    expect($cart->fresh()->items)->toHaveCount(1);
});
