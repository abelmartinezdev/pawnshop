<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    preview: {
        type: Object,
        required: true,
    },
})

const showConfirmModal = ref(false)

const form = useForm({
    counted_cash: props.preview?.summary?.expected_cash ?? 0,
    comments: '',
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const expectedCash = computed(() => Number(props.preview?.summary?.expected_cash || 0))
const countedCash = computed(() => Number(form.counted_cash || 0))
const difference = computed(() => Number((countedCash.value - expectedCash.value).toFixed(2)))

const canClose = computed(() => props.preview?.can_close !== false)
const closeMessage = computed(() => props.preview?.close_message || 'No hay movimientos pendientes por cerrar.')

const recentClosures = computed(() => props.preview?.recentClosures || [])
const transactions = computed(() => props.preview?.transactions || [])
const breakdown = computed(() => props.preview?.summary?.breakdown || [])

const differenceLabel = computed(() => {
    if (difference.value === 0) return 'Caja exacta'
    if (difference.value > 0) return 'Sobrante de caja'

    return 'Faltante de caja'
})

const differenceTextClass = computed(() => {
    if (difference.value === 0) return 'text-emerald-600'
    if (difference.value > 0) return 'text-blue-600'

    return 'text-red-600'
})

const differencePanelClass = computed(() => {
    if (difference.value === 0) return 'border-emerald-200 bg-emerald-50'
    if (difference.value > 0) return 'border-blue-200 bg-blue-50'

    return 'border-red-200 bg-red-50'
})

const cashInPercentage = computed(() => {
    const cashIn = Number(props.preview?.summary?.cash_in || 0)
    const cashOut = Number(props.preview?.summary?.cash_out || 0)
    const total = cashIn + cashOut

    if (total <= 0) return 0

    return Math.round((cashIn / total) * 100)
})

const cashOutPercentage = computed(() => {
    const cashIn = Number(props.preview?.summary?.cash_in || 0)
    const cashOut = Number(props.preview?.summary?.cash_out || 0)
    const total = cashIn + cashOut

    if (total <= 0) return 0

    return Math.round((cashOut / total) * 100)
})

const openConfirmModal = () => {
    if (!canClose.value) {
        return
    }

    showConfirmModal.value = true
}

const submit = () => {
    form.post(route('closures.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmModal.value = false
        },
    })
}

const absMoney = (value) => money(Math.abs(Number(value || 0)))

const movementAmountClass = (amount) => {
    return Number(amount || 0) >= 0 ? 'text-emerald-600' : 'text-red-600'
}

const movementIconClass = (amount) => {
    return Number(amount || 0) >= 0
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-red-100 text-red-700'
}

const movementSign = (amount) => {
    return Number(amount || 0) >= 0 ? '+' : '-'
}

const differenceClass = (value) => {
    const amount = Number(value || 0)

    if (amount === 0) return 'text-emerald-600'
    if (amount > 0) return 'text-blue-600'

    return 'text-red-600'
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        income: 'M12 3v12M7 10l5 5 5-5M4 21h16M5 17h14',
        expense: 'M12 21V9M7 14l5-5 5 5M4 3h16M5 7h14',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        building: 'M4 21V6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V21M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M9 21v-5h6v5',
        calculator: 'M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2ZM8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01M8 19h.01M12 19h.01M16 19h.01',
        list: 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01',
        warning: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        check: 'M20 6 9 17l-5-5',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        print: 'M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 9V3h10v6M7 14h10v7H7v-7ZM17 12h.01',
    }

    return icons[icon] || icons.cash
}
</script>

<template>
    <Head title="Cerrar caja" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
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
                        Cerrar caja
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Revisa el efectivo actual, captura el efectivo físico contado y registra el cierre de caja.
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Sucursal
                    </p>
                    <p class="mt-1 text-lg font-black text-slate-950">
                        {{ preview.office?.name || 'Sucursal activa' }}
                    </p>
                    <p v-if="preview.office?.company?.name" class="mt-1 text-xs font-bold text-slate-400">
                        {{ preview.office.company.name }}
                    </p>
                </div>
            </div>

            <div
                v-if="!canClose"
                class="mb-6 rounded-[1.75rem] border border-amber-200 bg-amber-50 p-5 text-amber-800 shadow-sm"
            >
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                        <path
                            :d="iconPath('warning')"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <div>
                        <p class="font-black">
                            No hay cierre pendiente
                        </p>
                        <p class="mt-1 text-sm leading-6">
                            {{ closeMessage }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mb-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Efectivo en caja
                    </p>
                    <p class="mt-2 text-2xl font-black text-slate-950">
                        {{ money(preview.summary.expected_cash) }}
                    </p>
                    <p class="mt-1 text-xs font-semibold text-slate-400">
                        Caja actual abierta
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Último corte
                    </p>
                    <p class="mt-2 text-xl font-black text-slate-950">
                        {{ preview.lastClosure?.period_end_at || preview.lastClosure?.closed_at || 'Sin cortes' }}
                    </p>
                    <p v-if="preview.lastClosure" class="mt-1 text-xs font-semibold text-slate-400">
                        Corte #{{ preview.lastClosure.id }}
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Entradas en efectivo
                    </p>
                    <p class="mt-2 text-2xl font-black text-emerald-600">
                        {{ money(preview.summary.cash_in) }}
                    </p>
                    <p class="mt-1 text-xs font-semibold text-slate-400">
                        Desde el último corte
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-red-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Salidas en efectivo
                    </p>
                    <p class="mt-2 text-2xl font-black text-red-600">
                        {{ money(preview.summary.cash_out) }}
                    </p>
                    <p class="mt-1 text-xs font-semibold text-slate-400">
                        Desde el último corte
                    </p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="bg-gradient-to-br from-[#5b55a4] to-[#312d65] px-6 py-7 text-white">
                            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-white/60">
                                        Efectivo esperado
                                    </p>

                                    <p class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">
                                        {{ money(preview.summary.expected_cash) }}
                                    </p>

                                    <p class="mt-3 max-w-md text-sm leading-6 text-white/70">
                                        Este es el efectivo que el sistema espera encontrar físicamente en caja.
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-white/10 px-4 py-3 text-right backdrop-blur">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                        Periodo
                                    </p>
                                    <p class="mt-1 text-sm font-black text-white">
                                        {{ preview.period.start }}
                                    </p>
                                    <p class="text-xs text-white/60">
                                        a {{ preview.period.end }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <div class="mb-4 flex items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-black text-slate-950">
                                        Flujo de efectivo
                                    </h2>
                                    <p class="text-sm text-slate-500">
                                        Relación entre entradas y salidas del periodo.
                                    </p>
                                </div>

                                <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-[#5b55a4]">
                                    {{ preview.summary.total_transactions }} movimientos
                                </span>
                            </div>

                            <div class="overflow-hidden rounded-full bg-slate-100">
                                <div class="flex h-4">
                                    <div class="bg-emerald-500" :style="{ width: `${cashInPercentage}%` }" />
                                    <div class="bg-red-500" :style="{ width: `${cashOutPercentage}%` }" />
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl bg-emerald-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-black text-emerald-700">
                                            Entradas
                                        </p>
                                        <p class="text-xs font-black text-emerald-500">
                                            {{ cashInPercentage }}%
                                        </p>
                                    </div>
                                    <p class="mt-1 text-xl font-black text-emerald-600">
                                        {{ money(preview.summary.cash_in) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-red-50 p-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-black text-red-700">
                                            Salidas
                                        </p>
                                        <p class="text-xs font-black text-red-500">
                                            {{ cashOutPercentage }}%
                                        </p>
                                    </div>
                                    <p class="mt-1 text-xl font-black text-red-600">
                                        {{ money(preview.summary.cash_out) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Desglose por tipo
                        </h2>

                        <div class="mt-5 space-y-3">
                            <div
                                v-for="item in breakdown"
                                :key="item.type"
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                            >
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-black text-slate-800">
                                            {{ item.label }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-slate-400">
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

                            <div
                                v-if="!breakdown.length"
                                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center"
                            >
                                <p class="text-sm font-bold text-slate-500">
                                    No hay movimientos en efectivo para este periodo.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Cierres recientes
                            </h2>
                            <p class="text-sm text-slate-500">
                                Historial rápido de cortes de caja.
                            </p>
                        </div>

                        <div v-if="recentClosures.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Fecha
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Contado
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Diferencia
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="closure in recentClosures" :key="closure.id">
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-800">
                                                {{ closure.period_end_at || closure.closed_at }}
                                            </p>
                                            <p class="text-xs font-semibold text-slate-400">
                                                Corte #{{ closure.id }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black text-slate-700">
                                            {{ money(closure.counted_cash) }}
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black" :class="differenceClass(closure.difference)">
                                            {{ money(closure.difference) }}
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <Link
                                                :href="route('closures.show', closure.id)"
                                                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-950 hover:text-white"
                                            >
                                                Ver
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="p-8 text-center text-sm font-semibold text-slate-500">
                            Aún no hay cierres registrados.
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('calculator')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-xl font-black text-slate-950">
                                    Captura del efectivo
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Cuenta el efectivo físico y registra el cierre.
                                </p>
                            </div>
                        </div>

                        <form class="space-y-5" @submit.prevent="openConfirmModal">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Efectivo contado
                                </label>

                                <div class="relative">
                                    <span class="pointer-events-none absolute left-5 top-1/2 -translate-y-1/2 text-lg font-black text-slate-400">
                                        $
                                    </span>

                                    <input
                                        v-model="form.counted_cash"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="h-16 w-full rounded-[1.5rem] border border-slate-200 bg-white pl-10 pr-4 text-2xl font-black text-slate-950 outline-none transition placeholder:text-slate-300 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100 disabled:bg-slate-100 disabled:text-slate-400"
                                        placeholder="0.00"
                                        :disabled="!canClose"
                                    />
                                </div>

                                <p v-if="form.errors.counted_cash" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.counted_cash }}
                                </p>
                            </div>

                            <div class="rounded-[1.75rem] border p-5" :class="differencePanelClass">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <span class="inline-flex rounded-full border border-white/70 bg-white/70 px-3 py-1 text-xs font-black" :class="differenceTextClass">
                                            {{ differenceLabel }}
                                        </span>

                                        <p class="mt-3 text-sm font-semibold text-slate-500">
                                            Diferencia contra efectivo esperado
                                        </p>
                                    </div>

                                    <p class="text-4xl font-black tracking-tight" :class="differenceTextClass">
                                        {{ money(difference) }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Comentarios
                                </label>

                                <textarea
                                    v-model="form.comments"
                                    rows="5"
                                    class="w-full rounded-[1.5rem] border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100 disabled:bg-slate-100 disabled:text-slate-400"
                                    placeholder="Observaciones del cierre..."
                                    :disabled="!canClose"
                                />

                                <p v-if="form.errors.comments" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.comments }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                class="w-full rounded-2xl bg-[#5b55a4] px-6 py-4 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5 hover:bg-[#4f4896] disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="form.processing || !canClose"
                            >
                                {{ form.processing ? 'Guardando...' : 'Hacer el cierre de caja' }}
                            </button>
                        </form>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Movimientos incluidos
                            </h2>
                            <p class="text-sm text-slate-500">
                                Transacciones en efectivo pendientes de cierre.
                            </p>
                        </div>

                        <div v-if="transactions.length" class="max-h-[560px] overflow-y-auto">
                            <div
                                v-for="transaction in transactions"
                                :key="transaction.id"
                                class="border-b border-slate-100 px-6 py-4 last:border-b-0"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex min-w-0 gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl"
                                            :class="movementIconClass(transaction.amount)"
                                        >
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                <path
                                                    :d="Number(transaction.amount || 0) >= 0 ? iconPath('income') : iconPath('expense')"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                />
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-black text-slate-800">
                                                {{ transaction.type_label }}
                                                <span class="font-semibold text-slate-400">
                                                    #{{ transaction.id }}
                                                </span>
                                            </p>

                                            <p class="mt-1 truncate text-xs font-semibold text-slate-500">
                                                {{ transaction.comments || 'Sin comentarios' }}
                                            </p>

                                            <p class="mt-1 text-xs font-bold text-slate-400">
                                                {{ transaction.created_at }}
                                                <span v-if="transaction.user?.name">
                                                    · {{ transaction.user.name }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <p class="shrink-0 text-sm font-black" :class="movementAmountClass(transaction.amount)">
                                        {{ movementSign(transaction.amount) }}{{ absMoney(transaction.amount) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="p-10 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-[#5b55a4]">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('receipt')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <h3 class="mt-5 text-xl font-black text-slate-950">
                                Sin movimientos nuevos
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                No hay transacciones en efectivo desde el último cierre.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div
            v-if="showConfirmModal"
            class="fixed inset-0 z-[90] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="bg-[#5b55a4] px-6 py-5 text-white">
                    <h2 class="text-xl font-black">
                        Confirmar cierre de caja
                    </h2>
                    <p class="mt-1 text-sm text-white/70">
                        Revisa los importes antes de guardar el cierre.
                    </p>
                </div>

                <div class="p-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Efectivo esperado
                            </p>
                            <p class="mt-1 text-xl font-black text-slate-950">
                                {{ money(expectedCash) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Efectivo contado
                            </p>
                            <p class="mt-1 text-xl font-black text-slate-950">
                                {{ money(countedCash) }}
                            </p>
                        </div>

                        <div class="rounded-2xl border p-4 sm:col-span-2" :class="differencePanelClass">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">
                                Resultado
                            </p>
                            <div class="mt-2 flex items-center justify-between gap-4">
                                <p class="text-sm font-black text-slate-700">
                                    {{ differenceLabel }}
                                </p>
                                <p class="text-2xl font-black" :class="differenceTextClass">
                                    {{ money(difference) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="difference !== 0 && !form.comments"
                        class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800"
                    >
                        Hay una diferencia en caja. Es recomendable agregar un comentario antes de guardar.
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            @click="showConfirmModal = false"
                        >
                            Revisar de nuevo
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#5b55a4] px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:bg-[#4f4896] disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="form.processing"
                            @click="submit"
                        >
                            <svg v-if="!form.processing" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path
                                    :d="iconPath('check')"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            {{ form.processing ? 'Guardando...' : 'Confirmar cierre' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>