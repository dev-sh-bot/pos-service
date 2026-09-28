@extends('layouts.admin')

@section('title', 'Edit Service')
@section('content-header', 'Edit Service')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('services.update', $service) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="snd-form-section-heading">
                <x-snd-icon name="briefcase" />
                <div>
                    <h3>Service Details</h3>
                    <p>Set the service information used when creating a POS order.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Service Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name', $service->name) }}">
                        @error('name')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" id="category_id">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) old('category_id', $service->category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="barcode">Barcode</label>
                        <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" id="barcode" value="{{ old('barcode', $service->barcode) }}">
                        @error('barcode')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="rate">Rate</label>
                        <input type="number" step="0.01" min="0" name="rate" class="form-control @error('rate') is-invalid @enderror" id="rate" value="{{ old('rate', $service->rate) }}">
                        @error('rate')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror" id="status">
                            <option value="1" {{ old('status', $service->status) ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !old('status', $service->status) ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="image">Image</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" name="image" id="image">
                            <label class="custom-file-label" for="image">Choose file</label>
                        </div>
                        @if($service->image)
                            <img src="{{ $service->image_url }}" alt="{{ $service->name }}" style="width:64px;height:64px;object-fit:cover;border-radius:8px;margin-top:10px;">
                        @endif
                        @error('image')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="description">{{ old('description', $service->description) }}</textarea>
                        @error('description')<span class="invalid-feedback"><strong>{{ $message }}</strong></span>@enderror
                    </div>
                </div>
            </div>

            <div class="snd-form-actions">
                <button class="btn btn-primary" type="submit"><x-snd-icon name="save" class="mr-1" />Update Service</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        bsCustomFileInput.init();
    });
</script>
@endsection
