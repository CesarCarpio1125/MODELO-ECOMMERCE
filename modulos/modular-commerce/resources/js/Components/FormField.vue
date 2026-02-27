<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    label: String,
    labelClass: String,
    type: {
        type: String,
        default: 'text',
    },
    modelValue: [String, Number],
    error: String,
    required: Boolean,
    autofocus: Boolean,
    autocomplete: String,
});

const emit = defineEmits(['update:modelValue', 'input']);

const updateValue = (value) => {
    emit('update:modelValue', value);
    emit('input', value);
};
</script>

<template>
    <div>
        <InputLabel :for="$attrs.id" :value="label" :class="labelClass" />

        <TextInput
            :id="$attrs.id"
            :type="type"
            class="mt-1 block w-full text-black bg-gray-50 dark:bg-zinc-700 border-gray-300 dark:border-zinc-600 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400 rounded-md"
            :modelValue="modelValue"
            @update:modelValue="updateValue"
            :required="required"
            :autofocus="autofocus"
            :autocomplete="autocomplete"
            v-bind="$attrs"
        />

        <InputError class="mt-2" :message="error" />
    </div>
</template>

<style scoped>
/* Custom selection styles */
input::selection {
    background-color: #4a5568; /* A darker gray for better contrast */
    color: #ffffff; /* Keep text white */
}

input::-moz-selection {
    background-color: #4a5568;
    color: #ffffff;
}
</style>
