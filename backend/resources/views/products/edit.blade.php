@extends('products.layout')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Edit Product</h2>
                <a class="btn btn-primary" href="{{ route('products.index') }}"> Back</a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-4">
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT') {{-- Use PUT method for update --}}

            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <strong>Name:</strong>
                        <input type="text" name="name" value="{{ $product->name }}" class="form-control" placeholder="Product Name">
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <strong>Description:</strong>
                        <textarea class="form-control" style="height:150px" name="description" placeholder="Product Description">{{ $product->description }}</textarea>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <strong>Price:</strong>
                        <input type="number" name="price" value="{{ $product->price }}" class="form-control" placeholder="Price" step="0.01">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-group">
                        <strong>Stock:</strong>
                        <input type="number" name="stock" value="{{ $product->stock }}" class="form-control" placeholder="Stock">
                    </div>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <strong>Current Image:</strong>
                        @if ($product->image)
                            <img src="{{ $product->image }}" class="img-fluid rounded mb-2" style="max-width: 200px; height: auto;" alt="{{ $product->name }}">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="clear_image" id="clear_image" value="1">
                                <label class="form-check-label" for="clear_image">
                                    Clear current image
                                </label>
                            </div>
                        @else
                            <p>No image uploaded.</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <strong>New Image (optional):</strong>
                        <input type="file" name="image" class="form-control">
                    </div>
                </div>
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </div>
        </form>
    </div>
@endsection


