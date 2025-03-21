<script setup lang="ts">
import { cn } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

// Components
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

import { useInitials } from '@/composables/useInitials';
import { LoaderCircle, Upload, X } from 'lucide-vue-next';

interface Props {
    userId: number;
    name: string;
    avatarUrl?: string;
    className?: string;
}

const props = withDefaults(defineProps<Props>(), {
    className: '',
});

const { getInitials } = useInitials();
const showErrorMessage = ref(false);
const errorMessage = ref('');
const dialogOpen = ref(false);
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isLoading = ref(false);

// Create a form for uploading the avatar
const form = useForm({
    avatar: null as File | null,
    _method: 'PUT',
});

// Reset the form and previews
const resetForm = () => {
    form.reset();
    imagePreview.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Handle file selection
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files?.length) return;

    const file = target.files[0];

    // Validate file type
    if (!file.type.startsWith('image/')) {
        showErrorMessage.value = true;
        errorMessage.value = 'Please select an image file.';
        resetForm();
        return;
    }

    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        showErrorMessage.value = true;
        errorMessage.value = 'Image size must be less than 2MB.';
        resetForm();
        return;
    }

    // Set the form data
    form.avatar = file;

    // Create an image preview
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target?.result as string;
        showErrorMessage.value = false;
    };
    reader.readAsDataURL(file);
};

// Upload the avatar
const uploadAvatar = () => {
    isLoading.value = true;

    form.post(route('profile.avatar.update'), {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
            dialogOpen.value = false;
            resetForm();
        },
        onError: () => {
            isLoading.value = false;
            showErrorMessage.value = true;
            errorMessage.value = 'Failed to upload avatar. Please try again.';
        },
    });
};

// Remove the avatar
const removeAvatar = () => {
    isLoading.value = true;

    useForm({
        _method: 'DELETE',
    }).post(route('profile.avatar.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
            dialogOpen.value = false;
            resetForm();
        },
        onError: () => {
            isLoading.value = false;
            showErrorMessage.value = true;
            errorMessage.value = 'Failed to remove avatar. Please try again.';
        },
    });
};
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogTrigger as-child>
            <div :class="cn('group relative cursor-pointer rounded-lg', props.className)" aria-label="Change profile picture">
                <Avatar :class="cn('h-20 w-20 overflow-hidden rounded-lg border bg-background', props.className)">
                    <AvatarImage v-if="avatarUrl" :src="avatarUrl" :alt="name" />
                    <AvatarFallback :class="cn('bg-muted text-lg font-medium text-foreground')">
                        {{ getInitials(name) }}
                    </AvatarFallback>
                </Avatar>
                <div
                    class="absolute inset-0 flex h-full w-full items-center justify-center rounded-lg bg-black/40 opacity-0 transition-opacity group-hover:opacity-100"
                >
                    <Upload class="h-6 w-6 text-white" />
                </div>
            </div>
        </DialogTrigger>

        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Profile Picture</DialogTitle>
                <DialogDescription> Upload a new profile picture or remove your current one. </DialogDescription>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <div class="flex flex-col items-center gap-4">
                    <Avatar class="h-24 w-24 overflow-hidden rounded-lg border">
                        <AvatarImage v-if="imagePreview" :src="imagePreview" :alt="name" class="object-cover" />
                        <AvatarImage v-else-if="avatarUrl" :src="avatarUrl" :alt="name" class="object-cover" />
                        <AvatarFallback :class="cn('bg-muted text-xl font-medium text-foreground')">
                            {{ getInitials(name) }}
                        </AvatarFallback>
                    </Avatar>

                    <div class="grid w-full gap-2">
                        <div class="flex gap-2">
                            <Button variant="secondary" class="flex-1 gap-2" @click="() => fileInput?.click()" :disabled="isLoading">
                                <Upload class="h-4 w-4" />
                                <span>Select image</span>
                                <input ref="fileInput" type="file" class="hidden" accept="image/*" @change="handleFileSelect" />
                            </Button>

                            <Button v-if="avatarUrl || imagePreview" variant="destructive" size="icon" @click="removeAvatar" :disabled="isLoading">
                                <X v-if="!isLoading" class="h-4 w-4" />
                                <LoaderCircle v-else class="h-4 w-4 animate-spin" />
                            </Button>
                        </div>

                        <p v-if="showErrorMessage" class="text-sm text-destructive">
                            {{ errorMessage }}
                        </p>
                        <p v-else class="text-sm text-muted-foreground">Recommended: Square JPG, PNG, or GIF, 2MB maximum.</p>
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button
                    variant="ghost"
                    @click="
                        () => {
                            dialogOpen = false;
                            resetForm();
                        }
                    "
                    :disabled="isLoading"
                >
                    Cancel
                </Button>
                <Button variant="default" @click="uploadAvatar" :disabled="!form.avatar || isLoading">
                    <LoaderCircle v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                    Save
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
