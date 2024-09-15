<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;


class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/register",
     *      summary="Register a new user",
     *      description="Create a new user account",
     *      tags={"Auth"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="securePassword123!")
     *          )
     *      ),
     *      @OA\Response(response=200, description="User registered successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *              @OA\Property(property="user", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="John Doe"),
     *                  @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=422, description="Validation failed",
     *          @OA\JsonContent(
     *              @OA\Property(property="errors", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="message", type="string"),
     *                      @OA\Property(property="field", type="string")
     *                  )
     *              )
     *          )
     * )
     * )
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/login",
     *      summary="Login an existing user",
     *      description="Authenticate an existing user",
     *      tags={"Auth"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *              @OA\Property(property="password", type="string", example="securePassword123!")
     *          )
     *      ),
     *      @OA\Response(response=200, description="User logged in successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="access_token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."),
     *              @OA\Property(property="user", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="John Doe"),
     *                  @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *              )
     *          )
     *      ),
     *      @OA\Response(response=401, description="Invalid credentials",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="The provided credentials are incorrect.")
     *          )
     *      )
     * )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        return $this->issueToken($user);

    }

    /**
     * @OA\Post(
     *      path="/api/logout",
     *      summary="Logout the current user",
     *      description="Revoke the authentication token",
     *      tags={"Auth"},
     *      @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="user token.",
     *         required=true,
     *      ),
     *      @OA\Response(response=401, description="Unauthorized.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Logged out successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Logged out successfully")
     *          )
     *      )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * @OA\Get(
     *      path="/api/profile",
     *      summary="Get current user profile",
     *      description="Retrieve the authenticated user's details",
     *      tags={"Auth"},
     *      @OA\Parameter(
     *         name="authorization",
     *         in="header",
     *         description="user token.",
     *         required=true,
     *      ),
     *      @OA\Response(response=401, description="Unauthorized.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated.")
     *          )
     *      ),
     *      @OA\Response(response=200, description="User profile retrieved successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *          )
     *      )
     * )
     */
    public function profile(Request $request)
    {
        return $request->user();
    }
}
