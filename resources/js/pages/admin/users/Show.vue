<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ChevronLeft,
    Pencil,
    Mail,
    Calendar,
    MapPin,
    Phone,
    GraduationCap,
    Shield,
    Building,
    UserCheck,
    AlertTriangle
} from 'lucide-vue-next';

// Components
import Heading from '@/components/Heading.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { useInitials } from '@/composables/useInitials';
import { computed } from 'vue';

// Types
interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    phone?: string;
    bio?: string;
    address?: string;
    city?: string;
    state?: string;
    zip_code?: string;
    country?: string;
    created_at: string;
    updated_at: string;
    is_approved: boolean;
    roles: {
        id: number;
        name: string;
    }[];
}

interface Props {
    user: User;
}

const props = defineProps<Props>();
const page = usePage();
const { getInitials } = useInitials();

// Format date for display
const formatDate = (dateString: string | null) => {
    if (!dateString) return 'Not Available';

    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        timeZoneName: 'short'
    }).format(date);
};

// Get role badge color
const getRoleBadgeColor = (role: string) => {
    const colors: Record<string, string> = {
        'super-admin': 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
        'admin': 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        'volunteer': 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    };

    return colors[role] || 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300';
};

// Full address
const fullAddress = computed(() => {
    const parts = [
        props.user.address,
        props.user.city,
        props.user.state,
        props.user.zip_code,
        props.user.country
    ].filter(Boolean);

    return parts.length > 0 ? parts.join(', ') : null;
});

// Is the user a super admin?
const isSuperAdmin = computed(() => {
    return props.user.roles.some(role => role.name === 'super-admin');
});

// Check if current user can edit this profile
const canEditUser = computed(() => {
    const authUser = page.props.auth.user;
    // Can edit if has permission and either: is not editing a super-admin OR is a super-admin themselves
    return authUser.can.editUsers && (!isSuperAdmin.value || authUser.roles.includes('super-admin'));
});

// Permissions based on roles (simplified for display)
const userPermissions = computed(() => {
    const permissions = [];

    if (isSuperAdmin.value) {
        return [
            'Full System Access',
            'Access Admin Dashboard',
            'Manage Users',
            'Manage Roles',
            'Manage Permissions',
            'Edit Profile',
            'View Profile',
        ];
    }

    if (props.user.roles.some(r => r.name === 'admin')) {
        permissions.push(
            'Access Admin Dashboard',
            'View Users',
            'Create Users',
            'Edit Users',
            'View Roles',
            'View Permissions',
            'Edit Profile',
            'View Profile'
        );
    }

    if (props.user.roles.some(r => r.name === 'volunteer')) {
        permissions.push(
            'View Profile',
            'Edit Profile'
        );
    }

    // Return unique permissions
    return [...new Set(permissions)];
});

// Breadcrumb data
const breadcrumbs = [
    {
        title: 'Users',
        href: route('admin.users.index')
    },
    {
        title: props.user.name,
        href: route('admin.users.show', props.user.id)
    }
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`User: ${user.name}`" />

        <div class="container mx-auto p-6">
            <div class="mb-6 flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('admin.users.index')">
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <Heading :title="user.name" description="User profile and information" />
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- User Profile Card -->
                <Card class="md:col-span-1">
                    <CardHeader class="relative pb-0">
                        <div class="absolute right-6 top-4 flex space-x-2">
                            <Button
                                v-if="canEditUser"
                                variant="ghost"
                                size="icon"
                                as-child
                            >
                                <Link :href="route('admin.users.edit', user.id)">
                                    <Pencil class="h-4 w-4" />
                                    <span class="sr-only">Edit</span>
                                </Link>
                            </Button>
                        </div>
                    </CardHeader>

                    <CardContent class="flex flex-col items-center pt-6 text-center">
                        <div class="relative">
                            <Avatar class="h-24 w-24 mb-4">
                                <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                <AvatarFallback class="text-2xl">
                                    {{ getInitials(user.name) }}
                                </AvatarFallback>
                            </Avatar>

                            <div v-if="!user.is_approved" class="absolute -top-2 -right-2">
                                <Badge variant="destructive" class="rounded-full h-6 w-6 p-0 flex items-center justify-center">
                                    <AlertTriangle class="h-3 w-3" />
                                </Badge>
                            </div>
                        </div>

                        <h3 class="text-xl font-medium">{{ user.name }}</h3>

                        <div class="mt-2 inline-flex items-center">
                            <Mail class="mr-1 h-4 w-4 text-muted-foreground" />
                            <a
                                :href="`mailto:${user.email}`"
                                class="text-sm text-muted-foreground hover:text-foreground hover:underline"
                            >
                                {{ user.email }}
                            </a>
                        </div>

                        <div v-if="user.phone" class="mt-1 inline-flex items-center">
                            <Phone class="mr-1 h-4 w-4 text-muted-foreground" />
                            <a
                                :href="`tel:${user.phone}`"
                                class="text-sm text-muted-foreground hover:text-foreground hover:underline"
                            >
                                {{ user.phone }}
                            </a>
                        </div>

                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                            <Badge
                                v-for="role in user.roles"
                                :key="role.id"
                                :class="getRoleBadgeColor(role.name)"
                                variant="outline"
                            >
                                {{ role.name }}
                            </Badge>
                            <Badge v-if="user.roles.length === 0" variant="outline">
                                No roles
                            </Badge>
                        </div>

                        <Separator class="my-4" />

                        <div class="w-full space-y-2 text-left">
                            <div class="flex items-start">
                                <UserCheck class="mr-2 h-4 w-4 mt-0.5 text-muted-foreground" />
                                <div>
                                    <div class="text-xs text-muted-foreground">Account Status</div>
                                    <div class="text-sm">
                                        <Badge :variant="user.is_approved ? 'default' : 'destructive'">
                                            {{ user.is_approved ? 'Approved' : 'Pending Approval' }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <Calendar class="mr-2 h-4 w-4 mt-0.5 text-muted-foreground" />
                                <div>
                                    <div class="text-xs text-muted-foreground">Joined</div>
                                    <div class="text-sm">{{ formatDate(user.created_at) }}</div>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <Shield class="mr-2 h-4 w-4 mt-0.5 text-muted-foreground" />
                                <div>
                                    <div class="text-xs text-muted-foreground">Email Verification</div>
                                    <div class="text-sm">
                                        <Badge :variant="user.email_verified_at ? 'default' : 'outline'">
                                            {{ user.email_verified_at ? 'Verified' : 'Not Verified' }}
                                        </Badge>
                                        <div v-if="user.email_verified_at" class="mt-0.5 text-xs text-muted-foreground">
                                            {{ formatDate(user.email_verified_at) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="fullAddress" class="flex items-start">
                                <MapPin class="mr-2 h-4 w-4 mt-0.5 text-muted-foreground" />
                                <div>
                                    <div class="text-xs text-muted-foreground">Address</div>
                                    <div class="text-sm">{{ fullAddress }}</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="justify-center pb-6">
                        <Button
                            v-if="canEditUser"
                            as-child
                        >
                            <Link :href="route('admin.users.edit', user.id)" class="flex items-center">
                                <Pencil class="mr-2 h-4 w-4" />
                                Edit User
                            </Link>
                        </Button>
                        <Button
                            v-else-if="isSuperAdmin && !page.props.auth.user.roles.includes('super-admin')"
                            variant="outline"
                            disabled
                        >
                            Super Admin Profile
                        </Button>
                    </CardFooter>
                </Card>

                <!-- User Details Cards -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Account Status Card -->
                    <Card v-if="!user.is_approved" class="border-destructive">
                        <CardHeader class="pb-3">
                            <CardTitle class="flex items-center text-destructive">
                                <AlertTriangle class="mr-2 h-5 w-5" />
                                Pending Approval
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-sm">
                                This account is pending administrator approval. The user cannot access restricted sections of the platform until approved.
                            </p>
                        </CardContent>
                        <CardFooter v-if="canEditUser">
                            <Button variant="default" as-child>
                                <Link :href="route('admin.users.edit', user.id)" class="flex items-center">
                                    <UserCheck class="mr-2 h-4 w-4" />
                                    Approve User
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>

                    <!-- Bio Card -->
                    <Card v-if="user.bio">
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <GraduationCap class="mr-2 h-5 w-5" />
                                About
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="whitespace-pre-line text-sm">{{ user.bio }}</p>
                        </CardContent>
                    </Card>

                    <!-- Address Card -->
                    <Card v-if="fullAddress">
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Building class="mr-2 h-5 w-5" />
                                Address Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div v-if="user.address">
                                    <div class="text-xs text-muted-foreground">Street Address</div>
                                    <div class="text-sm font-medium">{{ user.address }}</div>
                                </div>

                                <div v-if="user.city">
                                    <div class="text-xs text-muted-foreground">City</div>
                                    <div class="text-sm font-medium">{{ user.city }}</div>
                                </div>

                                <div v-if="user.state">
                                    <div class="text-xs text-muted-foreground">State/Province</div>
                                    <div class="text-sm font-medium">{{ user.state }}</div>
                                </div>

                                <div v-if="user.zip_code">
                                    <div class="text-xs text-muted-foreground">ZIP/Postal Code</div>
                                    <div class="text-sm font-medium">{{ user.zip_code }}</div>
                                </div>

                                <div v-if="user.country" class="sm:col-span-2">
                                    <div class="text-xs text-muted-foreground">Country</div>
                                    <div class="text-sm font-medium">{{ user.country }}</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Permissions Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Shield class="mr-2 h-5 w-5" />
                                Roles & Permissions
                            </CardTitle>
                            <CardDescription>
                                User's assigned roles and resulting permissions
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div>
                                    <h4 class="mb-2 font-medium">Assigned Roles</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <Badge
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            :class="getRoleBadgeColor(role.name)"
                                            variant="outline"
                                            class="text-sm px-3 py-1"
                                        >
                                            {{ role.name }}
                                        </Badge>
                                        <span v-if="user.roles.length === 0" class="text-sm text-muted-foreground">No roles assigned</span>
                                    </div>
                                </div>

                                <Separator />

                                <div>
                                    <h4 class="mb-2 font-medium">Effective Permissions</h4>
                                    <div class="text-sm text-muted-foreground mb-2">
                                        Based on the assigned roles, this user has the following permissions:
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div v-for="permission in userPermissions" :key="permission">
                                            <Badge variant="outline" class="bg-neutral-100 dark:bg-neutral-800">
                                                {{ permission }}
                                            </Badge>
                                        </div>

                                        <div v-if="userPermissions.length === 0" class="md:col-span-2">
                                            <span class="text-sm text-muted-foreground">No permissions available</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter v-if="canEditUser">
                            <Button variant="outline" as-child>
                                <Link :href="route('admin.users.edit', user.id)" class="flex items-center">
                                    <Shield class="mr-2 h-4 w-4" />
                                    Modify Roles
                                </Link>
                            </Button>
                        </CardFooter>
                    </Card>

                    <!-- Activity Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Calendar class="mr-2 h-5 w-5" />
                                Account Timeline
                            </CardTitle>
                            <CardDescription>
                                User account history and activities
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div class="relative pl-5 before:absolute before:top-0 before:left-0 before:h-full before:w-px before:bg-muted">
                                    <div class="relative mb-4 pl-5">
                                        <div class="absolute -left-1.5 top-1.5 h-3 w-3 rounded-full bg-primary"></div>
                                        <div class="text-sm">
                                            <div class="font-medium">Account Created</div>
                                            <div class="text-xs text-muted-foreground">{{ formatDate(user.created_at) }}</div>
                                        </div>
                                    </div>

                                    <div v-if="user.email_verified_at" class="relative mb-4 pl-5">
                                        <div class="absolute -left-1.5 top-1.5 h-3 w-3 rounded-full bg-primary"></div>
                                        <div class="text-sm">
                                            <div class="font-medium">Email Verified</div>
                                            <div class="text-xs text-muted-foreground">{{ formatDate(user.email_verified_at) }}</div>
                                        </div>
                                    </div>

                                    <div v-if="user.updated_at !== user.created_at" class="relative mb-4 pl-5">
                                        <div class="absolute -left-1.5 top-1.5 h-3 w-3 rounded-full bg-primary"></div>
                                        <div class="text-sm">
                                            <div class="font-medium">Profile Updated</div>
                                            <div class="text-xs text-muted-foreground">{{ formatDate(user.updated_at) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
