<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import products from '@/routes/products';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: products.index().url,
    },
    {
        title: 'Create Product',
        href: products.create().url,
    },
];
</script>
<template>
    <Head title="Create Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5"></div>
    </AppLayout>
</template>
