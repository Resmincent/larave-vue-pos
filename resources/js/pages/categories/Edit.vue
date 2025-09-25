<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import categories from '@/routes/categories';
import { BreadcrumbItem } from '@/types';
import { Category } from '@/types/categories';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    category: Category;
    parents: Category[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: categories.index().url,
    },
    {
        title: 'Edit Category',
        href: categories.edit(props.category.id).url,
    },
];

const form = useForm({
    name: props.category.name,
    parent_id: props.category.parent?.id ?? null,
});

function update() {
    form.put(categories.update(props.category.id).url);
}
</script>

<template>
    <Head title="Edit Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center px-8 py-5">
            <form @submit.prevent="update">
                <div class="w-full rounded-xl bg-white shadow-md">
                    <!-- Header -->
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Edit Category" description="Update the category details" class="text-gray-800" />
                    </div>
                </div>

                <div class="h-5"></div>

                <div class="w-full rounded-xl bg-white px-6 py-4 shadow-md">
                    <!-- Form -->
                    <!-- Name -->
                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                            placeholder="Category name"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Parent -->
                    <div class="py-3">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Parent Category</label>
                        <select
                            v-model="form.parent_id"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-blue-500 focus:ring focus:ring-blue-200"
                        >
                            <option :value="null">-- None --</option>
                            <option v-for="cat in props.parents" :key="cat.id" :value="cat.id" :disabled="cat.id === props.category.id">
                                {{ cat.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.parent_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.parent_id }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2">
                        <Link :href="categories.index().url" class="rounded-md border px-4 py-2 text-sm text-gray-600 hover:bg-gray-100">
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700 disabled:opacity-50"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
