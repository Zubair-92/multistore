<?php

use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\DemoAccountSeeder;

beforeEach(function () {
    Role::ensureDefaults();
});

test('demo account seeder creates default marketplace users and sample data', function () {
    $this->seed(DemoAccountSeeder::class);

    $admin = User::where('email', 'admin@multistore.com')->first();
    $subadmin = User::where('email', 'subadmin@multistore.com')->first();
    $customer = User::where('email', 'subair@gmail.com')->first();
    $rahathOwner = User::where('email', 'admin@rahath.com')->first();
    $beniceOwner = User::where('email', 'admin@benice.com')->first();
    $rahathStore = Store::where('email', 'rahath@rahath.com')->first();
    $beniceStore = Store::where('email', 'benice@benice.com')->first();
    $rahathProduct = Product::whereHas('translations', fn ($query) => $query->where('name', 'Rahath Demo Product'))->first();
    $beniceProduct = Product::whereHas('translations', fn ($query) => $query->where('name', 'Benice Demo Product'))->first();

    expect($admin)->not->toBeNull();
    expect($admin->role->name)->toBe('admin');

    expect($subadmin)->not->toBeNull();
    expect($subadmin->role->name)->toBe('sub_admin');

    expect($customer)->not->toBeNull();
    expect($customer->role->name)->toBe('user');

    expect($rahathOwner)->not->toBeNull();
    expect($rahathOwner->role->name)->toBe('store');

    expect($beniceOwner)->not->toBeNull();
    expect($beniceOwner->role->name)->toBe('store');

    expect($rahathStore)->not->toBeNull();
    expect($rahathStore->approved)->toBeTrue();

    expect($beniceStore)->not->toBeNull();
    expect($beniceStore->approved)->toBeTrue();

    expect($rahathProduct)->not->toBeNull();
    expect($rahathProduct->store_id)->toBe($rahathStore->id);

    expect($beniceProduct)->not->toBeNull();
    expect($beniceProduct->store_id)->toBe($beniceStore->id);
});
