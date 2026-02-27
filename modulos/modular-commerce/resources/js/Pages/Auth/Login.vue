<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import FormField from '@/Components/FormField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const loginWithGoogle = () => {
    window.location.href = route('auth.google');
};
</script>

<template>
    <Head title="Log in" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <div class="relative flex items-center justify-center min-h-screen px-6 py-12">
            <div class="w-full max-w-md">
                <!-- Login Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-2xl overflow-hidden">
                    <div class="px-8 py-10">
                        <div class="text-center mb-8">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Welcome Back</h1>
                            <p class="text-gray-600 dark:text-gray-300">Sign in to your account</p>
                        </div>

                        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
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

                            <FormField
                                id="password"
                                label="Password"
                                type="password"
                                v-model="form.password"
                                :error="form.errors.password"
                                required
                                autocomplete="current-password"
                                labelClass="text-indigo-600 dark:text-indigo-400 font-semibold"
                                @input="form.errors.password = null"
                            />

                            <div class="flex items-center justify-between">
                                <label class="flex items-center">
                                    <Checkbox name="remember" v-model:checked="form.remember" />
                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-300">Remember me</span>
                                </label>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm text-gray-600 dark:text-gray-300 underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    Forgot password?
                                </Link>
                            </div>

                            <PrimaryButton
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium py-3 px-6 rounded-md transition-all duration-200 shadow-lg justify-center"
                            >
                                {{ form.processing ? 'Signing in...' : 'Sign In' }}
                            </PrimaryButton>
                        </form>

                        <div class="mt-6">
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-300" />
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="bg-white dark:bg-zinc-900 px-2 text-gray-500">Or</span>
                                </div>
                            </div>

                            <div class="mt-6">
                                <button
                                    @click="loginWithGoogle"
                                    type="button"
                                    class="w-full flex justify-center items-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-800 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                                >
                                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                                        <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                        <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                        <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                        <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                    </svg>
                                    Continue with Google
                                </button>
                            </div>

                            <div class="mt-6 text-center">
                                <Link
                                    :href="route('register')"
                                    class="text-sm text-gray-600 dark:text-gray-300 underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    Don't have an account? Sign up
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
