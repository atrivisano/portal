<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { cn } from '@/lib/utils';

// Components
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

interface Props {
    id: string;
    modelValue: string;
    label?: string;
    placeholder?: string;
    type?: string;
    error?: string;
    required?: boolean;
    autocomplete?: string;
    autofocus?: boolean;
    disabled?: boolean;
    pattern?: string;
    minlength?: number;
    maxlength?: number;
    min?: string | number;
    max?: string | number;
    immediate?: boolean; // Enable validation on every keystroke
    customValidation?: (value: string) => string | null; // Custom validation function
    className?: string;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    required: false,
    autofocus: false,
    disabled: false,
    immediate: false,
    className: '',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'validation', valid: boolean): void;
}>();

const input = ref<HTMLInputElement | null>(null);
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
    const target = e.target as HTMLInputElement;
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
    // First check native HTML validation
    const nativeValidity = input.value?.validity;
    if (nativeValidity && !nativeValidity.valid) {
        // Translate native validation errors to user-friendly messages
        if (nativeValidity.valueMissing) {
            nativeValidationError.value = `${props.label || 'This field'} is required`;
        } else if (nativeValidity.typeMismatch) {
            nativeValidationError.value = `Please enter a valid ${props.type}`;
        } else if (nativeValidity.patternMismatch) {
            nativeValidationError.value = `Please match the requested format`;
        } else if (nativeValidity.tooShort) {
            nativeValidationError.value = `Please use at least ${props.minlength} characters`;
        } else if (nativeValidity.tooLong) {
            nativeValidationError.value = `Please use no more than ${props.maxlength} characters`;
        } else if (nativeValidity.rangeUnderflow) {
            nativeValidationError.value = `Value must be greater than or equal to ${props.min}`;
        } else if (nativeValidity.rangeOverflow) {
            nativeValidationError.value = `Value must be less than or equal to ${props.max}`;
        } else {
            nativeValidationError.value = `Invalid input`;
        }
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
const inputStatus = computed(() => {
    if (validationError.value) return 'error';
    if (isTouched.value && isDirty.value && !validationError.value) return 'success';
    return 'default';
});

// Focus the input if autofocus is true
const focus = () => {
    input.value?.focus();
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

        <Input
            :id="id"
            :type="type"
            ref="input"
            v-model="localValue"
            :placeholder="placeholder"
            :required="required"
            :autocomplete="autocomplete"
            :autofocus="autofocus"
            :disabled="disabled"
            :pattern="pattern"
            :minlength="minlength"
            :maxlength="maxlength"
            :min="min"
            :max="max"
            @input="handleInput"
            @blur="handleBlur"
            :class="cn(
        props.className,
        inputStatus === 'error' && 'border-destructive focus-visible:ring-destructive',
        inputStatus === 'success' && 'border-green-500 focus-visible:ring-green-500',
      )"
        />

        <InputError v-if="validationError" :message="validationError" />
    </div>
</template>
