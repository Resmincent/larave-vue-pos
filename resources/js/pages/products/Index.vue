<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import product from '@/routes/products';
import { type BreadcrumbItem } from '@/types';
import { ProductPagination } from '@/types/products';
import { Head, Link, router } from '@inertiajs/vue3';
import { createColumnHelper, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: product.index().url,
    },
];

const props = defineProps<{
    products: ProductPagination;
    filters: { query?: string };
}>();

const search = ref(props.filters.query || '');

watch(search, (value) => {
    router.get(product.index().url, { query: value }, { preserveState: true, replace: true });
});

const items = computed(() =>
    props.products.data.map((item) => ({
        id: item.id,
        sku: item.sku,
        name: item.name,
        category: item.category?.name,
        taxId: item.tax?.rate,
        sellPrice: item.sell_price,
        costPrice: item.cost_price,
        unit: item.unit,
        isActive: item.is_active,
    })),
);

const columnHelper = createColumnHelper<any>();

const columns = [
    columnHelper.accessor('id', {
        header: 'ID',
    }),
    columnHelper.accessor('sku', {
        header: 'SKU',
    }),
    columnHelper.accessor('unit', {
        header: 'Unit',
    }),
    columnHelper.accessor('isActive', {
        header: 'Is Active',
        cell: (info) => (info.getValue() ? 'Yes' : 'No'),
    }),
    columnHelper.accessor('name', {
        header: 'Name',
    }),
    columnHelper.accessor('category', {
        header: 'Category',
    }),
    columnHelper.accessor('taxId', {
        header: 'Tax',
    }),
    columnHelper.accessor('sellPrice', {
        header: 'Sell Price',
        cell: (info) => `Rp${Number(info.getValue()).toLocaleString('id-ID')}`,
    }),
    columnHelper.accessor('costPrice', {
        header: 'Cost Price',
        cell: (info) => `Rp${Number(info.getValue()).toLocaleString('id-ID')}`,
    }),
];

const tableData = useVueTable({
    data: items,
    columns: columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-lg bg-white shadow-md">
            <div class="space-y-4 p-6">
                <div class="flex items-center justify-between">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search product"
                        class="h-10 w-64 rounded-md border px-3 py-2 text-black focus:ring focus:ring-cyan-200 focus:outline-none"
                    />

                    <Link
                        :href="product.create().url"
                        class="inline-flex items-center gap-2 rounded bg-cyan-800 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                    >
                        Add Product
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
                    <Pagination :pagination="props.products" class="p-4" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
