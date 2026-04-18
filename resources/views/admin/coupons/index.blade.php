@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Coupons</h2>
            <p class="text-muted mb-0">Create and manage discount codes for cart and checkout promotions.</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">Add Coupon</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Minimum</th>
                        <th>Validity</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td><strong>{{ $coupon->code }}</strong></td>
                            <td>{{ ucfirst($coupon->type) }}</td>
                            <td>{{ $coupon->type === 'percent' ? number_format((float) $coupon->value, 0).'%' : '$'.number_format((float) $coupon->value, 2) }}</td>
                            <td>${{ number_format((float) $coupon->minimum_amount, 2) }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ $coupon->starts_at?->format('d M Y') ?? 'Any time' }}
                                    <br>
                                    {{ $coupon->expires_at?->format('d M Y') ?? 'No expiry' }}
                                </small>
                            </td>
                            <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                            <td><span class="badge bg-{{ $coupon->is_active ? 'success' : 'secondary' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this coupon?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">No coupons created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($coupons->hasPages())
        <div class="mt-4">
            {{ $coupons->links() }}
        </div>
    @endif
</div>
@endsection
