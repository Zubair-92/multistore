@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Edit Subcategory</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ✅ enctype REQUIRED for file update --}}
    <form action="{{ route('admin.subcategories.update', $subcategory->id) }}" 
          method="POST" 
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ✅ CATEGORY --}}
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ $subcategory->category_id == $category->id ? 'selected' : '' }}>
                        {{ optional($category->translation)->category }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ✅ CURRENT LOGO PREVIEW --}}
        <div class="mb-3">
            <label class="form-label">Current Logo</label><br>

            @if($subcategory->logo)
                <img src="{{ asset('storage/'.$subcategory->logo) }}" 
                     width="80" 
                     class="mb-2 border rounded p-2">
            @else
                <p class="text-muted">No logo uploaded</p>
            @endif
        </div>

        {{-- ✅ NEW LOGO UPLOAD --}}
        <div class="mb-3">
            <label class="form-label">Change Logo (optional)</label>
            <input type="file" name="logo" class="form-control">
        </div>

        {{-- ✅ ENGLISH NAME --}}
        <div class="mb-3">
            <label class="form-label">Subcategory Name (English)</label>
            <input type="text" 
                   name="name_en" 
                   class="form-control"
                   value="{{ optional($subcategory->translations->where('locale','en')->first())->sub_category }}"
                   required>
        </div>

        {{-- ✅ ARABIC NAME --}}
        <div class="mb-3">
            <label class="form-label">Subcategory Name (Arabic)</label>
            <input type="text" 
                   name="name_ar" 
                   class="form-control text-end" 
                   dir="rtl"
                   value="{{ optional($subcategory->translations->where('locale','ar')->first())->sub_category }}"
                   required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
