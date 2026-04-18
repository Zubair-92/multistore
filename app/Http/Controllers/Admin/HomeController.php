<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $lowStockThreshold = 5;

        $metrics = [
            'stores_total' => Store::count(),
            'stores_approved' => Store::where('approved', true)->count(),
            'products_total' => Product::count(),
            'products_low_stock' => Product::where('stock', '<=', $lowStockThreshold)->count(),
            'customers_total' => User::whereHas('role', fn ($query) => $query->where('name', 'user'))->count(),
            'orders_total' => Order::count(),
            'orders_pending' => Order::where('status', 'pending')->count(),
            'revenue_total' => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
            'revenue_this_month' => Order::where('status', '!=', 'cancelled')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount'),
        ];

        $recentOrders = Order::with(['user.translation', 'items.store.translation'])
            ->latestFirst()
            ->take(8)
            ->get();

        $lowStockProducts = Product::with(['translations', 'translation', 'store.translations', 'store.translation'])
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $topStores = OrderItem::query()
            ->selectRaw('store_id, SUM(price * quantity) as revenue_total, SUM(quantity) as items_sold')
            ->whereHas('order', fn ($query) => $query->where('status', '!=', 'cancelled'))
            ->groupBy('store_id')
            ->orderByDesc('revenue_total')
            ->with(['store.translations', 'store.translation'])
            ->take(5)
            ->get();

        return view('admin.home', compact('metrics', 'recentOrders', 'lowStockProducts', 'topStores', 'lowStockThreshold'));
    }
}
