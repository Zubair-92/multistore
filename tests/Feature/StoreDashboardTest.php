<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTranslation;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreCategoryTranslation;
use App\Models\StoreTranslation;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Role::ensureDefaults();
});

function createStoreOwnerFixture(): array
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
        'name' => 'Original Store',
        'description' => 'Original description',
        'address' => 'Old Riyadh',
    ]);

    StoreTranslation::create([
        'store_id' => $store->id,
        'locale' => 'ar',
        'name' => 'متجر',
        'description' => 'وصف',
        'address' => 'الرياض',
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

test('store owner can update store dashboard profile data', function () {
    ['store' => $store, 'owner' => $owner] = createStoreOwnerFixture();

    $response = $this->actingAs($owner, 'store')->patch(route('store.profile.update'), [
        'owner_name' => 'Updated Owner',
        'owner_email' => 'updated-owner@example.com',
        'store_email' => 'updated-store@example.com',
        'phone' => '0555555555',
        'name_en' => 'Updated Store',
        'name_ar' => 'متجر محدث',
        'desc_en' => 'Updated description',
        'desc_ar' => 'وصف محدث',
        'addr_en' => 'New Riyadh',
        'addr_ar' => 'الرياض الجديدة',
    ]);

    $response->assertRedirect(route('store.profile'));

    expect($owner->fresh()->email)->toBe('updated-owner@example.com');
    expect($owner->fresh()->name)->toBe('Updated Owner');
    expect($store->fresh()->email)->toBe('updated-store@example.com');
    expect($store->fresh()->phone)->toBe('0555555555');
    expect($store->fresh()->translations()->where('locale', 'en')->first()->name)->toBe('Updated Store');
    expect($store->fresh()->translations()->where('locale', 'en')->first()->address)->toBe('New Riyadh');
});

test('store products page only shows products for the logged in store', function () {
    ['store' => $store, 'owner' => $owner] = createStoreOwnerFixture();

    $category = Category::create();

    $ownProduct = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 10,
        'offer_price' => null,
        'stock' => 5,
        'image' => 'products/own.png',
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $ownProduct->id,
        'locale' => 'en',
        'name' => 'Own Product',
        'description' => 'Own description',
    ]);

    $otherFixture = createStoreOwnerFixture();
    $otherStore = $otherFixture['store'];

    $otherProduct = Product::create([
        'store_id' => $otherStore->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 20,
        'offer_price' => null,
        'stock' => 4,
        'image' => 'products/other.png',
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $otherProduct->id,
        'locale' => 'en',
        'name' => 'Other Product',
        'description' => 'Other description',
    ]);

    $this->actingAs($owner, 'store')
        ->get(route('store.products'))
        ->assertOk()
        ->assertSee('Own Product')
        ->assertDontSee('Other Product');
});

test('store owner can create a product from the vendor panel', function () {
    ['store' => $store, 'owner' => $owner] = createStoreOwnerFixture();

    $category = Category::create();
    $subcategory = SubCategory::create(['category_id' => $category->id]);
    $productCategory = ProductCategory::create();

    $response = $this->actingAs($owner, 'store')->post(route('store.products.store'), [
        'category_id' => $category->id,
        'subcategory_id' => $subcategory->id,
        'product_category_id' => $productCategory->id,
        'name_en' => 'Vendor Tea',
        'name_ar' => 'شاي المتجر',
        'description_en' => 'Fresh tea leaves',
        'description_ar' => 'شاي طازج',
        'price' => 24.5,
        'offer_price' => 19.99,
        'stock' => 15,
        'status' => 1,
    ]);

    $response->assertRedirect(route('store.products'));

    $product = Product::query()->latest('id')->first();

    expect($product)->not->toBeNull();
    expect($product->store_id)->toBe($store->id);
    expect((float) $product->price)->toBe(24.5);
    expect($product->translations()->where('locale', 'en')->first()->name)->toBe('Vendor Tea');
    expect($product->translations()->where('locale', 'ar')->first()->name)->toBe('شاي المتجر');
});

test('store owner can update their own product', function () {
    ['store' => $store, 'owner' => $owner] = createStoreOwnerFixture();

    $category = Category::create();
    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 10,
        'offer_price' => null,
        'stock' => 5,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Old Name',
        'description' => 'Old description',
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'ar',
        'name' => 'اسم قديم',
        'description' => 'وصف قديم',
    ]);

    $response = $this->actingAs($owner, 'store')->put(route('store.products.update', $product), [
        'category_id' => $category->id,
        'name_en' => 'Updated Name',
        'name_ar' => 'اسم محدث',
        'description_en' => 'Updated description',
        'description_ar' => 'وصف محدث',
        'price' => 14.75,
        'offer_price' => 12.25,
        'stock' => 8,
        'status' => 0,
    ]);

    $response->assertRedirect(route('store.products'));

    expect((float) $product->fresh()->price)->toBe(14.75);
    expect((int) $product->fresh()->stock)->toBe(8);
    expect((bool) $product->fresh()->status)->toBeFalse();
    expect($product->fresh()->translations()->where('locale', 'en')->first()->name)->toBe('Updated Name');
    expect($product->fresh()->translations()->where('locale', 'ar')->first()->name)->toBe('اسم محدث');
});

test('store owner cannot edit another stores product', function () {
    ['owner' => $owner] = createStoreOwnerFixture();

    $otherFixture = createStoreOwnerFixture();
    $otherStore = $otherFixture['store'];
    $category = Category::create();

    $product = Product::create([
        'store_id' => $otherStore->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 10,
        'offer_price' => null,
        'stock' => 5,
        'image' => null,
        'status' => true,
    ]);

    $this->actingAs($owner, 'store')
        ->get(route('store.products.edit', $product))
        ->assertNotFound();
});

test('store owner can delete their own product', function () {
    ['store' => $store, 'owner' => $owner] = createStoreOwnerFixture();

    $category = Category::create();
    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 10,
        'offer_price' => null,
        'stock' => 5,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Delete Me',
        'description' => 'To delete',
    ]);

    $response = $this->actingAs($owner, 'store')
        ->delete(route('store.products.destroy', $product));

    $response->assertRedirect(route('store.products'));
    expect(Product::find($product->id))->toBeNull();
    expect(ProductTranslation::where('product_id', $product->id)->count())->toBe(0);
});

test('store products page supports filters and pagination', function () {
    ['store' => $store, 'owner' => $owner] = createStoreOwnerFixture();
    $category = Category::create();

    foreach (range(1, 13) as $index) {
        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'subcategory_id' => null,
            'product_category_id' => null,
            'price' => 10 + $index,
            'offer_price' => null,
            'stock' => $index === 13 ? 0 : 5,
            'image' => null,
            'status' => $index !== 13,
        ]);

        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => 'en',
            'name' => 'Catalog Item '.$index,
            'description' => 'Description '.$index,
        ]);
    }

    $this->actingAs($owner, 'store')
        ->get(route('store.products', ['search' => 'Catalog Item 1', 'status' => 'active']))
        ->assertOk()
        ->assertSee('Catalog Item 1')
        ->assertDontSee('Catalog Item 13');

    $this->actingAs($owner, 'store')
        ->get(route('store.products', ['page' => 2]))
        ->assertOk()
        ->assertSee('Reset')
        ->assertSee('?page=1', false);
});
