<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        // Check if user has permission to access admin dashboard
        // This assumes a permission called 'access admin dashboard' exists
        if (!auth()->user()->can('access admin dashboard') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Get counts for dashboard widgets
        $counts = [
            'users' => User::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
        ];

        // Get recent users
        $recentUsers = User::with('roles:id,name')
            ->latest()
            ->take(5)
            ->get();

        // Get roles with user counts
        $roles = Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('admin/Index', [
            'counts' => $counts,
            'recentUsers' => $recentUsers,
            'roles' => $roles,
        ]);
    }
}
