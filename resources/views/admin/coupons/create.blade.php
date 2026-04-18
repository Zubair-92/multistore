@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Create Coupon</h2>
    <div class="card shadow-sm border-0"><div class="card-body">
        <form action="{{ route('admin.coupons.store') }}" method="POST">
            @csrf
            @include('admin.coupons.partials.form')
        </form>
    </div></div>
</div>
@endsection
