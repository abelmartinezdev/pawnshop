<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import {
  LayoutGrid,
  Building2,     // empresas
  Store,         // sucursales
  ShieldCheck,
  Users,
  KeyRound,

  HandCoins,     // empeños
  Receipt,       // transacciones
  Wallet,        // cierres/cortes
  Gavel,         // subastas (opcional)
  ShoppingCart,  // ventas (opcional)

  BookOpen,
  Folder,
  ChevronRight,
} from 'lucide-vue-next'

import AppLogo from '@/components/AppLogo.vue'

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  SidebarSeparator,
} from '@/components/ui/sidebar'

import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from '@/components/ui/collapsible'

type NavItem = {
  title: string
  href?: string
  icon?: any
  permission?: string
  external?: boolean
  children?: NavItem[]
}

const page = usePage()

/** Permisos: page.props.auth.can = { 'pawns.view': true, ... } */
const can = (permission: string) => {
  const map = ((page.props as any)?.auth?.can ?? {}) as Record<string, boolean>
  if (permission in map) return !!map[permission]

  const perms = ((page.props as any)?.auth?.permissions ?? []) as string[]
  return perms.includes(permission)
}

/** etiqueta sucursal (OFFICE) */
const officeLabel = computed(() => {
  const o = (page.props as any)?.auth?.office
  return o ? `${o.name} (${o.code})` : 'Sin sucursal'
})

/** helpers active */
const currentUrl = computed(() => (page as any).url as string)
const isActive = (href?: string) => {
  if (!href) return false
  return currentUrl.value === href || currentUrl.value.startsWith(href + '/')
}
const isGroupActive = (item: NavItem) =>
  isActive(item.href) || (item.children?.some((c) => isActive(c.href)) ?? false)

/** Menú principal */
const mainNavItems = computed<NavItem[]>(() => {
  const items: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
  ]

  // -----------------------------
  // OPERACIÓN
  // -----------------------------
  const opsChildren: NavItem[] = []

  if (can('pawns.view')) {
    opsChildren.push({
      title: 'Empeños',
      href: '/pawns',
      icon: HandCoins,
      permission: 'pawns.view',
    })
  }

  if (can('transactions.view')) {
    opsChildren.push({
      title: 'Transacciones',
      href: '/transactions',
      icon: Receipt,
      permission: 'transactions.view',
    })
  }

  if (can('closures.manage')) {
    opsChildren.push({
      title: 'Corte / Cierre de caja',
      href: '/closures/create',
      icon: Wallet,
      permission: 'closures.manage',
    })
  }

  // opcionales
  if (can('auctions.view')) {
    opsChildren.push({ title: 'Subastas', href: '/auctions', icon: Gavel, permission: 'auctions.view' })
  }
  if (can('sales.view')) {
    opsChildren.push({ title: 'Ventas', href: '/sales', icon: ShoppingCart, permission: 'sales.view' })
  }

  if (opsChildren.length) {
    items.push({
      title: 'Operación',
      href: opsChildren[0]?.href ?? '/pawns',
      icon: Wallet,
      children: opsChildren,
    })
  }

  // -----------------------------
  // ADMIN / CONFIG
  // -----------------------------
  const adminChildren: NavItem[] = []

  if (can('companies.manage')) {
    adminChildren.push({
      title: 'Empresas',
      href: '/companies',
      icon: Building2,
      permission: 'companies.manage',
    })
  }

  if (can('offices.manage')) {
    adminChildren.push({
      title: 'Sucursales',
      href: '/offices',
      icon: Store,
      permission: 'offices.manage',
    })
  }

  if (adminChildren.length) {
    items.push({
      title: 'Catálogos',
      href: adminChildren[0]?.href ?? '/companies',
      icon: Building2,
      children: adminChildren,
    })
  }

  // -----------------------------
  // ACCESOS
  // -----------------------------
  const accessChildren: NavItem[] = []

  if (can('users.manage')) {
    accessChildren.push({
      title: 'Usuarios',
      href: '/access/users',
      icon: Users,
      permission: 'users.manage',
    })
  }

  if (can('roles.manage')) {
    accessChildren.push(
      { title: 'Roles', href: '/access/roles', icon: ShieldCheck, permission: 'roles.manage' },
      { title: 'Permisos', href: '/access/permissions', icon: KeyRound, permission: 'roles.manage' },
    )
  }

  if (accessChildren.length) {
    items.push({
      title: 'Accesos',
      href: accessChildren[0]?.href ?? '/access/users',
      icon: ShieldCheck,
      children: accessChildren,
    })
  }

  return items
})

/** Footer */
const footerNavItems: NavItem[] = [
  { title: 'Repo', href: 'https://github.com/', icon: Folder, external: true },
  { title: 'Docs', href: 'https://laravel.com/docs', icon: BookOpen, external: true },
]

const logout = () => router.post('/logout')
</script>

<template>
  <Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link href="/dashboard" class="flex items-center gap-3">
              <AppLogo />
              <div class="flex flex-col leading-tight">
                <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                  Casa Julieta
                </span>
                <span class="text-[11px] text-zinc-500 dark:text-zinc-400">
                  {{ officeLabel }}
                </span>
              </div>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <SidebarGroup>
        <SidebarGroupLabel>Navegación</SidebarGroupLabel>

        <SidebarGroupContent>
          <SidebarMenu>
            <template v-for="item in mainNavItems" :key="item.title">
              <!-- Item simple -->
              <SidebarMenuItem v-if="!item.children?.length">
                <SidebarMenuButton as-child :is-active="isActive(item.href)">
                  <Link :href="item.href || '#'">
                    <component v-if="item.icon" :is="item.icon" class="size-4" />
                    <span>{{ item.title }}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>

              <!-- Item con submenú -->
              <SidebarMenuItem v-else>
                <Collapsible :default-open="isGroupActive(item)">
                  <CollapsibleTrigger as-child>
                    <SidebarMenuButton :is-active="isGroupActive(item)">
                      <component v-if="item.icon" :is="item.icon" class="size-4" />
                      <span>{{ item.title }}</span>
                      <ChevronRight
                        class="ml-auto size-4 transition-transform duration-200"
                        :class="isGroupActive(item) ? 'rotate-90' : ''"
                      />
                    </SidebarMenuButton>
                  </CollapsibleTrigger>

                  <CollapsibleContent>
                    <SidebarMenuSub>
                      <SidebarMenuSubItem
                        v-for="child in item.children"
                        :key="child.title"
                      >
                        <SidebarMenuSubButton as-child :is-active="isActive(child.href)">
                          <Link :href="child.href || '#'">
                            <component v-if="child.icon" :is="child.icon" class="size-4" />
                            <span>{{ child.title }}</span>
                          </Link>
                        </SidebarMenuSubButton>
                      </SidebarMenuSubItem>
                    </SidebarMenuSub>
                  </CollapsibleContent>
                </Collapsible>
              </SidebarMenuItem>
            </template>
          </SidebarMenu>
        </SidebarGroupContent>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter>
      <SidebarSeparator />

      <SidebarMenu>
        <SidebarMenuItem v-for="item in footerNavItems" :key="item.title">
          <SidebarMenuButton as-child>
            <a :href="item.href" target="_blank" rel="noreferrer">
              <component v-if="item.icon" :is="item.icon" class="size-4" />
              <span>{{ item.title }}</span>
            </a>
          </SidebarMenuButton>
        </SidebarMenuItem>

        <SidebarMenuItem>
          <SidebarMenuButton @click="logout">
            <span>Cerrar sesión</span>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarFooter>
  </Sidebar>

  <slot />
</template>