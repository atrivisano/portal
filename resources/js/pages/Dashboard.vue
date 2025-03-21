// resources/js/pages/Dashboard.vue
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Calendar, Users, ShieldCheck, Bell, BookOpen } from 'lucide-vue-next';
import { computed } from 'vue';
import { type BreadcrumbItem } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth.user);
const userRoles = computed(() => user.value.roles || []);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Determine if user has specific roles
const isSuperAdmin = computed(() => userRoles.value.includes('super-admin'));
const isAdmin = computed(() => userRoles.value.includes('admin'));
const isVolunteer = computed(() => userRoles.value.includes('volunteer'));

// Get greeting based on time of day
const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
};

// Quick actions based on user role
const quickActions = computed(() => {
    const actions = [];

    if (isSuperAdmin.value || isAdmin.value) {
        actions.push({
            title: 'Manage Users',
            icon: Users,
            description: 'View and manage system users',
            href: route('admin.users.index')
        });

        actions.push({
            title: 'Manage Roles',
            icon: ShieldCheck,
            description: 'Configure user roles and permissions',
            href: route('admin.roles.index')
        });
    }

    actions.push({
        title: 'Update Profile',
        icon: Calendar,
        description: 'Manage your personal information',
        href: route('profile.edit')
    });

    return actions;
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto p-6">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight">{{ getGreeting() }}, {{ user.name }}</h1>
                <p class="text-muted-foreground">Here's what's happening with your account today.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Quick Action Cards -->
                <Card v-for="action in quickActions" :key="action.title" class="flex flex-col">
                    <CardHeader class="flex-row items-center gap-4 space-y-0 pb-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-primary/10">
                            <component :is="action.icon" class="h-5 w-5 text-primary" />
                        </div>
                        <div class="space-y-1">
                            <CardTitle class="text-base">{{ action.title }}</CardTitle>
                            <CardDescription>{{ action.description }}</CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="flex-1">
                        <!-- Additional content can go here -->
                    </CardContent>
                    <CardFooter>
                        <Button variant="outline" class="w-full" asChild>
                            <Link :href="action.href">
                                <span>Access</span>
                                <ChevronRight class="ml-auto h-4 w-4" />
                            </Link>
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Admin-specific content -->
            <div v-if="isSuperAdmin || isAdmin" class="mt-10">
                <h2 class="mb-4 text-xl font-semibold">Administration</h2>
                <div class="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Admin Dashboard</CardTitle>
                            <CardDescription>Manage your system</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm text-muted-foreground">
                                Access the admin dashboard to manage users, roles, and system settings.
                            </p>
                        </CardContent>
                        <CardFooter>
                            <Button asChild>
                                <Link :href="route('admin.dashboard')">
                                    Go to Admin Dashboard
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
