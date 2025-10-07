<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import paymentMethod from '@/routes/payment-methods';
import { type BreadcrumbItem } from '@/types';
import { PaymentMethodPagination } from '@/types/sales';
import { Head, Link } from '@inertiajs/vue3';
import { createColumnHelper, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, h } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Payment Methods',
        href: paymentMethod.index().url,
    },
];

const props = defineProps<{
    paymentMethods: PaymentMethodPagination;
}>();

const columnHelper = createColumnHelper<any>();

const columns = [
    columnHelper.accessor('id', {
        header: 'ID',
    }),
    columnHelper.accessor('name', {
        header: 'Name',
    }),
    columnHelper.accessor('code', {
        header: 'Code',
    }),
    columnHelper.accessor('isActive', {
        header: 'Is Active',
        cell: (info) => (info.getValue() ? 'Yes' : 'No'),
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
                        href: paymentMethod.edit(id).url,
                        class: 'flex items-center gap-1 text-blue-600 hover:text-blue-800',
                    },
                    () => h('span', { class: 'text-md' }, 'Edit'),
                ),
                h(
                    'button',
                    {
                        type: 'button',
                        class: 'text-red-600 hover:text-red-800',
                        // onClick: () => deleteCategory(id),
                    },
                    'Delete',
                ),
            ]);
        },
    }),
];

const items = computed(() =>
    props.paymentMethods.data.map((item) => ({
        id: item.id,
        code: item.code,
        name: item.name,
        isActive: item.is_active,
    })),
);

const table = useVueTable({
    data: items,
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Payment Methods" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-lg bg-white shadow-md">
            <div class="space-y-4 p-6">
                <!-- Button -->
                <div class="flex items-center justify-end">
                    <Link
                        :href="paymentMethod.create().url"
                        class="inline-flex items-center gap-2 rounded bg-cyan-800 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                    >
                        Add Payment Method
                    </Link>
                </div>

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
                            <tr v-if="table.getRowModel().rows.length === 0">
                                <td colspan="6" class="border-b px-4 py-8 text-center text-sm text-gray-500">No category data found</td>
                            </tr>
                            <tr v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-gray-50">
                                <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="border-b px-4 py-2 text-sm text-gray-600">
                                    <component :is="FlexRender" :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :pagination="props.paymentMethods" class="p-4" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
