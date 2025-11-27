<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('user');

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::log(
            action: 'api_register',
            description: 'User registered via API',
            model: $user
        );

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::log(
            action: 'api_login',
            description: 'User logged in via API',
            model: $user
        );

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('roles'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        ActivityLog::log(
            action: 'api_logout',
            description: 'User logged out via API',
            model: $request->user()
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('roles'),
        ]);
    }

    /**
     * Get statistics (admin only).
     */
    public function statistics(): JsonResponse
    {
        return response()->json([
            'statistics' => [
                'total_users' => User::count(),
                'total_posts' => Post::count(),
                'published_posts' => Post::published()->count(),
                'total_comments' => Comment::count(),
                'pending_comments' => Comment::pending()->count(),
                'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
                'new_posts_this_week' => Post::where('created_at', '>=', now()->subWeek())->count(),
            ],
        ]);
    }
}

