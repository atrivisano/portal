<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import AvatarUpload from '@/components/AvatarUpload.vue';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

console.log(user);

// Extended form with additional fields
const form = useForm({
    name: user.name,
    email: user.email,
    phone: user.phone || '',
    bio: user.bio || '',
    address: user.address || '',
    city: user.city || '',
    state: user.state || '',
    zip_code: user.zip_code || '',
    country: user.country || '',
});

// Status message for form submission
const showSuccessMessage = ref(false);

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 3000);
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <!-- Personal Information Section -->
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Profile information" description="Update your personal information and how others see you on the platform" />

                <div class="flex flex-col sm:flex-row items-start gap-6">
                    <!-- Avatar Upload Component -->
                    <AvatarUpload
                        :user-id="user.id"
                        :name="user.name"
                        :avatar-url="user.avatar"
                        className="h-24 w-24"
                    />

                    <!-- Form Container -->
                    <div class="w-full">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label for="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                        autocomplete="name"
                                        placeholder="Full name"
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="email">Email address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.email"
                                        required
                                        autocomplete="username"
                                        placeholder="Email address"
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div class="grid gap-2">
                                    <Label for="phone">Phone number</Label>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.phone"
                                        autocomplete="tel"
                                        placeholder="Phone number"
                                    />
                                    <InputError class="mt-2" :message="form.errors.phone" />
                                </div>
                            </div>

                            <!-- Bio -->
                            <div class="grid gap-2">
                                <Label for="bio">Bio</Label>
                                <Textarea
                                    id="bio"
                                    v-model="form.bio"
                                    placeholder="Tell us a bit about yourself"
                                    class="min-h-[100px]"
                                />
                                <InputError class="mt-2" :message="form.errors.bio" />
                            </div>

                            <!-- Email Verification Notice -->
                            <div v-if="mustVerifyEmail && !user.email_verified_at" class="mt-2 text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 p-2 rounded-md">
                                <p class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle">
                                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                                        <path d="M12 9v4"></path>
                                        <path d="M12 17h.01"></path>
                                    </svg>
                                    Your email address is unverified.
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
                                    >
                                        Click here to resend the verification email.
                                    </Link>
                                </p>
                            </div>

                            <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600 bg-green-50 dark:bg-green-900/20 p-2 rounded-md">
                                <p class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    A new verification link has been sent to your email address.
                                </p>
                            </div>

                            <Separator />

                            <!-- Address Information -->
                            <div>
                                <h3 class="text-base font-medium">Address Information</h3>
                                <p class="text-sm text-muted-foreground mb-4">
                                    Your address information will be used for shipping and billing purposes.
                                </p>

                                <div class="grid gap-6 sm:grid-cols-2">
                                    <div class="grid gap-2 sm:col-span-2">
                                        <Label for="address">Street Address</Label>
                                        <Input
                                            id="address"
                                            v-model="form.address"
                                            placeholder="123 Main St."
                                        />
                                        <InputError class="mt-2" :message="form.errors.address" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="city">City</Label>
                                        <Input
                                            id="city"
                                            v-model="form.city"
                                            placeholder="City"
                                        />
                                        <InputError class="mt-2" :message="form.errors.city" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="state">State / Province</Label>
                                        <Input
                                            id="state"
                                            v-model="form.state"
                                            placeholder="State / Province"
                                        />
                                        <InputError class="mt-2" :message="form.errors.state" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="zip_code">ZIP / Postal Code</Label>
                                        <Input
                                            id="zip_code"
                                            v-model="form.zip_code"
                                            placeholder="ZIP / Postal Code"
                                        />
                                        <InputError class="mt-2" :message="form.errors.zip_code" />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="country">Country</Label>
                                        <Input
                                            id="country"
                                            v-model="form.country"
                                            placeholder="Country"
                                        />
                                        <InputError class="mt-2" :message="form.errors.country" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <Button type="submit" :disabled="form.processing">Save changes</Button>

                                <Transition
                                    enter-active-class="transition ease-in-out duration-300"
                                    enter-from-class="opacity-0 transform scale-95"
                                    leave-active-class="transition ease-in-out duration-300"
                                    leave-to-class="opacity-0 transform scale-95"
                                >
                                    <p v-if="showSuccessMessage" class="text-sm text-green-600 bg-green-50 dark:bg-green-900/20 px-3 py-1 rounded-md">
                    <span class="flex items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check">
                        <polyline points="20 6 9 17 4 12"></polyline>
                      </svg>
                      Profile updated successfully!
                    </span>
                                    </p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <Separator class="my-8" />

            <!-- Delete User Account Section -->
            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
