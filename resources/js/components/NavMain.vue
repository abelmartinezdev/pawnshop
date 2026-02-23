<script setup lang="ts">
import { computed, reactive } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { ChevronRight } from 'lucide-vue-next'
import { type NavItem } from '@/types'
import { SidebarMenu, SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar'

const props = defineProps<{ items: NavItem[] }>()
const page = usePage()

/**
 * Permisos: auth.can = { 'branches.manage': true, ... }
 * Si no existe, deja pasar todo (útil en dev).
 */
const can = (permission?: string) => {
  if (!permission) return true
  const map = ((page.props as any)?.auth?.can ?? null) as Record<string, boolean> | null
  if (!map) return true
  return !!map[permission]
}

const urlPath = computed(() => {
  const u = (page.url ?? '/') as string
  return u.split('?')[0]
})

const normalize = (s: string) => s.replace(/\/+$/, '') || '/'
const isActive = (href: string) => {
  const a = normalize(href)
  const p = normalize(urlPath.value)
  return p === a || (a !== '/' && p.startsWith(a + '/'))
}

const isParentActive = (item: NavItem) => {
  if (isActive(item.href)) return true
  if (!item.children?.length) return false
  return item.children.some((c) => isActive(c.href))
}

/**
 * Estado de acordeón por href del parent
 */
const open = reactive<Record<string, boolean>>({})

const ensureOpenDefaults = () => {
  // abre por defecto si el parent está "activo" (por child)
  for (const item of props.items) {
    if (item.children?.length) {
      const key = item.href
      if (open[key] === undefined) open[key] = isParentActive(item)
    }
  }
}
ensureOpenDefaults()

const visibleItems = computed(() => {
  // Filtra por permiso y también filtra children por permiso
  return (props.items ?? [])
    .filter((i) => can(i.permission))
    .map((i) => {
      const children = (i.children ?? []).filter((c) => can(c.permission))
      return { ...i, children }
    })
    .filter((i) => {
      // Si es grupo y se queda sin children, lo ocultamos (a menos que tenga href propio útil)
      if (i.children?.length) return true
      return true
    })
})

const toggle = (href: string) => {
  open[href] = !open[href]
}
</script>

<template>
  <div class="px-2 py-2">
    <SidebarMenu>
      <template v-for="item in visibleItems" :key="item.href">
        <SidebarMenuItem>
          <!-- Si tiene children: botón tipo toggle + link (click en chevron) -->
          <SidebarMenuButton
            as-child
            :data-active="isParentActive(item)"
            class="group rounded-xl px-3 py-2 transition
                   data-[active=true]:bg-zinc-900 data-[active=true]:text-white
                   dark:data-[active=true]:bg-white dark:data-[active=true]:text-zinc-900"
          >
            <Link :href="item.href" class="flex items-center gap-3">
              <component
                v-if="item.icon"
                :is="item.icon"
                class="h-4 w-4 opacity-80 group-data-[active=true]:opacity-100"
              />
              <span class="text-sm font-medium">{{ item.title }}</span>

              <span
                v-if="item.badge !== undefined"
                class="ml-auto rounded-full bg-zinc-200 text-zinc-800 text-[11px] px-2 py-0.5
                       dark:bg-zinc-800 dark:text-zinc-200
                       group-data-[active=true]:bg-white/20 group-data-[active=true]:text-white
                       dark:group-data-[active=true]:bg-zinc-900/10 dark:group-data-[active=true]:text-zinc-900"
              >
                {{ item.badge }}
              </span>

              <!-- Toggle children -->
              <button
                v-if="item.children?.length"
                type="button"
                class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-lg
                       hover:bg-zinc-100 dark:hover:bg-zinc-800/40"
                @click.prevent.stop="toggle(item.href)"
                aria-label="Toggle submenu"
              >
                <ChevronRight
                  class="h-4 w-4 opacity-50 transition"
                  :class="open[item.href] ? 'rotate-90' : ''"
                />
              </button>
            </Link>
          </SidebarMenuButton>

          <!-- Children -->
          <div
            v-if="item.children?.length && open[item.href]"
            class="mt-1 ml-3 border-l border-zinc-200 dark:border-zinc-800 pl-3 space-y-1"
          >
            <SidebarMenuButton
              v-for="child in item.children"
              :key="child.href"
              as-child
              :data-active="isActive(child.href)"
              class="rounded-xl px-3 py-2 text-sm transition
                     data-[active=true]:bg-zinc-100 data-[active=true]:text-zinc-900
                     dark:data-[active=true]:bg-zinc-900 dark:data-[active=true]:text-zinc-100"
            >
              <Link :href="child.href" class="flex items-center gap-3">
                <component v-if="child.icon" :is="child.icon" class="h-4 w-4 opacity-70" />
                <span class="text-sm">{{ child.title }}</span>
              </Link>
            </SidebarMenuButton>
          </div>
        </SidebarMenuItem>
      </template>
    </SidebarMenu>
  </div>
</template>