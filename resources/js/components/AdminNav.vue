<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    Users,
    ShieldCheck,
    LayoutDashboard,
    Settings,
    UserCog
} from 'lucide-vue-next';

// Navigation items for admin panel
const adminNavItems = [
    {
        name: 'Dashboard',
        route: 'admin.dashboard',
        icon: LayoutDashboard,
        permission: 'accessAdminDashboard'
    },
    {
        name: 'Users',
        route: 'admin.users.index',
        icon: Users,
        permission: 'viewUsers'
    },
    {
        name: 'Roles',
        route: 'admin.roles.index',
        icon: ShieldCheck,
        permission: 'viewRoles'
    },
    {
        name: 'Settings',
        route: 'admin.settings',
        icon: Settings,
        permission: 'accessAdminDashboard'
    }
];

const page = usePage();

// Filter navigation items based on user permissions
const filteredNavItems = computed(() => {
    const userPermissions = page.props.auth.user.can;

    return adminNavItems.filter(item => {
        // Allow all items for super-admin
        if (page.props.auth.user.roles.includes('super-admin')) {
            return true;
        }

        // Otherwise filter by permission
        return userPermissions[item.permission];
    });
});

// Check if the current route matches the nav item
const isCurrentRoute = (routeName: string) => {
    const currentRoute = page.component.value as string;
    const routeParts = currentRoute.split('/');

    if (routeName === 'admin.dashboard' && routeParts[1] === 'admin' && !routeParts[2]) {
        return true;
    }

    // For nested routes like users.index, users.show, etc.
    if (routeName.startsWith('admin.users') && routeParts[1] === 'admin' && routeParts[2] === 'users') {
        return true;
    }

    if (routeName.startsWith('admin.roles') && routeParts[1] === 'admin' && routeParts[2] === 'roles') {
        return true;
    }

    if (routeName.startsWith('admin.settings') && routeParts[1] === 'admin' && routeParts[2] === 'settings') {
        return true;
    }

    return false;
};
</script>

<template>
    <div class="mb-6 border rounded-lg overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 bg-muted/50">
            <h2 class="text-sm font-medium">Administration</h2>
        </div>

        <nav class="flex overflow-x-auto">
            <Link
                v-for="item in filteredNavItems"
                :key="item.name"
                :href="route(item.route)"
                class="inline-flex min-w-[100px] flex-col items-center justify-center px-4 py-3 text-sm transition-colors hover:bg-accent hover:text-accent-foreground sm:min-w-0 sm:flex-1 sm:flex-row sm:gap-2 sm:justify-start"
                :class="{
          'bg-accent/50 text-accent-foreground': isCurrentRoute(item.route),
          'text-muted-foreground': !isCurrentRoute(item.route)
        }"
            >
                <component :is="item.icon" class="h-5 w-5 sm:h-4 sm:w-4" />
                <span>{{ item.name }}</span>
            </Link>
        </nav>
    </div>
</template>
