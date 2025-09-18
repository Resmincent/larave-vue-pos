<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { sidebarMenus } from '@/components/ui/sidebar/customSidebar';
import { dashboard } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';

const { props } = usePage();
const user = props.auth?.user;
const role = user?.roles?.[0]?.name || 'Admin';

const menus = sidebarMenus[role] || { main: [], footer: [] };
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="menus.main" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="menus.footer" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
