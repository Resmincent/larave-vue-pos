<script setup lang="ts">
import ModalDelete from '@/components/modules/Modal/ModalDelete.vue';
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import permission from '@/routes/permissions';
import { type BreadcrumbItem } from '@/types';
import { PermissionPagination } from '@/types/permission';
import { Head, Link, router } from '@inertiajs/vue3';
import { createColumnHelper, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, h, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Permissions',
        href: permission.index().url,
    },
];

const props = defineProps<{
    permissions: PermissionPagination;
}>();

const search = ref('');

const columnHelper = createColumnHelper<any>();

const modal = ref<InstanceType<typeof ModalDelete> | null>(null);

const columns = [
    columnHelper.accessor('id', {
        header: 'ID',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('name', {
        header: 'Name',
        cell: (info) => info.getValue(),
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
                        href: permission.edit(id).url,
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

function deleteCategory(id: number) {
    modal.value?.open('Are you sure you want to delete this permission?', () => {
        router.delete(permission.destroy(id).url);
    });
}

const items = computed(() =>
    props.permissions.data.map((item) => ({
        id: item.id,
        name: item.name,
    })),
);

const filteredItems = computed(() => items.value.filter((item) => item.name.toLowerCase().includes(search.value.toLowerCase())));

const tableData = useVueTable({
    data: filteredItems,
    columns: columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Permissions" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-6/12 rounded-lg bg-white shadow-md">
            <div class="space-y-4 p-6">
                <!-- Search + Button -->
                <div class="flex items-center justify-between">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search permission..."
                        class="h-10 w-64 rounded-md border px-3 py-2 text-black focus:ring focus:ring-cyan-200 focus:outline-none"
                    />

                    <Link
                        :href="permission.create().url"
                        class="inline-flex items-center gap-2 rounded bg-cyan-800 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                    >
                        Add Permission
                    </Link>
                </div>

                <div class="overflow-x-auto rounded-lg border">
                    <table class="min-w-full border-collapse bg-white">
                        <thead class="bg-gray-100">
                            <tr v-for="headerGroup in tableData.getHeaderGroups()" :key="headerGroup.id">
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
                            <tr v-for="row in tableData.getRowModel().rows" :key="row.id" class="hover:bg-gray-50">
                                <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="border-b px-4 py-2 text-sm text-gray-600">
                                    <component :is="FlexRender" :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :pagination="props.permissions" class="p-4" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
