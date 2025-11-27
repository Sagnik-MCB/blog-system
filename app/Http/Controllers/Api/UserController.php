<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->role($role);
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $users = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $user->load(['roles', 'posts' => fn($q) => $q->latest()->take(5)]);
        
        $user->loadCount(['posts', 'comments']);

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        $user->syncRoles([$validated['role']]);

        ActivityLog::log(
            action: 'api_update_user',
            description: "Admin updated user: {$user->name}",
            model: $user
        );

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot delete your own account',
            ], 403);
        }

        ActivityLog::log(
            action: 'api_delete_user',
            description: "Admin deleted user: {$user->name}",
            model: $user
        );

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot deactivate your own account',
            ], 403);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        ActivityLog::log(
            action: 'api_toggle_user_status',
            description: "Admin {$status} user: {$user->name}",
            model: $user,
            properties: ['new_status' => $user->is_active]
        );

        return response()->json([
            'message' => "User {$status} successfully",
            'user' => $user->load('roles'),
        ]);
    }
}

