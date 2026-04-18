<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
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

function createCartStore(bool $approved = true): Store
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
        'approved' => $approved,
    ]);

    StoreTranslation::create([
        'store_id' => $store->id,
        'locale' => 'en',
        'name' => 'Cart Store',
        'description' => 'Cart store description',
        'address' => 'Riyadh',
    ]);

    return $store;
}

function createCartProduct(Store $store, bool $active = true): Product
{
    $category = Category::create();

    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 30,
        'offer_price' => null,
        'stock' => 12,
        'image' => null,
        'status' => $active,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Cart Product',
        'description' => 'Cart product description',
    ]);

    return $product;
}

test('guest cart page supports session cart items', function () {
    $store = createCartStore();
    $product = createCartProduct($store);

    session()->put('cart', [
        $product->id => [
            'product_id' => $product->id,
            'name' => 'Cart Product',
            'price' => 30,
            'image' => null,
            'quantity' => 2,
        ],
    ]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Cart Product')
        ->assertSee('60.00');
});

test('cart page removes logged in items for unapproved or invalid store products', function () {
    $store = createCartStore(false);
    $product = createCartProduct($store);
    $user = User::factory()->create();

    $cart = Cart::create(['user_id' => $user->id]);

    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 30,
    ]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Your cart is empty.');

    expect($cart->fresh()->items)->toHaveCount(0);
});
