<?php

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreCategoryTranslation;
use App\Models\StoreTranslation;

beforeEach(function () {
    Role::ensureDefaults();
});

function createPublicStore(string $name = 'Public Store', bool $approved = true): Store
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
        'name' => $name,
        'description' => $name . ' description',
        'address' => 'Riyadh',
    ]);

    return $store;
}

function createPublicCategory(string $name = 'Category'): Category
{
    $category = Category::create();

    CategoryTranslation::create([
        'category_id' => $category->id,
        'locale' => 'en',
        'category' => $name,
    ]);

    return $category;
}

function createPublicProduct(Store $store, Category $category, string $name = 'Public Product', bool $active = true): Product
{
    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 20,
        'offer_price' => 15,
        'stock' => 6,
        'image' => null,
        'status' => $active,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => $name,
        'description' => $name . ' description',
    ]);

    return $product;
}

test('public product page shows approved active product details', function () {
    $store = createPublicStore();
    $category = createPublicCategory('Coffee');
    $product = createPublicProduct($store, $category, 'Signature Coffee');
    createPublicProduct($store, $category, 'Backup Coffee');

    $this->get(route('frontend.products.show', $product))
        ->assertOk()
        ->assertSee('Signature Coffee')
        ->assertSee('Visit Public Store')
        ->assertSee('Related Products');
});

test('public products index shows marketplace products', function () {
    $store = createPublicStore();
    $category = createPublicCategory('Hardware');
    createPublicProduct($store, $category, 'Hammer Pro');

    $this->get(route('frontend.products.index'))
        ->assertOk()
        ->assertSee('Hammer Pro')
        ->assertSee('Catalog');
});

test('public product page hides inactive products', function () {
    $store = createPublicStore();
    $category = createPublicCategory();
    $product = createPublicProduct($store, $category, 'Hidden Product', false);

    $this->get(route('frontend.products.show', $product))
        ->assertNotFound();
});

test('public store page shows only active products from approved store', function () {
    $store = createPublicStore('Showcase Store');
    $category = createPublicCategory();
    createPublicProduct($store, $category, 'Visible Product', true);
    createPublicProduct($store, $category, 'Hidden Product', false);

    $this->get(route('frontend.stores.show', $store))
        ->assertOk()
        ->assertSee('Showcase Store')
        ->assertSee('Visible Product')
        ->assertDontSee('Hidden Product');
});

test('public stores index shows approved stores', function () {
    createPublicStore('Alpha Store', true);
    createPublicStore('Pending Store', false);

    $this->get(route('frontend.stores.index'))
        ->assertOk()
        ->assertSee('Alpha Store')
        ->assertDontSee('Pending Store');
});

test('public store page hides unapproved stores', function () {
    $store = createPublicStore('Pending Store', false);

    $this->get(route('frontend.stores.show', $store))
        ->assertNotFound();
});

test('homepage shows active coupons and hides inactive ones', function () {
    $store = createPublicStore();
    $category = createPublicCategory();
    createPublicProduct($store, $category, 'Deal Product');

    Coupon::create([
        'code' => 'SAVE10',
        'type' => 'percent',
        'value' => 10,
        'minimum_amount' => 50,
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
        'usage_limit' => 100,
        'used_count' => 0,
        'is_active' => true,
    ]);

    Coupon::create([
        'code' => 'OLDDEAL',
        'type' => 'fixed',
        'value' => 5,
        'minimum_amount' => 20,
        'starts_at' => now()->subDays(10),
        'expires_at' => now()->subDay(),
        'usage_limit' => 100,
        'used_count' => 0,
        'is_active' => true,
    ]);

    $this->get(route('frontend.home'))
        ->assertOk()
        ->assertSee('Marketplace Deals')
        ->assertSee('SAVE10')
        ->assertDontSee('OLDDEAL');
});

test('viewing a product stores it in recently viewed list', function () {
    $store = createPublicStore();
    $category = createPublicCategory();
    $firstProduct = createPublicProduct($store, $category, 'Viewed Product');
    $secondProduct = createPublicProduct($store, $category, 'Next Product');

    $this->get(route('frontend.products.show', $firstProduct))->assertOk();
    $this->get(route('frontend.products.show', $secondProduct))->assertOk();

    $this->get(route('frontend.home'))
        ->assertOk()
        ->assertSee('Recently Viewed')
        ->assertSee('Viewed Product')
        ->assertSee('Next Product');
});
