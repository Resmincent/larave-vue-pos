<script setup lang="ts">
import ModalDelete from '@/components/modules/Modal/ModalDelete.vue';
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import category from '@/routes/categories';
import { type BreadcrumbItem } from '@/types';
import { CategoryPagination } from '@/types/categories';
import { Head, Link, router } from '@inertiajs/vue3';
import { createColumnHelper, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import moment from 'moment';
import { computed, h, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: category.index().url,
    },
];

const props = defineProps<{
    categories: CategoryPagination;
}>();

const modal = ref<InstanceType<typeof ModalDelete> | null>(null);

// search state
const search = ref('');

// column helper
const columnHelper = createColumnHelper<any>();

// define columns
const columns = [
    columnHelper.accessor('name', {
        header: 'Name',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('parent_name', {
        header: 'Parent',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('created_at', {
        header: 'Created At',
        cell: (info) => {
            // Format date using moment.js
            // Make sure moment is imported: import moment from 'moment';
            const value = info.getValue();
            return value ? moment(value).format('YYYY-MM-DD HH:mm') + ' WIB' : '-';
        },
    }),
    columnHelper.accessor('updated_at', {
        header: 'Updated At',
        cell: (info) => {
            const value = info.getValue();
            return value ? moment(value).format('YYYY-MM-DD HH:mm') + ' WIB' : '-';
        },
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
        cell: (info) => {
            const id = info.row.original.id;
            return h('div', { class: 'flex gap-4' }, [
                h(
                    Link,
                    {
                        href: category.edit(id).url,
                        class: 'flex items-center gap-1 text-blue-600 hover:text-blue-800',
                    },
                    () => h('span', { class: 'text-md' }, 'Edit'),
                ),
                h(
                    'button',
                    {
                        type: 'button',
                        class: 'text-red-600 hover:text-red-800',
                        onClick: () => deleteCategory(id),
                    },
                    'Delete',
                ),
            ]);
        },
    }),
];

// data reactive
const items = computed(() =>
    props.categories.data.map((item) => ({
        id: item.id,
        name: item.name,
        parent_name: item.parent?.name || '-',
        created_at: item.created_at,
        updated_at: item.updated_at || '-',
    })),
);

// filter pencarian
const filteredItems = computed(() =>
    items.value.filter(
        (i) => i.name.toLowerCase().includes(search.value.toLowerCase()) || i.parent_name.toLowerCase().includes(search.value.toLowerCase()),
    ),
);

// build table instance
const table = useVueTable({
    data: filteredItems,
    columns,
    getCoreRowModel: getCoreRowModel(),
});

function deleteCategory(id: number) {
    modal.value?.open('Are you sure you want to delete this category?', () => {
        router.delete(category.destroy(id).url);
    });
}
</script>

<template>
    <Head title="Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-lg bg-white shadow-md">
            <!-- debug categories -->
            <!-- <pre class="text-sm text-black">{{ props.categories }}</pre> -->

            <div class="space-y-4 p-6">
                <!-- Search + Button -->
                <div class="flex items-center justify-between">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search category..."
                        class="h-10 w-64 rounded-md border px-3 py-2 text-black focus:ring focus:ring-cyan-200 focus:outline-none"
                    />

                    <Link
                        :href="category.create().url"
                        class="inline-flex items-center gap-2 rounded bg-cyan-800 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                    >
                        Add Category
                    </Link>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-lg border">
                    <table class="min-w-full border-collapse bg-white">
                        <thead class="bg-gray-100">
                            <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                <th
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                    class="border-b px-4 py-2 text-left text-sm font-semibold text-gray-700"
                                >
                                    <component :is="FlexRender" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-gray-50">
                                <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="border-b px-4 py-2 text-sm text-gray-600">
                                    <component :is="FlexRender" :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :pagination="props.categories" class="p-4" />
                </div>
            </div>
        </div>
        <ModalDelete ref="modal" />
    </AppLayout>
</template>
