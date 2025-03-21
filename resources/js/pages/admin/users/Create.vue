<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ChevronLeft, Eye, EyeOff, Key, Mail, User } from 'lucide-vue-next';
import { ref } from 'vue';

// Components
import AdminNav from '@/components/AdminNav.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

// Types
interface Role {
    id: number;
    name: string;
}

interface Props {
    roles: Role[];
}

const props = defineProps<Props>();

// Form
const form = useForm({
    name: '',
    email: '',
    password: '',
    roles: [] as number[],
    send_verification_email: true,
});

const passwordVisible = ref(false);
const togglePasswordVisibility = () => {
    passwordVisible.value = !passwordVisible.value;
};

// Toggle role selection
const toggleRole = (roleId: number) => {
    const index = form.roles.indexOf(roleId);
    if (index === -1) {
        form.roles.push(roleId);
    } else {
        form.roles.splice(index, 1);
    }
};

// Submit form
const submit = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};

// Generate a random secure password
const generatePassword = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_-+=<>?';
    let password = '';

    // Ensure at least one of each character type
    password += chars.substr(Math.floor(Math.random() * 26), 1); // uppercase
    password += chars.substr(26 + Math.floor(Math.random() * 26), 1); // lowercase
    password += chars.substr(52 + Math.floor(Math.random() * 10), 1); // number
    password += chars.substr(62 + Math.floor(Math.random() * 14), 1); // special

    // Fill the rest randomly
    for (let i = 0; i < 8; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    // Shuffle the password
    password = password
        .split('')
        .sort(() => 0.5 - Math.random())
        .join('');

    form.password = password;
    passwordVisible.value = true;
};

// Get role color for styling
const getRoleColor = (role: string) => {
    const colors: Record<string, string> = {
        'super-admin': 'border-rose-500 bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-300',
        admin: 'border-amber-500 bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-300',
        volunteer: 'border-emerald-500 bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-300',
    };

    return colors[role] || 'border-blue-500 bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-300';
};
</script>

<template>
    <AppLayout>
        <Head title="Create User" />

        <div class="container mx-auto p-6">
            <div class="mb-6 flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="route('admin.users.index')">
                        <ChevronLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <Heading title="Create User" description="Add a new user to your system" />
            </div>

            <AdminNav />

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>User Information</CardTitle>
                        <CardDescription>Enter the basic details for the new user account.</CardDescription>
                    </CardHeader>

                    <CardContent class="space-y-4">
                        <!-- Name -->
                        <div class="grid gap-2">
                            <Label for="name" class="flex items-center gap-1">
                                <User class="h-4 w-4" />
                                <span>Full Name</span>
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                autocomplete="name"
                                :class="{ 'border-destructive': form.errors.name }"
                                placeholder="Enter full name"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Email -->
                        <div class="grid gap-2">
                            <Label for="email" class="flex items-center gap-1">
                                <Mail class="h-4 w-4" />
                                <span>Email Address</span>
                                <span class="text-destructive">*</span>
                            </Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                :class="{ 'border-destructive': form.errors.email }"
                                placeholder="Enter email address"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="flex items-center gap-1">
                                    <Key class="h-4 w-4" />
                                    <span>Password</span>
                                    <span class="text-destructive">*</span>
                                </Label>
                                <Button type="button" variant="ghost" size="sm" @click="generatePassword"> Generate </Button>
                            </div>
                            <div class="relative">
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    :type="passwordVisible ? 'text' : 'password'"
                                    required
                                    autocomplete="new-password"
                                    :class="{ 'border-destructive': form.errors.password, 'pr-10': form.password }"
                                    placeholder="Enter password"
                                />
                                <button
                                    v-if="form.password"
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                    @click="togglePasswordVisibility"
                                >
                                    <Eye v-if="!passwordVisible" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />

                            <div class="mt-2 flex items-center space-x-2">
                                <Checkbox id="send_verification_email" v-model:checked="form.send_verification_email" />
                                <Label for="send_verification_email"> Send verification email to the user </Label>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>User Roles</CardTitle>
                        <CardDescription>Assign roles to define user permissions.</CardDescription>
                    </CardHeader>

                    <CardContent>
                        <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                            <div
                                v-for="role in roles"
                                :key="role.id"
                                class="flex items-start space-x-3 rounded-lg border p-4 transition-colors"
                                :class="[form.roles.includes(role.id) ? getRoleColor(role.name) : 'border-border']"
                            >
                                <Checkbox
                                    :id="`role-${role.id}`"
                                    :checked="form.roles.includes(role.id)"
                                    @update:checked="toggleRole(role.id)"
                                    class="mt-1"
                                />
                                <div>
                                    <Label :for="`role-${role.id}`" class="font-medium">
                                        {{ role.name }}
                                    </Label>
                                    <p class="text-sm text-muted-foreground">
                                        {{ getRoleDescription(role.name) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.roles" class="mt-2" />
                    </CardContent>

                    <CardFooter class="flex justify-end space-x-4">
                        <Button type="button" variant="outline" as-child>
                            <Link :href="route('admin.users.index')">Cancel</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="min-w-[120px]">
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create User</span>
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>

<script>
function getRoleDescription(role) {
    const descriptions = {
        'super-admin': 'Full access to all system features and settings.',
        admin: 'Can manage users, content, and most system settings.',
        volunteer: 'Basic access to the system and volunteer-specific features.',
    };

    return descriptions[role] || 'Standard user permissions';
}
</script>
