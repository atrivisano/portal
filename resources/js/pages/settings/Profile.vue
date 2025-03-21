<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Calendar, CheckCircle2, Clock, Mail, MapPin, Phone, Shield, User } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

// Components
import EnhancedAvatarUpload from '@/components/AvatarUpload.vue';
import DeleteUser from '@/components/DeleteUser.vue';
import InputError from '@/components/InputError.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    successMessage?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;
const activeTab = ref('profile');
const showSuccessMessage = ref(false);
const successMessage = ref('');

// Set initial success message if provided
onMounted(() => {
    if (props.successMessage) {
        successMessage.value = props.successMessage;
        showSuccessMessage.value = true;
        setTimeout(() => {
            showSuccessMessage.value = false;
        }, 5000);
    }
});

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

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Profile updated successfully!';
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 5000);
        },
    });
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return 'Not verified';

    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Handle avatar update
const handleAvatarUpdate = () => {
    // Refresh the page to show updated avatar
    window.location.reload();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <!-- Success message -->
                <Alert v-if="showSuccessMessage" variant="success" class="bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                    <CheckCircle2 class="h-4 w-4" />
                    <AlertTitle>Success</AlertTitle>
                    <AlertDescription>{{ successMessage }}</AlertDescription>
                </Alert>

                <!-- Profile tabs -->
                <Tabs v-model="activeTab" class="w-full space-y-6">
                    <TabsList class="mb-6 grid w-full grid-cols-2">
                        <TabsTrigger value="profile" class="flex gap-2">
                            <User class="h-4 w-4" />
                            <span>Profile</span>
                        </TabsTrigger>
                        <TabsTrigger value="account" class="flex gap-2">
                            <Shield class="h-4 w-4" />
                            <span>Account</span>
                        </TabsTrigger>
                    </TabsList>

                    <!-- Profile Tab -->
                    <TabsContent value="profile" class="space-y-6">
                        <!-- Header Section with Avatar -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Profile Information</CardTitle>
                                <CardDescription> Update your personal information and how others see you on the platform </CardDescription>
                            </CardHeader>

                            <CardContent>
                                <div class="flex flex-col items-start gap-6 sm:flex-row">
                                    <!-- Avatar Section -->
                                    <div class="flex w-full flex-col items-center gap-2 sm:w-auto">
                                        <EnhancedAvatarUpload
                                            :user-id="user.id"
                                            :name="user.name"
                                            :avatar-url="user.avatar"
                                            size="xl"
                                            shape="circle"
                                            class="mb-2"
                                            @updated="handleAvatarUpdate"
                                        />
                                        <span class="text-sm text-muted-foreground">Profile Photo</span>
                                    </div>

                                    <!-- Form Container -->
                                    <div class="w-full">
                                        <form @submit.prevent="submit" class="space-y-6">
                                            <!-- Basic Information -->
                                            <div class="grid gap-6 sm:grid-cols-2">
                                                <div class="grid gap-2">
                                                    <Label for="name" class="flex items-center gap-1">
                                                        <User class="h-4 w-4" />
                                                        <span>Full Name</span>
                                                        <span class="text-destructive">*</span>
                                                    </Label>
                                                    <Input
                                                        id="name"
                                                        class="mt-1 block w-full"
                                                        v-model="form.name"
                                                        required
                                                        autocomplete="name"
                                                        placeholder="Full name"
                                                    />
                                                    <InputError :message="form.errors.name" />
                                                </div>

                                                <div class="grid gap-2">
                                                    <Label for="email" class="flex items-center gap-1">
                                                        <Mail class="h-4 w-4" />
                                                        <span>Email address</span>
                                                        <span class="text-destructive">*</span>
                                                    </Label>
                                                    <Input
                                                        id="email"
                                                        type="email"
                                                        class="mt-1 block w-full"
                                                        v-model="form.email"
                                                        required
                                                        autocomplete="username"
                                                        placeholder="Email address"
                                                    />
                                                    <InputError :message="form.errors.email" />
                                                </div>

                                                <div class="grid gap-2">
                                                    <Label for="phone" class="flex items-center gap-1">
                                                        <Phone class="h-4 w-4" />
                                                        <span>Phone number</span>
                                                    </Label>
                                                    <Input
                                                        id="phone"
                                                        type="tel"
                                                        class="mt-1 block w-full"
                                                        v-model="form.phone"
                                                        autocomplete="tel"
                                                        placeholder="Phone number"
                                                    />
                                                    <InputError :message="form.errors.phone" />
                                                </div>
                                            </div>

                                            <!-- Bio -->
                                            <div class="grid gap-2">
                                                <Label for="bio" class="flex items-center gap-1">
                                                    <User class="h-4 w-4" />
                                                    <span>About You</span>
                                                </Label>
                                                <Textarea
                                                    id="bio"
                                                    v-model="form.bio"
                                                    placeholder="Tell us a bit about yourself"
                                                    class="min-h-[100px]"
                                                />
                                                <InputError :message="form.errors.bio" />
                                            </div>

                                            <!-- Button row -->
                                            <div class="flex items-center gap-4">
                                                <Button type="submit" :disabled="form.processing">
                                                    <RefreshCw v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                                    Save changes
                                                </Button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Address Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <MapPin class="h-5 w-5" />
                                    Address Information
                                </CardTitle>
                                <CardDescription> Your address information will be used for shipping and billing purposes. </CardDescription>
                            </CardHeader>

                            <CardContent>
                                <form @submit.prevent="submit" class="space-y-6">
                                    <div class="grid gap-6 sm:grid-cols-2">
                                        <div class="grid gap-2 sm:col-span-2">
                                            <Label for="address">Street Address</Label>
                                            <Input id="address" v-model="form.address" placeholder="123 Main St." />
                                            <InputError :message="form.errors.address" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="city">City</Label>
                                            <Input id="city" v-model="form.city" placeholder="City" />
                                            <InputError :message="form.errors.city" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="state">State / Province</Label>
                                            <Input id="state" v-model="form.state" placeholder="State / Province" />
                                            <InputError :message="form.errors.state" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="zip_code">ZIP / Postal Code</Label>
                                            <Input id="zip_code" v-model="form.zip_code" placeholder="ZIP / Postal Code" />
                                            <InputError :message="form.errors.zip_code" />
                                        </div>

                                        <div class="grid gap-2">
                                            <Label for="country">Country</Label>
                                            <Input id="country" v-model="form.country" placeholder="Country" />
                                            <InputError :message="form.errors.country" />
                                        </div>
                                    </div>

                                    <Button type="submit" :disabled="form.processing">
                                        <RefreshCw v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                                        Save address
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <!-- Account Tab -->
                    <TabsContent value="account" class="space-y-6">
                        <!-- Account Information -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <Shield class="h-5 w-5" />
                                    Account Information
                                </CardTitle>
                                <CardDescription> View your account details and verification status</CardDescription>
                            </CardHeader>

                            <CardContent>
                                <div class="space-y-6">
                                    <!-- Email verification status -->
                                    <div class="flex items-start space-x-4">
                                        <div class="mt-0.5">
                                            <Badge
                                                variant="outline"
                                                :class="
                                                    user.email_verified_at
                                                        ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-300'
                                                        : 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-300'
                                                "
                                            >
                                                <Clock v-if="!user.email_verified_at" class="mr-1 h-3 w-3" />
                                                <CheckCircle2 v-else class="mr-1 h-3 w-3" />
                                                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                            </Badge>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-medium">Email Verification</h4>
                                            <p class="text-sm text-muted-foreground">
                                                {{
                                                    user.email_verified_at
                                                        ? 'Your email is verified on ' + formatDate(user.email_verified_at)
                                                        : 'Your email address has not been verified.'
                                                }}
                                            </p>

                                            <div v-if="mustVerifyEmail && !user.email_verified_at" class="mt-2">
                                                <p class="text-sm text-muted-foreground">Verify your email address to access all features.</p>
                                                <Button variant="outline" size="sm" as-child class="mt-2">
                                                    <Link :href="route('verification.send')" method="post" as="button">
                                                        Resend verification email
                                                    </Link>
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <Separator />

                                    <!-- Account creation date -->
                                    <div class="flex items-start space-x-4">
                                        <Calendar class="mt-0.5 h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <h4 class="text-sm font-medium">Account Created</h4>
                                            <p class="text-sm text-muted-foreground">
                                                {{ formatDate(user.created_at) }}
                                            </p>
                                        </div>
                                    </div>

                                    <Separator />

                                    <!-- User roles -->
                                    <div class="flex items-start space-x-4">
                                        <Shield class="mt-0.5 h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <h4 class="text-sm font-medium">Your Roles</h4>
                                            <div class="mt-1 flex flex-wrap gap-2">
                                                <Badge v-for="role in user.roles" :key="role" variant="secondary" class="capitalize">
                                                    {{ role }}
                                                </Badge>
                                                <span v-if="!user.roles?.length" class="text-sm text-muted-foreground">
                                                    No special roles assigned
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <Separator />

                                    <!-- Password reset link -->
                                    <div class="flex items-start space-x-4">
                                        <Lock class="mt-0.5 h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <h4 class="text-sm font-medium">Password</h4>
                                            <p class="text-sm text-muted-foreground">Update your password regularly for security</p>
                                            <Button variant="outline" size="sm" as-child class="mt-2">
                                                <Link :href="route('password.edit')"> Change password</Link>
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Delete Account Card -->
                        <DeleteUser />
                    </TabsContent>
                </Tabs>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
