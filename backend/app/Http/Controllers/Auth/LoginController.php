<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Import the ValidationException

/**
 * @OA\Info(
 * version="1.0.0",
 * title="My Laravel API",
 * description="API documentation for my Laravel project",
 * @OA\Contact(
 * email="your@example.com"
 * ),
 * @OA\License(
 * name="Apache 2.0",
 * url="http://www.apache.org/licenses/LICENSE-2.0.html"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="sanctum",
 * type="apiKey",
 * in="header",
 * name="Authorization",
 * description="Enter token in format (Bearer <token>)"
 * )
 */
class LoginController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @OA\Post(
     * path="/api/login",
     * operationId="loginUser",
     * tags={"Authentication"},
     * summary="Authenticate a user and issue an API token",
     * description="Logs in a user with provided credentials and returns a Sanctum API token.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     * ),
     * @OA\Response(
     * response=200,
     * description="Login successful.",
     * @OA\JsonContent(
     * @OA\Property(property="success", type="string", example="Login successful."),
     * @OA\Property(property="token", type="string", example="1|abcdefghijklmnopqrstuvwxyz0123456789"),
     * @OA\Property(property="token_type", type="string", example="Bearer")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation error",
     * @OA\JsonContent(
     * @OA\Property(property="errors", type="object",
     * @OA\Property(property="email", type="array", @OA\Items(type="string", example="The email field is required.")),
     * @OA\Property(property="password", type="array", @OA\Items(type="string", example="The password field is required."))
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthorized",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="These credentials do not match our records.")
     * )
     * )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $request->authenticate(); // This will validate and attempt authentication
        } catch (ValidationException $e) {
            // Catch the ValidationException thrown by authenticate()
            return response()->json([
                'errors' => $e->errors(), // Get the validation messages
            ], 422); // Use HTTP status code 422 for unprocessable entity (validation errors)
        }

        $user = Auth::user();

        // Delete existing tokens if you want only one active token per device/login
        // $user->tokens()->delete();

        // Create a new Sanctum token
        // You can specify abilities (scopes) for the token if needed
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'success' => 'Login successful.',
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @OA\Post(
     * path="/api/logout",
     * operationId="logoutUser",
     * tags={"Authentication"},
     * summary="Log out the authenticated user",
     * description="Revokes the current API token of the authenticated user.",
     * security={{"sanctum":{}}},
     * @OA\Response(
     * response=200,
     * description="Successfully logged out.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Successfully logged out.")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Unauthenticated.")
     * )
     * )
     * )
     */
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }
}