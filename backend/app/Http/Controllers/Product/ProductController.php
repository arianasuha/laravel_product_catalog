<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

// L5 Swagger Annotations
use OpenApi\Annotations as OA;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/products",
     * operationId="getProductsList",
     * tags={"Products"},
     * summary="Get list of products",
     * description="Returns list of products with pagination. Requires authentication.",
     * security={ {"sanctum": {} } },
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Page number for pagination",
     * required=false,
     * @OA\Schema(
     * type="integer",
     * format="int32"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(
     * type="object",
     * @OA\Property(
     * property="data",
     * type="array",
     * @OA\Items(ref="#/components/schemas/Product")
     * ),
     * @OA\Property(
     * property="links",
     * type="object"
     * ),
     * @OA\Property(
     * property="meta",
     * type="object"
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="This action is unauthorized.")
     * )
     * )
     * )
     */
    public function index(): JsonResponse
    {
        $products = Product::latest()->paginate(10);
        return response()->json($products);
    }

    /**
     * @OA\Post(
     * path="/api/products",
     * operationId="storeProduct",
     * tags={"Products"},
     * summary="Store a newly created product",
     * description="Stores a new product in the database. Requires authentication.",
     * security={ {"sanctum": {} } },
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"name", "price", "stock"},
     * @OA\Property(
     * property="name",
     * type="string",
     * maxLength=255,
     * description="Name of the product"
     * ),
     * @OA\Property(
     * property="description",
     * type="string",
     * nullable=true,
     * description="Description of the product"
     * ),
     * @OA\Property(
     * property="price",
     * type="number",
     * format="float",
     * description="Price of the product"
     * ),
     * @OA\Property(
     * property="stock",
     * type="integer",
     * format="int32",
     * description="Stock quantity of the product"
     * ),
     * @OA\Property(
     * property="image",
     * type="string",
     * format="binary",
     * nullable=true,
     * description="Product image file (max 2MB, jpeg, png, jpg, gif, svg)"
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Product created successfully",
     * @OA\JsonContent(ref="#/components/schemas/Product")
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request - Validation error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="This action is unauthorized.")
     * )
     * )
     * )
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public/products');
        }

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath ? Storage::url($imagePath) : null,
        ]);

        return response()->json($product, 201);
    }

    /**
     * @OA\Get(
     * path="/api/products/{id}",
     * operationId="getProductById",
     * tags={"Products"},
     * summary="Get product information",
     * description="Returns product data by ID. Requires authentication.",
     * security={ {"sanctum": {} } },
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID of product to return",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * format="int64"
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(ref="#/components/schemas/Product")
     * ),
     * @OA\Response(
     * response=404,
     * description="Product not found",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 123")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="This action is unauthorized.")
     * )
     * )
     * )
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    /**
     * @OA\Put(
     * path="/api/products/{id}",
     * operationId="updateProduct",
     * tags={"Products"},
     * summary="Update existing product",
     * description="Updates an existing product in the database. Requires authentication.",
     * security={ {"sanctum": {} } },
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID of product to update",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * format="int64"
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\MediaType(
     * mediaType="multipart/form-data",
     * @OA\Schema(
     * required={"name", "price", "stock"},
     * @OA\Property(
     * property="name",
     * type="string",
     * maxLength=255,
     * description="Name of the product"
     * ),
     * @OA\Property(
     * property="description",
     * type="string",
     * nullable=true,
     * description="Description of the product"
     * ),
     * @OA\Property(
     * property="price",
     * type="number",
     * format="float",
     * description="Price of the product"
     * ),
     * @OA\Property(
     * property="stock",
     * type="integer",
     * format="int32",
     * description="Stock quantity of the product"
     * ),
     * @OA\Property(
     * property="image",
     * type="string",
     * format="binary",
     * nullable=true,
     * description="New product image file (optional, max 2MB, jpeg, png, jpg, gif, svg)"
     * ),
     * @OA\Property(
     * property="clear_image",
     * type="boolean",
     * nullable=true,
     * description="Set to 1 to clear the current image. Only applicable if 'image' is not provided."
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Product updated successfully",
     * @OA\JsonContent(ref="#/components/schemas/Product")
     * ),
     * @OA\Response(
     * response=400,
     * description="Bad Request - Validation error",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="This action is unauthorized.")
     * )
     * )
     * )
     */
    public function update(ProductUpdateRequest $request, Product $product): JsonResponse
    {
        $imagePath = $product->image;

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::delete(str_replace('/storage/', 'public/', $product->image));
            }
            $imagePath = $request->file('image')->store('public/products');
            $imagePath = Storage::url($imagePath);
        } elseif ($request->boolean('clear_image')) {
            if ($product->image) {
                Storage::delete(str_replace('/storage/', 'public/', $product->image));
            }
            $imagePath = null;
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
        ]);

        return response()->json($product);
    }

    /**
     * @OA\Delete(
     * path="/api/products/{id}",
     * operationId="deleteProduct",
     * tags={"Products"},
     * summary="Delete existing product",
     * description="Deletes a product and its associated image. Requires authentication.",
     * security={ {"sanctum": {} } },
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID of product to delete",
     * required=true,
     * @OA\Schema(
     * type="integer",
     * format="int64"
     * )
     * ),
     * @OA\Response(
     * response=204,
     * description="Product deleted successfully (No Content)",
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="This action is unauthorized.")
     * )
     * ),
     * @OA\Response(
     * response=404,
     * description="Product not found",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 123")
     * )
     * )
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        if ($product->image) {
            Storage::delete(str_replace('/storage/', 'public/', $product->image));
        }

        $product->delete();

        return response()->json(null, 204);
    }
}
