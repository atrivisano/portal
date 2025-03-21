<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Check, ChevronLeft } from 'lucide-vue-next';

// Components
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/AppLayout.vue';

// Types
interface Permission {
    id: number;
    name: string;
}

interface Props {
    permissions: Record<string, Permission[]>;
}

const props = defineProps<Props>();

// Form
const form = useForm({
    name: '',
    permissions: [] as number[],
});

// Handle form submission
const submit = () => {
    form.post(route('admin.roles.store'));
};

// Toggle all permissions in a group
const toggleGroup = (group: string, checked: boolean) => {
    const permissionsInGroup = props.permissions[group] || [];

    if (checked) {
        // Add all permissions from this group
        const permissionIds = permissionsInGroup.map((p) => p.id);
        const newPermissions = [...new Set([...form.permissions, ...permissionIds])];
        form.permissions = newPermissions;
    } else {
        // Remove all permissions from this group
        const permissionIds = permissionsInGroup.map((p) => p.id);
        form.permissions = form.permissions.filter((id) => !permissionIds.includes(id));
    }
};

// Check if all permissions in a group are selected
const isGroupSelected = (group: string) => {
    const permissionsInGroup = props.permissions[group] || [];
    if (permissionsInGroup.length === 0) return false;

    return permissionsInGroup.every((p) => form.permissions.includes(p.id));
};

// Check if some permissions in a group are selected
const isGroupIndeterminate = (group: string) => {
    const permissionsInGroup = props.permissions[group] || [];
    if (permissionsInGroup.length === 0) return false;

    const selectedCount = permissionsInGroup.filter((p) => form.permissions.includes(p.id)).length;
    return selectedCount > 0 && selectedCount < permissionsInGroup.length;
};

// Format permission name for display
const formatPermissionName = (name: string) => {
    // Remove the group prefix (e.g., "users.create" -> "create")
    const parts = name.split('.');
    if (parts.length > 1) {
        // Convert to sentence case
        return parts[1].charAt(0).toUpperCase() + parts[1].slice(1).replace(/_/g, ' ');
    }

    // If no prefix, just capitalize
    return name.charAt(0).toUpperCase() + name.slice(1).replace(/_/g, ' ');
};

// Format group name for display
const formatGroupName = (name: string) => {
    return name.charAt(0).toUpperCase() + name.slice(1).replace(/_/g, ' ');
};

// Toggle a specific permission
const togglePermission = (permissionId: number) => {
    if (form.permissions.includes(permissionId)) {
        form.permissions = form.permissions.filter((id) => id !== permissionId);
    } else {
        form.permissions.push(permissionId);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Create Role" />

        <div class="container mx-auto p-6">
            <div class="mb-6 flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('admin.roles.index')">
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <Heading title="Create Role" description="Create a new role with permissions" />
            </div>

            <form @submit.prevent="submit">
                <div class="grid gap-6">
                    <!-- Role Name Input -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Role Information</CardTitle>
                            <CardDescription> Provide a unique name for this role </CardDescription>
                        </CardHeader>

                        <CardContent>
                            <div class="grid w-full items-center gap-3">
                                <Label for="name" class="required">Role Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="e.g. Editor, Manager"
                                    :class="{ 'border-destructive': form.errors.name }"
                                />
                                <InputError :message="form.errors.name" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Permissions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Permissions</CardTitle>
                            <CardDescription> Select the permissions to assign to this role </CardDescription>
                        </CardHeader>

                        <CardContent>
                            <div class="grid gap-4">
                                <Accordion type="multiple" :default-value="Object.keys(permissions)">
                                    <AccordionItem v-for="(groupPermissions, group) in permissions" :key="group" :value="group">
                                        <AccordionTrigger class="group">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="relative h-5 w-5 rounded border border-primary"
                                                    :class="{
                                                        'bg-primary text-primary-foreground': isGroupSelected(group),
                                                        'bg-primary/20': isGroupIndeterminate(group),
                                                    }"
                                                >
                                                    <Check v-if="isGroupSelected(group)" class="absolute inset-0 m-0.5 h-4 w-4" strokeWidth="{3}" />
                                                    <div
                                                        v-else-if="isGroupIndeterminate(group)"
                                                        class="absolute inset-0 flex items-center justify-center"
                                                    >
                                                        <div class="h-2 w-2 rounded-sm bg-primary" />
                                                    </div>
                                                </div>
                                                <span class="font-medium">{{ formatGroupName(group) }}</span>
                                            </div>
                                        </AccordionTrigger>

                                        <AccordionContent>
                                            <div class="pl-8 pt-2">
                                                <div class="mb-3 flex items-center">
                                                    <Switch
                                                        :id="`group-${group}`"
                                                        :checked="isGroupSelected(group)"
                                                        @update:checked="toggleGroup(group, $event)"
                                                    />
                                                    <Label :for="`group-${group}`" class="ml-2">
                                                        Select all {{ formatGroupName(group) }} permissions
                                                    </Label>
                                                </div>

                                                <Separator class="my-3" />

                                                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                                                    <div
                                                        v-for="permission in groupPermissions"
                                                        :key="permission.id"
                                                        class="flex items-center space-x-2"
                                                    >
                                                        <Switch
                                                            :id="`perm-${permission.id}`"
                                                            :checked="form.permissions.includes(permission.id)"
                                                            @update:checked="togglePermission(permission.id)"
                                                        />
                                                        <Label :for="`perm-${permission.id}`" class="ml-2">
                                                            {{ formatPermissionName(permission.name) }}
                                                        </Label>
                                                    </div>
                                                </div>
                                            </div>
                                        </AccordionContent>
                                    </AccordionItem>
                                </Accordion>

                                <InputError :message="form.errors.permissions" />
                            </div>
                        </CardContent>

                        <CardFooter class="flex justify-end gap-3">
                            <Button type="button" variant="outline" as-child>
                                <Link :href="route('admin.roles.index')"> Cancel </Link>
                            </Button>

                            <Button type="submit" :disabled="form.processing"> Create Role </Button>
                        </CardFooter>
                    </Card>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
