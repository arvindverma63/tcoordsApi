<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @OA\Info(
 *    title="Swagger with Laravel",
 *    version="1.0.0",
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */


class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration data",
     *         @OA\JsonContent(
     *             required={"name", "email", "password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="linkedin_id", type="string", example="linkedin-profile-id"),
     *             @OA\Property(property="avatar", type="string", format="uri", example="http://example.com/path-to-avatar.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="avatar", type="string", example="http://example.com/path-to-avatar.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation errors",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Validation failed"),
     *             @OA\Property(property="details", type="object", additionalProperties={"type":"array", "items":{"type":"string"}})
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        // Validate incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'linkedin_id' => 'nullable|string|max:255',
            'avatar' => 'nullable',  // Added avatar validation
        ]);

        // If validation fails, return validation errors
        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'details' => $validator->errors()], 422);
        }

        try {


            // Create a new user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'linkedin_id' => $request->linkedin_id,
                'avatar' => $request->avatar,  // Save avatar path or URL
            ]);

            // Generate JWT token after user registration
            $token = JWTAuth::fromUser($user);

            // Return success response with user data and JWT token
            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (\Exception $e) {
            // Return error response in case of an exception
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/getUser/{id}",
     *     summary="Retrieve user by LinkedIn ID",
     *     description="This endpoint retrieves a user by their LinkedIn ID. If found, it returns a JWT token for authentication.",
     *     tags={"Auth"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The LinkedIn ID of the user",
     *         @OA\Schema(type="string", example="linkedin_12345")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User found and token generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="message", type="string", example="Database error")
     *         )
     *     )
     * )
     */
    public function getUser($id)
    {
        $data = User::where('linkedin_id', $id)->first();
        if ($data) {
            $token = JWTAuth::fromUser($data);
            return response()->json([
                'token' => $token,
            ]);
        } else {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
    }
}
