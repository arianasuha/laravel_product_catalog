<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA; // This line is crucial for Swagger annotations

/**
 * @OA\Schema(
 * title="Product",
 * description="Product model",
 * @OA\Xml(
 * name="Product"
 * )
 * )
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     *
     * @OA\Property(
     * property="id",
     * type="integer",
     * format="int64",
     * description="Product ID",
     * readOnly="true"
     * )
     * @OA\Property(
     * property="name",
     * type="string",
     * maxLength=255,
     * description="Product name"
     * )
     * @OA\Property(
     * property="description",
     * type="string",
     * nullable=true,
     * description="Product description"
     * )
     * @OA\Property(
     * property="price",
     * type="number",
     * format="float",
     * description="Product price"
     * )
     * @OA\Property(
     * property="stock",
     * type="integer",
     * format="int32",
     * description="Product stock quantity"
     * )
     * @OA\Property(
     * property="image",
     * type="string",
     * nullable=true,
     * description="URL to product image"
     * )
     * @OA\Property(
     * property="created_at",
     * type="string",
     * format="date-time",
     * description="Creation timestamp",
     * readOnly="true"
     * )
     * @OA\Property(
     * property="updated_at",
     * type="string",
     * format="date-time",
     * description="Last update timestamp",
     * readOnly="true"
     * )
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image',
    ];

    public function casts() {
        return [
            'price' => 'float',
            'stock' => 'integer',
        ];
    }
}
