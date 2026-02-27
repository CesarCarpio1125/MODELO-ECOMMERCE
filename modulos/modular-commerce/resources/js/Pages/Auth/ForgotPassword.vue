<script setup>
import FormField from '@/Components/FormField.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <div class="relative flex items-center justify-center min-h-screen px-6 py-12">
            <div class="w-full max-w-md">
                <!-- Forgot Password Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-2xl overflow-hidden">
                    <div class="px-8 py-10">
                        <div class="text-center mb-8">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Forgot Password?</h1>
                            <p class="text-gray-600 dark:text-gray-300">No problem. We'll send you a reset link.</p>
                        </div>

                        <div class="mb-6 text-sm text-gray-600 dark:text-gray-300">
                            Forgot your password? No problem. Just let us know your email
                            address and we will email you a password reset link that will allow
                            you to choose a new one.
                        </div>

                        <div
                            v-if="status"
                            class="mb-4 text-sm font-medium text-green-600"
                        >
                            {{ status }}
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <FormField
                                id="email"
                                label="Email Address"
                                type="email"
                                v-model="form.email"
                                :error="form.errors.email"
                                required
                                autocomplete="username"
                                labelClass="text-indigo-600 dark:text-indigo-400 font-semibold"
                                @input="form.errors.email = null"
                            />

                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium py-3 px-6 rounded-md transition-all duration-200 shadow-lg justify-center"
                            >
                                {{ form.processing ? 'Sending...' : 'Send Reset Link' }}
                            </PrimaryButton>
                        </form>

                        <div class="mt-6 text-center">
                            <Link
                                :href="route('login')"
                                class="text-sm text-gray-600 dark:text-gray-300 underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                Back to login
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
