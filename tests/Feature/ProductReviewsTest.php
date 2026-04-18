<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductReview;
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

function createReviewFixture(): array
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
        'name' => 'Review Store',
        'description' => 'Store description',
        'address' => 'Riyadh',
    ]);

    $category = Category::create();

    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 25,
        'offer_price' => null,
        'stock' => 8,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Review Product',
        'description' => 'Review description',
    ]);

    $customer = User::factory()->create();
    $customer->syncTranslation(['name' => 'Review Customer'], 'en');

    return compact('store', 'product', 'customer');
}

function createDeliveredOrder(User $customer, Product $product, Store $store): Order
{
    $order = Order::create([
        'user_id' => $customer->id,
        'total_amount' => 25,
        'status' => 'delivered',
        'payment_method' => 'cod',
        'payment_status' => 'paid',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'product_name' => $product->name,
        'price' => 25,
        'quantity' => 1,
    ]);

    return $order;
}

test('customer who purchased a product can submit a review', function () {
    ['store' => $store, 'product' => $product, 'customer' => $customer] = createReviewFixture();
    createDeliveredOrder($customer, $product, $store);

    $response = $this->actingAs($customer, 'web')->post(route('products.reviews.store', $product), [
        'rating' => 5,
        'title' => 'Excellent product',
        'review' => 'Very happy with the quality.',
    ]);

    $response->assertRedirect();

    expect(ProductReview::where('product_id', $product->id)->where('user_id', $customer->id)->exists())->toBeTrue();
    expect(ProductReview::where('product_id', $product->id)->where('user_id', $customer->id)->first()->rating)->toBe(5);
});

test('customer who did not purchase a product cannot submit a review', function () {
    ['product' => $product, 'customer' => $customer] = createReviewFixture();

    $this->actingAs($customer, 'web')->post(route('products.reviews.store', $product), [
        'rating' => 3,
        'title' => 'Blocked review',
        'review' => 'This should not save.',
    ])->assertForbidden();

    expect(ProductReview::count())->toBe(0);
});

test('public product page shows review summary and customer feedback', function () {
    ['store' => $store, 'product' => $product, 'customer' => $customer] = createReviewFixture();
    createDeliveredOrder($customer, $product, $store);

    ProductReview::create([
        'product_id' => $product->id,
        'user_id' => $customer->id,
        'rating' => 4,
        'title' => 'Solid choice',
        'review' => 'Works as expected.',
    ]);

    $this->get(route('frontend.products.show', $product))
        ->assertOk()
        ->assertSee('4.0/5 rating')
        ->assertSee('Solid choice')
        ->assertSee('Works as expected.');
});
