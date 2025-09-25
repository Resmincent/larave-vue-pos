<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import category from '@/routes/categories';
import { BreadcrumbItem } from '@/types';
import { Category } from '@/types/categories';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: category.index().url,
    },
    {
        title: 'Create Categories',
        href: category.create().url,
    },
];

const props = defineProps<{
    categories: Category[];
}>();

const form = useForm({
    name: '',
    parent_id: null as number | null,
});

function submit() {
    form.post(category.store().url);
}

const selectedCategory = ref<{ id: number | null; name: string }>({
    id: form.parent_id ?? null,
    name: props.categories.find((c) => c.id === form.parent_id)?.name || '-- None --',
});

function selectCategory(cat: { id: number | null; name: string }) {
    selectedCategory.value = cat;
    form.parent_id = cat.id;
}
</script>

<template>
    <Head title="Create Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center px-8 py-5">
            <form @submit.prevent="submit">
                <div class="w-full rounded-xl bg-white shadow-md">
                    <!-- Header -->
                    <div class="border-b px-6 py-4">
                        <HeadingSmall title="Create Category" description="Create a new category for your products" class="text-gray-800" />
                    </div>
                </div>
                <div class="h-5"></div>
                <div class="w-full rounded-xl bg-white px-6 py-4 shadow-md">
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

                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-md border px-3 py-2 text-left text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                >
                                    <span>{{ selectedCategory.name }}</span>
                                    <ChevronDown class="h-4 w-4 opacity-70" />
                                </button>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent>
                                <DropdownMenuItem @click="selectCategory({ id: null, name: '-- None --' })">
                                    <DropdownMenuLabel>Select Category</DropdownMenuLabel>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuSeparator />
                                <DropdownMenuItem v-for="cat in props.categories" :key="cat.id" @click="selectCategory(cat)" class="w-full">
                                    {{ cat.name }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <div v-if="form.errors?.parent_id" class="mt-1 text-sm text-red-600">
                            {{ form.errors.parent_id }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2">
                        <Link :href="category.index().url" class="rounded-md border px-4 py-2 text-sm text-gray-600 hover:bg-gray-100"> Cancel </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-cyan-600 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700 disabled:opacity-50"
                        >
                            Save Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
