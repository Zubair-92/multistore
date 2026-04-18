@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <h2 class="mb-1">Categories</h2>
        <p class="text-muted mb-0">Manage storefront category groups and keep the catalog organized.</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Add Category</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Logo</th>
                    <th>Name (English)</th>
                    <th>Name (Arabic)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $categories->firstItem() + $loop->index }}</td>
                        <td>
                            @if($category->logo)
                                <img src="{{ asset('storage/'.$category->logo) }}" width="60" alt="Category logo">
                            @endif
                        </td>
                        <td>{{ optional($category->translations->where('locale', 'en')->first())->category ?? '-' }}</td>
                        <td>{{ optional($category->translations->where('locale', 'ar')->first())->category ?? '-' }}</td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($categories->hasPages())
    <div class="mt-4">
        {{ $categories->links() }}
    </div>
@endif
@endsection
