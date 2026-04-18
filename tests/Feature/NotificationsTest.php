<?php

use App\Models\Cart;
use App\Models\CartItem;
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
use App\Notifications\LowStockAlertNotification;
use App\Notifications\NewOrderPlacedNotification;
use App\Notifications\OrderStatusUpdatedNotification;
use App\Notifications\StoreApprovedNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Role::ensureDefaults();
});

function createStoreOwnerForNotifications(bool $approved = false): array
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
        'name' => 'Notify Store',
        'description' => 'Description',
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

test('approving a store notifies the store owner', function () {
    Notification::fake();

    $admin = User::create([
        'role_id' => Role::query()->where('name', 'admin')->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    ['store' => $store, 'owner' => $owner] = createStoreOwnerForNotifications(false);

    $this->actingAs($admin, 'admin')->put(route('admin.stores.update', $store), [
        'store_category_id' => $store->store_category_id,
        'name_en' => 'Notify Store',
        'name_ar' => 'متجر',
        'desc_en' => 'Description',
        'desc_ar' => 'وصف',
        'email' => $store->email,
        'phone' => $store->phone,
        'approved' => 1,
    ])->assertRedirect(route('admin.stores.index'));

    Notification::assertSentTo($owner, StoreApprovedNotification::class);
});

test('placing an order sends customer store and low stock notifications', function () {
    Notification::fake();

    ['store' => $store, 'owner' => $owner] = createStoreOwnerForNotifications(true);
    $category = Category::create();
    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 25,
        'offer_price' => null,
        'stock' => 5,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Notify Product',
        'description' => 'Description',
    ]);

    $customer = User::factory()->create();

    $cart = Cart::create(['user_id' => $customer->id]);
    CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'price' => 25,
    ]);

    $this->actingAs($customer)->post('/checkout/place-order', [
        'delivery_name' => 'Customer Name',
        'delivery_email' => 'customer@example.com',
        'delivery_phone' => '0555555555',
        'delivery_address' => 'Riyadh Test Address',
        'payment_method' => 'cod',
    ])->assertOk();

    Notification::assertSentTo($customer, NewOrderPlacedNotification::class);
    Notification::assertSentTo($owner, NewOrderPlacedNotification::class);
    Notification::assertSentTo($owner, LowStockAlertNotification::class);
});

test('changing an order status notifies the customer', function () {
    Notification::fake();

    $admin = User::create([
        'role_id' => Role::query()->where('name', 'admin')->value('id'),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    ['store' => $store] = createStoreOwnerForNotifications(true);
    $customer = User::factory()->create();
    $category = Category::create();
    $product = Product::create([
        'store_id' => $store->id,
        'category_id' => $category->id,
        'subcategory_id' => null,
        'product_category_id' => null,
        'price' => 25,
        'offer_price' => null,
        'stock' => 4,
        'image' => null,
        'status' => true,
    ]);

    ProductTranslation::create([
        'product_id' => $product->id,
        'locale' => 'en',
        'name' => 'Notify Product',
        'description' => 'Description',
    ]);

    $order = Order::create([
        'user_id' => $customer->id,
        'total_amount' => 25,
        'status' => 'pending',
        'payment_method' => 'cod',
        'payment_status' => 'unpaid',
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'store_id' => $store->id,
        'product_name' => $product->name,
        'price' => 25,
        'quantity' => 1,
    ]);

    $this->actingAs($admin, 'admin')->patch(route('admin.orders.update', $order), [
        'status' => 'confirmed',
        'payment_status' => 'pending',
    ])->assertRedirect(route('admin.orders.show', $order));

    Notification::assertSentTo($customer, OrderStatusUpdatedNotification::class);
});
