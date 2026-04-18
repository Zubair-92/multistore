<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use App\Notifications\OrderStatusUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    protected function filteredOrdersQuery(array $filters)
    {
        return Order::with(['user.translation', 'items.store.translation'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, $paymentStatus) => $query->where('payment_status', $paymentStatus))
            ->when($filters['store_id'] ?? null, fn ($query, $storeId) => $query->whereHas('items', fn ($itemQuery) => $itemQuery->where('store_id', $storeId)))
            ->when($filters['date_from'] ?? null, fn ($query, $dateFrom) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($filters['date_to'] ?? null, fn ($query, $dateTo) => $query->whereDate('created_at', '<=', $dateTo))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id', $search)
                        ->orWhereHas('user.translations', fn ($translationQuery) => $translationQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
                });
            });
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'payment_status' => ['nullable', Rule::in(Order::PAYMENT_STATUSES)],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $ordersQuery = $this->filteredOrdersQuery($filters);

        $summary = [
            'orders_count' => (clone $ordersQuery)->count(),
            'revenue_total' => (float) (clone $ordersQuery)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'paid_orders' => (clone $ordersQuery)->where('payment_status', 'paid')->count(),
            'cancelled_orders' => (clone $ordersQuery)->where('status', 'cancelled')->count(),
        ];

        $orders = $ordersQuery
            ->latestFirst()
            ->paginate(12)
            ->withQueryString();

        $stores = Store::with('translation')->orderByDesc('id')->get();
        $statuses = Order::STATUSES;
        $paymentStatuses = Order::PAYMENT_STATUSES;

        return view('admin.orders.index', compact('orders', 'stores', 'statuses', 'paymentStatuses', 'filters', 'summary'));
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
            'payment_status' => ['nullable', Rule::in(Order::PAYMENT_STATUSES)],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $orders = $this->filteredOrdersQuery($filters)
            ->latestFirst()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=admin-orders-report.csv',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order ID', 'Customer', 'Customer Email', 'Stores', 'Total', 'Status', 'Payment Method', 'Payment Status', 'Created At']);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->user->name ?? $order->user->email,
                    $order->user->email,
                    $order->items->pluck('store.name')->filter()->unique()->join(', '),
                    $order->total_amount,
                    $order->status,
                    $order->payment_method,
                    $order->payment_status,
                    $order->created_at->toDateTimeString(),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Order $order)
    {
        $order->load([
            'user.translation',
            'items.product.translations',
            'items.store.translation',
        ]);

        $statuses = Order::STATUSES;

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    public function update(Request $request, Order $order)
    {
        $previousStatus = $order->status;

        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'payment_status' => ['nullable', Rule::in(Order::PAYMENT_STATUSES)],
            'transaction_id' => ['nullable', 'string', 'max:255'],
        ]);

        $order->syncInventoryForStatusChange($validated['status']);
        $order->payment_status = $validated['payment_status'] ?? $order->payment_status;
        $order->transaction_id = $validated['transaction_id'] ?? $order->transaction_id;
        $order->save();

        if ($previousStatus !== $order->status) {
            $order->user?->notify(new OrderStatusUpdatedNotification($order));
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order updated successfully.');
    }
}
