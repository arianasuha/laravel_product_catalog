@extends('products.layout')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Show Product</h2>
                <a class="btn btn-primary" href="{{ route('products.index') }}"> Back</a>
            </div>
        </div>
    </div>

    <div class="card p-4">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                <div class="form-group">
                    <strong>Name:</strong>
                    {{ $product->name }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                <div class="form-group">
                    <strong>Description:</strong>
                    {{ $product->description }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                <div class="form-group">
                    <strong>Price:</strong>
                    ${{ number_format($product->price, 2) }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                <div class="form-group">
                    <strong>Stock:</strong>
                    {{ $product->stock }}
                </div>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12 mb-3">
                <div class="form-group">
                    <strong>Image:</strong>
                    @if ($product->image)
                        <img src="{{ $product->image }}" class="img-fluid rounded" style="max-width: 300px; height: auto;" alt="{{ $product->name }}">
                    @else
                        <img src="https://placehold.co/300x200/e0e0e0/555555?text=No+Image" class="img-fluid rounded" alt="No Image">
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection


