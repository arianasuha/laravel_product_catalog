<?php

namespace App\Http\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Product Catalog API",
 * description="API documentation for the Product Catalog project",
 * @OA\Contact(
 * email="support@example.com"
 * )
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="first_name", type="string"),
 *     @OA\Property(property="last_name", type="string"),
 *     @OA\Property(property="email", type="string", format="email"),
 *     @OA\Property(property="username", type="string"),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="is_staff", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     required={"first_name", "last_name", "email", "username", "password"},
 *     @OA\Property(property="first_name", type="string", maxLength=50),
 *     @OA\Property(property="last_name", type="string", maxLength=50),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255),
 *     @OA\Property(property="username", type="string", maxLength=30),
 *     @OA\Property(property="password", type="string", minLength=8)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateUserRequest",
 *     @OA\Property(property="first_name", type="string", maxLength=50),
 *     @OA\Property(property="last_name", type="string", maxLength=50),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255),
 *     @OA\Property(property="username", type="string", maxLength=30),
 *     @OA\Property(property="password", type="string", minLength=8),
 *     @OA\Property(property="is_active", type="boolean"),
 *     @OA\Property(property="is_staff", type="boolean")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="The given data was invalid."
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         example={
 *             "email": {"The email field is required."},
 *             "password": {"The password must be at least 8 characters."}
 *         }
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     @OA\Property(property="first", type="string"),
 *     @OA\Property(property="last", type="string"),
 *     @OA\Property(property="prev", type="string", nullable=true),
 *     @OA\Property(property="next", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="from", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="path", type="string"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="to", type="integer"),
 *     @OA\Property(property="total", type="integer")
 * )
 */
class Annotations
{
    // This class is just a container for the annotations. No actual code needed here.
}
