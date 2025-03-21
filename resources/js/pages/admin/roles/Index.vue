<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Search, Trash } from 'lucide-vue-next';
import { ref } from 'vue';

// Components
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';

// Types
interface Role {
    id: number;
    name: string;
    permissions_count: number;
    users_count: number;
}

interface Props {
    roles: {
        data: Role[];
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
    filters: {
        search: string;
    };
}

const props = defineProps<Props>();

// Delete confirmation dialog
const showDeleteModal = ref(false);
const deleteForm = useForm({});
const roleToDelete = ref<Role | null>(null);

const confirmDelete = (role: Role) => {
    roleToDelete.value = role;
    showDeleteModal.value = true;
};

const deleteRole = () => {
    if (!roleToDelete.value) return;

    deleteForm.delete(route('admin.roles.destroy', roleToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            roleToDelete.value = null;
        },
    });
};

// Search form
const searchForm = useForm({
    search: props.filters.search || '',
});

const search = () => {
    searchForm.get(route('admin.roles.index'), {
        preserveState: true,
        preserveScroll: true,
    });
};

const resetSearch = () => {
    searchForm.search = '';
    search();
};

// Generate a light color based on role name for badges
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
        <Head title="Roles" />

        <div class="container mx-auto p-6">
            <div class="mb-6 flex items-center justify-between">
                <Heading title="Roles" description="Manage user roles and permissions" />

                <Button v-if="$page.props.auth.user.can.create_roles" as-child>
                    <Link :href="route('admin.roles.create')">
                        <Plus class="mr-2 h-4 w-4" />
                        New Role
                    </Link>
                </Button>
            </div>

            <!-- Search & Filters -->
            <div class="mb-6 flex items-center gap-4">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchForm.search" @keyup.enter="search" placeholder="Search roles..." class="pl-10" />
                </div>

                <Button v-if="searchForm.search" variant="ghost" @click="resetSearch"> Clear </Button>

                <Button variant="default" @click="search" :disabled="searchForm.processing"> Search </Button>
            </div>

            <!-- Roles Table -->
            <div class="rounded-lg border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Name</TableHead>
                            <TableHead>Permissions</TableHead>
                            <TableHead>Users</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="role in roles.data" :key="role.id">
                            <TableCell>
                                <Badge :style="{ backgroundColor: getRoleColor(role.name) }" class="text-foreground">
                                    {{ role.name }}
                                </Badge>
                            </TableCell>
                            <TableCell>
                                {{ role.permissions_count }}
                            </TableCell>
                            <TableCell>
                                {{ role.users_count }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button v-if="$page.props.auth.user.can.view_roles" variant="ghost" size="icon" as-child>
                                        <Link :href="route('admin.roles.show', role.id)">
                                            <Eye class="h-4 w-4" />
                                            <span class="sr-only">View</span>
                                        </Link>
                                    </Button>

                                    <Button v-if="$page.props.auth.user.can.edit_roles" variant="ghost" size="icon" as-child>
                                        <Link :href="route('admin.roles.edit', role.id)">
                                            <Pencil class="h-4 w-4" />
                                            <span class="sr-only">Edit</span>
                                        </Link>
                                    </Button>

                                    <Button
                                        v-if="$page.props.auth.user.can.delete_roles"
                                        variant="ghost"
                                        size="icon"
                                        @click="confirmDelete(role)"
                                        :disabled="role.name === 'super-admin'"
                                    >
                                        <Trash class="h-4 w-4" />
                                        <span class="sr-only">Delete</span>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="roles.data.length === 0">
                            <TableCell colspan="4" class="py-8 text-center text-muted-foreground"> No roles found. </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="roles.meta.last_page > 1" class="mt-6 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">Showing {{ roles.meta.from }} to {{ roles.meta.to }} of {{ roles.meta.total }} roles</div>

                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in roles.meta.links"
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
                    <DialogTitle>Delete Role</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete the role "{{ roleToDelete?.name }}"? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                    <Button variant="outline" @click="showDeleteModal = false" :disabled="deleteForm.processing"> Cancel </Button>
                    <Button variant="destructive" @click="deleteRole" :disabled="deleteForm.processing"> Delete </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
