<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
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

function createCouponFixture(): array
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
        'name' => 'Coupon Store',
        'description' => 'Store description',
        'address' => 'Riyadh',
    ]);

    $category = Category::create();
    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 100,
        'offer_price' => null,
        'stock' => 10,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Coupon Product',
        'description' => 'Description',
    ]);

    $customer = User::factory()->create();
    $cart = Cart::create(['user_id' => $customer->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 100,
    ]);

    return compact('store', 'product', 'customer', 'cart');
}

test('admin can create a coupon', function () {
    $admin = User::create([
        'role_id' => Role::query()->where('name', 'admin')->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $admin->syncTranslation(['name' => 'Admin User'], 'en');

    $response = $this->actingAs($admin, 'admin')->post(route('admin.coupons.store'), [
        'code' => 'SAVE10',
        'type' => 'percent',
        'value' => 10,
        'minimum_amount' => 50,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.coupons.index'));
    expect(Coupon::where('code', 'SAVE10')->exists())->toBeTrue();
});

test('customer can apply coupon and discounted total is saved on order', function () {
    ['customer' => $customer] = createCouponFixture();

    Coupon::create([
        'code' => 'SAVE10',
        'type' => 'percent',
        'value' => 10,
        'minimum_amount' => 50,
        'is_active' => true,
    ]);

    $this->actingAs($customer, 'web')->post(route('cart.coupon.apply'), [
        'code' => 'SAVE10',
    ])->assertRedirect();

    $response = $this->actingAs($customer, 'web')->post(route('placeorder'), [
        'delivery_name' => 'Coupon User',
        'delivery_email' => 'coupon@example.com',
        'delivery_phone' => '0555555555',
        'delivery_address' => 'Riyadh',
        'payment_method' => 'cod',
    ]);

    $response->assertOk()->assertJson(['success' => true]);

    $order = Order::latest('id')->first();
    expect($order->subtotal_amount)->toBe('100.00');
    expect($order->discount_code)->toBe('SAVE10');
    expect($order->discount_amount)->toBe('10.00');
    expect($order->total_amount)->toBe('90.00');
});
