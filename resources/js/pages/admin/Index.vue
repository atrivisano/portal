<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Activity, AlertTriangle, ArrowUpRight, Building, Calendar, KeyRound, ShieldCheck, UserCheck, UserCog, Users } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Components
import AdminNav from '@/components/AdminNav.vue';
import Heading from '@/components/Heading.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { useInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';

// Types
interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    created_at: string;
    is_approved: boolean;
    roles: {
        id: number;
        name: string;
    }[];
}

interface Role {
    id: number;
    name: string;
    users_count: number;
}

interface Stats {
    total_users: number;
    new_users_this_month: number;
    pending_approval: number;
    total_roles: number;
    total_permissions: number;
}

interface ActivityLog {
    id: number;
    user: {
        name: string;
        avatar?: string;
    };
    action: string;
    target_type: string;
    target_name: string;
    created_at: string;
}

interface Props {
    stats: Stats;
    recent_users: User[];
    roles_summary: Role[];
    activities?: ActivityLog[];
}

const props = defineProps<Props>();
const { getInitials } = useInitials();

// Prepare data for system health chart
const systemHealth = ref({
    users: {
        label: 'Users',
        value: props.stats?.total_users || 0,
        maxExpected: 1000,
        icon: Users,
    },
    roles: {
        label: 'Roles',
        value: props.stats?.total_roles || 0,
        maxExpected: 10,
        icon: ShieldCheck,
    },
    permissions: {
        label: 'Permissions',
        value: props.stats?.total_permissions || 0,
        maxExpected: 50,
        icon: KeyRound,
    },
});

// Format date for display
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Format time ago
const formatTimeAgo = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffSec = Math.round(diffMs / 1000);
    const diffMin = Math.round(diffSec / 60);
    const diffHour = Math.round(diffMin / 60);
    const diffDay = Math.round(diffHour / 24);

    if (diffSec < 60) {
        return `${diffSec} second${diffSec !== 1 ? 's' : ''} ago`;
    } else if (diffMin < 60) {
        return `${diffMin} minute${diffMin !== 1 ? 's' : ''} ago`;
    } else if (diffHour < 24) {
        return `${diffHour} hour${diffHour !== 1 ? 's' : ''} ago`;
    } else if (diffDay < 7) {
        return `${diffDay} day${diffDay !== 1 ? 's' : ''} ago`;
    } else {
        return formatDate(dateString);
    }
};

// Get role badge color
const getRoleColor = (name: string) => {
    const colors: Record<string, string> = {
        'super-admin': 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
        admin: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        volunteer: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    };

    return colors[name] || 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300';
};

// Calculate percentage for system health metrics
const calculatePercentage = (value: number, maxExpected: number) => {
    return Math.min(Math.round((value / maxExpected) * 100), 100);
};

// Determine color based on percentage
const getHealthColor = (percentage: number) => {
    if (percentage < 50) return 'bg-emerald-500';
    if (percentage < 80) return 'bg-amber-500';
    return 'bg-rose-500';
};

// Check if current user has specific permissions
const page = usePage();
const userPermissions = computed(() => page.props.auth.user.can || {});

// Breadcrumb data
const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
];

// Animation
onMounted(() => {
    // Animate numbers on load
    const counters = document.querySelectorAll('.counter-value');
    counters.forEach((counter) => {
        const target = parseInt(counter.getAttribute('data-target') || '0');
        let count = 0;
        const updateCounter = () => {
            const increment = target / 20;
            if (count < target) {
                count += Math.ceil(increment);
                counter.textContent = count.toString();
                setTimeout(updateCounter, 40);
            } else {
                counter.textContent = target.toString();
            }
        };
        updateCounter();
    });
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin Dashboard" />

        <div class="container mx-auto p-6">
            <div class="mb-6 flex items-center justify-between">
                <Heading title="Admin Dashboard" description="Overview and insights for platform management" />
            </div>

            <AdminNav />

            <!-- Alert for pending approvals -->
            <Alert
                v-if="stats?.pending_approval > 0"
                variant="warning"
                class="mb-6 bg-amber-50 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300"
            >
                <AlertTriangle class="h-4 w-4" />
                <AlertTitle>Pending Approvals</AlertTitle>
                <AlertDescription>
                    There are {{ stats?.pending_approval }} user{{ stats?.pending_approval !== 1 ? 's' : '' }} waiting for approval.
                    <Button variant="outline" size="sm" as-child class="ml-2">
                        <Link :href="route('admin.users.index', { role: '' })"> Review Now </Link>
                    </Button>
                </AlertDescription>
            </Alert>

            <!-- Stats Cards -->
            <div class="mb-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Users Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <Users class="h-4 w-4 text-primary" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="counter-value text-2xl font-bold" data-target="{{ stats?.total_users || 0 }}">0</div>
                        <p class="text-xs text-muted-foreground">
                            <span class="text-emerald-500">+{{ stats?.new_users_this_month || 0 }}</span> new this month
                        </p>
                    </CardContent>
                    <CardFooter class="p-2">
                        <Link :href="route('admin.users.index')" class="flex w-full items-center text-xs text-primary hover:underline">
                            View all users
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>

                <!-- Roles Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">User Roles</CardTitle>
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <ShieldCheck class="h-4 w-4 text-primary" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="counter-value text-2xl font-bold" data-target="{{ stats?.total_roles || 0 }}">0</div>
                        <p class="text-xs text-muted-foreground">Active roles in the system</p>
                    </CardContent>
                    <CardFooter class="p-2">
                        <Link :href="route('admin.roles.index')" class="flex w-full items-center text-xs text-primary hover:underline">
                            Manage roles
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>

                <!-- Permissions Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Permissions</CardTitle>
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary/10">
                            <KeyRound class="h-4 w-4 text-primary" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="counter-value text-2xl font-bold" data-target="{{ stats?.total_permissions || 0 }}">0</div>
                        <p class="text-xs text-muted-foreground">Available permissions</p>
                    </CardContent>
                </Card>

                <!-- Pending Approval Card -->
                <Card :class="{ 'border-amber-200 dark:border-amber-800': stats?.pending_approval > 0 }">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium" :class="{ 'text-amber-700 dark:text-amber-400': stats?.pending_approval > 0 }">
                            Pending Approval
                        </CardTitle>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full"
                            :class="stats?.pending_approval > 0 ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-primary/10'"
                        >
                            <AlertTriangle class="h-4 w-4" :class="stats?.pending_approval > 0 ? 'text-amber-600' : 'text-primary'" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div
                            class="counter-value text-2xl font-bold"
                            :class="{ 'text-amber-700 dark:text-amber-400': stats?.pending_approval > 0 }"
                            data-target="{{ stats?.pending_approval || 0 }}"
                        >
                            0
                        </div>
                        <p class="text-xs text-muted-foreground">Users waiting for approval</p>
                    </CardContent>
                    <CardFooter class="p-2" v-if="stats?.pending_approval > 0">
                        <Link
                            :href="route('admin.users.index', { role: '' })"
                            class="flex w-full items-center text-xs text-amber-600 hover:underline"
                        >
                            Review pending users
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>
            </div>

            <!-- System health and activity -->
            <div class="mb-6 grid gap-6 md:grid-cols-2">
                <!-- System Health Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Activity class="h-5 w-5" />
                            System Health
                        </CardTitle>
                        <CardDescription>Current system metrics and performance</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            <div v-for="(item, key) in systemHealth" :key="key" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <component :is="item.icon" class="mr-2 h-4 w-4 text-muted-foreground" />
                                        <span class="text-sm font-medium">{{ item.label }}</span>
                                    </div>
                                    <span class="text-sm">{{ item.value }} / {{ item.maxExpected }}</span>
                                </div>
                                <Progress
                                    :value="calculatePercentage(item.value, item.maxExpected)"
                                    :class="getHealthColor(calculatePercentage(item.value, item.maxExpected))"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Activity Log Card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            Recent Activity
                        </CardTitle>
                        <CardDescription>Latest actions in the system</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="activities && activities.length > 0" class="space-y-4">
                            <div v-for="activity in activities" :key="activity.id" class="flex items-start space-x-3">
                                <Avatar class="h-8 w-8">
                                    <AvatarImage v-if="activity.user.avatar" :src="activity.user.avatar" :alt="activity.user.name" />
                                    <AvatarFallback class="text-xs">
                                        {{ getInitials(activity.user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm leading-none">
                                        <span class="font-medium">{{ activity.user.name }}</span>
                                        <span class="text-muted-foreground"> {{ activity.action }} </span>
                                        <span class="font-medium">{{ activity.target_name }}</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">{{ formatTimeAgo(activity.created_at) }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center py-6 text-center">
                            <Calendar class="mb-2 h-8 w-8 text-muted-foreground opacity-50" />
                            <p class="text-sm font-medium">No recent activity</p>
                            <p class="text-xs text-muted-foreground">Activity will appear here as users interact with the system</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Users & Top Roles -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Recent Users -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-5 w-5" />
                            Recent Users
                        </CardTitle>
                        <CardDescription>The latest registered users in the system</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="user in recent_users" :key="user.id" class="flex items-center">
                                <div class="relative">
                                    <Avatar class="mr-3 h-9 w-9">
                                        <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                        <AvatarFallback class="text-xs">
                                            {{ getInitials(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div v-if="!user.is_approved" class="absolute -right-1 -top-1">
                                        <Badge variant="destructive" class="flex h-3 w-3 items-center justify-center rounded-full p-0">
                                            <AlertTriangle class="h-2 w-2" />
                                        </Badge>
                                    </div>
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium leading-none">{{ user.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <div class="mb-1 flex gap-1">
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            variant="outline"
                                            :class="getRoleColor(role.name)"
                                            class="text-xs"
                                        >
                                            {{ role.name }}
                                        </Badge>
                                        <Badge v-if="user.roles.length === 0" variant="outline" class="text-xs">No roles </Badge>
                                    </div>
                                    <div class="flex items-center text-xs text-muted-foreground">
                                        <Calendar class="mr-1 h-3 w-3" />
                                        {{ formatDate(user.created_at) }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="recent_users.length === 0" class="py-8 text-center text-muted-foreground">
                                <UserCog class="mx-auto mb-2 h-8 w-8 opacity-50" />
                                <p>No users found</p>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Link :href="route('admin.users.index')" class="flex items-center text-xs text-blue-500 hover:underline">
                            View all users
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>

                <!-- Top Roles -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ShieldCheck class="h-5 w-5" />
                            Top Roles by Usage
                        </CardTitle>
                        <CardDescription>Roles with the most assigned users</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="(role, index) in roles_summary" :key="role.id" class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted">
                                        <span class="text-xs font-bold">{{ index + 1 }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <Badge variant="outline" :class="getRoleColor(role.name)">
                                            {{ role.name }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium">{{ role.users_count }}</span>
                                    <span class="ml-1 text-xs text-muted-foreground">users</span>
                                </div>
                            </div>

                            <div v-if="roles_summary.length === 0" class="py-8 text-center text-muted-foreground">
                                <ShieldCheck class="mx-auto mb-2 h-8 w-8 opacity-50" />
                                <p>No roles found</p>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Link :href="route('admin.roles.index')" class="flex items-center text-xs text-blue-500 hover:underline">
                            Manage roles
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common administrative tasks you can perform</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                            <Card class="bg-muted/50">
                                <CardContent class="flex flex-col items-center p-4 text-center">
                                    <UserCog class="mb-2 h-8 w-8 text-primary" />
                                    <h3 class="text-sm font-medium">Create User</h3>
                                    <p class="mb-4 text-xs text-muted-foreground">Add a new user to the system</p>
                                    <Button variant="outline" size="sm" class="mt-auto w-full" as-child>
                                        <Link :href="route('admin.users.create')">Create User</Link>
                                    </Button>
                                </CardContent>
                            </Card>

                            <Card class="bg-muted/50">
                                <CardContent class="flex flex-col items-center p-4 text-center">
                                    <ShieldCheck class="mb-2 h-8 w-8 text-primary" />
                                    <h3 class="text-sm font-medium">Create Role</h3>
                                    <p class="mb-4 text-xs text-muted-foreground">Define a new role with permissions</p>
                                    <Button variant="outline" size="sm" class="mt-auto w-full" as-child>
                                        <Link :href="route('admin.roles.create')">Create Role</Link>
                                    </Button>
                                </CardContent>
                            </Card>

                            <Card class="bg-muted/50" v-if="stats?.pending_approval > 0">
                                <CardContent class="flex flex-col items-center p-4 text-center">
                                    <UserCheck class="mb-2 h-8 w-8 text-primary" />
                                    <h3 class="text-sm font-medium">Approve Users</h3>
                                    <p class="mb-4 text-xs text-muted-foreground">{{ stats?.pending_approval }} users waiting for approval</p>
                                    <Button variant="outline" size="sm" class="mt-auto w-full" as-child>
                                        <Link :href="route('admin.users.index', { role: '' })">Review Users</Link>
                                    </Button>
                                </CardContent>
                            </Card>

                            <Card class="bg-muted/50">
                                <CardContent class="flex flex-col items-center p-4 text-center">
                                    <Building class="mb-2 h-8 w-8 text-primary" />
                                    <h3 class="text-sm font-medium">System Settings</h3>
                                    <p class="mb-4 text-xs text-muted-foreground">Configure platform settings</p>
                                    <Button variant="outline" size="sm" class="mt-auto w-full" as-child>
                                        <Link :href="route('admin.settings')">Settings</Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
