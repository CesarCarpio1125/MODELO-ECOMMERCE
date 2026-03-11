<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    qrCodeUrl: String,
});

const form = useForm({
    code: '',
});

const submit = () => {
    form.post(route('two-factor.setup'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <Head title="Enable Two-Factor Authentication" />

            <div class="mb-4">
                <h2 class="text-2xl font-bold text-center">Enable 2FA</h2>
                <p class="text-sm text-gray-600 text-center mt-2">
                    Scan the QR code with your authenticator app and enter the code below.
                </p>
            </div>

            <div class="mb-4 flex justify-center">
                <img :src="qrCodeUrl" alt="QR Code" class="w-48 h-48" />
            </div>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label for="code" class="block text-sm font-medium text-gray-700">Authenticator Code</label>
                    <TextInput
                        id="code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.code"
                        required
                        autofocus
                    />
                </div>

                <div class="flex items-center justify-end">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Enable 2FA
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
