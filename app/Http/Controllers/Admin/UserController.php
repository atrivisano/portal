<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        if (!auth()->user()->can('view users')) {
            abort(403, 'Unauthorized action.');
        }

        $users = User::query()
            ->with('roles:id,name')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                $query->whereHas('roles', function ($query) use ($role) {
                    $query->where('id', $role);
                });
            })
            ->orderBy($request->sort ?? 'created_at', $request->direction ?? 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'sort', 'direction']),
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        if (!auth()->user()->can('create users')) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('admin/users/Create', [
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('create users')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.index')
            ->with('message', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        if (!auth()->user()->can('view users')) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('admin/users/Show', [
            'user' => $user->load('roles:id,name'),
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        if (!auth()->user()->can('edit users')) {
            abort(403, 'Unauthorized action.');
        }

        // Super-admin can only be edited by another super-admin
        if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('admin/users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles()->pluck('id'),
            ],
            'roles' => Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        if (!auth()->user()->can('edit users')) {
            abort(403, 'Unauthorized action.');
        }

        // Super-admin can only be edited by another super-admin
        if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (isset($validated['password']) && $validated['password']) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->route('admin.users.index')
            ->with('message', 'User updated successfully.');
    }

    /**
     * Update the specified user's roles.
     */
    public function updateRoles(Request $request, User $user)
    {
        if (!auth()->user()->can('assign permissions')) {
            abort(403, 'Unauthorized action.');
        }

        // Super-admin roles can only be edited by another super-admin
        if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent users from updating their own roles
        if (auth()->id() === $user->id) {
            abort(403, 'You cannot update your own roles.');
        }

        $validated = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->syncRoles($validated['roles']);

        return back()
            ->with('message', 'User roles updated successfully.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->can('delete users')) {
            abort(403, 'Unauthorized action.');
        }

        // Super-admin can only be deleted by another super-admin
        if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent users from deleting themselves
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('message', 'User deleted successfully.');
    }
}
