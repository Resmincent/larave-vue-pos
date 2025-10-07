<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import sale from '@/routes/sales';
import type { BreadcrumbItem } from '@/types';
import type { SalePagination } from '@/types/sales';
import { Head, Link, router } from '@inertiajs/vue3';
import { createColumnHelper, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, h, ref, watch, watchEffect } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Sales', href: sale.index().url }];

const props = defineProps<{ sales: SalePagination; filters: { query?: string } }>();

const search = ref(props.filters.query || '');

// --- debounce search ---
let t: any = null;
watch(search, (value) => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(sale.index().url, { query: value }, { preserveState: true, replace: true });
    }, 350);
});

// --- helper format ---
const fmtIDR = (n: number | string | null | undefined) => `Rp${Number(n ?? 0).toLocaleString('id-ID')}`;

const fmtDate = (iso: string | null) => (iso ? new Date(iso).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-');

// --- normalisasi data untuk tabel ---
const items = computed(() =>
    props.sales.data.map((item) => ({
        id: item.id,
        code: item.code,
        customerId: item.customer?.user.name ?? '-',
        userId: item.user?.name ?? '-',
        status: String(item.status || '').toLowerCase(),
        subtotal: item.subtotal,
        discountTotal: item.discount_total,
        taxTotal: item.tax_total,
        grandTotal: item.grand_total,
        paidTotal: item.paid_total,
        changeDue: item.change_due,
        soldAt: item.sold_at,
    })),
);

const columnHelper = createColumnHelper<any>();

// --- badge status ---
const getStatusBadge = (status: string) => {
    const s = String(status || '').toLowerCase();
    const map: Record<string, { label: string; cls: string }> = {
        open: { label: 'Open', cls: 'bg-amber-100 text-amber-800 border-amber-300' },
        paid: { label: 'Paid', cls: 'bg-green-100 text-green-800 border-green-300' },
        void: { label: 'Void', cls: 'bg-red-100 text-red-800 border-red-300' },
    };
    const m = map[s] ?? { label: String(status).toUpperCase(), cls: 'bg-gray-100 text-gray-800 border-gray-300' };

    return h(
        'span',
        {
            class: `inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium ${m.cls}`,
            'aria-label': `Status ${m.label}`,
        },
        [h('span', { class: 'inline-block h-2 w-2 rounded-full bg-current opacity-70' }), m.label],
    );
};

const columns = [
    columnHelper.accessor('code', { header: 'Code' }),
    columnHelper.accessor('customerId', { header: 'Customer' }),
    columnHelper.accessor('userId', { header: 'Cashier' }),
    columnHelper.accessor('status', {
        header: 'Status',
        cell: (info) => getStatusBadge(info.getValue() as string),
    }),
    columnHelper.accessor('changeDue', {
        header: 'Change Due',
        cell: (info) => fmtIDR(info.getValue()),
    }),
    columnHelper.accessor('soldAt', {
        header: 'Date Sold',
        cell: (info) => fmtDate(info.getValue()),
    }),
    columnHelper.accessor('subtotal', {
        header: 'Sub Total',
        cell: (info) => fmtIDR(info.getValue()),
    }),
    columnHelper.accessor('discountTotal', {
        header: 'Discount',
        cell: (info) => fmtIDR(info.getValue()),
    }),
    columnHelper.accessor('taxTotal', {
        header: 'Tax',
        cell: (info) => fmtIDR(info.getValue()),
    }),
    columnHelper.accessor('paidTotal', {
        header: 'Paid Total',
        cell: (info) => fmtIDR(info.getValue()),
    }),
    columnHelper.accessor('grandTotal', {
        header: 'Grand Total',
        cell: (info) => fmtIDR(info.getValue()),
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
        cell: (info) => {
            const id = info.row.original.id;
            return h('div', { class: 'flex gap-4' }, [h('a', { href: sale.show(id).url, class: 'text-cyan-600 hover:underline' }, 'View')]);
        },
    }),
];

// --- inisiasi table + reaktif terhadap perubahan data ---
const tableData = useVueTable({
    data: items.value,
    columns,
    getCoreRowModel: getCoreRowModel(),
});

watchEffect(() => {
    tableData.setOptions((prev) => ({
        ...prev,
        data: items.value,
    }));
});
</script>

<template>
    <Head title="Sales" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-5 rounded-lg bg-white shadow-md">
            <div class="space-y-4 p-6">
                <div class="flex items-center justify-between">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search sale"
                        class="h-10 w-64 rounded-md border px-3 py-2 text-black focus:ring focus:ring-cyan-200 focus:outline-none"
                    />

                    <Link
                        :href="sale.create().url"
                        class="inline-flex items-center gap-2 rounded bg-cyan-800 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                    >
                        Add Sale
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
                            <tr v-if="tableData.getRowModel().rows.length === 0">
                                <td colspan="12" class="border-b px-4 py-8 text-center text-sm text-gray-500">No sale data found</td>
                            </tr>
                            <tr v-for="row in tableData.getRowModel().rows" :key="row.id" class="hover:bg-gray-50">
                                <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="border-b px-4 py-2 text-sm text-gray-600">
                                    <component :is="FlexRender" :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :pagination="props.sales" class="p-4" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
