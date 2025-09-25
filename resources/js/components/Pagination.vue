<script setup lang="ts">
import { PaginationMeta } from '@/types/pagination';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps<{
    pagination: PaginationMeta;
}>();
</script>

<template>
    <div v-if="pagination && pagination.links.length" class="mt-6 flex flex-col items-center justify-between gap-4 sm:flex-row">
        <!-- Info -->
        <div class="text-sm text-gray-600">
            Showing
            <span class="font-semibold text-gray-800">{{ pagination.from }}</span>
            to
            <span class="font-semibold text-gray-800">{{ pagination.to }}</span>
            of
            <span class="font-semibold text-gray-800">{{ pagination.total }}</span>
            results
        </div>

        <!-- Links -->
        <div class="flex items-center space-x-1">
            <Link
                v-for="(link, i) in pagination.links"
                :key="i"
                :href="link.url ?? '#'"
                class="inline-flex items-center justify-center rounded-md px-3 py-1.5 text-sm transition-colors"
                :class="{
                    'border border-cyan-600 bg-cyan-600 text-white hover:bg-cyan-700': link.active,
                    'border border-gray-300 text-gray-500 hover:bg-gray-100': !link.active && link.url,
                    'cursor-not-allowed border border-gray-200 text-gray-400': !link.url,
                }"
            >
                <template v-if="link.label.includes('Previous')">
                    <ChevronLeft class="h-4 w-4" />
                </template>
                <template v-else-if="link.label.includes('Next')">
                    <ChevronRight class="h-4 w-4" />
                </template>
                <template v-else>
                    {{ link.label }}
                </template>
            </Link>
        </div>
    </div>
</template>
