<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    id: {
        type: String,
        default: '',
    },
    rows: {
        type: [String, Number],
        default: 3,
    },
    placeholder: {
        type: String,
        default: '',
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    class: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const classes = computed(() => {
    const baseClasses = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';
    const disabledClasses = props.disabled ? 'bg-gray-100 cursor-not-allowed' : '';
    const customClasses = props.class || '';
    
    return [baseClasses, disabledClasses, customClasses].filter(Boolean).join(' ');
});

const input = computed({
    get() {
        return props.modelValue;
    },
    set(value) {
        emit('update:modelValue', value);
    },
});
</script>

<template>
    <textarea
        :id="id"
        v-model="input"
        :rows="rows"
        :placeholder="placeholder"
        :readonly="readonly"
        :disabled="disabled"
        :required="required"
        :class="classes"
    />
</template>
