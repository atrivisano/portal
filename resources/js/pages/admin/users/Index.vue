<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Eye,
    Pencil,
    Plus,
    Search,
    Trash,
    FilterX,
    ChevronDown,
    ArrowUpDown,
    Check,
    Shield,
    AlertTriangle
} from 'lucide-vue-next';
import { ref } from 'vue';

// Components
import Heading from '@/components/Heading.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { useInitials } from '@/composables/useInitials';

// Types
interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
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
}

interface Props {
    users: {
        data: User[];
        links: any[];
        meta: {
            current_page: number;
            from: number;
            last_page: number;
            links: any[];
            path: string;
            per_page: number;
            to: number;
            total: number;
        };
    };
    roles: Role[];
    filters: {
        search: string;
        role: number | null;
        sort: string;
        direction: 'asc' | 'desc';
    };
}

const props = defineProps<Props>();
const { getInitials } = useInitials();

// Delete confirmation dialog
const showDeleteModal = ref(false);
const deleteForm = useForm({});
const userToDelete = ref<User | null>(null);

const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    if (!userToDelete.value) return;

    deleteForm.delete(route('admin.users.destroy', userToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            userToDelete.value = null;
        },
    });
};

// Search and filter form
const searchForm = useForm({
    search: props.filters.search || '',
    role: props.filters.role || '',
    sort: props.filters.sort || 'created_at',
    direction: props.filters.direction || 'desc',
});

const search = () => {
    searchForm.get(route('admin.users.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetSearch = () => {
    searchForm.search = '';
    searchForm.role = '';
    search();
};

// Sort table
const sort = (column: string) => {
    searchForm.sort = column;
    searchForm.direction = searchForm.sort === column && searchForm.direction === 'asc' ? 'desc' : 'asc';
    search();
};

// Format date for display
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
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

// Breadcrumb data
const breadcrumbs = [
    {
        title: 'Users',
        href: route('admin.users.index')
    }
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Users Management" />

        <div class="container mx-auto p-6">
            <div class="mb-6 flex items-center justify-between">
                <Heading title="Users" description="Manage system users and their access" />

                <Button v-if="$page.props.auth.user.can.createUsers" as-child>
                    <Link :href="route('admin.users.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        New User
                    </Link>
                </Button>
            </div>

            <!-- Search & Filters -->
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchForm.search"
                        @keyup.enter="search"
                        placeholder="Search users by name or email..."
                        class="pl-10"
                    />
                </div>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" class="flex min-w-[120px]">
                            <Shield class="mr-2 h-4 w-4" />
                            <span class="flex-1 truncate">{{ searchForm.role ? props.roles.find(r => r.id === Number(searchForm.role))?.name : 'Filter by Role' }}</span>
                            <ChevronDown class="ml-2 h-4 w-4 opacity-50" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent class="w-56">
                        <DropdownMenuLabel>Select Role</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            @click="searchForm.role = ''; search();"
                            class="flex items-center justify-between"
                        >
                            All Roles
                            <Check v-if="searchForm.role === ''" class="h-4 w-4" />
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            v-for="role in roles"
                            :key="role.id"
                            @click="searchForm.role = role.id.toString(); search();"
                            class="flex items-center justify-between"
                        >
                            {{ role.name }}
                            <Check v-if="searchForm.role === role.id.toString()" class="h-4 w-4" />
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <Button
                    v-if="searchForm.search || searchForm.role"
                    variant="ghost"
                    @click="resetSearch"
                    class="flex shrink-0 md:ml-2"
                >
                    <FilterX class="mr-2 h-4 w-4" />
                    Clear Filters
                </Button>

                <Button variant="default" @click="search" :disabled="searchForm.processing" class="shrink-0 md:ml-2">
                    <Search class="mr-2 h-4 w-4" />
                    Search
                </Button>
            </div>

            <!-- Users Table -->
            <div class="rounded-lg border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[50px]">
                                <!-- Avatar column - no sort -->
                            </TableHead>
                            <TableHead>
                                <Button
                                    variant="ghost"
                                    class="flex items-center hover:bg-transparent p-0 font-medium"
                                    @click="sort('name')"
                                >
                                    Name
                                    <ArrowUpDown
                                        class="ml-2 h-4 w-4"
                                        :class="{
                      'text-foreground': searchForm.sort === 'name',
                      'text-muted-foreground': searchForm.sort !== 'name'
                    }"
                                    />
                                </Button>
                            </TableHead>
                            <TableHead>
                                <Button
                                    variant="ghost"
                                    class="flex items-center hover:bg-transparent p-0 font-medium"
                                    @click="sort('email')"
                                >
                                    Email
                                    <ArrowUpDown
                                        class="ml-2 h-4 w-4"
                                        :class="{
                      'text-foreground': searchForm.sort === 'email',
                      'text-muted-foreground': searchForm.sort !== 'email'
                    }"
                                    />
                                </Button>
                            </TableHead>
                            <TableHead>Roles</TableHead>
                            <TableHead>
                                <Button
                                    variant="ghost"
                                    class="flex items-center hover:bg-transparent p-0 font-medium"
                                    @click="sort('created_at')"
                                >
                                    Joined
                                    <ArrowUpDown
                                        class="ml-2 h-4 w-4"
                                        :class="{
                      'text-foreground': searchForm.sort === 'created_at',
                      'text-muted-foreground': searchForm.sort !== 'created_at'
                    }"
                                    />
                                </Button>
                            </TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in users.data" :key="user.id" :class="{'opacity-60': !user.is_approved}">
                            <TableCell>
                                <div class="relative">
                                    <Avatar class="h-8 w-8">
                                        <AvatarImage v-if="user.avatar" :src="user.avatar" :alt="user.name" />
                                        <AvatarFallback>
                                            {{ getInitials(user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div v-if="!user.is_approved" class="absolute -top-1 -right-1">
                                        <Badge variant="destructive" class="h-4 w-4 rounded-full p-0 flex items-center justify-center">
                                            <AlertTriangle class="h-2 w-2" />
                                        </Badge>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="font-medium">{{ user.name }}</div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center">
                                    {{ user.email }}
                                    <Badge
                                        v-if="!user.email_verified_at"
                                        variant="outline"
                                        class="ml-2 text-xs"
                                    >
                                        Unverified
                                    </Badge>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="role in user.roles"
                                        :key="role.id"
                                        :class="getRoleBadgeColor(role.name)"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        {{ role.name }}
                                    </Badge>
                                    <span v-if="user.roles.length === 0" class="text-xs text-muted-foreground">No roles</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                {{ formatDate(user.created_at) }}
                            </TableCell>
                            <TableCell>
                                <Badge :variant="user.is_approved ? 'default' : 'destructive'">
                                    {{ user.is_approved ? 'Approved' : 'Pending' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button v-if="$page.props.auth.user.can.viewUsers" variant="ghost" size="icon" as-child>
                                        <Link :href="route('admin.users.show', user.id)">
                                            <Eye class="h-4 w-4" />
                                            <span class="sr-only">View</span>
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="$page.props.auth.user.can.editUsers"
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                        :disabled="user.roles.some(r => r.name === 'super-admin') && !$page.props.auth.user.roles.includes('super-admin')"
                                    >
                                        <Link :href="route('admin.users.edit', user.id)">
                                            <Pencil class="h-4 w-4" />
                                            <span class="sr-only">Edit</span>
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="$page.props.auth.user.can.deleteUsers"
                                        variant="ghost"
                                        size="icon"
                                        @click="confirmDelete(user)"
                                        :disabled="user.roles.some(r => r.name === 'super-admin') || user.id === $page.props.auth.user.id"
                                    >
                                        <Trash class="h-4 w-4" />
                                        <span class="sr-only">Delete</span>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="users.data.length === 0">
                            <TableCell colspan="7" class="py-10 text-center text-muted-foreground">
                                <div class="flex flex-col items-center justify-center gap-1">
                                    <Users class="h-8 w-8 text-muted-foreground/50" />
                                    <h3 class="mt-2 text-sm font-medium">No users found</h3>
                                    <p class="text-xs text-muted-foreground">
                                        {{ searchForm.search || searchForm.role ? 'Try changing your search filters' : 'Get started by adding a new user' }}
                                    </p>
                                    <Button
                                        v-if="$page.props.auth.user.can.createUsers && !searchForm.search && !searchForm.role"
                                        variant="outline"
                                        size="sm"
                                        as-child
                                        class="mt-4"
                                    >
                                        <Link :href="route('admin.users.create')">
                                            <Plus class="mr-2 h-4 w-4" />
                                            Add New User
                                        </Link>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="users.meta && users.meta.last_page > 1" class="mt-6 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Showing {{ users.meta.from }} to {{ users.meta.to }} of {{ users.meta.total }} users
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in users.meta.links"
                        :key="link.label"
                        :disabled="!link.url || link.active"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        as-child
                    >
                        <Link v-if="link.url" :href="link.url" v-html="link.label" />
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteModal">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete User</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete the user "{{ userToDelete?.name }}"? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false" :disabled="deleteForm.processing">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="deleteUser" :disabled="deleteForm.processing">
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
