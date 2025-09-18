<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import categories from '@/routes/categories';
import inventories from '@/routes/inventories';
import permissions from '@/routes/permissions';
import products from '@/routes/products';
import roles from '@/routes/roles';
import sale from '@/routes/sales';
import users from '@/routes/users';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Box, Dock, Folder, LayoutGrid, SquareMenu, SquareUser, WalletCards } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Products',
        href: products.index(),
        icon: Box,
    },
    {
        title: 'Inventory',
        href: inventories.index(),
        icon: Dock,
    },
    {
        title: 'Sales',
        href: sale.index(),
        icon: WalletCards,
    },
    {
        title: 'Categories',
        href: categories.index(),
        icon: SquareMenu,
    },
    {
        title: 'Roles & Permissions',
        children: [
            {
                title: 'Roles',
                href: roles.index(),
            },
            {
                title: 'Permissions',
                href: permissions.index(),
            },
        ],
    },
    {
        title: 'Users',
        href: users.index(),
        icon: SquareUser,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
