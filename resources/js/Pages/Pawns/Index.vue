<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

const props = defineProps({
    pawns: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    options: {
        type: Object,
        default: () => ({
            statuses: [],
        }),
    },
})

const records = computed(() => props.pawns?.data || [])

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'active',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    per_page: props.filters.per_page || 15,
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const cleanFilters = () => {
    return Object.fromEntries(
        Object.entries(form).filter(([_, value]) => value !== '' && value !== null && value !== undefined),
    )
}

const submitFilters = () => {
    router.get(route('pawns.index'), cleanFilters(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const clearFilters = () => {
    form.search = ''
    form.status = 'active'
    form.date_from = ''
    form.date_to = ''
    form.per_page = 15

    router.get(route('pawns.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const statusClass = (status) => {
    return {
        active: 'sicem-status-active',
        expired: 'sicem-status-expired',
        paid: 'sicem-status-paid',
        cancelled: 'sicem-status-cancelled',
        countersigned: 'sicem-status-countersigned',
        auctioned: 'sicem-status-auctioned',
    }[status] || 'sicem-status-muted'
}

const rowClass = (pawn) => {
    return {
        expired: 'bg-amber-50/45',
        cancelled: 'bg-red-50/45',
        paid: 'bg-blue-50/35',
        auctioned: 'bg-orange-50/45',
        countersigned: 'bg-violet-50/35',
    }[pawn.status] || 'bg-white'
}

const iconPath = (icon) => {
    const icons = {
        plus: 'M12 5v14M5 12h14',
        search: 'M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z',
        filter: 'M4 5h16M7 12h10M10 19h4',
        gem: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        check: 'M20 6 9 17l-5-5',
        clock: 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        image: 'M4 5h16v14H4V5ZM8 13l2.5-3 3 4 2-2.5L20 17M8 8h.01',
        empty: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
    }

    return icons[icon] || icons.gem
}
</script>

<template>
    <Head title="Empeños" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Caja / empeños
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Empeños
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Consulta los contratos prendarios, estatus, vencimientos, saldos y movimientos relacionados.
                    </p>
                </div>

                <Link
                    :href="route('pawns.create')"
                    class="sicem-btn-primary inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('plus')" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    Nuevo empeño
                </Link>
            </div>

            <div class="mb-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="sicem-card-hero p-5 text-white">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                            Pendiente activo
                        </p>
                        <p class="mt-2 text-3xl font-black">
                            {{ money(summary.active_amount) }}
                        </p>
                    </div>
                    <div class="p-5 text-sm font-semibold text-slate-500">
                        Capital prestado vigente
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Pendientes
                    </p>
                    <p class="mt-2 text-3xl font-black text-emerald-600">
                        {{ summary.active || 0 }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        Empeños vigentes
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-amber-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Vencidos
                    </p>
                    <p class="mt-2 text-3xl font-black text-amber-600">
                        {{ summary.expired || 0 }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        Requieren seguimiento
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-blue-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Total
                    </p>
                    <p class="mt-2 text-3xl font-black text-blue-600">
                        {{ summary.total || 0 }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        Contratos registrados
                    </p>
                </div>
            </div>

            <section class="mb-6 rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <form class="p-5 sm:p-6" @submit.prevent="submitFilters">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('filter')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-black text-slate-950">
                                Filtros
                            </h2>
                            <p class="text-sm text-slate-500">
                                Busca por folio, cliente, teléfono, RFC o identificación.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                        <div class="xl:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Buscar
                            </label>

                            <div class="relative">
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('search')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>

                                <input
                                    v-model="form.search"
                                    type="search"
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                                    placeholder="Folio, cliente, RFC..."
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Estado
                            </label>

                            <select v-model="form.status" :class="selectClass">
                                <option
                                    v-for="status in options.statuses"
                                    :key="status.value"
                                    class="bg-white text-slate-900"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Desde
                            </label>
                            <input v-model="form.date_from" type="date" :class="inputClass" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Hasta
                            </label>
                            <input v-model="form.date_to" type="date" :class="inputClass" />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Por página
                            </label>

                            <select v-model="form.per_page" :class="selectClass">
                                <option class="bg-white text-slate-900" :value="10">10</option>
                                <option class="bg-white text-slate-900" :value="15">15</option>
                                <option class="bg-white text-slate-900" :value="25">25</option>
                                <option class="bg-white text-slate-900" :value="50">50</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            @click="clearFilters"
                        >
                            Limpiar
                        </button>

                        <button
                            type="submit"
                            class="sicem-btn-primary rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition"
                        >
                            Aplicar filtros
                        </button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                    <h2 class="text-xl font-black text-slate-950">
                        Listado de empeños
                    </h2>
                    <p class="text-sm text-slate-500">
                        Contratos de la sucursal activa.
                    </p>
                </div>

                <div v-if="records.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Folio / Cliente
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Prendas
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Préstamo
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    A liquidar
                                </th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">
                                    Fechas
                                </th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">
                                    Estado
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="pawn in records"
                                :key="pawn.id"
                                class="transition hover:bg-slate-50/80"
                                :class="rowClass(pawn)"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('gem')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>

                                        <div>
                                            <Link
                                                :href="route('pawns.show', pawn.id)"
                                                class="text-sm font-black text-slate-950 hover:text-[#5b55a4] hover:underline"
                                            >
                                                {{ pawn.folio }}
                                            </Link>

                                            <p class="mt-1 text-sm font-bold text-slate-700">
                                                {{ pawn.customer?.name || 'Sin cliente' }}
                                            </p>

                                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                                {{ pawn.customer?.phone || 'Sin teléfono' }}
                                                <span v-if="pawn.creator?.name"> · {{ pawn.creator.name }}</span>
                                            </p>

                                            <div v-if="!pawn.has_photos" class="mt-2 inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-1 text-[10px] font-black uppercase text-red-600">
                                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none">
                                                    <path :d="iconPath('image')" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                Sin fotos
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div v-if="pawn.items_preview?.length" class="space-y-1">
                                        <p
                                            v-for="item in pawn.items_preview"
                                            :key="item.id"
                                            class="text-sm font-semibold text-slate-700"
                                        >
                                            {{ item.product?.description || 'Producto' }}
                                            <span class="text-slate-400">
                                                · {{ item.quantity }} {{ item.product?.unit || '' }}
                                            </span>
                                        </p>

                                        <p v-if="pawn.items_count > pawn.items_preview.length" class="text-xs font-black text-[#5b55a4]">
                                            + {{ pawn.items_count - pawn.items_preview.length }} más
                                        </p>
                                    </div>

                                    <p v-else class="text-sm font-semibold text-slate-400">
                                        Sin prendas
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <p class="text-sm font-black text-slate-950">
                                        {{ money(pawn.total) }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        {{ pawn.items_count }} artículo(s)
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <p class="text-sm font-black text-emerald-600">
                                        {{ money(pawn.amount_to_liquidate) }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        Interés {{ money(pawn.interest_to_pay) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <p class="text-sm font-black text-slate-800">
                                        {{ pawn.created_at }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        Vence: {{ pawn.date_expiration || 'N/A' }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        Remate: {{ pawn.date_auction || 'N/A' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="sicem-status-pill" :class="statusClass(pawn.status)">
                                        {{ pawn.status_label }}
                                    </span>

                                    <p
                                        v-if="pawn.can_be_auctioned"
                                        class="mt-2 text-xs font-black text-orange-600"
                                    >
                                        Listo para remate
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="route('pawns.show', pawn.id)"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-[#5b55a4] hover:text-white"
                                            title="Ver detalle"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </Link>

                                        <Link
                                            v-if="pawn.status === 'active'"
                                            :href="route('pawns.payForm', pawn.id)"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-white text-emerald-600 transition hover:bg-emerald-500 hover:text-white"
                                            title="Liquidar / Abonar"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('cash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-10 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-[#5b55a4]">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('empty')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-950">
                        No hay empeños
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Registra el primer empeño de la sucursal.
                    </p>

                    <Link
                        :href="route('pawns.create')"
                        class="sicem-btn-primary mt-6 inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition"
                    >
                        Nuevo empeño
                    </Link>
                </div>

                <div
                    v-if="pawns.links?.length"
                    class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <p class="text-sm font-semibold text-slate-500">
                        Mostrando {{ pawns.from || 0 }} a {{ pawns.to || 0 }} de {{ pawns.total || 0 }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="link in pawns.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            preserve-scroll
                            preserve-state
                            class="rounded-xl border px-3 py-2 text-sm font-black transition"
                            :class="[
                                link.active
                                    ? 'border-[#5b55a4] bg-[#5b55a4] text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                                !link.url ? 'pointer-events-none opacity-40' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
.sicem-card-hero {
    background: linear-gradient(135deg, #5b55a4, #312d65) !important;
}

.sicem-btn-primary {
    background-color: #5b55a4 !important;
    color: #ffffff !important;
    border-color: #5b55a4 !important;
}

.sicem-btn-primary:hover {
    background-color: #4f4896 !important;
    color: #ffffff !important;
}

.sicem-status-pill {
    display: inline-flex;
    border-radius: 9999px;
    border-width: 1px;
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 900;
}

.sicem-status-active {
    border-color: #bbf7d0 !important;
    background-color: #f0fdf4 !important;
    color: #15803d !important;
}

.sicem-status-expired {
    border-color: #fed7aa !important;
    background-color: #fff7ed !important;
    color: #c2410c !important;
}

.sicem-status-paid {
    border-color: #bfdbfe !important;
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
}

.sicem-status-cancelled {
    border-color: #fecaca !important;
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}

.sicem-status-countersigned {
    border-color: #ddd6fe !important;
    background-color: #f5f3ff !important;
    color: #5b55a4 !important;
}

.sicem-status-auctioned {
    border-color: #fdba74 !important;
    background-color: #fff7ed !important;
    color: #ea580c !important;
}

.sicem-status-muted {
    border-color: #e2e8f0 !important;
    background-color: #f8fafc !important;
    color: #475569 !important;
}
</style>