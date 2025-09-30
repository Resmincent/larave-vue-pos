<script setup lang="ts">
import ModalDelete from '@/components/modules/Modal/ModalDelete.vue';
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import customer from '@/routes/customers';
import { type BreadcrumbItem } from '@/types';
import { CustomerPagination } from '@/types/customers';
import { Head, Link, router } from '@inertiajs/vue3';
import { createColumnHelper, FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, h, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Customers',
        href: customer.index().url,
    },
];

const props = defineProps<{
    customers: CustomerPagination;
    filters: { query?: string };
}>();

const search = ref(props.filters.query || '');

watch(search, (value) => {
    router.get(customer.index().url, { query: value }, { preserveState: true, replace: true });
});

const modal = ref<InstanceType<typeof ModalDelete> | null>(null);

function deleteCustomer(id: number) {
    modal.value?.open('Are you sure you want to delete this user customer?', () => {
        router.delete(customer.destroy(id).url);
    });
}

const items = computed(() =>
    props.customers.data.map((item) => ({
        id: item.id,
        name: item.user.name || '-',
        email: item.user?.email || '-',
        address: item.address,
        phone: item.phone,
    })),
);

const columnHelper = createColumnHelper<any>();

const columns = [
    columnHelper.accessor('id', {
        header: 'ID',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('name', {
        header: 'Nama',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('email', {
        header: 'Email',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('phone', {
        header: 'Phone',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('address', {
        header: 'Address',
        cell: (info) => info.getValue(),
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
        cell: (info) => {
            const id = info.row.original.id;
            return h('div', { class: 'flex gap-4' }, [
                h(
                    'a',
                    {
                        href: customer.edit(id).url,
                        class: 'text-cyan-400 hover:underline',
                    },
                    'Edit',
                ),
                h(
                    'button',
                    {
                        type: 'button',
                        class: 'text-red-600 hover:text-red-800',
                        onClick: () => deleteCustomer(id),
                    },
                    'Delete',
                ),
            ]);
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
    <Head title="Customers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-lg bg-white shadow-md">
            <div class="space-y-4 p-6">
                <div class="flex items-center justify-between">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search customer..."
                        class="h-10 w-64 rounded-md border px-3 py-2 text-black focus:ring focus:ring-cyan-200 focus:outline-none"
                    />

                    <Link
                        :href="customer.create().url"
                        class="inline-flex items-center gap-2 rounded bg-cyan-800 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                    >
                        Add Customer
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
                    <Pagination :pagination="props.customers" class="p-4" />
                </div>
            </div>
        </div>
        <ModalDelete ref="modal" />
    </AppLayout>
</template>
