<script setup lang="ts">
import type { PaginationMeta } from '@/types/pagination';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    meta: PaginationMeta;
}>();
</script>

<template>
    <div class="mt-6 flex flex-col items-center justify-between gap-4 sm:flex-row">
        <!-- Info -->
        <div class="text-sm text-gray-600">
            Showing
            <span class="font-semibold text-gray-800">{{ props.meta.from }}</span>
            to
            <span class="font-semibold text-gray-800">{{ props.meta.to }}</span>
            of
            <span class="font-semibold text-gray-800">{{ props.meta.total }}</span>
            results
        </div>

        <!-- Pagination -->
        <nav aria-label="Pagination">
            <ul class="inline-flex h-8 -space-x-px text-sm">
                <li v-for="(link, index) in props.meta.links" :key="index">
                    <Link
                        :href="link.url ?? '#'"
                        class="flex items-center justify-center border px-3 leading-tight"
                        :class="{
                            'border-cyan-600 bg-cyan-600 text-white hover:bg-cyan-700': link.active,
                            'border-gray-300 bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-700': !link.active && link.url,
                            'cursor-not-allowed border-gray-200 bg-gray-100 text-gray-400': !link.url,
                            'rounded-s-lg': index === 0,
                            'rounded-e-lg': index === props.meta.links.length - 1,
                        }"
                    >
                        <span v-html="link.label"></span>
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>
