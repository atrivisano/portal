<script setup lang="ts">
import { cn } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

// Components
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Progress } from '@/components/ui/progress';
import { useInitials } from '@/composables/useInitials';
import { Camera, ImagePlus, RefreshCw, X } from 'lucide-vue-next';

interface Props {
    userId: number;
    name: string;
    avatarUrl?: string | null;
    className?: string;
    size?: 'sm' | 'md' | 'lg' | 'xl';
    shape?: 'circle' | 'square';
}

const props = withDefaults(defineProps<Props>(), {
    className: '',
    size: 'md',
    shape: 'circle',
});

const { getInitials } = useInitials();
const dialogOpen = ref(false);
const imagePreview = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isLoading = ref(false);
const uploadProgress = ref(0);
const errorMessage = ref('');
const showError = ref(false);
const isImageCropped = ref(false);
const imageCropperCanvas = ref<HTMLCanvasElement | null>(null);

// Size class mapping
const sizeClasses = {
    sm: 'h-12 w-12',
    md: 'h-20 w-20',
    lg: 'h-28 w-28',
    xl: 'h-36 w-36',
};

// Shape class mapping
const shapeClasses = {
    circle: 'rounded-full',
    square: 'rounded-lg',
};

// Computed CSS classes for avatar
const avatarClasses = computed(() => {
    return cn(sizeClasses[props.size], shapeClasses[props.shape], 'overflow-hidden border bg-background', props.className);
});

// Create a form for uploading the avatar
const form = useForm({
    avatar: null as File | null,
    _method: 'PUT',
});

// Reset the form and previews
const resetForm = () => {
    form.reset();
    imagePreview.value = null;
    isImageCropped.value = false;
    uploadProgress.value = 0;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

// Show error message
const showErrorMessage = (message: string) => {
    errorMessage.value = message;
    showError.value = true;
    setTimeout(() => {
        showError.value = false;
    }, 5000);
};

// Handle file selection
const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files?.length) return;

    const file = target.files[0];

    // Validate file type
    if (!file.type.startsWith('image/')) {
        showErrorMessage('Please select an image file.');
        resetForm();
        return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showErrorMessage('Image size must be less than 5MB.');
        resetForm();
        return;
    }

    // Set the form data
    form.avatar = file;

    // Create an image preview
    const reader = new FileReader();
    reader.onload = (e) => {
        imagePreview.value = e.target?.result as string;
        showError.value = false;
    };
    reader.readAsDataURL(file);
};

// Simulate upload progress (for demo)
const simulateProgress = () => {
    uploadProgress.value = 0;
    const interval = setInterval(() => {
        uploadProgress.value += Math.random() * 10;
        if (uploadProgress.value >= 100) {
            uploadProgress.value = 100;
            clearInterval(interval);
        }
    }, 200);
};

// Upload the avatar
const uploadAvatar = () => {
    if (!form.avatar) return;

    isLoading.value = true;
    simulateProgress();

    form.post(route('profile.avatar.update'), {
        preserveScroll: true,
        onSuccess: () => {
            isLoading.value = false;
            dialogOpen.value = false;
            resetForm();
        },
        onError: (errors) => {
            isLoading.value = false;
            if (errors.avatar) {
                showErrorMessage(errors.avatar);
            } else {
                showErrorMessage('Failed to upload avatar. Please try again.');
            }
        },
    });
};

// Remove the avatar
const removeAvatar = () => {
    isLoading.value = true;
    simulateProgress();

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
            showErrorMessage('Failed to remove avatar. Please try again.');
        },
    });
};

// Emit event when dialog is closed
const emit = defineEmits(['updated']);

// Watch dialog open state
watch(dialogOpen, (newValue) => {
    if (!newValue) {
        // Dialog was closed
        emit('updated');
    }
});
</script>

<template>
    <Dialog v-model:open="dialogOpen">
        <DialogTrigger as-child>
            <div :class="cn('group relative cursor-pointer', props.className)" aria-label="Change profile picture">
                <Avatar :class="avatarClasses">
                    <AvatarImage v-if="avatarUrl" :src="avatarUrl" :alt="name" />
                    <AvatarFallback
                        :class="
                            cn(
                                'flex items-center justify-center bg-muted text-foreground',
                                props.size === 'sm' && 'text-sm',
                                props.size === 'md' && 'text-lg',
                                props.size === 'lg' && 'text-xl',
                                props.size === 'xl' && 'text-2xl',
                            )
                        "
                    >
                        {{ getInitials(name) }}
                    </AvatarFallback>
                </Avatar>
                <div
                    :class="
                        cn(
                            'absolute inset-0 flex h-full w-full items-center justify-center bg-black/50 opacity-0 transition-opacity group-hover:opacity-100',
                            shapeClasses[props.shape],
                        )
                    "
                >
                    <Camera class="h-6 w-6 text-white" />
                </div>
            </div>
        </DialogTrigger>

        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Profile Picture</DialogTitle>
                <DialogDescription> Upload a new profile picture or remove your current one.</DialogDescription>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <div class="flex flex-col items-center gap-4">
                    <Avatar class="h-24 w-24 border-2">
                        <AvatarImage v-if="imagePreview" :src="imagePreview" :alt="name" class="object-cover" />
                        <AvatarImage v-else-if="avatarUrl" :src="avatarUrl" :alt="name" class="object-cover" />
                        <AvatarFallback class="text-xl font-medium">
                            {{ getInitials(name) }}
                        </AvatarFallback>
                    </Avatar>

                    <div class="grid w-full gap-2">
                        <div class="flex gap-2">
                            <Button variant="secondary" class="flex-1 gap-2" @click="() => fileInput?.click()" :disabled="isLoading">
                                <ImagePlus class="h-4 w-4" />
                                <span>Select image</span>
                                <input ref="fileInput" type="file" class="hidden" accept="image/*" @change="handleFileSelect" />
                            </Button>

                            <Button v-if="avatarUrl || imagePreview" variant="destructive" size="icon" @click="removeAvatar" :disabled="isLoading">
                                <X v-if="!isLoading" class="h-4 w-4" />
                                <RefreshCw v-else class="h-4 w-4 animate-spin" />
                            </Button>
                        </div>

                        <!-- Progress bar for uploading -->
                        <Progress v-if="isLoading" :value="uploadProgress" class="h-2 w-full" />

                        <!-- Error message -->
                        <p v-if="showError" class="text-sm text-destructive">
                            {{ errorMessage }}
                        </p>
                        <p v-else class="text-sm text-muted-foreground">Square JPG, PNG, or GIF, 5MB maximum.</p>
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
                    <RefreshCw v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
                    Save
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
