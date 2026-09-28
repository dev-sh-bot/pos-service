@extends('layouts.admin')

@section('title', 'Edit Category')
@section('content-header', 'Edit Category')
@section('content-actions')
<a href="{{ route('categories.index') }}" class="btn btn-secondary"><x-snd-icon name="arrow-left" /> Back</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="snd-form-section-heading">
                <x-snd-icon name="list" />
                <div>
                    <h3>Category Details</h3>
                    <p>Update how this service group appears in the catalog.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Category Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name', $category->name) }}">
                        @error('name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" id="slug" value="{{ old('slug', $category->slug) }}">
                        @error('slug')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" min="0" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" value="{{ old('sort_order', $category->sort_order) }}">
                        @error('sort_order')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" id="status">
                            <option value="1" {{ old('status', $category->status) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !old('status', $category->status) ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="description">{{ old('description', $category->description) }}</textarea>
                        @error('description')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
            </div>

            <div class="snd-form-actions">
                <button class="btn btn-primary" type="submit"><x-snd-icon name="check" class="mr-1" />Update Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
