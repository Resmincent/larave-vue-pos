<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

defineProps<{
    items: NavItem[];
}>();

const page = usePage();

// 🔍 Debug items yang diterima
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">
                <SidebarMenuItem v-if="!item.children">
                    <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                        <Link :href="item.href">
                            <component :is="item.icon" v-if="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                <SidebarMenuItem v-else>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-500">
                            <component :is="item.icon" v-if="item.icon" />
                            {{ item.title }}
                        </div>
                        <div class="ml-6 space-y-1 border-l pl-4">
                            <Link
                                v-for="child in item.children"
                                :key="child.title"
                                :href="child.href"
                                class="flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-gray-500"
                                :class="{ 'bg-gray-200 font-semibold': child.href === page.url }"
                            >
                                <component :is="child.icon" v-if="child.icon" class="h-4 w-4" />
                                {{ child.title }}
                            </Link>
                        </div>
                    </div>
                </SidebarMenuItem>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
