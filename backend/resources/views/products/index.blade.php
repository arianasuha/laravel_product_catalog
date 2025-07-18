@extends('products.layout')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Product Catalog</h2>
                <a class="btn btn-success" href="{{ route('products.create') }}"> Create New Product</a>
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <div class="card p-4">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th width="280px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>
                        @if ($product->image)
                            <img src="{{ $product->image }}" class="product-image" alt="{{ $product->name }}">
                        @else
                            <img src="https://placehold.co/100x100/e0e0e0/555555?text=No+Image" class="product-image" alt="No Image">
                        @endif
                    </td>
                    <td>{{ $product->name }}</td>
                    <td>{{ Str::limit($product->description, 50) }}</td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>
                        <form action="{{ route('products.destroy',$product->id) }}" method="POST">
                            <a class="btn btn-info btn-sm" href="{{ route('products.show',$product->id) }}">Show</a>
                            <a class="btn btn-primary btn-sm" href="{{ route('products.edit',$product->id) }}">Edit</a>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {!! $products->links('pagination::bootstrap-5') !!} {{-- For Bootstrap 5 pagination styling --}}

@endsection


