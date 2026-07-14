<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    pawns: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({}),
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const search = ref(props.filters.search || '')
const perPage = ref(Number(props.filters.per_page || 10))

const hasSearch = computed(() => String(props.filters.search || '').trim() !== '')
const hasPawns = computed(() => Array.isArray(props.pawns.data) && props.pawns.data.length > 0)

const submitFilters = () => {
    if (!props.urls.index) {
        return
    }

    router.get(props.urls.index, {
        search: search.value.trim() || undefined,
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const clearFilters = () => {
    search.value = ''
    submitFilters()
}

const changePerPage = () => {
    submitFilters()
}

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const quantity = (value) => {
    const number = Number(value || 0)
    const decimals = Number.isInteger(number) ? 0 : 3

    return number.toLocaleString('es-MX', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    })
}

const paginationLabel = (label) => {
    return String(label || '')
        .replace('&laquo; Previous', 'Anterior')
        .replace('Previous', 'Anterior')
        .replace('Next &raquo;', 'Siguiente')
        .replace('Next', 'Siguiente')
}

const iconPath = (icon) => {
    const icons = {
        search: 'M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z',
        gavel: 'm14 5 5 5M12.5 6.5l5 5M3 21l6.5-6.5M9 4l2-2 7 7-2 2L9 4Zm-4 4 2-2 7 7-2 2-7-7Z',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        clock: 'M12 7v5l3 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        box: 'M21 8l-9-5-9 5 9 5 9-5ZM3 8v8l9 5 9-5V8M12 13v8',
        check: 'M20 6 9 17l-5-5',
        arrowRight: 'M5 12h14M13 6l6 6-6 6',
        eye: 'M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6S2.5 12 2.5 12ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        x: 'M18 6 6 18M6 6l12 12',
    }

    return icons[icon] || icons.gavel
}
</script>

<template>
    <Head title="Remates" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Caja / remates
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Folios listos para remate
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Empeños que alcanzaron su fecha de remate y todavía no se han enviado al inventario.
                    </p>
                </div>
            </div>

            <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.5rem] border border-violet-200 bg-violet-50 p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-violet-600">Pendientes</p>
                            <p class="mt-2 text-3xl font-black text-violet-950">{{ stats.pending_count || 0 }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-violet-700 shadow-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('gavel')" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-600">Capital pendiente</p>
                            <p class="mt-2 text-2xl font-black text-emerald-950">{{ money(stats.pending_principal) }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-emerald-700 shadow-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('cash')" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-amber-600">Mayor atraso</p>
                            <p class="mt-2 text-3xl font-black text-amber-950">{{ stats.oldest_overdue_days || 0 }}</p>
                            <p class="text-xs font-bold text-amber-700">día(s)</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-amber-700 shadow-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('clock')" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-[1.5rem] border border-blue-200 bg-blue-50 p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-blue-600">Inventario actual</p>
                            <p class="mt-2 text-3xl font-black text-blue-950">{{ stats.inventory_count || 0 }}</p>
                            <p class="text-xs font-bold text-blue-700">{{ stats.auctioned_today || 0 }} procesado(s) hoy</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-blue-700 shadow-sm">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('box')" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sicem-info-banner mb-6 rounded-[1.5rem] border p-5">
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('alert')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div>
                        <p class="font-black">Estos folios todavía no forman parte del inventario de remates.</p>
                        <p class="mt-1 text-sm font-semibold leading-6">
                            Revisa cada empeño y utiliza “Pasar a remate”. La operación generará los artículos de remate y marcará el folio como procesado.
                        </p>
                    </div>
                </div>
            </div>

            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <form
                    class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between"
                    @submit.prevent="submitFilters"
                >
                    <div class="relative w-full max-w-2xl">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('search')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>

                        <input
                            v-model="search"
                            type="search"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-12 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                            placeholder="Buscar folio, cliente, bolsa, producto o descripción..."
                        />

                        <button
                            v-if="search"
                            type="button"
                            class="absolute right-3 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            @click="clearFilters"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('x')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <select
                            v-model="perPage"
                            class="h-12 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                            @change="changePerPage"
                        >
                            <option :value="10">10 por página</option>
                            <option :value="25">25 por página</option>
                            <option :value="50">50 por página</option>
                        </select>

                        <button
                            type="submit"
                            class="sicem-btn-primary inline-flex h-12 items-center justify-center gap-2 rounded-2xl px-5 text-sm font-black shadow-lg shadow-violet-100 transition"
                            :disabled="!urls.index"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('search')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                            Buscar
                        </button>
                    </div>
                </form>

                <div v-if="hasPawns" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Folio</th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Cliente y prendas</th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">Fechas</th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Préstamo</th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">Datos</th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="pawn in pawns.data" :key="pawn.id" class="align-top transition hover:bg-slate-50/70">
                                <td class="px-5 py-5">
                                    <p class="text-base font-black text-slate-950">{{ pawn.folio }}</p>
                                    <span class="mt-2 inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-black text-amber-800">
                                        {{ pawn.overdue_days }} día(s) en fecha de remate
                                    </span>
                                </td>

                                <td class="min-w-[22rem] px-5 py-5">
                                    <p class="text-sm font-black text-slate-950">{{ pawn.customer.name }}</p>
                                    <div class="mt-3 space-y-2">
                                        <div v-for="item in pawn.items.slice(0, 3)" :key="item.id" class="text-xs leading-5 text-slate-600">
                                            <span class="font-black text-slate-800">
                                                {{ quantity(item.quantity) }} {{ item.unit || '' }} · {{ item.product_name }}
                                            </span>
                                            <span v-if="item.description"> — {{ item.description }}</span>
                                        </div>
                                        <p v-if="pawn.items_count > 3" class="text-xs font-black text-[#5b55a4]">
                                            + {{ pawn.items_count - 3 }} artículo(s) más
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-5 text-center text-xs">
                                    <p class="font-semibold text-slate-500">Entrada</p>
                                    <p class="mt-1 font-black text-slate-800">{{ pawn.created_at || '-' }}</p>
                                    <p class="mt-3 font-semibold text-slate-500">Vencimiento</p>
                                    <p class="mt-1 font-black text-slate-800">{{ pawn.date_expiration || '-' }}</p>
                                    <p class="mt-3 font-semibold text-amber-700">Remate</p>
                                    <p class="mt-1 font-black text-amber-900">{{ pawn.date_auction || '-' }}</p>
                                </td>

                                <td class="px-5 py-5 text-right">
                                    <p class="text-base font-black text-slate-950">{{ money(pawn.total) }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        Tasa diaria {{ Number(pawn.daily_interest_rate || 0).toFixed(3) }}%
                                    </p>
                                </td>

                                <td class="px-5 py-5 text-center text-xs">
                                    <p class="font-semibold text-slate-500">Bolsa</p>
                                    <p class="mt-1 font-black text-slate-800">{{ pawn.bag || 'Sin bolsa' }}</p>
                                    <p class="mt-3 font-semibold text-slate-500">Artículos</p>
                                    <p class="mt-1 font-black text-slate-800">{{ pawn.items_count }}</p>
                                </td>

                                <td class="px-5 py-5">
                                    <div class="flex min-w-[10rem] flex-col items-stretch gap-2">
                                        <Link
                                            v-if="pawn.urls.show"
                                            :href="pawn.urls.show"
                                            class="sicem-btn-blue inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            Ver empeño
                                        </Link>

                                        <button
                                            v-else
                                            type="button"
                                            class="sicem-action-disabled rounded-xl px-4 py-2.5 text-xs font-black"
                                            disabled
                                        >
                                            Ver empeño
                                        </button>

                                        <Link
                                            v-if="pawn.urls.send_to_auction && pawn.can_send_to_auction"
                                            :href="pawn.urls.send_to_auction"
                                            class="sicem-btn-orange inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black transition"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('arrowRight')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            Pasar a remate
                                        </Link>

                                        <button
                                            v-else
                                            type="button"
                                            class="sicem-action-disabled rounded-xl px-4 py-2.5 text-xs font-black"
                                            :title="pawn.disabled_reason || 'Acción no disponible'"
                                            disabled
                                        >
                                            Pasar a remate
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-6 py-16 text-center">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[2rem] bg-emerald-50 text-emerald-600">
                        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('check')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <h2 class="mt-5 text-xl font-black text-slate-950">
                        {{ hasSearch ? 'No se encontraron folios' : 'No hay folios pendientes de remate' }}
                    </h2>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500">
                        {{ hasSearch
                            ? 'Prueba con otro folio, cliente, bolsa o descripción.'
                            : 'Cuando un empeño alcance su fecha de remate aparecerá automáticamente en esta lista.'
                        }}
                    </p>

                    <button
                        v-if="hasSearch"
                        type="button"
                        class="sicem-btn-primary mt-5 rounded-2xl px-5 py-3 text-sm font-black transition"
                        @click="clearFilters"
                    >
                        Limpiar búsqueda
                    </button>
                </div>

                <div
                    v-if="Array.isArray(pawns.links) && pawns.links.length > 3"
                    class="flex flex-col gap-4 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm font-semibold text-slate-500">
                        Mostrando {{ pawns.from || 0 }} a {{ pawns.to || 0 }} de {{ pawns.total || 0 }} folios
                    </p>

                    <nav class="flex flex-wrap gap-2">
                        <template v-for="(link, index) in pawns.links" :key="`${link.label}-${index}`">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-xs font-black transition"
                                :class="link.active
                                    ? 'border-[#5b55a4] bg-[#5b55a4] text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                preserve-scroll
                            >
                                {{ paginationLabel(link.label) }}
                            </Link>

                            <span
                                v-else
                                class="inline-flex min-h-10 min-w-10 cursor-not-allowed items-center justify-center rounded-xl border border-slate-100 bg-slate-50 px-3 text-xs font-black text-slate-300"
                            >
                                {{ paginationLabel(link.label) }}
                            </span>
                        </template>
                    </nav>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
.sicem-btn-primary {
    background-color: #5b55a4 !important;
    border-color: #5b55a4 !important;
    color: #ffffff !important;
}

.sicem-btn-primary:hover:not(:disabled) {
    background-color: #4f4896 !important;
    border-color: #4f4896 !important;
    color: #ffffff !important;
}

.sicem-btn-blue {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
    color: #ffffff !important;
}

.sicem-btn-blue:hover {
    background-color: #1d4ed8 !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
}

.sicem-btn-orange {
    background-color: #f97316 !important;
    border-color: #f97316 !important;
    color: #ffffff !important;
}

.sicem-btn-orange:hover {
    background-color: #ea580c !important;
    border-color: #ea580c !important;
    color: #ffffff !important;
}

.sicem-action-disabled {
    background-color: #e2e8f0 !important;
    border-color: #e2e8f0 !important;
    color: #94a3b8 !important;
    cursor: not-allowed !important;
}

.sicem-info-banner {
    background-color: #eff6ff !important;
    border-color: #bfdbfe !important;
    color: #1e3a8a !important;
}
</style>
