<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, LayoutDashboard, Settings, ShieldCheck, Users } from 'lucide-vue-next';
import { computed } from 'vue';

// Navigation items for admin panel
const adminNavItems = [
    {
        name: 'Dashboard',
        route: 'admin.dashboard',
        icon: LayoutDashboard,
        permission: 'accessAdminDashboard',
    },
    {
        name: 'Users',
        route: 'admin.users.index',
        icon: Users,
        permission: 'viewUsers',
    },
    {
        name: 'Roles',
        route: 'admin.roles.index',
        icon: ShieldCheck,
        permission: 'viewRoles',
    },
    {
        name: 'Settings',
        route: 'admin.settings',
        icon: Settings,
        permission: 'accessAdminDashboard',
    },
];

const page = usePage();

// Filter navigation items based on user permissions
const filteredNavItems = computed(() => {
    const userPermissions = page.props.auth.user.can;
    const userRoles = page.props.auth.user.roles || [];

    return adminNavItems.filter((item) => {
        // Allow all items for super-admin
        if (userRoles.includes('super-admin')) {
            return true;
        }

        // Otherwise filter by permission
        return userPermissions[item.permission];
    });
});

// Check if the current route matches the nav item
const isCurrentRoute = (routeName: string) => {
    // Use page.url which gives the current URL path
    const currentPath = page.url;

    // Handle the case where currentPath might be undefined
    if (!currentPath) return false;

    // Remove any query parameters and split the path
    const pathOnly = currentPath.split('?')[0];
    const routeParts = pathOnly.split('/').filter(Boolean);

    if (routeName === 'admin.dashboard' && routeParts[0] === 'admin' && !routeParts[1]) {
        return true;
    }

    // For nested routes like users.index, users.show, etc.
    if (routeName.startsWith('admin.users') && routeParts[0] === 'admin' && routeParts[1] === 'users') {
        return true;
    }

    if (routeName.startsWith('admin.roles') && routeParts[0] === 'admin' && routeParts[1] === 'roles') {
        return true;
    }

    if (routeName.startsWith('admin.settings') && routeParts[0] === 'admin' && routeParts[1] === 'settings') {
        return true;
    }

    return false;
};
</script>

<template>
    <div class="mb-6 overflow-hidden rounded-lg border bg-card">
        <div class="flex items-center justify-between border-b px-4 py-3">
            <h2 class="text-sm font-medium">Administration</h2>
        </div>

        <nav class="scrollbar-hide flex overflow-x-auto">
            <Link
                v-for="item in filteredNavItems"
                :key="item.name"
                :href="route(item.route)"
                class="group flex min-w-[120px] flex-col items-center justify-center px-6 py-3 text-sm transition-colors hover:bg-accent hover:text-accent-foreground sm:min-w-0 sm:flex-1 sm:flex-row sm:justify-start sm:gap-3"
                :class="{
                    'bg-accent/50 text-accent-foreground': isCurrentRoute(item.route),
                    'text-muted-foreground': !isCurrentRoute(item.route),
                }"
            >
                <component :is="item.icon" class="h-5 w-5 sm:h-4 sm:w-4" />
                <span>{{ item.name }}</span>
                <ChevronRight
                    class="ml-auto hidden h-4 w-4 opacity-0 transition-opacity group-hover:opacity-70 sm:block"
                    :class="{ 'opacity-70': isCurrentRoute(item.route) }"
                />
            </Link>
        </nav>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
