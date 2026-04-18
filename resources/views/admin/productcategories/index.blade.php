@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <h2 class="mb-1">Product Categories</h2>
        <p class="text-muted mb-0">Keep product taxonomy structured for filtering and vendor product setup.</p>
    </div>
    <a href="{{ route('admin.productcategories.create') }}" class="btn btn-success">Add Product Category</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Sub Category</th>
                    <th>Name (EN)</th>
                    <th>Name (AR)</th>
                    <th>Logo</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productcategories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->subcategory?->category?->translation?->category ?? 'N/A' }}</td>
                        <td>{{ $category->subcategory?->translation?->sub_category ?? 'N/A' }}</td>
                        <td>{{ optional($category->translations->where('locale', 'en')->first())->product_category ?? 'N/A' }}</td>
                        <td>{{ optional($category->translations->where('locale', 'ar')->first())->product_category ?? 'N/A' }}</td>
                        <td>
                            @if($category->logo)
                                <img src="{{ asset('storage/'.$category->logo) }}" width="50" alt="Product category logo">
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.productcategories.edit', $category->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.productcategories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No product categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($productcategories->hasPages())
    <div class="mt-4">
        {{ $productcategories->links() }}
    </div>
@endif
@endsection
