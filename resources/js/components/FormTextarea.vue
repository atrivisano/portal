<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { cn } from '@/lib/utils';

// Components
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface Props {
    id: string;
    modelValue: string;
    label?: string;
    placeholder?: string;
    error?: string;
    required?: boolean;
    autofocus?: boolean;
    disabled?: boolean;
    minlength?: number;
    maxlength?: number;
    rows?: number;
    immediate?: boolean; // Enable validation on every keystroke
    customValidation?: (value: string) => string | null; // Custom validation function
    className?: string;
}

const props = withDefaults(defineProps<Props>(), {
    required: false,
    autofocus: false,
    disabled: false,
    immediate: false,
    rows: 3,
    className: '',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'validation', valid: boolean): void;
}>();

const textarea = ref<HTMLTextAreaElement | null>(null);
const localValue = ref(props.modelValue);
const isDirty = ref(false);
const isTouched = ref(false);
const validationError = ref<string | null>(null);
const nativeValidationError = ref<string | null>(null);

// When props.modelValue changes from parent
watch(() => props.modelValue, (newVal) => {
    localValue.value = newVal;
});

// When localValue changes from user input
watch(localValue, (newVal) => {
    emit('update:modelValue', newVal);
    if (isDirty.value && (props.immediate || isTouched.value)) {
        validateInput();
    }
});

// Watch for external error prop changes
watch(() => props.error, (newError) => {
    if (newError) {
        validationError.value = newError;
        emit('validation', false);
    } else if (!nativeValidationError.value) {
        validationError.value = null;
        emit('validation', true);
    }
});

// Handle input
const handleInput = (e: Event) => {
    const target = e.target as HTMLTextAreaElement;
    localValue.value = target.value;
    isDirty.value = true;

    if (props.immediate) {
        validateInput();
    }
};

// Handle blur (when field loses focus)
const handleBlur = () => {
    isTouched.value = true;
    if (isDirty.value) {
        validateInput();
    }
};

// Validation logic
const validateInput = () => {
    // First check if the field is required but empty
    if (props.required && !localValue.value.trim()) {
        nativeValidationError.value = `${props.label || 'This field'} is required`;
        validationError.value = nativeValidationError.value;
        emit('validation', false);
        return false;
    }

    // Check length constraints
    if (props.minlength && localValue.value.length < props.minlength) {
        nativeValidationError.value = `Please use at least ${props.minlength} characters`;
        validationError.value = nativeValidationError.value;
        emit('validation', false);
        return false;
    }

    if (props.maxlength && localValue.value.length > props.maxlength) {
        nativeValidationError.value = `Please use no more than ${props.maxlength} characters`;
        validationError.value = nativeValidationError.value;
        emit('validation', false);
        return false;
    }

    // Then check custom validation if provided
    if (props.customValidation) {
        const customError = props.customValidation(localValue.value);
        if (customError) {
            validationError.value = customError;
            nativeValidationError.value = null;
            emit('validation', false);
            return false;
        }
    }

    // If there's an explicit error prop, use that
    if (props.error) {
        validationError.value = props.error;
        nativeValidationError.value = null;
        emit('validation', false);
        return false;
    }

    // Otherwise we're valid
    validationError.value = null;
    nativeValidationError.value = null;
    emit('validation', true);
    return true;
};

// Computed status for styling
const textareaStatus = computed(() => {
    if (validationError.value) return 'error';
    if (isTouched.value && isDirty.value && !validationError.value) return 'success';
    return 'default';
});

// Focus the textarea if autofocus is true
const focus = () => {
    textarea.value?.focus();
};

// Expose validation method
defineExpose({
    validate: validateInput,
    focus,
});
</script>

<template>
    <div class="grid gap-2">
        <Label v-if="label" :for="id" class="flex items-start gap-1">
            {{ label }}
            <span v-if="required" class="text-destructive">*</span>
        </Label>

        <Textarea
            :id="id"
            ref="textarea"
            v-model="localValue"
            :placeholder="placeholder"
            :rows="rows"
            :required="required"
            :autofocus="autofocus"
            :disabled="disabled"
            :maxlength="maxlength"
            @input="handleInput"
            @blur="handleBlur"
            :class="cn(
        props.className,
        textareaStatus === 'error' && 'border-destructive focus-visible:ring-destructive',
        textareaStatus === 'success' && 'border-green-500 focus-visible:ring-green-500',
      )"
        />

        <div class="flex items-center justify-between">
            <InputError v-if="validationError" :message="validationError" />

            <div v-if="maxlength && isDirty"
                 :class="cn(
             'ml-auto text-xs text-muted-foreground',
             localValue.length > maxlength * 0.9 && 'text-amber-600',
             localValue.length >= maxlength && 'text-destructive'
           )"
            >
                {{ localValue.length }}/{{ maxlength }}
            </div>
        </div>
    </div>
</template>
