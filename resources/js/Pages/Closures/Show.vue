<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    closure: {
        type: Object,
        required: true,
    },
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const breakdown = computed(() => {
    return props.closure.data?.breakdown || props.closure.data?.summary?.breakdown || []
})

const movementIds = computed(() => {
    return props.closure.data?.transactions || []
})

const differenceLabel = computed(() => {
    const amount = Number(props.closure.difference || 0)

    if (amount === 0) return 'Caja exacta'
    if (amount > 0) return 'Sobrante de caja'

    return 'Faltante de caja'
})

const differenceTextClass = computed(() => {
    const amount = Number(props.closure.difference || 0)

    if (amount === 0) return 'text-emerald-600'
    if (amount > 0) return 'text-blue-600'

    return 'text-red-600'
})

const differenceBadgeClass = computed(() => {
    const amount = Number(props.closure.difference || 0)

    if (amount === 0) return 'border-emerald-200 bg-emerald-50 text-emerald-700'
    if (amount > 0) return 'border-blue-200 bg-blue-50 text-blue-700'

    return 'border-red-200 bg-red-50 text-red-700'
})

const printPage = () => {
    window.print()
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        building: 'M4 21V6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V21M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M9 21v-5h6v5',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        income: 'M12 3v12M7 10l5 5 5-5M4 21h16M5 17h14',
        expense: 'M12 21V9M7 14l5-5 5 5M4 3h16M5 7h14',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        print: 'M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 9V3h10v6M7 14h10v7H7v-7ZM17 12h.01',
        plus: 'M12 5v14M5 12h14',
    }

    return icons[icon] || icons.cash
}
</script>

<template>
    <Head :title="`Corte #${closure.id}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8 print:px-0 print:py-0">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between print:hidden">
                <div>
                    <Link
                        :href="route('closures.index')"
                        class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path
                                :d="iconPath('arrowLeft')"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Volver
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Caja
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Corte #{{ closure.id }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Detalle del cierre de caja registrado.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                        @click="printPage"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path
                                :d="iconPath('print')"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Imprimir
                    </button>

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
            </div>

            <div class="mx-auto max-w-7xl print:max-w-none">
                <div class="hidden print:block">
                    <div class="mb-6 border-b border-slate-300 pb-4">
                        <h1 class="text-2xl font-black text-slate-950">
                            SICEM · Corte de caja #{{ closure.id }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ closure.office?.name || 'Sucursal' }} · {{ closure.closed_at }}
                        </p>
                    </div>
                </div>

                <div class="mb-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm print:rounded-xl print:shadow-none">
                        <div class="bg-gradient-to-br from-[#5b55a4] to-[#312d65] p-5 text-white print:bg-slate-900">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                Caja contada
                            </p>
                            <p class="mt-2 text-3xl font-black">
                                {{ money(closure.counted_cash) }}
                            </p>
                        </div>

                        <div class="p-5">
                            <span
                                class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                :class="differenceBadgeClass"
                            >
                                {{ differenceLabel }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm print:rounded-xl print:shadow-none">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                            Efectivo esperado
                        </p>
                        <p class="mt-2 text-2xl font-black text-slate-950">
                            {{ money(closure.expected_cash) }}
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">
                            Según movimientos del periodo
                        </p>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm print:rounded-xl print:shadow-none">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                            Diferencia
                        </p>
                        <p class="mt-2 text-2xl font-black" :class="differenceTextClass">
                            {{ money(closure.difference) }}
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">
                            Contado menos esperado
                        </p>
                    </div>

                    <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm print:rounded-xl print:shadow-none">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                            Movimientos
                        </p>
                        <p class="mt-2 text-2xl font-black text-[#5b55a4]">
                            {{ closure.total_transactions }}
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-500">
                            Transacciones incluidas
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
                    <section class="space-y-6">
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm print:rounded-xl print:shadow-none">
                            <h2 class="text-lg font-black text-slate-950">
                                Información del cierre
                            </h2>

                            <div class="mt-5 space-y-4">
                                <div class="flex gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 print:hidden">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                            <path
                                                :d="iconPath('calendar')"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                            Periodo cerrado
                                        </p>
                                        <p class="text-sm font-black text-slate-800">
                                            {{ closure.period_start_at }}
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            a {{ closure.period_end_at }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 print:hidden">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                            <path
                                                :d="iconPath('user')"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                            Cerró
                                        </p>
                                        <p class="text-sm font-black text-slate-800">
                                            {{ closure.user?.name || 'No especificado' }}
                                        </p>
                                        <p v-if="closure.user?.email" class="text-xs text-slate-500">
                                            {{ closure.user.email }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 print:hidden">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                            <path
                                                :d="iconPath('building')"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                            Sucursal
                                        </p>
                                        <p class="text-sm font-black text-slate-800">
                                            {{ closure.office?.name || 'No especificada' }}
                                        </p>
                                        <p v-if="closure.office?.company" class="text-xs text-slate-500">
                                            {{ closure.office.company }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm print:rounded-xl print:shadow-none">
                            <h2 class="text-lg font-black text-slate-950">
                                Comentarios
                            </h2>

                            <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-700 print:rounded-lg">
                                {{ closure.comments || 'Sin comentarios registrados.' }}
                            </p>
                        </div>
                    </section>

                    <section class="space-y-6">
                        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm print:rounded-xl print:shadow-none">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <h2 class="text-lg font-black text-slate-950">
                                    Resumen financiero
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Entradas, salidas y resultado del corte.
                                </p>
                            </div>

                            <div class="grid gap-4 p-6 md:grid-cols-2">
                                <div class="rounded-2xl bg-slate-50 p-4 print:rounded-lg">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Caja inicial
                                    </p>
                                    <p class="mt-1 text-xl font-black text-slate-950">
                                        {{ money(closure.opening_cash) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-emerald-50 p-4 print:rounded-lg">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                        Entradas
                                    </p>
                                    <p class="mt-1 text-xl font-black text-emerald-600">
                                        {{ money(closure.cash_in) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-red-50 p-4 print:rounded-lg">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-red-400">
                                        Salidas
                                    </p>
                                    <p class="mt-1 text-xl font-black text-red-600">
                                        {{ money(closure.cash_out) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 print:rounded-lg">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Esperado
                                    </p>
                                    <p class="mt-1 text-xl font-black text-slate-950">
                                        {{ money(closure.expected_cash) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm print:rounded-xl print:shadow-none">
                            <div class="border-b border-slate-100 px-6 py-5">
                                <h2 class="text-lg font-black text-slate-950">
                                    Desglose por tipo
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Movimientos agrupados incluidos en el cierre.
                                </p>
                            </div>

                            <div v-if="breakdown.length" class="divide-y divide-slate-100">
                                <div
                                    v-for="item in breakdown"
                                    :key="item.type"
                                    class="px-6 py-4"
                                >
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-black text-slate-800">
                                                {{ item.label }}
                                            </p>
                                            <p class="text-xs font-semibold text-slate-400">
                                                {{ item.count }} movimientos
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p
                                                class="text-sm font-black"
                                                :class="Number(item.net || 0) >= 0 ? 'text-emerald-600' : 'text-red-600'"
                                            >
                                                {{ money(item.net) }}
                                            </p>
                                            <p class="mt-1 text-[11px] font-bold text-slate-400">
                                                +{{ money(item.cash_in) }} / -{{ money(item.cash_out) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="p-8 text-center text-sm font-semibold text-slate-500">
                                No hubo movimientos en efectivo en este periodo.
                            </div>
                        </div>

                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm print:rounded-xl print:shadow-none">
                            <h2 class="text-lg font-black text-slate-950">
                                Transacciones incluidas
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Este corte incluye
                                <strong class="font-black text-slate-700">
                                    {{ movementIds.length }}
                                </strong>
                                transacciones registradas en el periodo.
                            </p>

                            <div v-if="movementIds.length" class="mt-4 flex flex-wrap gap-2">
                                <span
                                    v-for="id in movementIds"
                                    :key="id"
                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600"
                                >
                                    #{{ id }}
                                </span>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>