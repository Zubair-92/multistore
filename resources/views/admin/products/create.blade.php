@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Add Product</h2>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Store --}}
        <div class="mb-3">
            <label>Store</label>
            <select name="store_id" class="form-control" required>
                <option value="">Select Store</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">
                        {{ $store->translation->name ?? 'No Name' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Category --}}
        <div class="mb-3">
            <label>Category</label>
            <select name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->translation->category ?? 'No Name' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Subcategory --}}
        <div class="mb-3">
            <label>Subcategory</label>
            <select name="subcategory_id" class="form-control">
                <option value="">Select Subcategory</option>
                @foreach ($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}">
                        {{ $subcategory->translation->sub_category ?? 'No Name' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Product Category --}}
        <div class="mb-3">
            <label>Product Category</label>
            <select name="product_category_id" class="form-control">
                <option value="">Select Product Category</option>
                @foreach ($productCategories as $pc)
                    <option value="{{ $pc->id }}">
                        {{ $pc->translation->product_category ?? 'No Name' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Product Name --}}
        <div class="mb-3">
            <label>Name (EN)</label>
            <input type="text" name="name_en" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Name (AR)</label>
            <input type="text" name="name_ar" class="form-control" required>
        </div>

        {{-- Description --}}
        <div class="mb-3">
            <label>Description (EN)</label>
            <textarea name="description_en" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>Description (AR)</label>
            <textarea name="description_ar" class="form-control"></textarea>
        </div>

        {{-- Price --}}
        <div class="mb-3">
            <label>Price</label>
            <input type="number" name="price" class="form-control" step="0.01" required>
        </div>

        {{-- Offer Price --}}
        <div class="mb-3">
            <label>Offer Price</label>
            <input type="number" name="offer_price" class="form-control" step="0.01">
        </div>

        {{-- Stock --}}
        <div class="mb-3">
            <label>Stock</label>
            <input type="number" name="stock" class="form-control" required>
        </div>

        {{-- Status --}}
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        {{-- Image --}}
        <div class="mb-3">
            <label>Image</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Save Product</button>
    </form>
</div>
@endsection
