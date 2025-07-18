<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';

import SidebarMenuRecursive from '@/components/NavMainRecursive.vue';

defineProps<{
    items: NavItem[];
}>();

</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenuRecursive :items="items" />
    </SidebarGroup>
</template>
