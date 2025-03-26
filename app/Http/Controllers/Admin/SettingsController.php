<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        // Check permission to access admin dashboard
        if (!auth()->user()->can('access admin dashboard') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Get settings data - this would be populated from your settings tables/configurations
        $settings = [
            'general'  => [
                'site_name'              => config('app.name'),
                'site_description'       => 'Client Portal for Managing Users and Roles',
                'admin_email'            => 'admin@example.com',
                'users_require_approval' => true,
            ],
            'security' => [
                'password_expiry_days'      => 90,
                'session_timeout_minutes'   => 60,
                'two_factor_authentication' => false,
            ],
            'email'    => [
                'email_notifications'     => true,
                'welcome_email'           => true,
                'approval_required_email' => true,
            ],
        ];

        return Inertia::render('admin/settings/Index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        // Check permission to access admin dashboard
        if (!auth()->user()->can('access admin dashboard') && !auth()->user()->hasRole('super-admin')) {
            abort(403, 'Unauthorized action.');
        }

        // Validate the settings
        $validated = $request->validate([
            'general.site_name'                  => 'required|string|max:255',
            'general.site_description'           => 'nullable|string|max:1000',
            'general.admin_email'                => 'required|email',
            'general.users_require_approval'     => 'boolean',
            'security.password_expiry_days'      => 'integer|min:0|max:365',
            'security.session_timeout_minutes'   => 'integer|min:1|max:1440',
            'security.two_factor_authentication' => 'boolean',
            'email.email_notifications'          => 'boolean',
            'email.welcome_email'                => 'boolean',
            'email.approval_required_email'      => 'boolean',
        ]);

        // In a real app, you would save these settings to your database or config files
        // For this example, we'll just return success

        // Log the settings update
        ActivityLog::log(
            'updated',
            'settings',
            null,
            'System Settings',
            $validated,
            'System settings updated'
        );

        return back()->with('message', 'Settings updated successfully.');
    }
}
