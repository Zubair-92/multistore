<?php

use App\Models\Category;
use App\Models\CategoryTranslation;
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

function createCatalogStore(string $name, bool $approved = true): Store
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

function createCatalogCategory(string $name): Category
{
    $category = Category::create();

    CategoryTranslation::create([
        'category_id' => $category->id,
        'locale' => 'en',
        'category' => $name,
    ]);

    return $category;
}

function createCatalogProduct(Store $store, Category $category, array $attributes = []): Product
{
    $product = Product::create(array_merge([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 30,
        'offer_price' => null,
        'stock' => 10,
        'image' => null,
        'status' => true,
    ], $attributes));

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => $attributes['name_en'] ?? ('Product ' . $product->id),
        'description' => $attributes['description_en'] ?? 'Catalog description',
    ]);

    return $product;
}

test('homepage only shows active products from approved stores', function () {
    $category = createCatalogCategory('Groceries');

    $approvedStore = createCatalogStore('Approved Store', true);
    $pendingStore = createCatalogStore('Pending Store', false);

    createCatalogProduct($approvedStore, $category, ['name_en' => 'Visible Product']);
    createCatalogProduct($approvedStore, $category, ['name_en' => 'Inactive Product', 'status' => false]);
    createCatalogProduct($pendingStore, $category, ['name_en' => 'Pending Store Product']);

    $this->get(route('frontend.home'))
        ->assertOk()
        ->assertSee('Visible Product')
        ->assertDontSee('Inactive Product')
        ->assertDontSee('Pending Store Product');
});

test('homepage catalog filters products by search and stock', function () {
    $category = createCatalogCategory('Beverages');
    $store = createCatalogStore('Tea House', true);

    createCatalogProduct($store, $category, [
        'name_en' => 'Mint Tea',
        'description_en' => 'Fresh mint tea',
        'stock' => 8,
    ]);

    createCatalogProduct($store, $category, [
        'name_en' => 'Black Coffee',
        'description_en' => 'Dark roast coffee',
        'stock' => 0,
    ]);

    $this->get(route('frontend.home', ['search' => 'Mint', 'stock' => 'in_stock']))
        ->assertOk()
        ->assertSee('Mint Tea')
        ->assertDontSee('Black Coffee');
});

test('homepage catalog can filter by category and store', function () {
    $electronics = createCatalogCategory('Electronics');
    $fashion = createCatalogCategory('Fashion');

    $alphaStore = createCatalogStore('Alpha Store', true);
    $betaStore = createCatalogStore('Beta Store', true);

    createCatalogProduct($alphaStore, $electronics, ['name_en' => 'Laptop Bag']);
    createCatalogProduct($betaStore, $fashion, ['name_en' => 'Summer Hat']);

    $this->get(route('frontend.home', [
        'category' => $electronics->id,
        'store' => $alphaStore->id,
    ]))
        ->assertOk()
        ->assertSee('Laptop Bag')
        ->assertDontSee('Summer Hat');
});
