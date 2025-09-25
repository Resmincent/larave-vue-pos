<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import assign from '@/routes/assign';
import roles from '@/routes/roles';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Assign Permission',
        href: roles.index().url,
    },
    {
        title: 'Assign Permission',
        href: assign.permissions.index().url,
    },
];
</script>

<template>
    <Head title="Assign Permission" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"></div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"></div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border"></div>
        </div>
    </AppLayout>
</template>
