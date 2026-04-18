<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $orders = Order::with(['items.store.translation'])
            ->where('user_id', $request->user()->id)
            ->latestFirst()
            ->paginate(8, ['*'], 'orders_page')
            ->withQueryString();

        $wishlistProducts = $request->user()
            ->wishlistProducts()
            ->with([
                'translations',
                'translation',
                'store.translation',
                'category.translation',
            ])
            ->latest('wishlists.id')
            ->paginate(6, ['*'], 'wishlist_page')
            ->withQueryString();

        return view('frontend.customer.profile', [
            'orders' => $orders,
            'wishlistProducts' => $wishlistProducts,
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->fill([
            'email' => $validated['email'],
        ])->save();

        $user->syncTranslation([
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
        ]);

        return Redirect::to('/profile');
    }

    public function showOrder(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load([
            'user.translation',
            'items.product.translations',
            'items.store.translation',
        ]);

        return view('frontend.customer.show-order', compact('order'));
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
