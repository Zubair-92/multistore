@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Edit Coupon</h2>
    <div class="card shadow-sm border-0"><div class="card-body">
        <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.coupons.partials.form', ['coupon' => $coupon])
        </form>
    </div></div>
</div>
@endsection
