<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import assign from '@/routes/assign';
import role from '@/routes/roles';
import { type BreadcrumbItem } from '@/types';
import { RolePagination } from '@/types/role';
import { Head, Link } from '@inertiajs/vue3';
import { createColumnHelper, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, h, ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: role.index().url,
    },
];

const props = defineProps<{
    roles: RolePagination;
}>();

const search = ref('');

const columnHelper = createColumnHelper<any>();

const items = computed(() =>
    props.roles.data.map((item) => ({
        id: item.id,
        name: item.name,
    })),
);

const filteredItems = computed(() => items.value.filter((item) => item.name.toLowerCase().includes(search.value.toLowerCase())));

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
        id: 'assign',
        header: 'Assign Permission',
        cell: (info) => {
            const role = info.row.original;
            // kalau role Admin → tampilkan text muted
            if (role.name === 'Admin') {
                return h('i', { class: 'text-gray-500' }, 'Default Assign');
            }
            return h(
                Link,
                {
                    href: assign.permissions.edit(role).url,
                    class: 'inline-flex items-center justify-center rounded bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-200',
                },
                () => 'Set Assign Permission',
            );
        },
    }),
    columnHelper.display({
        id: 'permissions',
        header: 'Permissions',
        cell: (info) => {
            const role = info.row.original;
            if (role.name === 'Admin') {
                return h('i', { class: 'text-gray-500' }, 'Default Role Assign');
            }
            if (role.permissions && role.permissions.length > 0) {
                return role.permissions.map((p: any) =>
                    h('span', { class: 'inline-block rounded bg-blue-100 px-2 py-0.5 text-xs text-blue-800 mr-1 mb-1' }, p.name),
                );
            }
            return h('span', { class: 'text-gray-500' }, 'No Permissions');
        },
    }),
];

const tableData = useVueTable({
    data: filteredItems,
    columns: columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Roles" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-lg bg-white shadow-md">
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
                <Pagination :pagination="props.roles" class="p-4" />
            </div>
        </div>
    </AppLayout>
</template>
