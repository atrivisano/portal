<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Users,
    ShieldCheck,
    KeyRound,
    CalendarClock,
    ArrowUpRight
} from 'lucide-vue-next';

// Components
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import { useInitials } from '@/composables/useInitials';

// Types
interface User {
    id: number;
    name: string;
    email: string;
    avatar: string;
    created_at: string;
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
    // Simple hash function to generate a number from a string
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }

    // Convert to a light pastel color
    const hue = hash % 360;
    return `hsl(${hue}, 70%, 85%)`;
};
</script>

<template>
    <AppLayout>
        <Head title="Admin Dashboard" />

        <div class="container mx-auto p-6">
            <Heading title="Admin Dashboard" description="Overview of your system" />

            <!-- Stats Cards -->
            <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Users Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ counts.users }}</div>
                        <p class="text-xs text-muted-foreground">
                            Registered users in the system
                        </p>
                    </CardContent>
                    <CardFooter>
                        <Link
                            :href="route('admin.users.index')"
                            class="text-xs text-blue-500 flex items-center hover:underline"
                        >
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
                        <p class="text-xs text-muted-foreground">
                            Defined roles in the system
                        </p>
                    </CardContent>
                    <CardFooter>
                        <Link
                            :href="route('admin.roles.index')"
                            class="text-xs text-blue-500 flex items-center hover:underline"
                        >
                            Manage roles
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>

                <!-- Permissions Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Permissions</CardTitle>
                        <KeyRound class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ counts.permissions }}</div>
                        <p class="text-xs text-muted-foreground">
                            Available permissions
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Users & Top Roles -->
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <!-- Recent Users -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Users</CardTitle>
                        <CardDescription>
                            The latest registered users in the system
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="user in recentUsers" :key="user.id" class="flex items-center">
                                <Avatar class="h-9 w-9 mr-3">
                                    <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback class="text-xs">
                                        {{ getInitials(user.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium leading-none">{{ user.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ user.email }}</p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <div class="flex gap-1 mb-1">
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            variant="outline"
                                            :style="{ backgroundColor: getRoleColor(role.name) }"
                                            class="text-xs"
                                        >
                                            {{ role.name }}
                                        </Badge>
                                    </div>
                                    <div class="flex items-center text-xs text-muted-foreground">
                                        <CalendarClock class="mr-1 h-3 w-3" />
                                        {{ formatDate(user.created_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Link
                            :href="route('admin.users.index')"
                            class="text-xs text-blue-500 flex items-center hover:underline"
                        >
                            View all users
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>

                <!-- Top Roles -->
                <Card>
                    <CardHeader>
                        <CardTitle>Top Roles by Usage</CardTitle>
                        <CardDescription>
                            Roles with the most assigned users
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="role in roles" :key="role.id" class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <ShieldCheck class="mr-2 h-4 w-4 text-muted-foreground" />
                                    <div>
                                        <Badge
                                            variant="outline"
                                            :style="{ backgroundColor: getRoleColor(role.name) }"
                                        >
                                            {{ role.name }}
                                        </Badge>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium">{{ role.users_count }}</span>
                                    <span class="ml-1 text-xs text-muted-foreground">users</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Link
                            :href="route('admin.roles.index')"
                            class="text-xs text-blue-500 flex items-center hover:underline"
                        >
                            Manage roles
                            <ArrowUpRight class="ml-1 h-3 w-3" />
                        </Link>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
