<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import customer from '@/routes/customers';
import { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Customers', href: customer.index().url },
    { title: 'Create Customer', href: customer.create().url },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    address: '',
    phone: '',
    password_confirmation: '',
});

function submit() {
    form.post(customer.store().url);
}
</script>

<template>
    <Head title="Create Customer" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="w-full rounded-xl border-gray-200 bg-white shadow-md">
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Create Customer" description="Create a new customer for your application" class="text-gray-800" />
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Customer Information -->
                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Customer Account" description="Fill out customer account" class="text-gray-800" />

                    <!-- Email -->
                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="Email address"
                        />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</div>
                    </div>

                    <!-- Password -->
                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="Password"
                        />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</div>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Confirm Password</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="Confirm password"
                        />
                    </div>
                </div>

                <div class="h-5"></div>

                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Customer Information" description="Fill out customer details" class="text-gray-800" />

                    <!-- Name -->
                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="Full name"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                    </div>

                    <!-- Address -->
                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Address</label>
                        <input
                            v-model="form.address"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="Customer address"
                        />
                        <div v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</div>
                    </div>

                    <!-- Phone -->
                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            placeholder="Phone number"
                        />
                        <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="my-3 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="$inertia.visit(customer.index().url)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-700"
                        :disabled="form.processing"
                    >
                        Save Customer
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
