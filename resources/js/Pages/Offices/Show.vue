<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    office: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const confirmModal = ref(false)
const pendingAction = ref('archive')

const transactions = computed(() => props.office.transactions || [])

const openArchive = () => {
    pendingAction.value = 'archive'
    confirmModal.value = true
}

const openRestore = () => {
    pendingAction.value = 'restore'
    confirmModal.value = true
}

const closeConfirm = () => {
    confirmModal.value = false
    pendingAction.value = 'archive'
}

const executeAction = () => {
    if (pendingAction.value === 'restore') {
        router.post(props.urls.restore || route('offices.restore', props.office.id), {}, {
            preserveScroll: true,
            onFinish: () => closeConfirm(),
        })

        return
    }

    router.delete(props.urls.destroy || route('offices.destroy', props.office.id), {
        preserveScroll: true,
        onFinish: () => closeConfirm(),
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

const percent = (value) => {
    return `${Number(value || 0).toFixed(4)}%`
}

const statusClass = computed(() => {
    return props.office.is_deleted ? 'sicem-status-deleted' : 'sicem-status-active'
})

const cashClass = computed(() => {
    const cash = Number(props.office.cash || 0)

    if (cash < 0) {
        return 'text-red-100'
    }

    if (cash > 0) {
        return 'text-emerald-100'
    }

    return 'text-white/80'
})

const transactionAmountClass = (transaction) => {
    if (transaction.is_cancelled) {
        return 'text-slate-400 line-through'
    }

    const amount = Number(transaction.amount || 0)

    if (amount > 0) {
        return 'text-emerald-600'
    }

    if (amount < 0) {
        return 'text-red-600'
    }

    return 'text-slate-700'
}

const transactionBadgeClass = (transaction) => {
    if (transaction.is_cancelled) {
        return 'border-slate-200 bg-slate-50 text-slate-400'
    }

    const amount = Number(transaction.amount || 0)

    if (amount > 0) {
        return 'border-emerald-100 bg-emerald-50 text-emerald-700'
    }

    if (amount < 0) {
        return 'border-red-100 bg-red-50 text-red-700'
    }

    return 'border-blue-100 bg-blue-50 text-blue-700'
}

const cashBoxClass = computed(() => {
    const cash = Number(props.office.cash || 0)

    if (cash < 0) {
        return 'border-red-200 bg-red-50 text-red-700'
    }

    if (cash > 0) {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700'
    }

    return 'border-slate-200 bg-slate-50 text-slate-700'
})

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        office: 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M9 9h.01M9 13h.01M9 17h.01M15 13h.01M15 17h.01',
        building: 'M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 8h2a2 2 0 0 1 2 2v11M8 7h4M8 11h4M8 15h4M3 21h18',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        income: 'M12 3v12M7 10l5 5 5-5M5 21h14',
        expense: 'M12 21V9M7 14l5-5 5 5M5 3h14',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        refresh: 'M21 12a9 9 0 1 1-3-6.7M21 4v6h-6',
        phone: 'M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.32 1.77.57 2.61a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.47-1.14a2 2 0 0 1 2.11-.45c.84.25 1.71.45 2.61.57A2 2 0 0 1 22 16.92Z',
        map: 'M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Zm0 0V3M15 21V6',
        clock: 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        bank: 'M3 21h18M4 10h16M6 10v8M10 10v8M14 10v8M18 10v8M12 3l9 5H3l9-5Z',
        pawn: 'M6 21h12M12 3l7 7-7 7-7-7 7-7Z',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        empty: 'M4 7h16M4 12h16M4 17h16',
        x: 'M18 6 6 18M6 6l12 12',
    }

    return icons[icon] || icons.office
}
</script>

<template>
    <Head :title="office.name" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="urls.index || route('offices.index')"
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
                        Sucursal
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl font-black tracking-tight text-slate-950">
                            {{ office.name }}
                        </h1>

                        <span class="sicem-status-pill" :class="statusClass">
                            {{ office.status_label }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Caja, movimientos, tasas y operación de la sucursal.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link
                        v-if="!office.is_deleted"
                        :href="urls.edit || route('offices.edit', office.id)"
                        class="sicem-btn-blue inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-blue-100 transition"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path
                                :d="iconPath('edit')"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Editar
                    </Link>

                    <button
                        v-if="office.is_deleted"
                        type="button"
                        class="sicem-btn-green inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-emerald-100 transition"
                        @click="openRestore"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path
                                :d="iconPath('refresh')"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Restaurar
                    </button>

                    <button
                        v-else
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-black text-red-600 shadow-sm transition hover:bg-red-50"
                        @click="openArchive"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path
                                :d="iconPath('trash')"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        Archivar
                    </button>
                </div>
            </div>

            <div
                v-if="office.is_deleted"
                class="mb-6 rounded-[1.75rem] border border-red-200 bg-red-50 p-5 text-red-700"
            >
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                        <path
                            :d="iconPath('alert')"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <div>
                        <p class="font-black">
                            Sucursal archivada
                        </p>
                        <p class="mt-1 text-sm">
                            Esta sucursal no aparece como activa. Puedes restaurarla desde esta pantalla.
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="Number(office.cash || 0) < 0 && !office.is_deleted"
                class="mb-6 rounded-[1.75rem] border border-amber-200 bg-amber-50 p-5 text-amber-800"
            >
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                        <path
                            :d="iconPath('alert')"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <div>
                        <p class="font-black">
                            Caja negativa
                        </p>
                        <p class="mt-1 text-sm">
                            La caja de esta sucursal está en negativo. Revisa los últimos movimientos o registra un depósito.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.4fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="sicem-hero p-6 text-white">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-white/60">
                                        Caja actual
                                    </p>

                                    <p class="mt-2 text-5xl font-black tracking-tight" :class="cashClass">
                                        {{ money(office.cash) }}
                                    </p>

                                    <p class="mt-3 text-sm text-white/70">
                                        Serie:
                                        <span class="font-black text-white">{{ office.serie || 'N/A' }}</span>
                                        · Código:
                                        <span class="font-black text-white">{{ office.code || 'N/A' }}</span>
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[330px]">
                                    <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                            Ingresos hoy
                                        </p>
                                        <p class="mt-1 text-xl font-black text-white">
                                            {{ money(summary.income_today) }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                            Egresos hoy
                                        </p>
                                        <p class="mt-1 text-xl font-black text-white">
                                            {{ money(summary.expenses_today) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-3 xl:grid-cols-6">
                            <div class="rounded-2xl bg-violet-50 p-4 xl:col-span-2">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    Movimientos hoy
                                </p>
                                <p class="mt-1 text-2xl font-black text-[#5b55a4]">
                                    {{ summary.transactions_today || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4 xl:col-span-2">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Empeños activos
                                </p>
                                <p class="mt-1 text-2xl font-black text-emerald-600">
                                    {{ summary.pawns_active || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-red-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-red-500">
                                    Vencidos
                                </p>
                                <p class="mt-1 text-2xl font-black text-red-600">
                                    {{ summary.pawns_expired || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                    Liquidados
                                </p>
                                <p class="mt-1 text-2xl font-black text-blue-600">
                                    {{ summary.pawns_paid || 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-4">
                        <Link
                            v-if="urls.income && !office.is_deleted"
                            :href="urls.income"
                            class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('income')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <p class="mt-4 text-sm font-black text-slate-950">
                                Registrar depósito
                            </p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Entrada de efectivo a caja.
                            </p>
                        </Link>

                        <Link
                            v-if="urls.expense && !office.is_deleted"
                            :href="urls.expense"
                            class="rounded-[1.75rem] border border-red-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-100 text-red-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('expense')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <p class="mt-4 text-sm font-black text-slate-950">
                                Retirar efectivo
                            </p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Salida manual de caja.
                            </p>
                        </Link>

                        <Link
                            v-if="urls.pawns"
                            :href="urls.pawns"
                            class="rounded-[1.75rem] border border-violet-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('pawn')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <p class="mt-4 text-sm font-black text-slate-950">
                                Ver empeños
                            </p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Operaciones de esta sucursal.
                            </p>
                        </Link>

                        <Link
                            v-if="urls.edit && !office.is_deleted"
                            :href="urls.edit"
                            class="rounded-[1.75rem] border border-blue-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('edit')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <p class="mt-4 text-sm font-black text-slate-950">
                                Editar sucursal
                            </p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                Datos, tasas y caja.
                            </p>
                        </Link>
                    </div>

                    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-xl font-black text-slate-950">
                                Últimos movimientos
                            </h2>
                            <p class="text-sm text-slate-500">
                                Historial reciente de caja y operaciones de esta sucursal.
                            </p>
                        </div>

                        <div v-if="transactions.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Movimiento
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Referencia
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Importe
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Saldo
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Usuario
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr
                                        v-for="transaction in transactions"
                                        :key="transaction.id"
                                        class="transition hover:bg-slate-50/80"
                                    >
                                        <td class="px-5 py-4">
                                            <div class="flex items-start gap-3">
                                                <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border" :class="transactionBadgeClass(transaction)">
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                        <path
                                                            :d="iconPath('receipt')"
                                                            stroke="currentColor"
                                                            stroke-width="1.8"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />
                                                    </svg>
                                                </span>

                                                <div>
                                                    <p class="text-sm font-black text-slate-950">
                                                        {{ transaction.type_label }}
                                                    </p>
                                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                                        {{ transaction.created_at }} · {{ transaction.payment_type_label }}
                                                    </p>
                                                    <p
                                                        v-if="transaction.comments"
                                                        class="mt-1 max-w-xl text-xs font-semibold text-slate-500"
                                                    >
                                                        {{ transaction.comments }}
                                                    </p>
                                                    <p
                                                        v-if="transaction.is_cancelled"
                                                        class="mt-1 text-xs font-black text-red-600"
                                                    >
                                                        Cancelado
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-5 py-4">
                                            <template v-if="transaction.pawn">
                                                <p class="text-sm font-black text-slate-800">
                                                    Folio {{ transaction.pawn.folio }}
                                                </p>
                                                <p class="mt-1 text-xs font-semibold text-slate-400">
                                                    {{ transaction.pawn.customer_name || 'Cliente no especificado' }}
                                                </p>
                                            </template>

                                            <p v-else class="text-sm font-semibold text-slate-400">
                                                Sin referencia
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <p class="text-sm font-black" :class="transactionAmountClass(transaction)">
                                                {{ money(transaction.amount) }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <p class="text-sm font-black text-slate-800">
                                                {{ money(transaction.balance) }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-800">
                                                {{ transaction.user?.name || 'N/A' }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                                {{ transaction.user?.email || '' }}
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="p-10 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-[#5b55a4]">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('empty')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <h3 class="mt-5 text-xl font-black text-slate-950">
                                Sin movimientos
                            </h3>
                            <p class="mt-2 text-sm text-slate-500">
                                Esta sucursal todavía no tiene movimientos registrados.
                            </p>
                        </div>
                    </section>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[2rem] border p-6 shadow-sm" :class="cashBoxClass">
                        <p class="text-xs font-black uppercase tracking-[0.2em] opacity-70">
                            Caja actual
                        </p>
                        <p class="mt-2 text-4xl font-black">
                            {{ money(office.cash) }}
                        </p>
                        <p class="mt-2 text-sm font-semibold opacity-80">
                            Efectivo registrado actualmente en esta sucursal.
                        </p>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Datos de sucursal
                        </h2>

                        <div class="mt-5 space-y-3">
                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                                <svg class="mt-0.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('building')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Empresa
                                    </p>
                                    <Link
                                        v-if="urls.company"
                                        :href="urls.company"
                                        class="mt-1 block text-sm font-black text-[#5b55a4] hover:underline"
                                    >
                                        {{ office.company?.name || 'Sin empresa' }}
                                    </Link>
                                    <p v-else class="mt-1 text-sm font-black text-slate-800">
                                        {{ office.company?.name || 'Sin empresa' }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        {{ office.company?.code || 'Sin código' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                                <svg class="mt-0.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('phone')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Teléfono
                                    </p>
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ office.phone || 'No capturado' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                                <svg class="mt-0.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('map')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Dirección
                                    </p>
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ office.address || 'No capturada' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                                <svg class="mt-0.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('clock')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Horario
                                    </p>
                                    <p class="mt-1 whitespace-pre-line text-sm font-black text-slate-800">
                                        {{ office.schedule || 'No capturado' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                                <svg class="mt-0.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('bank')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Cuenta bancaria
                                    </p>
                                    <p class="mt-1 break-all text-sm font-black text-slate-800">
                                        {{ office.bank_account || 'No capturada' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Tasas de interés
                        </h2>

                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                    Interés diario
                                </p>
                                <p class="mt-1 text-2xl font-black text-blue-600">
                                    {{ percent(office.daily_interest_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    Interés mensual
                                </p>
                                <p class="mt-1 text-2xl font-black text-[#5b55a4]">
                                    {{ percent(office.monthly_interest_rate) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Historial
                        </h2>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Creada
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ office.created_at || 'N/A' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Última actualización
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ office.updated_at || 'N/A' }}
                                </p>
                            </div>

                            <div v-if="office.deleted_at" class="rounded-2xl bg-red-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-red-500">
                                    Archivada
                                </p>
                                <p class="mt-1 text-sm font-black text-red-700">
                                    {{ office.deleted_at }}
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <div
            v-if="confirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-[2rem] bg-white p-8 shadow-2xl">
                <div
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-full"
                    :class="pendingAction === 'restore' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'"
                >
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                        <path
                            :d="iconPath(pendingAction === 'restore' ? 'refresh' : 'trash')"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <h2 class="mt-6 text-center text-2xl font-black text-slate-950">
                    {{ pendingAction === 'restore' ? 'Restaurar sucursal' : 'Archivar sucursal' }}
                </h2>

                <p class="mt-2 text-center text-sm leading-6 text-slate-500">
                    {{ pendingAction === 'restore'
                        ? 'La sucursal volverá a estar disponible.'
                        : 'La sucursal se archivará y no aparecerá como activa.'
                    }}
                </p>

                <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-center text-sm font-black text-slate-800">
                    {{ office.name }}
                </p>

                <div class="mt-6 flex justify-center gap-3">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeConfirm"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl px-5 py-3 text-sm font-black text-white transition"
                        :class="pendingAction === 'restore' ? 'sicem-btn-green' : 'sicem-btn-danger'"
                        @click="executeAction"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.sicem-hero {
    background: linear-gradient(135deg, #5b55a4, #312d65) !important;
}

.sicem-btn-blue {
    background-color: #2563eb !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
}

.sicem-btn-blue:hover {
    background-color: #1d4ed8 !important;
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

.sicem-btn-danger {
    background-color: #dc2626 !important;
    color: #ffffff !important;
    border-color: #dc2626 !important;
}

.sicem-btn-danger:hover {
    background-color: #b91c1c !important;
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

.sicem-status-deleted {
    border-color: #fecaca !important;
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}
</style>