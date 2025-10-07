<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import sale from '@/routes/sales';
import { type BreadcrumbItem } from '@/types';
import { SalePagination } from '@/types/sales';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sales',
        href: sale.index().url,
    },
];

const props = defineProps<{
    sales: SalePagination;
    filters: { query?: string };
}>();

const search = ref(props.filters.query || '');

watch(search, (value) => {
    router.get(sale.index().url, { query: value }, { preserveState: true, replace: true });
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
            </div>
        </div>
    </AppLayout>
</template>
