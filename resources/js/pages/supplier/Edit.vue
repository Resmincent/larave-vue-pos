<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import suppliers from '@/routes/suppliers';
import { BreadcrumbItem } from '@/types';
import { Supplier } from '@/types/suppliers';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    supplier: Supplier;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Suppliers',
        href: suppliers.index().url,
    },
    {
        title: 'Edit supplier',
        href: suppliers.edit(props.supplier.id).url,
    },
];

const form = useForm({
    name: props.supplier.user?.name,
    email: props.supplier.user.email,
    address: props.supplier.address,
    phone: props.supplier.phone,
    is_active: props.supplier.is_active,
});

function updateSupllier() {
    form.put(suppliers.update(props.supplier.id).url);
}
</script>
<template>
    <Head title="Edit Supplier" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="updateSupllier">
                <!-- Header -->
                <div class="w-full rounded-xl border-gray-200 bg-white shadow-md">
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Create Supplier" description="Create a new supplier for your application" class="text-gray-800" />
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Supplier Information -->
                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Supplier Account" description="Fill out supllier account" class="text-gray-800" />

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
                </div>

                <div class="h-5"></div>

                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Supplier Information" description="Fill out supplier details" class="text-gray-800" />

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
                            placeholder="Supllier address"
                        />
                        <div v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</div>
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
                        Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
