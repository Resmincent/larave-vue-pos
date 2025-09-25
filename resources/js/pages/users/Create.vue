<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import user from '@/routes/users';
import { BreadcrumbItem } from '@/types';
import { Role } from '@/types/role';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: user.index().url,
    },
    {
        title: 'Create User',
        href: user.create().url,
    },
];

const props = defineProps<{
    roles: Role[];
}>();

const form = useForm({
    name: '',
    email: '',
    role: null as number | null,
});

function submit() {
    form.post(user.store().url);
}
</script>

<template>
    <Head title="Create User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <div class="w-full rounded-xl border-gray-200 bg-white shadow-md">
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Create User" description="Create a new user for your application" class="text-gray-800" />
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Name & Email -->
                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="User Information" description="Insert user information below" class="text-gray-800" />
                    <span
                        class="my-3 flex items-center justify-center rounded-full bg-cyan-100 px-2.5 py-2.5 text-xs font-medium text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300"
                    >
                        Default password for this user is pos123. Please change it after first login.
                    </span>

                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                            placeholder="User name"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                        <input
                            v-model="form.email"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                            placeholder="Email address"
                        />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </div>
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Role -->
                <div class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md">
                    <HeadingSmall title="Role" description="Assign a role to the user" class="text-gray-800" />
                    <div class="mt-3 space-y-2">
                        <label v-for="role in props.roles" :key="role.id" class="flex cursor-pointer items-center gap-2">
                            <input type="radio" class="text-blue-600 focus:ring focus:ring-blue-200" v-model="form.role" :value="role.id" />
                            <span class="text-gray-700">{{ role.name }}</span>
                        </label>
                    </div>

                    <div v-if="form.errors.role" class="mt-2 text-sm text-red-600">
                        {{ form.errors.role }}
                    </div>
                </div>

                <div class="h-5"></div>

                <!-- Actions -->
                <div class="flex justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        @click="$inertia.visit(user.index().url)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-700 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        :disabled="form.processing"
                    >
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
