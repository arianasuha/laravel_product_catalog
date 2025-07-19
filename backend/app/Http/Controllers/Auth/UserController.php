<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    /**
     * List all users
     *
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Users"},
     *     security={ {"sanctum": {} } },
     *     @OA\Response(
     *         response=200,
     *         description="List of paginated users",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/User")
     *             ),
     *             @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
     *             @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::paginate(10);
            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user
     *
     * @OA\Post(
     *     path="/api/user",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="email", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            // Note: Your User model's setPasswordAttribute should handle hashing
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => $validated['password'], // The setter will hash this
                'is_active' => true,
                'is_staff' => false,
            ]);

            return response()->json([
                "success" => "User created successfully. Please verify your email to activate your account.",
                "user" => $user->only(['id', 'username', 'email']) // Return minimal user data
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific user
     *
     * @OA\Get(
     *     path="/api/user/{user}",
     *     tags={"Users"},
     *     security={ {"sanctum": {} } },
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID or slug",
     *         required=true,
     *         @OA\Schema(
     *             oneOf={
     *                 @OA\Schema(type="integer"),
     *                 @OA\Schema(type="string")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function show(string $user): JsonResponse
{
    try {
        $foundUser = User::where('id', $user)
            ->orWhere('username', $user)
            ->first();

        if (!$foundUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($foundUser, 200);
    } catch (\Exception $e) {
        return response()->json([
            "error" => "An error occurred while retrieving the user.",
            "details" => $e->getMessage()
        ], 500);
    }
}

    /**
     * Update a user
     *
     * @OA\Patch(
     *     path="/api/user/{user}",
     *     tags={"Users"},
     *     security={ {"sanctum": {} } },
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID or slug",
     *         required=true,
     *         @OA\Schema(
     *             oneOf={
     *                 @OA\Schema(type="integer"),
     *                 @OA\Schema(type="string")
     *             }
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, string $user): JsonResponse
    {
        try {
            // Find the user by ID or slug first
            $foundUser = User::where('id', $user)->orWhere('slug', $user)->firstOrFail();

            // Authorization: Ensure the authenticated user can update this specific user
            // This is a crucial step for security.
            // Example:
            // $this->authorize('update', $foundUser);
            // If the current user is not the user being updated, and is not staff/admin:
            if ($request->user()->id !== $foundUser->id && !$request->user()->is_staff) {
                return response()->json([
                    'errors' => 'You are not authorized to update this user.'
                ], 403);
            }
            // Add more specific authorization rules as needed.
            // For instance, a regular user should NOT be able to change is_staff status.
            if (!$request->user()->is_staff && $request->has('is_staff')) {
                return response()->json([
                    'errors' => 'You are not authorized to change staff status.'
                ], 403);
            }


            $validated = $request->validated();

            // Handle password separately if it's being updated
            if (isset($validated['password'])) {
                // The setPasswordAttribute in your User model should handle hashing and strong password validation
                $foundUser->password = $validated['password'];
                unset($validated['password']); // Remove from validated array to prevent re-hashing
            }

            // Update user details with the remaining validated data
            $foundUser->update($validated);

            return response()->json($foundUser->fresh(), 200); // fresh() to get updated attributes
        } catch (\Exception $e) {
            return response()->json([
                "error" => "An error occurred while updating the user.",
                "details" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     *
     * @OA\Delete(
     *     path="/api/user/{user}",
     *     tags={"Users"},
     *     security={ {"sanctum": {} } },
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="User ID or slug",
     *         required=true,
     *         @OA\Schema(
     *             oneOf={
     *                 @OA\Schema(type="integer"),
     *                 @OA\Schema(type="string")
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string"),
     *             @OA\Property(property="details", type="string")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, string $user): JsonResponse
    {
        try {
            // Find the user by ID or slug
            $foundUser = User::where('id', $user)->orWhere('slug', $user)->firstOrFail();

            // Authorization: Only allow staff/admin or the user themselves to delete
            // (You should use Laravel Policies for more robust authorization)
            // Example using simple checks:
            if ($request->user()->id !== $foundUser->id && !$request->user()->is_staff) {
                return response()->json([
                    'errors' => 'You are not authorized to delete this user.'
                ], 403);
            }

            $foundUser->delete();

            // Return 204 No Content for successful deletion
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                "error" => "An error occurred while deleting the user.",
                "details" => $e->getMessage()
            ], 500);
        }
    }
}