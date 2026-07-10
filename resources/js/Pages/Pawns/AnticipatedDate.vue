<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    pawn: {
        type: Object,
        required: true,
    },
    date: {
        type: String,
        required: true,
    },
    total: {
        type: [Number, String],
        required: true,
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const selectedDate = ref(props.date)

const printPage = () => {
    if (typeof window === 'undefined') {
        return
    }

    if (!props.urls.print_ticket) {
        window.print()
        return
    }

    const url = new URL(props.urls.print_ticket, window.location.origin)

    url.searchParams.set('date', selectedDate.value)

    window.open(url.toString(), '_blank')
}

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const percent = (value) => {
    return `${Number(value || 0).toFixed(3)}%`
}

const normalizeRate = (value) => {
    const rate = Number(value || 0)

    return rate > 1 ? rate / 100 : rate
}

const parseLocalDate = (value) => {
    if (!value) {
        return null
    }

    return new Date(`${value}T12:00:00`)
}

const daysBetween = (start, end) => {
    const startDate = parseLocalDate(start)
    const endDate = parseLocalDate(end)

    if (!startDate || !endDate) {
        return 0
    }

    const ms = endDate.getTime() - startDate.getTime()

    return Math.max(0, Math.round(ms / 86400000))
}

const chargeableDays = computed(() => {
    return Math.max(
        Number(props.pawn.minimum_days || 5),
        daysBetween(props.pawn.created_at_iso, selectedDate.value)
    )
})

const dailyInterestWithoutIva = computed(() => {
    return Number(props.pawn.total || 0) * (Number(props.pawn.daily_interest_rate || 0) / 100)
})

const interestWithoutIva = computed(() => {
    return dailyInterestWithoutIva.value * chargeableDays.value
})

const ivaAmount = computed(() => {
    return interestWithoutIva.value * normalizeRate(props.pawn.iva_rate)
})

const interestWithIva = computed(() => {
    return interestWithoutIva.value + ivaAmount.value
})

const inapamDiscount = computed(() => {
    return interestWithIva.value * normalizeRate(props.pawn.inapam_rate)
})

const interestToPay = computed(() => {
    const amount = interestWithIva.value - inapamDiscount.value - Number(props.pawn.paid_amount || 0)

    return Math.max(amount, 0)
})

const totalToLiquidate = computed(() => {
    return Number(props.pawn.total || 0) + interestToPay.value
})

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        print: 'M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 9V3h10v6M7 14h10v7H7v-7ZM17 12h.01',
        gem: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
    }

    return icons[icon] || icons.gem
}
</script>

<template>
    <Head :title="`Fecha anticipada ${pawn.folio}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="urls.show || route('pawns.show', pawn.id)"
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
                        Regresar al empeño
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Calculadora de liquidación
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Fecha anticipada · Folio {{ pawn.folio }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Calcula cuánto tendría que pagar el cliente si liquida en una fecha específica.
                    </p>
                </div>

                <button
                    type="button"
                    class="sicem-btn-primary inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-100 transition"
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
                    Imprimir cálculo
                </button>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.42fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="sicem-hero p-6 text-white">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-white/60">
                                        Total estimado a liquidar
                                    </p>

                                    <p class="mt-2 text-5xl font-black tracking-tight text-white">
                                        {{ money(totalToLiquidate) }}
                                    </p>

                                    <p class="mt-3 text-sm text-white/70">
                                        Fecha seleccionada:
                                        <strong class="text-white">{{ selectedDate }}</strong>
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                        Días cobrados
                                    </p>
                                    <p class="mt-1 text-3xl font-black text-white">
                                        {{ chargeableDays }}
                                    </p>
                                    <p class="mt-1 text-xs text-white/60">
                                        Mínimo aplicado: {{ pawn.minimum_days }} días
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Fecha anticipada
                                </label>

                                <div class="relative">
                                    <input
                                        v-model="selectedDate"
                                        type="date"
                                        class="sicem-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-[#5b55a4] focus:outline-none focus:ring-4 focus:ring-violet-100"
                                    >

                                    <svg
                                        class="pointer-events-none absolute right-4 top-3.5 h-5 w-5 text-slate-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                    >
                                        <path
                                            :d="iconPath('calendar')"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <p class="mt-2 text-xs font-semibold text-slate-400">
                                    La fecha de inicio del empeño es {{ pawn.created_at }}.
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-5">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Cliente
                                </p>

                                <div class="mt-3 flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
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
                                        <p class="text-sm font-black text-slate-950">
                                            {{ pawn.customer?.name || 'Sin cliente' }}
                                        </p>
                                        <p class="text-xs font-semibold text-slate-400">
                                            {{ pawn.customer?.phone || pawn.customer?.email || 'Sin contacto' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Desglose del cálculo
                            </h2>
                            <p class="text-sm text-slate-500">
                                Importes estimados según la fecha seleccionada.
                            </p>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Principal
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ money(pawn.total) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Interés sin IVA
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ money(interestWithoutIva) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    IVA
                                </p>
                                <p class="mt-1 text-xl font-black text-[#5b55a4]">
                                    {{ money(ivaAmount) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                    Interés con IVA
                                </p>
                                <p class="mt-1 text-xl font-black text-blue-700">
                                    {{ money(interestWithIva) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-orange-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-500">
                                    Descuento INAPAM
                                </p>
                                <p class="mt-1 text-xl font-black text-orange-600">
                                    {{ money(inapamDiscount) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-rose-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-500">
                                    Abonos previos
                                </p>
                                <p class="mt-1 text-xl font-black text-rose-600">
                                    {{ money(pawn.paid_amount) }}
                                </p>
                            </div>
                        </div>

                        <div class="border-t border-slate-100 p-6">
                            <div class="rounded-[1.5rem] border border-[#5b55a4]/20 bg-violet-50 p-5">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                            Total final estimado
                                        </p>
                                        <p class="mt-1 text-sm font-semibold text-slate-500">
                                            Principal + interés calculado - descuentos/abonos.
                                        </p>
                                    </div>

                                    <p class="text-3xl font-black text-[#5b55a4]">
                                        {{ money(totalToLiquidate) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Prendas del empeño
                            </h2>
                            <p class="text-sm text-slate-500">
                                Artículos incluidos en este folio.
                            </p>
                        </div>

                        <div v-if="pawn.items?.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-[#172331] text-white">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase">
                                            Artículo
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase">
                                            Características
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase">
                                            Cantidad
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase">
                                            Préstamo
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="item in pawn.items" :key="item.id">
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-950">
                                                {{ item.product?.description || 'Producto sin catálogo' }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                                {{ item.product?.code || 'N/A' }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-sm leading-6 text-slate-700">
                                            {{ item.description }}
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black text-slate-700">
                                            {{ item.quantity }} {{ item.product?.unit || '' }}
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black text-slate-950">
                                            {{ money(item.value) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="p-10 text-center text-sm font-semibold text-slate-500">
                            No hay prendas registradas.
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Datos del empeño
                        </h2>

                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Folio
                                </p>
                                <p class="mt-1 text-lg font-black text-slate-950">
                                    {{ pawn.folio }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Sucursal
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ pawn.office?.name || 'N/A' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Vencimiento
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ pawn.date_expiration || 'N/A' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Remate
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ pawn.date_auction || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Tasas aplicadas
                        </h2>

                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    Préstamo
                                </p>
                                <p class="mt-1 text-lg font-black text-[#5b55a4]">
                                    {{ percent(pawn.loan_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Interés diario
                                </p>
                                <p class="mt-1 text-lg font-black text-emerald-600">
                                    {{ percent(pawn.daily_interest_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Interés mensual
                                </p>
                                <p class="mt-1 text-lg font-black text-emerald-600">
                                    {{ percent(pawn.monthly_interest_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    IVA
                                </p>
                                <p class="mt-1 text-lg font-black text-slate-950">
                                    {{ percent(pawn.iva_rate) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Acciones
                        </h2>

                        <div class="mt-5 grid gap-3">
                            <Link
                                :href="urls.show || route('pawns.show', pawn.id)"
                                class="sicem-btn-neutral inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black transition"
                            >
                                Ver detalle del empeño
                            </Link>

                            <Link
                                v-if="urls.pay"
                                :href="urls.pay"
                                class="sicem-btn-green inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black transition"
                            >
                                Liquidar / Abonar
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.sicem-hero {
    background: linear-gradient(135deg, #5b55a4, #312d65) !important;
}

.sicem-btn-primary {
    background-color: #5b55a4 !important;
    color: #ffffff !important;
    border-color: #5b55a4 !important;
}

.sicem-btn-primary:hover {
    background-color: #49438d !important;
    color: #ffffff !important;
}

.sicem-btn-green {
    background-color: #10b981 !important;
    color: #ffffff !important;
    border-color: #10b981 !important;
}

.sicem-btn-green:hover {
    background-color: #059669 !important;
    color: #ffffff !important;
}

.sicem-btn-neutral {
    background-color: #ffffff !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
}

.sicem-btn-neutral:hover {
    background-color: #f8fafc !important;
    color: #5b55a4 !important;
}

.sicem-input {
    color-scheme: light !important;
}

@media print {
    .sicem-btn-primary,
    .sicem-btn-green,
    .sicem-btn-neutral {
        display: none !important;
    }
}
</style>