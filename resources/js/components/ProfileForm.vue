<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Building, User } from 'lucide-vue-next';

// Define props
interface Props {
    form: any; // The form object from useForm
    processing: boolean;
    recentlySuccessful: boolean;
}

defineProps<Props>();

// Emit events
const emit = defineEmits(['submit']);

// Handle form submission
const handleSubmit = () => {
    emit('submit');
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Basic Information -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center">
                    <User class="mr-2 h-5 w-5" />
                    Personal Information
                </CardTitle>
                <CardDescription> Update your personal information </CardDescription>
            </CardHeader>

            <CardContent>
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name" class="required">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            required
                            :class="{ 'border-destructive': form.errors.name }"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" class="required">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            required
                            :class="{ 'border-destructive': form.errors.email }"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="phone">Phone Number</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            autocomplete="tel"
                            :class="{ 'border-destructive': form.errors.phone }"
                            placeholder="(123) 456-7890"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Bio -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center">
                    <User class="mr-2 h-5 w-5" />
                    About You
                </CardTitle>
                <CardDescription> Tell us a bit about yourself </CardDescription>
            </CardHeader>

            <CardContent>
                <div class="grid gap-2">
                    <Label for="bio">Biography</Label>
                    <Textarea
                        id="bio"
                        v-model="form.bio"
                        rows="5"
                        placeholder="Share a little about yourself..."
                        :class="{ 'border-destructive': form.errors.bio }"
                    />
                    <InputError :message="form.errors.bio" />
                    <p class="text-xs text-muted-foreground">This will be displayed on your profile.</p>
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
                <CardDescription> Update your address information </CardDescription>
            </CardHeader>

            <CardContent>
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="grid gap-2 sm:col-span-2">
                        <Label for="address">Street Address</Label>
                        <Input id="address" v-model="form.address" :class="{ 'border-destructive': form.errors.address }" placeholder="123 Main St" />
                        <InputError :message="form.errors.address" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="city">City</Label>
                        <Input id="city" v-model="form.city" :class="{ 'border-destructive': form.errors.city }" placeholder="New York" />
                        <InputError :message="form.errors.city" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="state">State / Province</Label>
                        <Input id="state" v-model="form.state" :class="{ 'border-destructive': form.errors.state }" placeholder="NY" />
                        <InputError :message="form.errors.state" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="zip_code">ZIP / Postal Code</Label>
                        <Input id="zip_code" v-model="form.zip_code" :class="{ 'border-destructive': form.errors.zip_code }" placeholder="10001" />
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

            <CardFooter class="flex items-center justify-between">
                <div v-if="recentlySuccessful" class="rounded-md bg-green-50 px-3 py-1 text-sm text-green-500 dark:bg-green-950/20">
                    <p class="flex items-center">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="14"
                            height="14"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="mr-2"
                        >
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Profile updated successfully!
                    </p>
                </div>
                <Button type="submit" :disabled="processing">
                    <span v-if="processing" class="flex items-center">
                        <svg
                            class="mr-2 h-4 w-4 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                        </svg>
                        Saving...
                    </span>
                    <span v-else>Save Changes</span>
                </Button>
            </CardFooter>
        </Card>
    </form>
</template>
