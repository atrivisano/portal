<script setup lang="ts">
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import {
    UserCheck,
    Building,
    BadgeCheck,
    UserCog,
    Shield
} from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';

// Define props
interface Role {
    id: number;
    name: string;
}

interface Props {
    form: any; // The form object from useForm
    roles: Role[];
    isEditMode: boolean;
    isSuperAdmin?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isSuperAdmin: false
});

// Emit events
const emit = defineEmits(['submit']);

// Handle form submission
const handleSubmit = () => {
    emit('submit');
};

// Compute whether editing super-admin
const isEditingSuperAdmin = computed(() => {
    return props.isEditMode && props.isSuperAdmin;
});

// Get roles based on user permissions
const currentUserIsSuperAdmin = computed(() => {
    return usePage().props.auth.user.roles.includes('super-admin');
});

// Check if user can manage super-admin role
const canAssignSuperAdmin = computed(() => {
    return currentUserIsSuperAdmin.value;
});

// Get role badge color
const getRoleBadgeColor = (role: string) => {
    const colors: Record<string, string> = {
        'super-admin': 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
        'admin': 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        'volunteer': 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    };

    return colors[role] || 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300';
};
</script>

<template>
    <form @submit.prevent="handleSubmit">
        <div class="grid gap-6">
            <!-- Basic Information -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <UserCog class="mr-2 h-5 w-5" />
                        User Information
                    </CardTitle>
                    <CardDescription>
                        {{ isEditMode ? 'Edit the user\'s basic information' : 'Enter the new user\'s basic information' }}
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="name" class="required">Full Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                :class="{ 'border-destructive': form.errors.name }"
                                placeholder="Enter full name"
                                :disabled="isEditingSuperAdmin && !currentUserIsSuperAdmin"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" class="required">Email Address</Label>
                            <Input
                                id="email"
                                type="email"
                                v-model="form.email"
                                :class="{ 'border-destructive': form.errors.email }"
                                placeholder="user@example.com"
                                :disabled="isEditingSuperAdmin && !currentUserIsSuperAdmin"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="phone">Phone Number</Label>
                            <Input
                                id="phone"
                                type="tel"
                                v-model="form.phone"
                                :class="{ 'border-destructive': form.errors.phone }"
                                placeholder="(123) 456-7890"
                            />
                            <InputError :message="form.errors.phone" />
                        </div>

                        <div class="grid gap-2" v-if="!isEditMode">
                            <Label for="password" :class="{ required: !isEditMode }">
                                {{ isEditMode ? 'New Password' : 'Password' }}
                            </Label>
                            <Input
                                id="password"
                                type="password"
                                v-model="form.password"
                                :class="{ 'border-destructive': form.errors.password }"
                                :placeholder="isEditMode ? 'Leave blank to keep current password' : 'Enter secure password'"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="sm:col-span-2 flex items-center space-x-2">
                            <Checkbox
                                id="is_approved"
                                v-model:checked="form.is_approved"
                                :disabled="isEditingSuperAdmin && !currentUserIsSuperAdmin"
                            />
                            <Label for="is_approved" class="font-normal">
                                Account is approved and active
                            </Label>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Bio -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <BadgeCheck class="mr-2 h-5 w-5" />
                        User Bio
                    </CardTitle>
                    <CardDescription>
                        Enter a brief biography or description for this user
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="grid gap-2">
                        <Label for="bio">Bio</Label>
                        <Textarea
                            id="bio"
                            v-model="form.bio"
                            :class="{ 'border-destructive': form.errors.bio }"
                            placeholder="Enter user description or biography"
                            rows="5"
                        />
                        <InputError :message="form.errors.bio" />
                        <p class="text-xs text-muted-foreground">
                            This information will be displayed on the user's profile.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Address Information -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <Building class="mr-2 h-5 w-5" />
                        Address Information
                    </CardTitle>
                    <CardDescription>
                        Enter the user's address information (optional)
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="grid gap-2 sm:col-span-2">
                            <Label for="address">Street Address</Label>
                            <Input
                                id="address"
                                v-model="form.address"
                                :class="{ 'border-destructive': form.errors.address }"
                                placeholder="123 Main St"
                            />
                            <InputError :message="form.errors.address" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="city">City</Label>
                            <Input
                                id="city"
                                v-model="form.city"
                                :class="{ 'border-destructive': form.errors.city }"
                                placeholder="New York"
                            />
                            <InputError :message="form.errors.city" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="state">State / Province</Label>
                            <Input
                                id="state"
                                v-model="form.state"
                                :class="{ 'border-destructive': form.errors.state }"
                                placeholder="NY"
                            />
                            <InputError :message="form.errors.state" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="zip_code">ZIP / Postal Code</Label>
                            <Input
                                id="zip_code"
                                v-model="form.zip_code"
                                :class="{ 'border-destructive': form.errors.zip_code }"
                                placeholder="10001"
                            />
                            <InputError :message="form.errors.zip_code" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="country">Country</Label>
                            <Input
                                id="country"
                                v-model="form.country"
                                :class="{ 'border-destructive': form.errors.country }"
                                placeholder="United States"
                            />
                            <InputError :message="form.errors.country" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Role Assignment -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center">
                        <Shield class="mr-2 h-5 w-5" />
                        Roles
                    </CardTitle>
                    <CardDescription>
                        Assign roles to the user to grant permissions
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <div v-if="isEditingSuperAdmin && !currentUserIsSuperAdmin" class="mb-4 rounded-md bg-amber-50 p-4 text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                        <p class="text-sm">
                            Only another Super Admin can modify roles for a Super Admin user.
                        </p>
                    </div>

                    <div class="grid gap-6">
                        <div>
                            <div class="mb-4">
                                <h4 class="text-sm font-medium mb-1">Available Roles</h4>
                                <p class="text-xs text-muted-foreground">
                                    Select one or more roles to assign to this user
                                </p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                <div v-for="role in roles" :key="role.id" class="flex items-center space-x-2">
                                    <Checkbox
                                        :id="'role_' + role.id"
                                        :name="'roles[]'"
                                        :value="role.id"
                                        v-model:checked="form.roles"
                                        :disabled="(role.name === 'super-admin' && !canAssignSuperAdmin) || (isEditingSuperAdmin && !currentUserIsSuperAdmin)"
                                    />
                                    <Label :for="'role_' + role.id" class="font-normal flex items-center">
                    <span
                        class="px-2 py-0.5 rounded-full text-xs font-medium"
                        :class="getRoleBadgeColor(role.name)"
                    >
                      {{ role.name }}
                    </span>
                                    </Label>
                                </div>
                            </div>

                            <InputError :message="form.errors.roles" class="mt-2" />
                        </div>

                        <div v-if="!canAssignSuperAdmin" class="text-xs text-muted-foreground">
                            <p>Note: Only Super Admins can assign the Super Admin role.</p>
                        </div>
                    </div>
                </CardContent>

                <CardFooter class="flex justify-between">
                    <Button
                        variant="outline"
                        type="button"
                        as-child
                    >
                        <Link :href="route('admin.users.index')">
                            Cancel
                        </Link>
                    </Button>

                    <Button
                        type="submit"
                        :disabled="form.processing || (isEditingSuperAdmin && !currentUserIsSuperAdmin)"
                    >
                        {{ isEditMode ? 'Update User' : 'Create User' }}
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </form>
</template>
