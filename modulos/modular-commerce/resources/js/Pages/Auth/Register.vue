<script setup>
import FormField from '@/Components/FormField.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50 min-h-screen">
        <div class="relative flex items-center justify-center min-h-screen px-6 py-12">
            <div class="w-full max-w-md">
                <!-- Register Card -->
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-2xl overflow-hidden">
                    <div class="px-8 py-10">
                        <div class="text-center mb-8">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create Account</h1>
                            <p class="text-gray-600 dark:text-gray-300">Join Modular Commerce today</p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <FormField
                                id="name"
                                label="Full Name"
                                type="text"
                                v-model="form.name"
                                :error="form.errors.name"
                                required
                                autocomplete="name"
                                labelClass="text-indigo-600 dark:text-indigo-400 font-semibold"
                                @input="form.errors.name = null"
                            />

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
                                autocomplete="new-password"
                                labelClass="text-indigo-600 dark:text-indigo-400 font-semibold"
                                @input="form.errors.password = null"
                            />

                            <FormField
                                id="password_confirmation"
                                label="Confirm Password"
                                type="password"
                                v-model="form.password_confirmation"
                                :error="form.errors.password_confirmation"
                                required
                                autocomplete="new-password"
                                labelClass="text-indigo-600 dark:text-indigo-400 font-semibold"
                                @input="form.errors.password_confirmation = null"
                            />

                            <div class="flex items-center justify-between pt-4">
                                <Link
                                    :href="route('login')"
                                    class="text-sm text-gray-600 dark:text-gray-300 underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    Already have an account?
                                </Link>

                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium py-3 px-6 rounded-md transition-all duration-200 shadow-lg"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Account' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
