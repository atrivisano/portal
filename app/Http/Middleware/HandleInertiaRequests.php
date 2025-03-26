<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name'  => config('app.name'),
            'quote' => $this->getRandomQuote(),
            'auth'  => [
                'user' => $request->user() ? [
                    'id'                => $request->user()->id,
                    'name'              => $request->user()->name,
                    'email'             => $request->user()->email,
                    'avatar'            => $request->user()->avatarUrl,
                    'phone'             => $request->user()->phone,
                    'bio'               => $request->user()->bio,
                    'address'           => $request->user()->address,
                    'city'              => $request->user()->city,
                    'state'             => $request->user()->state,
                    'zip_code'          => $request->user()->zip_code,
                    'country'           => $request->user()->country,
                    'roles'             => $request->user()->roles->pluck('name'),
                    'email_verified_at' => $request->user()->email_verified_at,
                    'created_at'        => $request->user()->created_at,
                    'can'               => $this->getUserPermissions($request->user()),
                ] : null,
            ],
            'ziggy' => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'message' => fn() => $request->session()->get('message'),
                'error'   => fn() => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Get a random quote for the UI
     */
    protected function getRandomQuote(): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return ['message' => trim($message), 'author' => trim($author)];
    }

    /**
     * Get user permissions for use in the frontend
     */
    protected function getUserPermissions($user): array
    {
        if (!$user) {
            return [];
        }

        // Convert permissions to camelCase for JavaScript conventions
        $permissions = [];

        // Check for super-admin role which gets all permissions
        if ($user->hasRole('super-admin')) {
            return [
                'accessAdminDashboard' => true,
                'viewUsers'            => true,
                'createUsers'          => true,
                'editUsers'            => true,
                'deleteUsers'          => true,
                'viewRoles'            => true,
                'createRoles'          => true,
                'editRoles'            => true,
                'deleteRoles'          => true,
                'viewProfile'          => true,
                'editProfile'          => true,
                'viewPermissions'      => true,
                'assignPermissions'    => true,
            ];
        }

        // For regular users, check specific permissions
        $permissions = [
            'accessAdminDashboard' => $user->can('access admin dashboard'),
            'viewUsers'            => $user->can('view users'),
            'createUsers'          => $user->can('create users'),
            'editUsers'            => $user->can('edit users'),
            'deleteUsers'          => $user->can('delete users'),
            'viewRoles'            => $user->can('view roles'),
            'createRoles'          => $user->can('create roles'),
            'editRoles'            => $user->can('edit roles'),
            'deleteRoles'          => $user->can('delete roles'),
            'viewProfile'          => $user->can('view profile'),
            'editProfile'          => $user->can('edit profile'),
            'viewPermissions'      => $user->can('view permissions'),
            'assignPermissions'    => $user->can('assign permissions'),
        ];

        return $permissions;
    }
}
