<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, ArrowUpRight, Calendar, CheckSquare, KeyRound, ShieldCheck, UserCog, Users } from 'lucide-vue-next';

// Components
import AdminNav from '@/components/AdminNav.vue';
import Heading from '@/components/Heading.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
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

interface Props {
    counts: {
        users: number;
        roles: number;
        permissions: number;
        pendingApproval: number;
    };
    recentUsers: User[];
    roles: Role[];
}

const props = defineProps<Props>();
const { getInitials } = useInitials();

// Format date for display
const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Get role color for badges
const getRoleColor = (name: string) => {
    const colors: Record<string, string> = {
        'super-admin': 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
        admin: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        volunteer: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    };

    return colors[name] || 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300';
};

// Breadcrumb data
const breadcrumbs = [
    {
        title: 'Admin Dashboard',
        href: route('admin.dashboard'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Admin Dashboard" />

        <div class="container mx-auto p-6">
            <Heading title="Admin Dashboard" description="Overview of your system" />

            <AdminNav />

            <!-- Stats Cards -->
            <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Users Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ counts.users }}</div>
                        <p class="text-xs text-muted-foreground">Registered users in the system</p>
                    </CardContent>
                    <CardFooter>
                        <Link :href="route('admin.users.index')" class="flex items-center text-xs text-blue-500 hover:underline">
                            View all users
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>

                <!-- Roles Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Roles</CardTitle>
                        <ShieldCheck class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ counts.roles }}</div>
                        <p class="text-xs text-muted-foreground">Defined roles in the system</p>
                    </CardContent>
                    <CardFooter>
                        <Link :href="route('admin.roles.index')" class="flex items-center text-xs text-blue-500 hover:underline">
                            Manage roles
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>

                <!-- Permissions Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Permissions</CardTitle>
                        <KeyRound class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ counts.permissions }}</div>
                        <p class="text-xs text-muted-foreground">Available permissions</p>
                    </CardContent>
                </Card>

                <!-- Pending Approval Card -->
                <Card :class="{ 'border-amber-200 dark:border-amber-800': counts.pendingApproval > 0 }">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium" :class="{ 'text-amber-700 dark:text-amber-400': counts.pendingApproval > 0 }">
                            Pending Approval
                        </CardTitle>
                        <AlertTriangle class="h-4 w-4" :class="counts.pendingApproval > 0 ? 'text-amber-500' : 'text-muted-foreground'" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold" :class="{ 'text-amber-700 dark:text-amber-400': counts.pendingApproval > 0 }">
                            {{ counts.pendingApproval }}
                        </div>
                        <p class="text-xs text-muted-foreground">Users waiting for approval</p>
                    </CardContent>
                    <CardFooter v-if="counts.pendingApproval > 0">
                        <Link :href="route('admin.users.index', { role: '' })" class="flex items-center text-xs text-amber-500 hover:underline">
                            Review pending users
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>
            </div>

            <!-- Recent Users & Top Roles -->
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <!-- Recent Users -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Users</CardTitle>
                        <CardDescription> The latest registered users in the system </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="user in recentUsers" :key="user.id" class="flex items-center">
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
                                        <Badge v-if="user.roles.length === 0" variant="outline" class="text-xs"> No roles </Badge>
                                    </div>
                                    <div class="flex items-center text-xs text-muted-foreground">
                                        <Calendar class="mr-1 h-3 w-3" />
                                        {{ formatDate(user.created_at) }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="recentUsers.length === 0" class="py-8 text-center text-muted-foreground">
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
                        <CardTitle>Top Roles by Usage</CardTitle>
                        <CardDescription> Roles with the most assigned users </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="role in roles" :key="role.id" class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <ShieldCheck class="mr-2 h-4 w-4 text-muted-foreground" />
                                    <div>
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

                            <div v-if="roles.length === 0" class="py-8 text-center text-muted-foreground">
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
                        <CardDescription> Common administrative tasks you can perform </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            <Card class="bg-muted/50">
                                <CardContent class="flex flex-col items-center p-4 text-center">
                                    <UserCog class="mb-2 h-8 w-8 text-primary" />
                                    <h3 class="text-sm font-medium">Create User</h3>
                                    <p class="mb-4 text-xs text-muted-foreground">Add a new user to the system</p>
                                    <Button variant="outline" size="sm" class="mt-auto w-full" as-child>
                                        <Link :href="route('admin.users.create')"> Create User </Link>
                                    </Button>
                                </CardContent>
                            </Card>

                            <Card class="bg-muted/50">
                                <CardContent class="flex flex-col items-center p-4 text-center">
                                    <ShieldCheck class="mb-2 h-8 w-8 text-primary" />
                                    <h3 class="text-sm font-medium">Create Role</h3>
                                    <p class="mb-4 text-xs text-muted-foreground">Define a new role with permissions</p>
                                    <Button variant="outline" size="sm" class="mt-auto w-full" as-child>
                                        <Link :href="route('admin.roles.create')"> Create Role </Link>
                                    </Button>
                                </CardContent>
                            </Card>

                            <Card class="bg-muted/50" v-if="counts.pendingApproval > 0">
                                <CardContent class="flex flex-col items-center p-4 text-center">
                                    <CheckSquare class="mb-2 h-8 w-8 text-primary" />
                                    <h3 class="text-sm font-medium">Approve Users</h3>
                                    <p class="mb-4 text-xs text-muted-foreground">{{ counts.pendingApproval }} users waiting for approval</p>
                                    <Button variant="outline" size="sm" class="mt-auto w-full" as-child>
                                        <Link :href="route('admin.users.index', { role: '' })"> Review Users </Link>
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
