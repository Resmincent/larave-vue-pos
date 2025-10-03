<script setup lang="ts">
import ModalAdjustStock from '@/components/modules/Modal/ModalAdjustStock.vue';
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import inventory from '@/routes/inventories';
import { type BreadcrumbItem } from '@/types';
import { InventoryPagination } from '@/types/inventories';
import { Head, router } from '@inertiajs/vue3';
import { createColumnHelper, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, h, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventories',
        href: inventory.index().url,
    },
];

const props = defineProps<{
    inventories: InventoryPagination;
    filters: { query?: string };
}>();

const search = ref(props.filters.query || '');

const showAdjust = ref(false);
const selectedProduct = ref<any | null>(null);

function openAdjust(product: any) {
    selectedProduct.value = product;
    showAdjust.value = true;
}

watch(search, (value) => {
    router.get('/inventories', { query: value }, { preserveState: true, replace: true });
});

const items = computed(() =>
    props.inventories.data.map((item) => ({
        id: item.id,
        product_id: item.id,
        product_name: item.name,
        sku: item.sku,
        unit: item.unit,
        qty: item.inventory ? item.inventory.qty : 0,
    })),
);

const columnHelper = createColumnHelper<any>();

const columns = [
    columnHelper.accessor('id', {
        header: 'ID',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('sku', {
        header: 'SKU',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('product_name', {
        header: 'Product Name',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('qty', {
        header: 'Stock Quantity',
        cell: (info) => {
            const qty = info.getValue();
            const colorClass = qty <= 0 ? 'text-red-600 font-semibold' : qty < 10 ? 'text-yellow-600' : 'text-green-600';
            return h('span', { class: colorClass }, qty);
        },
    }),
    columnHelper.accessor('unit', {
        header: 'Unit',
        cell: (info) => info.getValue(),
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
        cell: (info) => {
            const row = info.row.original;
            return h(
                'button',
                {
                    class: 'text-cyan-600 hover:underline font-medium',
                    onClick: () => openAdjust(row),
                },
                'Adjust Stock',
            );
        },
    }),
];

const tableData = useVueTable({
    data: items,
    columns: columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Inventories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-lg bg-white shadow-md">
            <div class="space-y-4 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by product name or SKU..."
                            class="h-10 w-80 rounded-md border px-3 py-2 text-black focus:ring focus:ring-cyan-200 focus:outline-none"
                        />
                    </div>

                    <div class="text-sm text-gray-600">
                        Total Items: <span class="font-semibold">{{ props.inventories.total }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border">
                    <table class="min-w-full border-collapse bg-white">
                        <thead class="bg-gray-100">
                            <tr v-for="headerGroup in tableData.getHeaderGroups()" :key="headerGroup.id">
                                <th
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                    class="border-b px-4 py-3 text-left text-sm font-semibold text-gray-700"
                                >
                                    <component :is="FlexRender" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-if="tableData.getRowModel().rows.length === 0">
                                <td colspan="6" class="border-b px-4 py-8 text-center text-sm text-gray-500">No inventory data found</td>
                            </tr>
                            <tr v-for="row in tableData.getRowModel().rows" :key="row.id" class="hover:bg-gray-50">
                                <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="border-b px-4 py-3 text-sm text-gray-600">
                                    <component :is="FlexRender" :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :pagination="props.inventories" class="p-4" />
                </div>
            </div>
        </div>
        <ModalAdjustStock :show="showAdjust" :product="selectedProduct" @close="showAdjust = false" @saved="router.reload()" />
    </AppLayout>
</template>
