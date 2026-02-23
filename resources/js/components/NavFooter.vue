<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { type NavItem } from '@/types'
import { SidebarMenu, SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar'

defineProps<{ items: NavItem[] }>()
</script>

<template>
  <div class="px-2 pb-2">
    <SidebarMenu>
      <SidebarMenuItem v-for="item in items" :key="item.href">
        <SidebarMenuButton as-child class="rounded-xl px-3 py-2 text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">
          <a :href="item.href" target="_blank" rel="noreferrer" class="flex items-center gap-3">
            <component v-if="item.icon" :is="item.icon" class="h-4 w-4 opacity-70" />
            <span class="text-sm">{{ item.title }}</span>
          </a>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  </div>
</template>