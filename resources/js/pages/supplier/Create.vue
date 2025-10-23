<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import suppliers from '@/routes/suppliers';
import { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Suplliers', href: suppliers.index().url },
    { title: 'Create Supplier', href: suppliers.create().url },
];

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    address: '',
    password_confirmation: '',
    is_active: false,
});

function submit() {
    form.post(suppliers.store().url);
}
</script>

<template>
    <Head title="Create Supllier" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <!-- Header -->
                <div class="mb-5 w-full rounded-xl border-gray-200 bg-white shadow-md">
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Create Customer" description="Create a new supllier for your application" class="text-gray-800" />
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="mb-5 w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Customer Account" description="Fill out supllier account" class="text-gray-800" />

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

                    <!-- Phone -->
                    <div class="flex flex-row py-3">
                        <div class="basis-192">
                            <label class="mb-1 block text-sm font-medium text-gray-700">Phone</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                placeholder="Phone number"
                            />
                            <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</div>
                        </div>
                        <div class="mb-3 flex basis-64 items-end justify-center">
                            <label class="flex items-center gap-4">
                                <input type="checkbox" v-model="form.is_active" class="h-6 w-6 text-blue-600 focus:ring focus:ring-blue-200" />
                                <span class="text-gray-700">Active</span>
                            </label>
                        </div>
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
                </div>

                <!-- Actions -->
                <div class="my-3 flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="$inertia.visit(suppliers.index().url)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-700"
                        :disabled="form.processing"
                    >
                        Save Supllier
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
