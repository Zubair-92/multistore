<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StorePageController extends Controller
{
    public function profile()
    {
        $store = auth()->user()->store()->with(['translations', 'storeCategory.translation'])->firstOrFail();
        $storeId = $store->id;
        $lowStockThreshold = 5;

        $revenueQuery = OrderItem::query()
            ->where('store_id', $storeId)
            ->whereHas('order', fn ($query) => $query->where('status', '!=', 'cancelled'));

        $metrics = [
            'products_total' => Product::where('store_id', $storeId)->count(),
            'products_active' => Product::where('store_id', $storeId)->where('status', true)->count(),
            'products_low_stock' => Product::where('store_id', $storeId)->where('stock', '<=', $lowStockThreshold)->count(),
            'orders_total' => Order::whereHas('items', fn ($query) => $query->where('store_id', $storeId))->count(),
            'orders_pending' => Order::where('status', 'pending')
                ->whereHas('items', fn ($query) => $query->where('store_id', $storeId))
                ->count(),
            'revenue_total' => (float) (clone $revenueQuery)->selectRaw('COALESCE(SUM(price * quantity), 0) as revenue_total')->value('revenue_total'),
            'revenue_this_month' => (float) (clone $revenueQuery)
                ->whereHas('order', function ($query) {
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                })
                ->selectRaw('COALESCE(SUM(price * quantity), 0) as revenue_total')
                ->value('revenue_total'),
        ];

        $recentProducts = Product::with(['translations'])
            ->where('store_id', $storeId)
            ->latest('created_at')
            ->take(5)
            ->get();

        $lowStockProducts = Product::with(['translations'])
            ->where('store_id', $storeId)
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $recentOrders = Order::with(['user.translation', 'items'])
            ->whereHas('items', fn ($query) => $query->where('store_id', $storeId))
            ->latest('created_at')
            ->take(5)
            ->get();

        return view('frontend.store.profile', compact('store', 'metrics', 'recentProducts', 'recentOrders', 'lowStockProducts', 'lowStockThreshold'));
    }

    public function orders(Request $request)
    {
        $store = auth()->user()->store;
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'payment_status' => ['nullable', Rule::in(Order::PAYMENT_STATUSES)],
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $ordersQuery = Order::with(['items', 'user.translation'])
            ->whereHas('items', function ($q) use ($store) {
                $q->where('store_id', $store->id);
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $paymentStatus) => $query->where('payment_status', $paymentStatus))
            ->when($filters['date_from'] ?? null, fn ($query, $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn ($query, $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id', $search)
                        ->orWhereHas('user.translations', fn ($translationQuery) => $translationQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
                });
            });

        $summary = [
            'orders_count' => (clone $ordersQuery)->count(),
            'revenue_total' => (float) OrderItem::query()
                ->where('store_id', $store->id)
                ->whereHas('order', function ($query) use ($filters) {
                    $query->when($filters['status'] ?? null, fn ($innerQuery, $status) => $innerQuery->where('status', $status))
                        ->when($filters['payment_status'] ?? null, fn ($innerQuery, $paymentStatus) => $innerQuery->where('payment_status', $paymentStatus))
                        ->when($filters['date_from'] ?? null, fn ($innerQuery, $dateFrom) => $innerQuery->whereDate('created_at', '>=', $dateFrom))
                        ->when($filters['date_to'] ?? null, fn ($innerQuery, $dateTo) => $innerQuery->whereDate('created_at', '<=', $dateTo))
                        ->where('status', '!=', 'cancelled');
                })
                ->selectRaw('COALESCE(SUM(price * quantity), 0) as revenue_total')
                ->value('revenue_total'),
            'paid_orders' => (clone $ordersQuery)->where('payment_status', 'paid')->count(),
            'pending_orders' => (clone $ordersQuery)->where('status', 'pending')->count(),
        ];

        $orders = $ordersQuery
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('frontend.store.orders', compact('orders', 'filters', 'summary'));
    }

    public function exportOrders(Request $request)
    {
        $store = $request->user('store')->store;
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'payment_status' => ['nullable', Rule::in(Order::PAYMENT_STATUSES)],
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $orders = Order::with(['items', 'user.translation'])
            ->whereHas('items', fn ($query) => $query->where('store_id', $store->id))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $paymentStatus) => $query->where('payment_status', $paymentStatus))
            ->when($filters['date_from'] ?? null, fn ($query, $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn ($query, $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id', $search)
                        ->orWhereHas('user.translations', fn ($translationQuery) => $translationQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=store-orders-report.csv',
        ];

        $callback = function () use ($orders, $store) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order ID', 'Customer', 'Customer Email', 'Store Total', 'Status', 'Payment Status', 'Created At']);

            foreach ($orders as $order) {
                $storeTotal = $order->items
                    ->where('store_id', $store->id)
                    ->sum(fn ($item) => $item->price * $item->quantity);

                fputcsv($handle, [
                    $order->id,
                    $order->user->name ?? $order->user->email,
                    $order->user->email,
                    $storeTotal,
                    $order->status,
                    $order->payment_status,
                    $order->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function products()
    {
        $products = Product::with(['translations', 'category.translation', 'subcategory.translation'])
            ->where('store_id', auth()->user()->store_id)
            ->latest('created_at')
            ->get();

        return view('frontend.store.products', compact('products'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $store = $user->store()->with('translations')->firstOrFail();

        $validated = $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'store_email' => ['required', 'email', Rule::unique('stores', 'email')->ignore($store->id)],
            'phone' => ['required', 'string', 'max:20'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'desc_en' => ['nullable', 'string'],
            'desc_ar' => ['nullable', 'string'],
            'addr_en' => ['nullable', 'string', 'max:255'],
            'addr_ar' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($user, $store, $validated) {
            $user->update([
                'email' => $validated['owner_email'],
            ]);

            $user->syncTranslation([
                'name' => $validated['owner_name'],
            ], 'en');

            $store->update([
                'email' => $validated['store_email'],
                'phone' => $validated['phone'],
            ]);

            $store->translations()->updateOrCreate(
                ['locale' => 'en'],
                [
                    'name' => $validated['name_en'],
                    'description' => $validated['desc_en'] ?? null,
                    'address' => $validated['addr_en'] ?? null,
                ]
            );

            $store->translations()->updateOrCreate(
                ['locale' => 'ar'],
                [
                    'name' => $validated['name_ar'],
                    'description' => $validated['desc_ar'] ?? null,
                    'address' => $validated['addr_ar'] ?? null,
                ]
            );
        });

        return redirect()
            ->route('store.profile')
            ->with('success', 'Store profile updated successfully.');
    }

    public function showOrder(Order $order)
    {
        $storeId = auth()->user()->store_id;

        abort_unless(
            $order->items()->where('store_id', $storeId)->exists(),
            404
        );

        $order->load([
            'user.translation',
            'items.product.translations',
            'items.store.translation',
        ]);

        return view('frontend.store.show-order', [
            'order' => $order,
            'storeId' => $storeId,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function updateOrder(Request $request, Order $order)
    {
        $storeId = auth()->user()->store_id;
        $previousStatus = $order->status;

        abort_unless(
            $order->items()->where('store_id', $storeId)->exists(),
            404
        );

        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $order->syncInventoryForStatusChange($validated['status']);
        $order->save();

        if ($previousStatus !== $order->status) {
            $order->user?->notify(new OrderStatusUpdatedNotification($order));
        }

        return redirect()
            ->route('store.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }
}
