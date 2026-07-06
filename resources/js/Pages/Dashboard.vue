<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({}),
    },
    chart: {
        type: Array,
        default: () => [],
    },
    recentTransactions: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

const activeOffice = computed(() => {
    return page.props.activeOffice
        || page.props.office
        || page.props.currentOffice
        || page.props.auth?.office
        || null
})

const officeName = computed(() => {
    return activeOffice.value?.name || activeOffice.value?.nombre || 'Matriz'
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const summaryCards = computed(() => [
    {
        label: 'Efectivo disponible',
        value: money(props.summary.cash ?? activeOffice.value?.cash ?? 0),
        icon: 'cashbox',
        color: 'cyan',
        description: 'Caja actual de la sucursal',
    },
    {
        label: 'Empeños',
        value: money(props.summary.pawns ?? 0),
        icon: 'document',
        color: 'red',
        description: 'Capital entregado',
    },
    {
        label: 'Refrendos',
        value: money(props.summary.countersigns ?? 0),
        icon: 'refresh',
        color: 'green',
        description: 'Ingresos por refrendo',
    },
    {
        label: 'Abono a interés',
        value: money(props.summary.interestPayments ?? 0),
        icon: 'thumb',
        color: 'orange',
        description: 'Pagos registrados',
    },
])

const chartRows = computed(() => {
    if (props.chart.length) {
        return props.chart
    }

    return [
        { label: 'Semana 1', income: 0, expense: 0 },
        { label: 'Semana 2', income: 0, expense: 0 },
        { label: 'Semana 3', income: 0, expense: 0 },
        { label: 'Semana 4', income: 0, expense: 0 },
        { label: 'Semana 5', income: 0, expense: 0 },
        { label: 'Semana 6', income: 0, expense: 0 },
    ]
})

const maxChartValue = computed(() => {
    const values = chartRows.value.flatMap((row) => [
        Number(row.income || 0),
        Number(row.expense || 0),
    ])

    return Math.max(...values, 1)
})

const chartPoints = (key) => {
    const width = 760
    const height = 220
    const paddingX = 30
    const paddingY = 24
    const usableWidth = width - paddingX * 2
    const usableHeight = height - paddingY * 2

    if (chartRows.value.length === 1) {
        const value = Number(chartRows.value[0][key] || 0)
        const y = height - paddingY - (value / maxChartValue.value) * usableHeight

        return `${paddingX},${y}`
    }

    return chartRows.value.map((row, index) => {
        const x = paddingX + (index / (chartRows.value.length - 1)) * usableWidth
        const value = Number(row[key] || 0)
        const y = height - paddingY - (value / maxChartValue.value) * usableHeight

        return `${x},${y}`
    }).join(' ')
}

const iconPath = (icon) => {
    const icons = {
        cashbox: 'M4 8h16v10H4V8ZM7 8V6h10v2M8 13h.01M16 13h.01M12 15a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z',
        document: 'M7 3h7l5 5v13H7V3ZM14 3v5h5M9 13h6M9 17h6',
        refresh: 'M20 12a8 8 0 0 1-14.9 4M4 12A8 8 0 0 1 18.9 8M19 4v4h-4M5 20v-4h4',
        thumb: 'M7 10v10H4V10h3ZM7 10l4-7c.5-.8 2-.5 2 1v5h5a2 2 0 0 1 2 2l-1 7a2 2 0 0 1-2 2H7V10Z',
        arrow: 'M5 12h14M13 6l6 6-6 6',
        empty: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
    }

    return icons[icon] || icons.empty
}

const cardClasses = (color) => {
    const classes = {
        cyan: {
            wrap: 'border-cyan-100 bg-white',
            icon: 'bg-cyan-500 text-white shadow-cyan-200',
            text: 'text-cyan-600',
        },
        red: {
            wrap: 'border-red-100 bg-white',
            icon: 'bg-red-500 text-white shadow-red-200',
            text: 'text-red-600',
        },
        green: {
            wrap: 'border-emerald-100 bg-white',
            icon: 'bg-emerald-500 text-white shadow-emerald-200',
            text: 'text-emerald-600',
        },
        orange: {
            wrap: 'border-orange-100 bg-white',
            icon: 'bg-orange-500 text-white shadow-orange-200',
            text: 'text-orange-600',
        },
    }

    return classes[color] || classes.cyan
}
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Panorama de operación
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        {{ officeName }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Resumen de caja, empeños y movimientos recientes de la sucursal activa.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Corte operativo
                    </p>
                    <p class="mt-1 text-sm font-black text-slate-800">
                        Últimos 30 días
                    </p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="card in summaryCards"
                    :key="card.label"
                    class="overflow-hidden rounded-[1.75rem] border shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl"
                    :class="cardClasses(card.color).wrap"
                >
                    <div class="flex items-stretch">
                        <div
                            class="flex w-24 items-center justify-center shadow-lg"
                            :class="cardClasses(card.color).icon"
                        >
                            <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none">
                                <path
                                    :d="iconPath(card.icon)"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div class="flex-1 p-5">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                {{ card.label }}
                            </p>

                            <p class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                                {{ card.value }}
                            </p>

                            <p class="mt-1 text-xs font-semibold" :class="cardClasses(card.color).text">
                                {{ card.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-7 grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
                <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-950">
                                Flujo de operación
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Comparativo de ingresos y salidas registradas.
                            </p>
                        </div>

                        <div class="flex items-center gap-4 text-xs font-bold">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-cyan-500"></span>
                                Ingresos
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-red-500"></span>
                                Salidas
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <svg
                                viewBox="0 0 760 260"
                                class="h-80 min-w-[760px] w-full"
                                role="img"
                            >
                                <defs>
                                    <linearGradient id="incomeGradient" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#06b6d4" stop-opacity="0.26" />
                                        <stop offset="100%" stop-color="#06b6d4" stop-opacity="0" />
                                    </linearGradient>

                                    <linearGradient id="expenseGradient" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.22" />
                                        <stop offset="100%" stop-color="#ef4444" stop-opacity="0" />
                                    </linearGradient>
                                </defs>

                                <g opacity="0.45">
                                    <line x1="30" y1="24" x2="730" y2="24" stroke="#e2e8f0" />
                                    <line x1="30" y1="68" x2="730" y2="68" stroke="#e2e8f0" />
                                    <line x1="30" y1="112" x2="730" y2="112" stroke="#e2e8f0" />
                                    <line x1="30" y1="156" x2="730" y2="156" stroke="#e2e8f0" />
                                    <line x1="30" y1="200" x2="730" y2="200" stroke="#e2e8f0" />
                                </g>

                                <polyline
                                    :points="`30,220 ${chartPoints('income')} 730,220`"
                                    fill="url(#incomeGradient)"
                                    stroke="none"
                                />

                                <polyline
                                    :points="`30,220 ${chartPoints('expense')} 730,220`"
                                    fill="url(#expenseGradient)"
                                    stroke="none"
                                />

                                <polyline
                                    :points="chartPoints('income')"
                                    fill="none"
                                    stroke="#06b6d4"
                                    stroke-width="5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <polyline
                                    :points="chartPoints('expense')"
                                    fill="none"
                                    stroke="#ef4444"
                                    stroke-width="5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />

                                <g
                                    v-for="(row, index) in chartRows"
                                    :key="row.label"
                                >
                                    <text
                                        :x="30 + (index / Math.max(chartRows.length - 1, 1)) * 700"
                                        y="250"
                                        text-anchor="middle"
                                        class="fill-slate-500 text-[11px] font-bold"
                                    >
                                        {{ row.label }}
                                    </text>
                                </g>
                            </svg>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h2 class="text-xl font-black text-slate-950">
                            Movimientos recientes
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Últimas operaciones capturadas.
                        </p>
                    </div>

                    <div class="p-5">
                        <div v-if="recentTransactions.length" class="space-y-3">
                            <div
                                v-for="transaction in recentTransactions"
                                :key="transaction.id"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-black text-slate-950">
                                            {{ transaction.type || 'Movimiento' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ transaction.comments || 'Sin comentarios' }}
                                        </p>
                                    </div>

                                    <p
                                        class="text-sm font-black"
                                        :class="Number(transaction.amount || 0) >= 0 ? 'text-emerald-600' : 'text-red-600'"
                                    >
                                        {{ money(transaction.amount) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
                        >
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-[#5b55a4] shadow-sm">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('empty')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <h3 class="mt-4 text-lg font-black text-slate-950">
                                Sin movimientos recientes
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Cuando registres empeños, pagos, ingresos o gastos aparecerán aquí.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>