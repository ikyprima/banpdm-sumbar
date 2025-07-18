<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { SidebarMenu, SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar';
import type { NavItem, SharedData } from '@/types';
import SidebarMenuRecursive from '@/components/NavMainRecursive.vue';
const props = defineProps<{
  items: NavItem[];
}>();

const page = usePage<SharedData>();
const openItems = ref<string[]>([]);

function toggleItem(title: string) {
  if (openItems.value.includes(title)) {
    openItems.value = openItems.value.filter(t => t !== title);
  } else {
    openItems.value.push(title);
  }
}

function isItemOpen(title: string) {
  return openItems.value.includes(title);
}

/**
 * Cek apakah salah satu children/descendants cocok dengan page.url
 */
function checkOpenItems(items: NavItem[], parentTitles: string[] = []) {
  for (const item of items) {
    if (item.href && item.href === page.url) {
      // kalau cocok, buka semua parent yang menuju ke sini
        openItems.value.push(...parentTitles);
    }
        if (item.children) {
            checkOpenItems(item.children, [...parentTitles, item.title]);
        }
    }
}

onMounted(() => {
  checkOpenItems(props.items);
});
</script>

<template>
  <SidebarMenu>
    <SidebarMenuItem v-for="item in items" :key="item.title">
      <SidebarMenuButton
        as-child
        :is-active="item.href === page.url"
        :tooltip="item.title"
        @click="item.children ? toggleItem(item.title) : null"
      >
        <Link v-if="item.href" :href="item.href">
          <component :is="item.icon" />
          <span>{{ item.title }}</span>
        </Link>
        <div v-else class="flex items-center">
          <component :is="item.icon" />
          <span>{{ item.title }}</span>
          <svg 
            v-if="item.children"
            class="w-4 h-4 ml-auto transition-transform"
            :class="isItemOpen(item.title) ? 'rotate-90' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </SidebarMenuButton>

      <!-- render children secara rekursif -->
      <SidebarMenu v-if="item.children && isItemOpen(item.title)" class="pl-4">
        <SidebarMenuRecursive :items="item.children" />
      </SidebarMenu>
    </SidebarMenuItem>
  </SidebarMenu>
</template>
