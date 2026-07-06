<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()
const sidebarOpen = ref(false)

const user = computed(() => page.props.auth?.user || null)

const appName = computed(() => {
    return page.props.name || import.meta.env.VITE_APP_NAME || 'SICEM'
})

const activeOffice = computed(() => {
    return page.props.activeOffice
        || page.props.office
        || page.props.currentOffice
        || page.props.auth?.office
        || null
})

const flash = computed(() => page.props.flash || {})

const safeRoute = (routeName, params = {}) => {
    try {
        return route(routeName, params)
    } catch (error) {
        return '#'
    }
}

const isRouteAvailable = (routeName, params = {}) => {
    try {
        route(routeName, params)
        return true
    } catch (error) {
        return false
    }
}

const isCurrent = (patterns) => {
    try {
        const items = Array.isArray(patterns) ? patterns : [patterns]

        return items.some((pattern) => route().current(pattern))
    } catch (error) {
        return false
    }
}

const navigationGroups = computed(() => [
    {
        label: 'Panel',
        items: [
            {
                name: 'Matriz',
                route: 'dashboard',
                active: isCurrent('dashboard'),
                icon: 'home',
            },
        ],
    },
    {
        label: 'Operación',
        items: [
            {
                name: 'Clientes',
                route: 'customers.index',
                active: isCurrent('customers.*'),
                icon: 'users',
            },
            {
                name: 'Empeños',
                route: 'pawns.index',
                active: isCurrent('pawns.*'),
                icon: 'gem',
            },
            {
                name: 'Cotizaciones',
                route: 'quotations.index',
                active: isCurrent('quotations.*'),
                icon: 'clipboard',
            },
        ],
    },
    {
        label: 'Caja',
        items: [
            {
                name: 'Transacciones',
                route: 'transactions.index',
                active: isCurrent('transactions.*'),
                icon: 'receipt',
            },
            {
                name: 'Ingresos',
                route: 'incomes.index',
                active: isCurrent('incomes.*'),
                icon: 'income',
            },
            {
                name: 'Gastos',
                route: 'expenses.index',
                active: isCurrent('expenses.*'),
                icon: 'expense',
            },
            {
                name: 'Corte de caja',
                route: 'closures.create',
                active: isCurrent('closures.*'),
                icon: 'cash',
            },
        ],
    },
    {
        label: 'Inventario',
        items: [
            {
                name: 'Departamentos',
                route: 'departments.index',
                active: isCurrent('departments.*'),
                icon: 'layers',
            },
            {
                name: 'Productos',
                route: 'products.index',
                active: isCurrent('products.*'),
                icon: 'box',
            },
            {
                name: 'Subastas',
                route: 'auctions.index',
                active: isCurrent('auctions.*'),
                icon: 'gavel',
            },
            {
                name: 'Punto de venta',
                route: 'point-of-sales.index',
                active: isCurrent('point-of-sales.*'),
                icon: 'store',
            },
            {
                name: 'Apartados',
                route: 'asides.index',
                active: isCurrent('asides.*'),
                icon: 'bookmark',
            },
        ],
    },
    {
        label: 'Análisis',
        items: [
            {
                name: 'Reportes',
                route: 'reports.index',
                active: isCurrent('reports.*'),
                icon: 'chart',
            },
            {
                name: 'Folios',
                route: 'folios.index',
                active: isCurrent('folios.*'),
                icon: 'hash',
            },
        ],
    },
    {
        label: 'Administración',
        items: [
            {
                name: 'Empresas',
                route: 'companies.index',
                active: isCurrent('companies.*'),
                icon: 'briefcase',
            },
            {
                name: 'Sucursales',
                route: 'offices.index',
                active: isCurrent(['offices.index', 'offices.create', 'offices.edit']),
                icon: 'building',
            },
            {
                name: 'Usuarios',
                route: 'access.users.index',
                active: isCurrent('access.users.*'),
                icon: 'userCog',
            },
            {
                name: 'Roles',
                route: 'access.roles.index',
                active: isCurrent('access.roles.*'),
                icon: 'shield',
            },
            {
                name: 'Permisos',
                route: 'access.permissions.index',
                active: isCurrent('access.permissions.*'),
                icon: 'key',
            },
        ],
    },
])

const visibleNavigationGroups = computed(() => {
    return navigationGroups.value
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => isRouteAvailable(item.route)),
        }))
        .filter((group) => group.items.length > 0)
})

const iconPath = (icon) => {
    const icons = {
        home: 'M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V10.5Z',
        users: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0M19 8a3 3 0 0 1 0 6M22 21a6 6 0 0 0-3-5.2',
        gem: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        clipboard: 'M9 4h6l1 2h3v15H5V6h3l1-2ZM9 10h6M9 14h6M9 18h4',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        income: 'M12 3v12M7 10l5 5 5-5M4 21h16M5 17h14',
        expense: 'M12 21V9M7 14l5-5 5 5M4 3h16M5 7h14',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        layers: 'M12 3 3 8l9 5 9-5-9-5ZM3 12l9 5 9-5M3 16l9 5 9-5',
        box: 'M21 8.5 12 3 3 8.5v7L12 21l9-5.5v-7ZM3.5 9 12 14l8.5-5M12 21v-7',
        gavel: 'M14 5l5 5M12 7l5 5M5 14l5 5M7 12l5 5M14 5l-7 7M17 12l-7 7M3 21h8',
        store: 'M4 10h16l-1-5H5l-1 5ZM6 10v10h12V10M9 20v-5h6v5M4 10a3 3 0 0 0 6 0M10 10a3 3 0 0 0 6 0M16 10a3 3 0 0 0 6 0',
        bookmark: 'M6 4h12v17l-6-4-6 4V4Z',
        chart: 'M4 19V5M4 19h16M8 16v-5M12 16V8M16 16v-8',
        hash: 'M9 3 7 21M17 3l-2 18M4 8h17M3 16h17',
        briefcase: 'M10 6V5a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2v1M4 7h16v12H4V7ZM4 12h16',
        building: 'M4 21V6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V21M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M9 21v-5h6v5',
        userCog: 'M15 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM3 21a8 8 0 0 1 13.5-5.8M19 14v2M19 20v1M16.4 15.5l1.4 1.4M20.2 19.1l1.4 1.4M15 18h2M21 18h2',
        shield: 'M12 3 20 7v5c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V7l8-4ZM9 12l2 2 4-5',
        key: 'M15 7a4 4 0 1 0 2.8 6.8L21 17v3h-3v-2h-2v-2h-2l-1.2-1.2A4 4 0 0 0 15 7ZM7 10h.01',
    }

    return icons[icon] || icons.home
}

const userInitial = computed(() => {
    return user.value?.name ? user.value.name.charAt(0).toUpperCase() : 'U'
})

const officeName = computed(() => {
    return activeOffice.value?.name || activeOffice.value?.nombre || 'Sucursal no seleccionada'
})
</script>

<template>
    <div class="min-h-screen bg-[#eef2f7]">
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        />

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-[#142126] text-white shadow-2xl transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center gap-3 border-b border-white/5 bg-[#5b55a4] px-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 text-white">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5Z"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <div>
                    <p class="text-xl font-black tracking-wide">
                        {{ appName }}
                    </p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-white/70">
                        Control prendario
                    </p>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-5">
                <div
                    v-for="group in visibleNavigationGroups"
                    :key="group.label"
                    class="mb-6 last:mb-0"
                >
                    <p class="mb-2 px-3 text-[10px] font-black uppercase tracking-[0.22em] text-slate-500">
                        {{ group.label }}
                    </p>

                    <div class="space-y-1">
                        <Link
                            v-for="item in group.items"
                            :key="item.name"
                            :href="safeRoute(item.route)"
                            class="group flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-bold transition"
                            :class="item.active
                                ? 'bg-white text-[#142126] shadow-lg shadow-black/20'
                                : 'text-slate-300 hover:bg-white/10 hover:text-white'"
                            @click="sidebarOpen = false"
                        >
                            <svg
                                class="h-5 w-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    :d="iconPath(item.icon)"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>

                            <span class="truncate">
                                {{ item.name }}
                            </span>
                        </Link>
                    </div>
                </div>
            </nav>

            <div class="border-t border-white/5 p-4">
                <div class="rounded-2xl bg-white/5 p-4">
                    <p class="text-sm font-black text-white">
                        {{ user?.name || 'Usuario' }}
                    </p>
                    <p class="mt-1 truncate text-xs font-medium text-slate-400">
                        {{ user?.email || 'Sin correo' }}
                    </p>
                </div>

                <Link
                    :href="safeRoute('logout')"
                    method="post"
                    as="button"
                    class="mt-3 flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-bold text-slate-200 transition hover:bg-rose-500 hover:text-white"
                >
                    Cerrar sesión
                </Link>
            </div>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between bg-[#5b55a4] px-4 text-white shadow-lg shadow-slate-300/40 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-white/20 lg:hidden"
                        @click="sidebarOpen = true"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M4 7h16M4 12h16M4 17h16"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            />
                        </svg>
                    </button>

                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.25em] text-white/60">
                            Panel administrativo
                        </p>
                        <h1 class="text-base font-black sm:text-lg">
                            Sistema de control
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden rounded-2xl bg-white/10 px-4 py-2 text-sm font-black sm:block">
                        {{ officeName }}
                    </div>

                    <Link
                        v-if="isRouteAvailable('offices.select')"
                        :href="safeRoute('offices.select')"
                        class="hidden rounded-2xl bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/20 md:block"
                    >
                        Cambiar sucursal
                    </Link>

                    <div class="hidden max-w-64 truncate rounded-2xl bg-[#514a97] px-4 py-2 text-sm font-black shadow-inner shadow-black/10 sm:block">
                        {{ user?.name || 'Usuario' }}
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-500 text-sm font-black text-white shadow-lg shadow-black/20">
                        {{ userInitial }}
                    </div>
                </div>
            </header>

            <div
                v-if="flash.success || flash.error || flash.warning"
                class="px-4 pt-5 sm:px-6 lg:px-8"
            >
                <div
                    v-if="flash.success"
                    class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700"
                >
                    {{ flash.success }}
                </div>

                <div
                    v-if="flash.warning"
                    class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-bold text-amber-800"
                >
                    {{ flash.warning }}
                </div>

                <div
                    v-if="flash.error"
                    class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-bold text-rose-700"
                >
                    {{ flash.error }}
                </div>
            </div>

            <main>
                <slot />
            </main>
        </div>
    </div>
</template>