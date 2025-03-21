<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request): Response
    {
        if (!auth()->user()->can('view roles')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::withCount(['permissions', 'users'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        if (!auth()->user()->can('create roles')) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('admin/roles/Create', [
            'permissions' => Permission::orderBy('name')->get()->groupBy(function ($permission) {
                // Group permissions by their prefix (before the first dot)
                return explode('.', $permission->name)[0] ?? 'general';
            }),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('create roles')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class)],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::create(['name' => $validated['name']]);

            if (!empty($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            }
        });

        return redirect()->route('admin.roles.index')
            ->with('message', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): Response
    {
        if (!auth()->user()->can('edit roles')) {
            abort(403, 'Unauthorized action.');
        }

        // Only super-admin can edit the super-admin role
        if ($role->name === 'super-admin' && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('admin/roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('id'),
            ],
            'permissions' => Permission::orderBy('name')->get()->groupBy(function ($permission) {
                // Group permissions by their prefix (before the first dot)
                return explode('.', $permission->name)[0] ?? 'general';
            }),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        if (!auth()->user()->can('edit roles')) {
            abort(403, 'Unauthorized action.');
        }

        // Only super-admin can edit the super-admin role
        if ($role->name === 'super-admin' && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class)->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        DB::transaction(function () use ($role, $validated) {
            $role->update(['name' => $validated['name']]);

            if (isset($validated['permissions'])) {
                $role->permissions()->sync($validated['permissions']);
            }
        });

        return redirect()->route('admin.roles.index')
            ->with('message', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        if (!auth()->user()->can('delete roles')) {
            abort(403, 'Unauthorized action.');
        }

        // Don't allow deletion of the super-admin role
        if ($role->name === 'super-admin') {
            return back()->with('error', 'The super-admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('message', 'Role deleted successfully.');
    }
}
