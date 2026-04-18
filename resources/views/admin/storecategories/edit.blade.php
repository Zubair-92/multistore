@extends('admin.layouts.app')

@section('content')
<h2>Edit Store Category</h2>

<form action="{{ route('admin.storecategories.update', $storecategory->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Name (English)</label>
        <input type="text" name="name_en" class="form-control"
               value="{{ optional($storecategory->translations->where('locale','en')->first())->name }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Name (Arabic)</label>
        <input type="text" name="name_ar" class="form-control text-end" dir="rtl"
               value="{{ optional($storecategory->translations->where('locale','ar')->first())->name }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Logo</label>
        <input type="file" name="logo" class="form-control">
        @if($storeCategory->logo)
            <img src="{{ asset('storage/'.$storecategory->logo) }}" alt="logo" width="80" class="mt-2">
        @endif
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
</form>
@endsection
