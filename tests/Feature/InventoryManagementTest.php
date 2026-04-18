<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Role;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreCategoryTranslation;
use App\Models\StoreTranslation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Role::ensureDefaults();
});

function createInventoryStoreOwner(): array
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
        'name' => 'Inventory Store',
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
    $owner->syncTranslation(['name' => 'Store Owner'], 'en');

    return compact('store', 'owner');
}

function createInventoryProduct(Store $store, int $stock = 5): Product
{
    $category = Category::create();

    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 20,
        'offer_price' => null,
        'stock' => $stock,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Inventory Product',
        'description' => 'Description',
    ]);

    return $product;
}

test('store owner can add stock and movement is logged', function () {
    ['store' => $store, 'owner' => $owner] = createInventoryStoreOwner();
    $product = createInventoryProduct($store, 5);

    $response = $this->actingAs($owner, 'store')->post(route('store.products.adjust-stock', $product), [
        'direction' => 'in',
        'quantity' => 3,
        'reason' => 'Restock shipment',
    ]);

    $response->assertRedirect(route('store.products'));
    expect($product->fresh()->stock)->toBe(8);
    expect(StockMovement::where('product_id', $product->id)->latest('id')->first()->reason)->toBe('Restock shipment');
});

test('store owner can reduce stock and movement is logged', function () {
    ['store' => $store, 'owner' => $owner] = createInventoryStoreOwner();
    $product = createInventoryProduct($store, 6);

    $response = $this->actingAs($owner, 'store')->post(route('store.products.adjust-stock', $product), [
        'direction' => 'out',
        'quantity' => 2,
        'reason' => 'Damaged items',
    ]);

    $response->assertRedirect(route('store.products'));
    expect($product->fresh()->stock)->toBe(4);
    expect(StockMovement::where('product_id', $product->id)->latest('id')->first()->direction)->toBe('out');
});

test('admin can adjust product stock from admin product list', function () {
    $admin = User::create([
        'role_id' => Role::query()->where('name', 'admin')->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $admin->syncTranslation(['name' => 'Admin User'], 'en');

    ['store' => $store] = createInventoryStoreOwner();
    $product = createInventoryProduct($store, 4);

    $response = $this->actingAs($admin, 'admin')->post(route('admin.products.adjust-stock', $product), [
        'direction' => 'in',
        'quantity' => 6,
        'reason' => 'Inventory correction',
    ]);

    $response->assertRedirect(route('admin.products.index'));
    expect($product->fresh()->stock)->toBe(10);
    expect(StockMovement::where('product_id', $product->id)->latest('id')->first()->reason)->toBe('Inventory correction');
});
