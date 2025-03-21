<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import { computed } from 'vue';

// Components
import Heading from '@/components/Heading.vue';
import UserForm from '@/components/admin/UserForm.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

// Types
interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    phone?: string;
    bio?: string;
    address?: string;
    city?: string;
    state?: string;
    zip_code?: string;
    country?: string;
    is_approved: boolean;
    roles: number[];
}

interface Props {
    user: User;
    roles: Role[];
}

const props = defineProps<Props>();

// Check if user is super-admin
const isSuperAdmin = computed(() => {
    return props.roles.some(role =>
            role.id.toString() === props.user.roles.find(r =>
                props.roles.find(role => role.id === r)?.name === 'super-admin'
            )?.toString()
    );
});

// Create form
const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '', // Empty for edit mode
    phone: props.user.phone || '',
    bio: props.user.bio || '',
    address: props.user.address || '',
    city: props.user.city || '',
    state: props.user.state || '',
    zip_code: props.user.zip_code || '',
    country: props.user.country || '',
    is_approved: props.user.is_approved,
    roles: props.user.roles,
    _method: 'PUT'
});

// Handle form submission
const submit = () => {
    form.post(route('admin.users.update', props.user.id));
};

// Get user name for display
const userName = computed(() => props.user.name);

// Breadcrumb data
const breadcrumbs = [
    {
        title: 'Users',
        href: route('admin.users.index')
    },
    {
        title: userName.value,
        href: route('admin.users.show', props.user.id)
    },
    {
        title: 'Edit',
        href: route('admin.users.edit', props.user.id)
    }
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Edit User: ${userName}`" />

        <div class="container mx-auto p-6">
            <div class="mb-6 flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('admin.users.show', user.id)">
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <Heading :title="`Edit User: ${userName}`" description="Update user details and permissions" />
            </div>

            <UserForm
                :form="form"
                :roles="roles"
                :isEditMode="true"
                :isSuperAdmin="isSuperAdmin"
                @submit="submit"
            />
        </div>
    </AppLayout>
</template>
