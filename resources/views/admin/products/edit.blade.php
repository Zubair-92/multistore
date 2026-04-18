@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Edit Product</h2>

    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- ✅ Store --}}
        <div class="mb-3">
            <label>Store</label>
            <select name="store_id" class="form-control" required>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" 
                        {{ $product->store_id == $store->id ? 'selected' : '' }}>
                        {{ $store->translation->name ?? 'No Name' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ✅ Category --}}
        <div class="mb-3">
            <label>Category</label>
            <select name="category_id" class="form-control" required>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" 
                        {{ $product->category_id == $category->id ? 'selected' : '' }}>
                        {{ $category->translation->name ?? 'No Name' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ✅ Subcategory --}}
        <div class="mb-3">
            <label>Subcategory</label>
            <select name="subcategory_id" class="form-control">
                <option value="">Select Subcategory</option>
                @foreach ($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}" 
                        {{ $product->subcategory_id == $subcategory->id ? 'selected' : '' }}>
                        {{ $subcategory->name ?? 'No Name' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ✅ Name --}}
        <div class="mb-3">
            <label>Name (EN)</label>
            <input type="text" name="name_en" 
                   value="{{ $product->translations->where('locale','en')->first()->name ?? '' }}" 
                   class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Name (AR)</label>
            <input type="text" name="name_ar" 
                   value="{{ $product->translations->where('locale','ar')->first()->name ?? '' }}" 
                   class="form-control" required>
        </div>

        {{-- ✅ Description --}}
        <div class="mb-3">
            <label>Description (EN)</label>
            <textarea name="description_en" class="form-control">{{ 
                $product->translations->where('locale','en')->first()->description ?? '' 
            }}</textarea>
        </div>

        <div class="mb-3">
            <label>Description (AR)</label>
            <textarea name="description_ar" class="form-control">{{ 
                $product->translations->where('locale','ar')->first()->description ?? '' 
            }}</textarea>
        </div>

        {{-- ✅ Price --}}
        <div class="mb-3">
            <label>Price</label>
            <input type="number" name="price" 
                   value="{{ $product->price }}" 
                   class="form-control" step="0.01" required>
        </div>

        {{-- ✅ Offer Price --}}
        <div class="mb-3">
            <label>Offer Price</label>
            <input type="number" name="offer_price" 
                   value="{{ $product->offer_price }}" 
                   class="form-control" step="0.01">
        </div>

        {{-- ✅ Stock --}}
        <div class="mb-3">
            <label>Stock</label>
            <input type="number" name="stock" 
                   value="{{ $product->stock }}" 
                   class="form-control" required>
        </div>

        {{-- ✅ Status --}}
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1" {{ $product->status == 1 ? 'selected' : '' }}>Active</option>
                <option value="0" {{ $product->status == 0 ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        {{-- ✅ Image --}}
        <div class="mb-3">
            <label>Image</label><br>
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" width="80" class="mb-2">
            @endif
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Update Product</button>
    </form>
</div>
@endsection
