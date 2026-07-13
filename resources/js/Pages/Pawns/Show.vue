<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import CancelPawnModal from '@/components/Pawns/CancelPawnModal.vue'
import ApplyDiscountModal from '@/components/Pawns/ApplyDiscountModal.vue'

const props = defineProps({
    pawn: {
        type: Object,
        required: true,
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
    cancellationOptions: {
        type: Array,
        default: () => [],
    },
    paymentTypes: {
        type: Array,
        default: () => [],
    },
})

const items = computed(() => props.pawn.items || [])
const transactions = computed(() => props.pawn.transactions || [])
const paymentOptions = computed(() => props.pawn.payment_options || [])
const photos = computed(() => props.pawn.photos || [])

const discountModalOpen = ref(false)

const openDiscountModal = () => {
    // if (!props.urls.discount_liquidation || !props.pawn.can_apply_discount) {
    //     return
    // }

    discountModalOpen.value = true
}

const closeDiscountModal = () => {
    discountModalOpen.value = false
}

const cancelModalOpen = ref(false)

const openCancelModal = () => {
    if (!props.urls.cancel || !props.pawn.can_cancel) {
        return
    }

    cancelModalOpen.value = true
}

const closeCancelModal = () => {
    cancelModalOpen.value = false
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

const statusClass = computed(() => {
    return {
        pending: 'sicem-status-warning',
        expired: 'sicem-status-danger',
        paid: 'sicem-status-info',
        auctioned: 'sicem-status-auction',
        cancelled: 'sicem-status-danger',
    }[props.pawn.status] || 'sicem-status-muted'
})

const transactionAmountClass = (amount) => {
    return Number(amount || 0) >= 0 ? 'text-emerald-600' : 'text-red-600'
}

const transactionSign = (amount) => {
    return Number(amount || 0) >= 0 ? '+' : '-'
}

const absMoney = (value) => {
    return money(Math.abs(Number(value || 0)))
}

const actionDisabled = (url) => !url

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        gem: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        print: 'M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 9V3h10v6M7 14h10v7H7v-7ZM17 12h.01',
        clock: 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        hammer: 'M14 5l5 5M4 20l8-8M12 3l9 9-3 3-9-9 3-3ZM6 18l-2 2',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        image: 'M4 5h16v14H4V5ZM8 13l2.5-3 3 4 2-2.5L20 17M8 8h.01',
        empty: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        check: 'M20 6 9 17l-5-5',
    }

    return icons[icon] || icons.gem
}
</script>

<template>
    <Head :title="`Folio ${pawn.folio}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="urls.index || route('pawns.index')"
                        class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Salir
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Detalle de empeño
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl font-black tracking-tight text-slate-950">
                            Folio {{ pawn.folio }}
                        </h1>

                        <span class="sicem-status-pill" :class="statusClass">
                            {{ pawn.status_label }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Información del contrato, prendas, pagos y acciones del empeño.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link
                        v-if="urls.pay && !pawn.is_paid && !pawn.is_cancelled && !pawn.is_auctioned"
                        :href="urls.pay"
                        class="sicem-btn-green inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-emerald-100 transition"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('cash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Liquidar / Abonar
                    </Link>

                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                        @click="window.print()"
                    >
                        Imprimir
                    </button>
                </div>
            </div>

            <div
                v-if="!pawn.has_photos"
                class="mb-6 rounded-[1.75rem] border border-red-200 bg-red-50 p-5 text-red-700"
            >
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('alert')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <div>
                        <p class="font-black">
                            Atención: no se han tomado las fotografías a la prenda.
                        </p>
                        <p class="mt-1 text-sm">
                            Puedes continuar, pero se recomienda tomar evidencia antes de entregar el dinero.
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="pawn.is_cancelled"
                class="mb-6 rounded-[1.75rem] border border-red-200 bg-red-50 p-5 text-red-700"
            >
                <p class="font-black">
                    Este empeño fue cancelado.
                </p>
                <p class="mt-1 text-sm">
                    Cancelado el {{ pawn.canceled_at }}
                    <span v-if="pawn.canceled_by?.name">por {{ pawn.canceled_by.name }}</span>.
                </p>
            </div>

            <div
                v-if="pawn.is_paid"
                class="mb-6 rounded-[1.75rem] border border-blue-200 bg-blue-50 p-5 text-blue-700"
            >
                <p class="font-black">
                    Este empeño ya fue liquidado.
                </p>
                <p class="mt-1 text-sm">
                    Liquidado el {{ pawn.paid_at }}.
                </p>
            </div>

            <div
                v-if="pawn.is_auctioned"
                class="mb-6 rounded-[1.75rem] border border-amber-200 bg-amber-50 p-5 text-amber-800"
            >
                <p class="font-black">
                    Este empeño ya fue enviado a remate.
                </p>
                <p class="mt-1 text-sm">
                    Procesado el {{ pawn.auction_at }}. Ya no admite pagos, descuentos ni cancelación.
                </p>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.42fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="sicem-hero p-6 text-white">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-white/60">
                                        Préstamo entregado
                                    </p>

                                    <p class="mt-2 text-5xl font-black tracking-tight text-white">
                                        {{ money(pawn.total) }}
                                    </p>

                                    <p class="mt-3 text-sm text-white/70">
                                        Cliente:
                                        <Link
                                            v-if="urls.customer"
                                            :href="urls.customer"
                                            class="font-black text-white hover:underline"
                                        >
                                            {{ pawn.customer?.name }}
                                        </Link>
                                        <span v-else class="font-black text-white">{{ pawn.customer?.name || 'Sin cliente' }}</span>
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                        Sucursal
                                    </p>
                                    <p class="mt-1 text-sm font-black text-white">
                                        {{ pawn.office?.name || 'No especificada' }}
                                    </p>
                                    <p class="mt-1 text-xs text-white/60">
                                        Registrado por {{ pawn.creator?.name || 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    A liquidar hoy
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ money(pawn.amount_to_liquidate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Interés
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ money(pawn.interest_to_pay) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    IVA
                                </p>
                                <p class="mt-1 text-xl font-black text-[#5b55a4]">
                                    {{ money(pawn.iva_to_pay) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                    Días cobrados
                                </p>
                                <p class="mt-1 text-xl font-black text-blue-700">
                                    {{ pawn.days_to_pay }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Datos del contrato
                            </h2>
                            <p class="text-sm text-slate-500">
                                Fechas, beneficiario, bolsa y condiciones del empeño.
                            </p>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Fecha</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ pawn.created_at }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Vencimiento</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ pawn.date_expiration || 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Remate</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ pawn.date_auction || 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Beneficiario</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ pawn.beneficiary || 'No capturado' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Bolsa</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ pawn.bag || 'No capturada' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Plazo</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ pawn.term }} días</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Descripción de la prenda
                            </h2>
                            <p class="text-sm text-slate-500">
                                Artículos incluidos en este empeño.
                            </p>
                        </div>

                        <div v-if="items.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-[#172331] text-white">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase">Artículo</th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase">Características</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase">Cantidad</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase">Préstamo</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="item in items" :key="item.id">
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

                        <div v-else class="p-10 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-[#5b55a4]">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('empty')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <h3 class="mt-5 text-xl font-black text-slate-950">
                                Sin prendas registradas
                            </h3>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Opciones de pago
                            </h2>
                            <p class="text-sm text-slate-500">
                                Opciones informativas para refrendo o desempeño.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Número</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Mutuo</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Interés</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">I.V.A.</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Por refrendo</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Por desempeño</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Fecha</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="option in paymentOptions" :key="option.number">
                                        <td class="px-5 py-4 text-sm font-black text-slate-900">{{ option.number }}</td>
                                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-700">{{ money(option.principal) }}</td>
                                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-700">{{ money(option.interest) }}</td>
                                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-700">{{ money(option.iva) }}</td>
                                        <td class="px-5 py-4 text-right text-sm font-black text-emerald-600">{{ money(option.refinance_total) }}</td>
                                        <td class="px-5 py-4 text-right text-sm font-black text-[#5b55a4]">{{ money(option.liquidate_total) }}</td>
                                        <td class="px-5 py-4 text-right text-sm font-semibold text-slate-700">{{ option.date }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="grid divide-y divide-slate-100 border-t border-slate-100 md:grid-cols-2 md:divide-x md:divide-y-0">
                            <div class="p-5 text-center">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Costo mensual total</p>
                                <p class="mt-1 text-lg font-black text-slate-950">
                                    {{ percent(pawn.monthly_interest_rate) }} fijo sin I.V.A.
                                </p>
                            </div>

                            <div class="p-5 text-center">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Costo diario total</p>
                                <p class="mt-1 text-lg font-black text-slate-950">
                                    {{ percent(pawn.daily_interest_rate) }} fijo sin I.V.A.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Movimientos
                            </h2>
                            <p class="text-sm text-slate-500">
                                Historial financiero relacionado con este empeño.
                            </p>
                        </div>

                        <div v-if="transactions.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Fecha</th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Movimiento</th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Pago</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Importe</th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Saldo caja</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="transaction in transactions" :key="transaction.id">
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-800">{{ transaction.created_at }}</p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">{{ transaction.user?.name || 'N/A' }}</p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-900">
                                                {{ transaction.type_label }}
                                                <span
                                                    v-if="transaction.is_cancelled"
                                                    class="ml-2 rounded bg-red-500 px-2 py-0.5 text-[10px] font-black text-white"
                                                >
                                                    Cancelado
                                                </span>
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                                {{ transaction.comments || 'Sin comentarios' }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-black text-slate-600">
                                                {{ transaction.payment_type_label }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black" :class="transactionAmountClass(transaction.amount)">
                                            {{ transactionSign(transaction.amount) }}{{ absMoney(transaction.amount) }}
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black text-slate-700">
                                            {{ money(transaction.balance) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="p-10 text-center text-sm font-semibold text-slate-500">
                            No hay movimientos registrados.
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Acciones
                            </h2>
                            <p class="text-sm text-slate-500">
                                Operaciones disponibles para este empeño.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 divide-x divide-y divide-slate-100">
                            <Link
                              v-if="urls.pay && !pawn.is_paid && !pawn.is_cancelled && !pawn.is_auctioned"
                              :href="urls.pay"
                              class="sicem-action-button"
                          >
                              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                  <path :d="iconPath('receipt')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                              </svg>
                              Movimiento / pago
                          </Link>

                          <button
                              v-else
                              type="button"
                              class="sicem-action-button sicem-action-disabled"
                              disabled
                          >
                              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                  <path :d="iconPath('receipt')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                              </svg>
                              Movimiento / pago
                          </button>

                            <a
                                v-if="urls.print_countersign && !pawn.is_paid && !pawn.is_cancelled && !pawn.is_auctioned"
                                :href="urls.print_countersign"
                                target="_blank"
                                class="sicem-action-button"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('print')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Ticket refrendo
                            </a>

                            <button
                                v-else
                                type="button"
                                class="sicem-action-button sicem-action-disabled"
                                disabled
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('print')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Ticket refrendo
                            </button>

                            <Link
                                v-if="urls.date_expiration && !pawn.is_paid && !pawn.is_cancelled && !pawn.is_auctioned"
                                :href="urls.date_expiration"
                                class="sicem-action-button"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('clock')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Fecha de espera
                            </Link>

                            <button
                                v-else
                                type="button"
                                class="sicem-action-button sicem-action-disabled"
                                disabled
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('clock')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Fecha de espera
                            </button>

                            <a
                                v-if="urls.print_big_ticket"
                                :href="urls.print_big_ticket"
                                target="_blank"
                                class="sicem-action-button"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('print')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Boleta
                            </a>

                            <button
                                v-else
                                type="button"
                                class="sicem-action-button sicem-action-disabled"
                                disabled
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('print')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Boleta
                            </button>

                            <Link
                                v-if="urls.previous_pawn"
                                :href="urls.previous_pawn"
                                class="sicem-action-button"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Ver anterior
                            </Link>

                            <button
                                v-else
                                type="button"
                                class="sicem-action-button sicem-action-disabled"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Ver anterior
                            </button>

                            <Link
                                v-if="urls.anticipated_date"
                                :href="urls.anticipated_date"
                                class="sicem-action-button"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('calendar')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Fecha anticipada
                            </Link>

                            <button
                                v-else
                                type="button"
                                class="sicem-action-button sicem-action-disabled"
                                disabled
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('calendar')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Fecha anticipada
                            </button>

                            <button
                                type="button"
                                class="sicem-action-button sicem-action-danger"
                                :class="{ 'sicem-action-disabled': !pawn.can_cancel || !urls.cancel }"
                                :disabled="!pawn.can_cancel || !urls.cancel"
                                @click="openCancelModal"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('trash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Cancelar
                            </button>

                            <Link
                                v-if="urls.send_to_auction && pawn.can_send_to_auction"
                                :href="urls.send_to_auction"
                                class="sicem-action-button sicem-action-warning"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('hammer')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Sacar a remate
                            </Link>

                            <button
                                v-else
                                type="button"
                                class="sicem-action-button sicem-action-warning sicem-action-disabled"
                                disabled
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('hammer')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                {{ pawn.is_auctioned ? 'En remate' : 'Sacar a remate' }}
                            </button>

                            <Link
                                v-if="urls.apply_discount && pawn.can_apply_discount"
                                :href="urls.apply_discount"
                                class="sicem-action-button"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('percent')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                Aplicar descuento
                            </Link>

                            <button
                                v-else
                                type="button"
                                class="sicem-action-button sicem-action-disabled"
                                disabled
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('percent')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                Aplicar descuento
                            </button>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Cliente
                        </h2>

                        <div v-if="pawn.customer" class="mt-5 space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                        <path :d="iconPath('user')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-black text-slate-950">
                                        {{ pawn.customer.name }}
                                    </p>
                                    <p class="text-xs font-semibold text-slate-400">
                                        {{ pawn.customer.type_label || 'Identificación' }} {{ pawn.customer.code_id || '' }}
                                    </p>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                                <p class="font-bold text-slate-400">RFC</p>
                                <p class="mt-1 font-black text-slate-800">{{ pawn.customer.rfc || 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                                <p class="font-bold text-slate-400">Email</p>
                                <p class="mt-1 font-black text-slate-800">{{ pawn.customer.email || 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                                <p class="font-bold text-slate-400">Teléfono</p>
                                <p class="mt-1 font-black text-slate-800">{{ pawn.customer.phone || 'N/A' }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                                <p class="font-bold text-slate-400">Dirección</p>
                                <p class="mt-1 font-black text-slate-800">{{ pawn.customer.full_address || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Fotos
                        </h2>

                        <div v-if="photos.length" class="mt-5 grid gap-3">
                            <a
                                v-for="photo in photos"
                                :key="photo"
                                :href="photo"
                                target="_blank"
                                class="group overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
                            >
                                <img
                                    :src="photo"
                                    alt="Foto de prenda"
                                    class="h-44 w-full object-cover transition group-hover:scale-105"
                                />
                            </a>
                        </div>

                        <div v-else class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                            <svg class="mx-auto h-8 w-8 text-slate-400" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('image')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                            <p class="mt-3 text-sm font-bold text-slate-500">
                                No hay fotos registradas.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Tasas
                        </h2>

                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">Préstamo</p>
                                <p class="mt-1 text-lg font-black text-[#5b55a4]">{{ percent(pawn.loan_rate) }}</p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">Interés diario</p>
                                <p class="mt-1 text-lg font-black text-emerald-600">{{ percent(pawn.daily_interest_rate) }}</p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">Interés mensual</p>
                                <p class="mt-1 text-lg font-black text-emerald-600">{{ percent(pawn.monthly_interest_rate) }}</p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">IVA</p>
                                <p class="mt-1 text-lg font-black text-slate-950">{{ percent(pawn.iva_rate) }}</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <CancelPawnModal
            :show="cancelModalOpen"
            :pawn="pawn"
            :url="urls.cancel"
            :cancellation-options="cancellationOptions"
            @close="closeCancelModal"
        />

        <ApplyDiscountModal
            :show="discountModalOpen"
            :pawn="pawn"
            :url="urls.discount_liquidation"
            :payment-types="paymentTypes"
            @close="closeDiscountModal"
        />
    </AdminLayout>
</template>

<style scoped>
.sicem-hero {
    background: linear-gradient(135deg, #5b55a4, #312d65) !important;
}

.sicem-status-pill {
    display: inline-flex;
    border-radius: 9999px;
    border-width: 1px;
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 900;
}

.sicem-status-warning {
    border-color: #fed7aa !important;
    background-color: #fff7ed !important;
    color: #c2410c !important;
}

.sicem-status-danger {
    border-color: #fecaca !important;
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}

.sicem-status-info {
    border-color: #bfdbfe !important;
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
}

.sicem-status-auction {
    border-color: #fde68a !important;
    background-color: #fffbeb !important;
    color: #b45309 !important;
}

.sicem-status-muted {
    border-color: #e2e8f0 !important;
    background-color: #f8fafc !important;
    color: #475569 !important;
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

.sicem-action-button {
    min-height: 92px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    padding: 1rem;
    background-color: #ffffff !important;
    color: #475569 !important;
    font-size: 0.75rem;
    font-weight: 900;
    text-align: center;
    transition: all 0.18s ease;
}

.sicem-action-button:hover:not(:disabled) {
    background-color: #f8fafc !important;
    color: #5b55a4 !important;
}

.sicem-action-danger {
    color: #dc2626 !important;
}

.sicem-action-warning {
    color: #d97706 !important;
}

.sicem-action-disabled,
.sicem-action-button:disabled {
    opacity: 0.45 !important;
    cursor: not-allowed !important;
}
</style>
