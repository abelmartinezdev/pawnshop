<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

const props = defineProps({
    closures: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

const page = usePage()

const form = reactive({
    search: props.filters.search || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    per_page: props.filters.per_page || 15,
})

const activeOffice = computed(() => page.props.activeOffice || null)

const records = computed(() => props.closures?.data || [])
const lastClosure = computed(() => records.value[0] || null)

const totalExpected = computed(() => {
    return records.value.reduce((total, closure) => total + Number(closure.expected_cash || 0), 0)
})

const totalCounted = computed(() => {
    return records.value.reduce((total, closure) => total + Number(closure.counted_cash || 0), 0)
})

const totalDifference = computed(() => {
    return records.value.reduce((total, closure) => total + Number(closure.difference || 0), 0)
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const cleanFilters = () => {
    return Object.fromEntries(
        Object.entries(form).filter(([_, value]) => value !== '' && value !== null && value !== undefined),
    )
}

const submitFilters = () => {
    router.get(route('closures.index'), cleanFilters(), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
}

const clearFilters = () => {
    form.search = ''
    form.date_from = ''
    form.date_to = ''
    form.per_page = 15

    router.get(route('closures.index'), {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
}

const differenceLabel = (value) => {
    const amount = Number(value || 0)

    if (amount === 0) return 'Exacto'
    if (amount > 0) return 'Sobrante'

    return 'Faltante'
}

const differenceTextClass = (value) => {
    const amount = Number(value || 0)

    if (amount === 0) return 'text-emerald-600'
    if (amount > 0) return 'text-blue-600'

    return 'text-red-600'
}

const differenceBadgeClass = (value) => {
    const amount = Number(value || 0)

    if (amount === 0) return 'border-emerald-200 bg-emerald-50 text-emerald-700'
    if (amount > 0) return 'border-blue-200 bg-blue-50 text-blue-700'

    return 'border-red-200 bg-red-50 text-red-700'
}

const iconPath = (icon) => {
    const icons = {
        plus: 'M12 5v14M5 12h14',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        search: 'M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z',
        filter: 'M4 5h16M7 12h10M10 19h4',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        chart: 'M4 19V5M4 19h16M8 16v-5M12 16V8M16 16v-8',
    }

    return icons[icon] || icons.cash
}
</script>

<template>
    <Head title="Cortes de caja" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Caja
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Cortes de caja
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Consulta el historial de cierres, diferencias y periodos cerrados de
                        <strong class="font-black text-slate-700">
                            {{ activeOffice?.name || 'la sucursal activa' }}
                        </strong>.
                    </p>
                </div>

                <Link
                    :href="route('closures.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#5b55a4] px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5 hover:bg-[#4f4896]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path
                            :d="iconPath('plus')"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                    Cerrar caja
                </Link>
            </div>

            <div class="mb-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="bg-gradient-to-br from-[#5b55a4] to-[#312d65] p-5 text-white">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                    Último corte
                                </p>
                                <p class="mt-2 text-xl font-black">
                                    {{ lastClosure?.closed_at || 'Sin cortes' }}
                                </p>
                            </div>

                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('cash')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                            Corte registrado
                        </p>
                        <p class="mt-1 text-lg font-black text-slate-950">
                            {{ lastClosure ? `#${lastClosure.id}` : 'Pendiente' }}
                        </p>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Total cortes
                            </p>
                            <p class="mt-2 text-3xl font-black text-slate-950">
                                {{ closures.total || 0 }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path
                                    :d="iconPath('receipt')"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-2 text-sm font-semibold text-slate-500">
                        Historial de la sucursal
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Contado mostrado
                            </p>
                            <p class="mt-2 text-2xl font-black text-slate-950">
                                {{ money(totalCounted) }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path
                                    :d="iconPath('cash')"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-2 text-sm font-semibold text-slate-500">
                        Según la página actual
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Diferencia mostrada
                            </p>
                            <p class="mt-2 text-2xl font-black" :class="differenceTextClass(totalDifference)">
                                {{ money(totalDifference) }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path
                                    :d="iconPath('chart')"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                    </div>

                    <p class="mt-2 text-sm font-semibold text-slate-500">
                        Sobrante o faltante acumulado en vista
                    </p>
                </div>
            </div>

            <section class="mb-6 rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <form class="p-5 sm:p-6" @submit.prevent="submitFilters">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path
                                    :d="iconPath('filter')"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-black text-slate-950">
                                Filtros
                            </h2>
                            <p class="text-sm text-slate-500">
                                Busca por ID, usuario, comentario o importes del cierre.
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
                                    <path
                                        :d="iconPath('search')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                </svg>

                                <input
                                    v-model="form.search"
                                    type="search"
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                                    placeholder="ID, usuario, comentario..."
                                />
                            </div>
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

                        <div class="flex items-end gap-3">
                            <button
                                type="button"
                                class="h-12 rounded-2xl border border-slate-200 bg-white px-5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                                @click="clearFilters"
                            >
                                Limpiar
                            </button>

                            <button
                                type="submit"
                                class="h-12 rounded-2xl bg-[#5b55a4] px-5 text-sm font-black text-white transition hover:bg-[#4f4896]"
                            >
                                Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-950">
                                Historial de cierres
                            </h2>
                            <p class="text-sm text-slate-500">
                                Cierres de caja realizados por periodo.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="records.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Corte
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Periodo
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Esperado
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Contado
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Diferencia
                                </th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">
                                    Estado
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr
                                v-for="closure in records"
                                :key="closure.id"
                                class="transition hover:bg-slate-50/80"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-white">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                <path
                                                    :d="iconPath('cash')"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="text-sm font-black text-slate-950">
                                                #{{ closure.id }}
                                            </p>
                                            <p class="text-xs font-semibold text-slate-500">
                                                {{ closure.closed_at }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm font-black text-slate-700">
                                        {{ closure.period_start_at }}
                                    </p>
                                    <p class="text-xs font-semibold text-slate-400">
                                        a {{ closure.period_end_at }}
                                    </p>
                                    <p v-if="closure.user" class="mt-1 text-xs font-bold text-slate-400">
                                        Cerró: {{ closure.user.name }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <p class="text-sm font-black text-slate-700">
                                        {{ money(closure.expected_cash) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <p class="text-sm font-black text-slate-700">
                                        {{ money(closure.counted_cash) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <p class="text-sm font-black" :class="differenceTextClass(closure.difference)">
                                        {{ money(closure.difference) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                        :class="differenceBadgeClass(closure.difference)"
                                    >
                                        {{ differenceLabel(closure.difference) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="route('closures.show', closure.id)"
                                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-950 hover:text-white"
                                    >
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <path
                                                :d="iconPath('eye')"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-10 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-[#5b55a4]">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                            <path
                                :d="iconPath('cash')"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-950">
                        No hay cortes registrados
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Cuando cierres caja, el historial aparecerá en esta sección.
                    </p>

                    <Link
                        :href="route('closures.create')"
                        class="mt-6 inline-flex items-center justify-center rounded-2xl bg-[#5b55a4] px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:bg-[#4f4896]"
                    >
                        Hacer cierre de caja
                    </Link>
                </div>

                <div
                    v-if="closures.links?.length"
                    class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <p class="text-sm font-semibold text-slate-500">
                        Mostrando {{ closures.from || 0 }} a {{ closures.to || 0 }} de {{ closures.total || 0 }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="link in closures.links"
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