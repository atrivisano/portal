<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with key statistics and information.
     */
    public function index(Request $request)
    {
        // Check permission to access admin dashboard
        if (!auth()->user()->can('access admin dashboard') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Get system statistics
        $stats = [
            'total_users'          => User::count(),
            'new_users_this_month' => User::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'pending_approval'     => User::where('is_approved', false)->count(),
            'total_roles'          => Role::count(),
            'total_permissions'    => Permission::count(),
        ];

        // Get recent users
        $recentUsers = User::with('roles:id,name')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'avatar'      => $user->avatarUrl,
                    'created_at'  => $user->created_at,
                    'is_approved' => $user->is_approved,
                    'roles'       => $user->roles,
                ];
            });

        // Get roles with user counts
        $rolesSummary = Role::withCount('users')
            ->orderBy('users_count', 'desc')
            ->take(5)
            ->get(['id', 'name', 'users_count']);

        // Get recent activities (if you have an activity log system)
        $activities = $this->getRecentActivities();

        return Inertia::render('admin/Index', [
            'stats'         => $stats,
            'recent_users'  => $recentUsers,
            'roles_summary' => $rolesSummary,
            'activities'    => $activities,
        ]);
    }

    /**
     * Get recent system activities
     */
    private function getRecentActivities()
    {
        $activities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id'          => $activity->id,
                    'user'        => [
                        'name'   => $activity->user ? $activity->user->name : 'System',
                        'avatar' => $activity->user ? $activity->user->avatarUrl : null,
                    ],
                    'action'      => $activity->action,
                    'target_type' => $activity->target_type,
                    'target_name' => $activity->target_name,
                    'created_at'  => $activity->created_at->toDateTimeString(),
                ];
            });

        // If no activities yet, return some placeholder data
        if ($activities->isEmpty()) {
            return [
                [
                    'id'          => 1,
                    'user'        => [
                        'name'   => auth()->user()->name,
                        'avatar' => auth()->user()->avatarUrl,
                    ],
                    'action'      => 'accessed',
                    'target_type' => 'dashboard',
                    'target_name' => 'Admin Dashboard',
                    'created_at'  => now()->toDateTimeString(),
                ],
            ];
        }

        return $activities;
    }
}
