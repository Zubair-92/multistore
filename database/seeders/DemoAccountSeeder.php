<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreCategoryTranslation;
use App\Models\StoreTranslation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    protected function createStoreWithOwner(
        StoreCategory $storeCategory,
        Category $category,
        array $storeData,
        array $ownerData,
        array $productData
    ): void {
        $store = Store::query()->updateOrCreate(
            ['email' => $storeData['email']],
            [
                'store_category_id' => $storeCategory->id,
                'phone' => $storeData['phone'],
                'logo' => $storeData['logo'] ?? null,
                'approved' => true,
            ]
        );

        StoreTranslation::query()->updateOrCreate(
            [
                'store_id' => $store->id,
                'locale' => 'en',
            ],
            [
                'name' => $storeData['name'],
                'description' => $storeData['description'],
                'address' => $storeData['address'],
            ]
        );

        $storeOwner = User::query()->updateOrCreate(
            ['email' => $ownerData['email']],
            [
                'role_id' => Role::query()->where('name', 'store')->value('id'),
                'store_id' => $store->id,
                'password' => Hash::make($ownerData['password']),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $storeOwner->syncTranslation(['name' => $ownerData['name']], 'en');

        $product = Product::query()->updateOrCreate(
            [
                'store_id' => $store->id,
                'category_id' => $category->id,
                'subcategory_id' => null,
                'product_category_id' => null,
            ],
            [
                'price' => $productData['price'],
                'offer_price' => $productData['offer_price'],
                'stock' => $productData['stock'],
                'image' => null,
                'status' => true,
            ]
        );

        ProductTranslation::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'locale' => 'en',
            ],
            [
                'name' => $productData['name'],
                'description' => $productData['description'],
            ]
        );
    }

    public function run(): void
    {
        Role::ensureDefaults();

        $storeCategory = StoreCategory::query()->firstOrCreate([]);

        StoreCategoryTranslation::query()->updateOrCreate(
            [
                'store_category_id' => $storeCategory->id,
                'locale' => 'en',
            ],
            [
                'store_category' => 'General Store',
            ]
        );

        $category = Category::query()->firstOrCreate([]);

        CategoryTranslation::query()->updateOrCreate(
            [
                'category_id' => $category->id,
                'locale' => 'en',
            ],
            [
                'category' => 'General',
            ]
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@multistore.com'],
            [
                'role_id' => Role::query()->where('name', 'admin')->value('id'),
                'store_id' => null,
                'password' => Hash::make('admin@admin'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->syncTranslation(['name' => 'Main Admin'], 'en');

        $subadmin = User::query()->updateOrCreate(
            ['email' => 'subadmin@multistore.com'],
            [
                'role_id' => Role::query()->where('name', 'sub_admin')->value('id'),
                'store_id' => null,
                'password' => Hash::make('subadmin@admin'),
                'is_active' => true,
                'email_verified_at' => now(),
                'admin_permissions' => ['catalog', 'products', 'orders'],
            ]
        );
        $subadmin->syncTranslation(['name' => 'Platform Subadmin'], 'en');

        $customer = User::query()->updateOrCreate(
            ['email' => 'subair@gmail.com'],
            [
                'role_id' => Role::query()->where('name', 'user')->value('id'),
                'store_id' => null,
                'password' => Hash::make('customer@admin'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $customer->syncTranslation([
            'name' => 'Subair Customer',
            'address' => 'West Bay, Doha',
        ], 'en');

        $this->createStoreWithOwner(
            $storeCategory,
            $category,
            [
                'email' => 'rahath@rahath.com',
                'phone' => '+97451111111',
                'logo' => 'stores/rahath.png',
                'name' => 'Rahath Store',
                'description' => 'Rahath marketplace demo store.',
                'address' => 'Doha, Qatar',
            ],
            [
                'email' => 'admin@rahath.com',
                'password' => 'admin@rahath',
                'name' => 'Rahath Store Owner',
            ],
            [
                'name' => 'Rahath Demo Product',
                'description' => 'Demo product for Rahath store.',
                'price' => 99.00,
                'offer_price' => 79.00,
                'stock' => 25,
            ]
        );

        $this->createStoreWithOwner(
            $storeCategory,
            $category,
            [
                'email' => 'benice@benice.com',
                'phone' => '+97452222222',
                'logo' => 'stores/benice.png',
                'name' => 'Benice Store',
                'description' => 'Benice marketplace demo store.',
                'address' => 'Lusail, Qatar',
            ],
            [
                'email' => 'admin@benice.com',
                'password' => 'admin@benice',
                'name' => 'Benice Store Owner',
            ],
            [
                'name' => 'Benice Demo Product',
                'description' => 'Demo product for Benice store.',
                'price' => 119.00,
                'offer_price' => 89.00,
                'stock' => 18,
            ]
        );
    }
}
