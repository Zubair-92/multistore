@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <div>
            <h2 class="mb-1">Subcategories</h2>
            <p class="text-muted mb-0">Extend category structure for more precise product mapping and filtering.</p>
        </div>
        <a href="{{ route('admin.subcategories.create') }}" class="btn btn-primary">Add Subcategory</a>
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
                        <th>Category</th>
                        <th>Name (EN)</th>
                        <th>Name (AR)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subcategories as $subcategory)
                        <tr>
                            <td>{{ $subcategories->firstItem() + $loop->index }}</td>
                            <td>
                                @if($subcategory->logo)
                                    <img src="{{ asset('storage/'.$subcategory->logo) }}" width="60" alt="Subcategory logo">
                                @else
                                    <span class="text-muted">No Logo</span>
                                @endif
                            </td>
                            <td>{{ optional($subcategory->category->translation)->category ?? 'N/A' }}</td>
                            <td>{{ optional($subcategory->translations->where('locale', 'en')->first())->sub_category ?? 'N/A' }}</td>
                            <td class="text-end" dir="rtl">{{ optional($subcategory->translations->where('locale', 'ar')->first())->sub_category ?? 'N/A' }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.subcategories.edit', $subcategory->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.subcategories.destroy', $subcategory->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No subcategories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($subcategories->hasPages())
        <div class="mt-4">
            {{ $subcategories->links() }}
        </div>
    @endif
</div>
@endsection
