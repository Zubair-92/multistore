<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerifyEmailController;

use App\Http\Controllers\Frontend\HomePageController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\CustomerController;
use App\Http\Controllers\Frontend\StorePageController;
use App\Http\Controllers\Frontend\StoreProductController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Frontend\ProductReviewController;

use App\Http\Controllers\StoreAuth\StoreLoginController;
use App\Http\Controllers\StoreAuth\StoreRegisterController;

use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\Auth\AdminRegisterController;

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\StoreCategoryController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SubadminController;
use App\Http\Controllers\Admin\CouponController;

/**
 * force logout
 */
Route::get('/force-logout', function () {
    Auth::guard('web')->logout();
    Auth::guard('store')->logout();
    Auth::guard('admin')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login'); // or register page
});

/*
|--------------------------------------------------------------------------
| Locale 
|--------------------------------------------------------------------------
*/
Route::get('/change-language/{locale}', function ($locale) {
    if (! in_array($locale, ['en', 'ar'])) {
        abort(400);
    }

    session(['locale' => $locale]);

    return back();
})->name('change.language');


/*
|--------------------------------------------------------------------------
| FRONTEND HOME
|--------------------------------------------------------------------------
*/
Route::name('frontend.')->group(function () { 
    Route::get('/', [HomePageController::class, 'index'])->name('home');
    Route::get('/products', [HomePageController::class, 'products'])->name('products.index');
    Route::get('/stores', [HomePageController::class, 'stores'])->name('stores.index');
    Route::get('/products/{product}', [HomePageController::class, 'showProduct'])->name('products.show');
    Route::get('/stores/{store}', [HomePageController::class, 'showStore'])->name('stores.show');
});

/*
|--------------------------------------------------------------------------
| CART (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

/*
|--------------------------------------------------------------------------
| USER AUTH (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| USER AUTH LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| USER PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/profile');
    })->name('dashboard');

    Route::get('/verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/orders/{order}', [ProfileController::class, 'showOrder'])->name('profile.orders.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth:web', 'role:user'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/place-order', [CheckoutController::class, 'placeOrder'])->name('placeorder');
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('products.reviews.store');
});

/*
|--------------------------------------------------------------------------
| STORE AUTH (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:store')->prefix('store')->group(function () {

    Route::get('/login', [StoreLoginController::class, 'showLoginForm'])->name('store.login');
    Route::post('/login', [StoreLoginController::class, 'login'])->name('store.login.post');

    Route::get('/register', [StoreRegisterController::class, 'showRegistrationForm'])->name('store.register');
    Route::post('/register', [StoreRegisterController::class, 'register'])->name('store.register.post');

});

/*
|--------------------------------------------------------------------------
| STORE PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:store', 'role:store', 'store.approved'])->prefix('store')->group(function () {

    Route::get('/profile', [StorePageController::class, 'profile'])->name('store.profile');
    Route::patch('/profile', [StorePageController::class, 'updateProfile'])->name('store.profile.update');
    Route::get('/products', [StoreProductController::class, 'index'])->name('store.products');
    Route::get('/products/create', [StoreProductController::class, 'create'])->name('store.products.create');
    Route::post('/products', [StoreProductController::class, 'store'])->name('store.products.store');
    Route::post('/products/{product}/adjust-stock', [StoreProductController::class, 'adjustStock'])->name('store.products.adjust-stock');
    Route::get('/products/{product}/edit', [StoreProductController::class, 'edit'])->name('store.products.edit');
    Route::put('/products/{product}', [StoreProductController::class, 'update'])->name('store.products.update');
    Route::delete('/products/{product}', [StoreProductController::class, 'destroy'])->name('store.products.destroy');
    Route::get('/orders', [StorePageController::class, 'orders'])->name('store.orders');
    Route::get('/orders/export', [StorePageController::class, 'exportOrders'])->name('store.orders.export');
    Route::get('/orders/{order}', [StorePageController::class, 'showOrder'])->name('store.orders.show');
    Route::patch('/orders/{order}', [StorePageController::class, 'updateOrder'])->name('store.orders.update');

    Route::post('/logout', [StoreLoginController::class, 'logout'])->name('store.logout');

});

/*
|--------------------------------------------------------------------------
| ADMIN AUTH (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest:admin')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('auth.login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.post');

    Route::get('/register', [AdminRegisterController::class, 'showRegistrationForm'])->name('auth.register');
    Route::post('/register', [AdminRegisterController::class, 'register'])->name('register.post');

});

/*
|--------------------------------------------------------------------------
| ADMIN PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin', 'role:admin,sub_admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::middleware('admin.permission:catalog')->group(function () {
        Route::resource('categories', CategoryController::class);
        Route::resource('subcategories', SubcategoryController::class);
        Route::resource('storecategories', StoreCategoryController::class);
        Route::resource('productcategories', ProductCategoryController::class);
    });

    Route::middleware('admin.permission:stores')->group(function () {
        Route::resource('stores', StoreController::class);

        Route::post('stores/toggle-approve', [StoreController::class, 'toggleApprove'])
            ->name('stores.toggleApprove');

        Route::post('stores/bulk-action', [StoreController::class, 'bulkAction'])
            ->name('stores.bulkAction');
    });

    Route::middleware('admin.permission:products')->group(function () {
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust-stock');
        Route::resource('coupons', CouponController::class);
    });

    Route::middleware('admin.permission:orders')->group(function () {
        Route::resource('orders', OrderController::class);
        Route::get('orders-export', [OrderController::class, 'export'])->name('orders.export');
    });

    Route::middleware('admin.permission:subadmins')->group(function () {
        Route::resource('subadmins', SubadminController::class)->parameters(['subadmins' => 'subadmin']);
    });



    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

});


Route::get('/pos', function () { return view('pos'); })->name('pos');
