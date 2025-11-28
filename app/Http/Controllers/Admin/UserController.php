<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::with('roles');

        // Search functionality
        $searchTerm = $request->query('search');
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by role - using join approach for reliability
        $roleName = $request->query('role');
        if (!empty($roleName)) {
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', '=', $roleName);
            });
        }

        // Filter by status
        $statusFilter = $request->query('status');
        if (!empty($statusFilter)) {
            $isActive = ($statusFilter === 'active');
            $query->where('is_active', $isActive);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $user->assignRole($validated['role']);

        ActivityLog::log(
            action: 'create_user',
            description: "Admin created new user: {$user->name}",
            model: $user
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        $user->load(['posts' => fn($q) => $q->latest()->take(5), 'roles']);
        $activities = ActivityLog::byUser($user->id)->latest()->take(10)->get();

        return view('admin.users.show', compact('user', 'activities'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
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
            'is_active' => $validated['is_active'] ?? false,
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        $user->syncRoles([$validated['role']]);

        ActivityLog::log(
            action: 'update_user',
            description: "Admin updated user: {$user->name}",
            model: $user
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        ActivityLog::log(
            action: 'delete_user',
            description: "Admin deleted user: {$user->name}",
            model: $user
        );

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account!');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        ActivityLog::log(
            action: 'toggle_user_status',
            description: "Admin {$status} user: {$user->name}",
            model: $user,
            properties: ['new_status' => $user->is_active]
        );

        return back()->with('success', "User {$status} successfully!");
    }
}

