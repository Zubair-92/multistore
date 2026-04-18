@extends('admin.layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
    <div>
        <h2 class="mb-1">Store Categories</h2>
        <p class="text-muted mb-0">Organize vendor types and keep store onboarding structured.</p>
    </div>
    <a href="{{ route('admin.storecategories.create') }}" class="btn btn-success">Add Store Category</a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Name (EN)</th>
                    <th>Name (AR)</th>
                    <th>Logo</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($storecategories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ optional($category->translations->where('locale', 'en')->first())->store_category ?? 'N/A' }}</td>
                        <td>{{ optional($category->translations->where('locale', 'ar')->first())->store_category ?? 'N/A' }}</td>
                        <td>
                            @if($category->logo)
                                <img src="{{ asset('storage/'.$category->logo) }}" width="50" alt="Store category logo">
                            @endif
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('admin.storecategories.edit', $category->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('admin.storecategories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No store categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($storecategories->hasPages())
    <div class="mt-4">
        {{ $storecategories->links() }}
    </div>
@endif
@endsection
