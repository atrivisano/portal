<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Mail, RefreshCw, Save, Settings, ShieldCheck } from 'lucide-vue-next';
import { ref } from 'vue';

// Components
import AdminNav from '@/components/AdminNav.vue';
import Heading from '@/components/Heading.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

interface SettingsProps {
    settings: {
        general: {
            site_name: string;
            site_description: string;
            admin_email: string;
            users_require_approval: boolean;
        };
        security: {
            password_expiry_days: number;
            session_timeout_minutes: number;
            two_factor_authentication: boolean;
        };
        email: {
            email_notifications: boolean;
            welcome_email: boolean;
            approval_required_email: boolean;
        };
    };
}

const props = defineProps<SettingsProps>();

// Breadcrumb data
const breadcrumbs = [
    {
        title: 'Settings',
        href: route('admin.settings'),
    },
];

// Form for general settings
const form = useForm({
    general: {
        site_name: props.settings.general.site_name,
        site_description: props.settings.general.site_description,
        admin_email: props.settings.general.admin_email,
        users_require_approval: props.settings.general.users_require_approval,
    },
    security: {
        password_expiry_days: props.settings.security.password_expiry_days,
        session_timeout_minutes: props.settings.security.session_timeout_minutes,
        two_factor_authentication: props.settings.security.two_factor_authentication,
    },
    email: {
        email_notifications: props.settings.email.email_notifications,
        welcome_email: props.settings.email.welcome_email,
        approval_required_email: props.settings.email.approval_required_email,
    },
});

// Active tab state
const activeTab = ref('general');

// Form submission
const saveSettings = () => {
    form.post(route('admin.settings.update'), {
        onSuccess: () => {
            // Show success message
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="System Settings" />

        <div class="container mx-auto p-6">
            <div class="mb-6 flex items-center justify-between">
                <Heading title="System Settings" description="Configure platform settings and preferences" />

                <Button @click="saveSettings" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    Save Changes
                </Button>
            </div>

            <AdminNav />

            <!-- Flash message for success -->
            <Alert
                v-if="$page.props.flash?.message"
                variant="default"
                class="mb-6 bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-300"
            >
                <RefreshCw class="h-4 w-4" />
                <AlertTitle>Success</AlertTitle>
                <AlertDescription>{{ $page.props.flash.message }}</AlertDescription>
            </Alert>

            <Tabs v-model="activeTab" class="space-y-4">
                <TabsList class="grid w-full grid-cols-3">
                    <TabsTrigger value="general" class="flex items-center gap-2">
                        <Settings class="h-4 w-4" />
                        <span>General</span>
                    </TabsTrigger>
                    <TabsTrigger value="security" class="flex items-center gap-2">
                        <ShieldCheck class="h-4 w-4" />
                        <span>Security</span>
                    </TabsTrigger>
                    <TabsTrigger value="email" class="flex items-center gap-2">
                        <Mail class="h-4 w-4" />
                        <span>Email</span>
                    </TabsTrigger>
                </TabsList>

                <!-- General Settings Tab -->
                <TabsContent value="general" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>General Settings</CardTitle>
                            <CardDescription>
                                Configure general platform settings like site name and administrative contact information
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="site_name">Site Name</Label>
                                <Input id="site_name" v-model="form.general.site_name" placeholder="My Client Portal" />
                                <p v-if="form.errors['general.site_name']" class="text-sm text-destructive">
                                    {{ form.errors['general.site_name'] }}
                                </p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="site_description">Site Description</Label>
                                <Textarea
                                    id="site_description"
                                    v-model="form.general.site_description"
                                    placeholder="A brief description of your client portal"
                                    rows="3"
                                />
                                <p v-if="form.errors['general.site_description']" class="text-sm text-destructive">
                                    {{ form.errors['general.site_description'] }}
                                </p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="admin_email">Admin Email</Label>
                                <Input id="admin_email" type="email" v-model="form.general.admin_email" placeholder="admin@example.com" />
                                <p v-if="form.errors['general.admin_email']" class="text-sm text-destructive">
                                    {{ form.errors['general.admin_email'] }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="users_require_approval" v-model:checked="form.general.users_require_approval" />
                                <Label for="users_require_approval">Require admin approval for new user registrations</Label>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Security Settings Tab -->
                <TabsContent value="security" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Security Settings</CardTitle>
                            <CardDescription> Configure security settings such as password policies and session timeouts </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="password_expiry_days">Password Expiry (Days)</Label>
                                <Input
                                    id="password_expiry_days"
                                    type="number"
                                    v-model="form.security.password_expiry_days"
                                    placeholder="90"
                                    min="0"
                                    max="365"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Number of days before users are required to change their password. Set to 0 to disable.
                                </p>
                                <p v-if="form.errors['security.password_expiry_days']" class="text-sm text-destructive">
                                    {{ form.errors['security.password_expiry_days'] }}
                                </p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="session_timeout_minutes">Session Timeout (Minutes)</Label>
                                <Input
                                    id="session_timeout_minutes"
                                    type="number"
                                    v-model="form.security.session_timeout_minutes"
                                    placeholder="60"
                                    min="1"
                                    max="1440"
                                />
                                <p class="text-xs text-muted-foreground">
                                    Number of minutes of inactivity before a user is automatically logged out.
                                </p>
                                <p v-if="form.errors['security.session_timeout_minutes']" class="text-sm text-destructive">
                                    {{ form.errors['security.session_timeout_minutes'] }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Switch id="two_factor_authentication" v-model:checked="form.security.two_factor_authentication" />
                                <Label for="two_factor_authentication">Enable Two-Factor Authentication</Label>
                            </div>
                            <div
                                v-if="form.security.two_factor_authentication"
                                class="rounded-md bg-amber-50 p-4 text-amber-800 dark:bg-amber-950/20 dark:text-amber-300"
                            >
                                <p class="text-sm">
                                    Note: Two-factor authentication implementation requires additional setup. Consult the documentation for
                                    integration details.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>

                <!-- Email Settings Tab -->
                <TabsContent value="email" class="space-y-4">
                    <Card>
                        <CardHeader>
                            <CardTitle>Email Settings</CardTitle>
                            <CardDescription> Configure email notifications and automated messages </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <Switch id="email_notifications" v-model:checked="form.email.email_notifications" />
                                <Label for="email_notifications">Enable Email Notifications</Label>
                            </div>

                            <div class="ml-8 space-y-4" :class="{ 'opacity-50': !form.email.email_notifications }">
                                <div class="flex items-center space-x-2">
                                    <Switch
                                        id="welcome_email"
                                        v-model:checked="form.email.welcome_email"
                                        :disabled="!form.email.email_notifications"
                                    />
                                    <Label for="welcome_email">Send Welcome Email to New Users</Label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Switch
                                        id="approval_required_email"
                                        v-model:checked="form.email.approval_required_email"
                                        :disabled="!form.email.email_notifications"
                                    />
                                    <Label for="approval_required_email"> Send Email When User Approval is Required </Label>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>

            <div class="mt-6 flex justify-end">
                <Button @click="saveSettings" :disabled="form.processing" size="lg" class="gap-2">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>
                        <Save class="h-4 w-4" />
                        Save All Settings
                    </span>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
