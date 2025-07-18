<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavMainRecursive from '@/components/NavMainParentRecursive.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Dokumen',
        icon: LayoutGrid,
        children: [
            { 
                title: 'Semua Dokumen', 
                icon: LayoutGrid,
                children: [
                    { 
                        title: 'sub Dokumen 1', 
                        icon: LayoutGrid,
                        href: '/semua-dokumen' },
                    { 
                        title: 'sub Arsip 1',
                        icon: LayoutGrid,
                        href: '/dokumen-a' 
                    },
                ] 
            },
            { 
                title: 'Kategori',
                icon: LayoutGrid,
                href: '/dokumen/kategori' 
            },
        ]
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Administrator',
        href: '/admin/dashboards',
        icon: Folder,
    },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMainRecursive :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavMain :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
