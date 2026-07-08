<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    company: {
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

const offices = computed(() => props.company.offices || [])

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
        router.post(props.urls.restore || route('companies.restore', props.company.id), {}, {
            preserveScroll: true,
            onFinish: () => closeConfirm(),
        })

        return
    }

    router.delete(props.urls.destroy || route('companies.destroy', props.company.id), {
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
    return `${Number(value || 0).toFixed(2)}%`
}

const statusClass = computed(() => {
    if (props.company.is_deleted) {
        return 'sicem-status-deleted'
    }

    return props.company.is_active ? 'sicem-status-active' : 'sicem-status-inactive'
})

const cashClass = (value) => {
    const cash = Number(value || 0)

    if (cash < 0) {
        return 'text-red-600'
    }

    if (cash > 0) {
        return 'text-emerald-600'
    }

    return 'text-slate-700'
}

const cashCardClass = (value) => {
    const cash = Number(value || 0)

    if (cash < 0) {
        return 'border-red-100 bg-red-50'
    }

    if (cash > 0) {
        return 'border-emerald-100 bg-emerald-50'
    }

    return 'border-slate-100 bg-slate-50'
}

const normalizeWebsite = (website) => {
    if (!website) {
        return null
    }

    if (website.startsWith('http://') || website.startsWith('https://')) {
        return website
    }

    return `https://${website}`
}

const transactionTypeLabel = (type) => {
    return {
        pawn: 'Empeño',
        countersign: 'Refrendo',
        liquidation: 'Liquidación',
        payment: 'Pago',
        manual_income: 'Ingreso manual',
        manual_expense: 'Gasto manual',
        sale: 'Venta',
        aside: 'Apartado',
        aside_payment: 'Abono apartado',
        adjustment: 'Ajuste',
        expiration_date_change: 'Cambio de fecha',
    }[type] || (type ? type.replaceAll('_', ' ') : 'Sin movimiento')
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        building: 'M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 8h2a2 2 0 0 1 2 2v11M8 7h4M8 11h4M8 15h4M3 21h18',
        office: 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M9 9h.01M9 13h.01M9 17h.01M15 13h.01M15 17h.01',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        phone: 'M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.32 1.77.57 2.61a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.47-1.14a2 2 0 0 1 2.11-.45c.84.25 1.71.45 2.61.57A2 2 0 0 1 22 16.92Z',
        mail: 'M4 4h16v16H4V4Zm0 4 8 5 8-5',
        map: 'M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Zm0 0V3M15 21V6',
        link: 'M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71',
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        refresh: 'M21 12a9 9 0 1 1-3-6.7M21 4v6h-6',
        plus: 'M12 5v14M5 12h14',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        check: 'M20 6 9 17l-5-5',
        x: 'M18 6 6 18M6 6l12 12',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        empty: 'M4 7h16M4 12h16M4 17h16',
    }

    return icons[icon] || icons.building
}
</script>

<template>
    <Head :title="company.name" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="urls.index || route('companies.index')"
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
                        Empresa
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        <h1 class="text-3xl font-black tracking-tight text-slate-950">
                            {{ company.name }}
                        </h1>

                        <span class="sicem-status-pill" :class="statusClass">
                            {{ company.status_label }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Datos fiscales, comisiones, sucursales y resumen operativo.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link
                        v-if="!company.is_deleted"
                        :href="urls.edit || route('companies.edit', company.id)"
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
                        v-if="company.is_deleted"
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
                v-if="company.is_deleted"
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
                            Empresa archivada
                        </p>
                        <p class="mt-1 text-sm">
                            Esta empresa no aparece como activa. Puedes restaurarla desde esta pantalla.
                        </p>
                    </div>
                </div>
            </div>

            <div
                v-if="!company.is_active && !company.is_deleted"
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
                            Empresa inactiva
                        </p>
                        <p class="mt-1 text-sm">
                            La empresa está registrada, pero marcada como inactiva.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.42fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="sicem-hero p-6 text-white">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-white/60">
                                        Empresa
                                    </p>

                                    <p class="mt-2 text-4xl font-black tracking-tight text-white">
                                        {{ company.name }}
                                    </p>

                                    <p class="mt-3 text-sm text-white/70">
                                        Código:
                                        <span class="font-black text-white">{{ company.code || 'N/A' }}</span>
                                        · RFC:
                                        <span class="font-black text-white">{{ company.rfc || 'N/A' }}</span>
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                        Caja total
                                    </p>
                                    <p class="mt-1 text-2xl font-black text-white">
                                        {{ money(summary.total_cash) }}
                                    </p>
                                    <p class="mt-1 text-xs text-white/60">
                                        {{ summary.offices || 0 }} sucursal(es)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-4">
                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    Sucursales
                                </p>
                                <p class="mt-1 text-2xl font-black text-[#5b55a4]">
                                    {{ summary.offices || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Caja total
                                </p>
                                <p class="mt-1 text-2xl font-black text-emerald-600">
                                    {{ money(summary.total_cash) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-red-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-red-500">
                                    Caja negativa
                                </p>
                                <p class="mt-1 text-2xl font-black text-red-600">
                                    {{ summary.negative_offices || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                    Estatus
                                </p>
                                <p class="mt-1 text-lg font-black text-blue-700">
                                    {{ company.status_label }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-xl font-black text-slate-950">
                                    Sucursales
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Puntos de operación relacionados con esta empresa.
                                </p>
                            </div>

                            <Link
                                v-if="urls.office_create"
                                :href="urls.office_create"
                                class="sicem-btn-primary inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-xs font-black shadow-lg shadow-violet-100 transition"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('plus')"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    />
                                </svg>
                                Nueva sucursal
                            </Link>
                        </div>

                        <div v-if="offices.length" class="grid gap-5 p-6 lg:grid-cols-2">
                            <article
                                v-for="office in offices"
                                :key="office.id"
                                class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                            >
                                <div
                                    class="border-b p-5"
                                    :class="cashCardClass(office.cash)"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                                Sucursal
                                            </p>

                                            <h3 class="mt-1 text-xl font-black text-slate-950">
                                                {{ office.name }}
                                            </h3>

                                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                                Serie {{ office.serie || 'N/A' }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                                Caja
                                            </p>
                                            <p class="mt-1 text-2xl font-black" :class="cashClass(office.cash)">
                                                {{ money(office.cash) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-3 p-5">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                                Interés diario
                                            </p>
                                            <p class="mt-1 text-sm font-black text-slate-800">
                                                {{ percent(office.daily_interest_rate) }}
                                            </p>
                                        </div>

                                        <div class="rounded-2xl bg-slate-50 p-4">
                                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                                Interés mensual
                                            </p>
                                            <p class="mt-1 text-sm font-black text-slate-800">
                                                {{ percent(office.monthly_interest_rate) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                            Dirección
                                        </p>
                                        <p class="mt-1 text-sm font-semibold text-slate-700">
                                            {{ office.address || 'Sin dirección' }}
                                        </p>
                                    </div>

                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                            Último movimiento
                                        </p>

                                        <template v-if="office.last_transaction">
                                            <p class="mt-1 text-sm font-black text-slate-800">
                                                {{ transactionTypeLabel(office.last_transaction.type) }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                                {{ money(office.last_transaction.amount) }}
                                                · Saldo {{ money(office.last_transaction.balance) }}
                                                · {{ office.last_transaction.created_at }}
                                            </p>
                                        </template>

                                        <p v-else class="mt-1 text-sm font-semibold text-slate-500">
                                            Sin movimientos.
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap justify-end gap-2 pt-2">
                                        <Link
                                            v-if="office.show_url"
                                            :href="office.show_url"
                                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-[#5b55a4] hover:text-white"
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

                                        <Link
                                            v-if="office.edit_url"
                                            :href="office.edit_url"
                                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-200 bg-white px-4 py-2 text-xs font-black text-blue-600 transition hover:bg-blue-500 hover:text-white"
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
                                    </div>
                                </div>
                            </article>
                        </div>

                        <div v-else class="p-10 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-[#5b55a4]">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('office')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <h3 class="mt-5 text-xl font-black text-slate-950">
                                No hay sucursales
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Esta empresa todavía no tiene sucursales registradas.
                            </p>

                            <Link
                                v-if="urls.office_create"
                                :href="urls.office_create"
                                class="sicem-btn-primary mt-6 inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition"
                            >
                                Crear sucursal
                            </Link>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Datos generales
                        </h2>

                        <div class="mt-5 space-y-3">
                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                                <svg class="mt-0.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('receipt')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        RFC
                                    </p>
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ company.rfc || 'No capturado' }}
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
                                        {{ company.phone || 'No capturado' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                                <svg class="mt-0.5 h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('mail')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Correo
                                    </p>
                                    <p class="mt-1 break-all text-sm font-black text-slate-800">
                                        {{ company.email || 'No capturado' }}
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
                                        {{ company.address || 'No capturada' }}
                                    </p>
                                </div>
                            </div>

                            <a
                                v-if="company.website"
                                :href="normalizeWebsite(company.website)"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-start gap-3 rounded-2xl bg-violet-50 p-4 transition hover:bg-violet-100"
                            >
                                <svg class="mt-0.5 h-5 w-5 text-[#5b55a4]" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('link')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                        Sitio web
                                    </p>
                                    <p class="mt-1 break-all text-sm font-black text-[#5b55a4]">
                                        {{ company.website }}
                                    </p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Comisiones
                        </h2>

                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Almacenaje
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ money(company.storage_commission) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                    Comercialización
                                </p>
                                <p class="mt-1 text-xl font-black text-blue-600">
                                    {{ money(company.marketing_commission) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-amber-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-500">
                                    Desempeño extemporáneo
                                </p>
                                <p class="mt-1 text-xl font-black text-amber-600">
                                    {{ money(company.delayed_payment_commission) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    Reposición contrato
                                </p>
                                <p class="mt-1 text-xl font-black text-[#5b55a4]">
                                    {{ money(company.replacement_contract_commission) }}
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
                                    {{ company.created_at || 'N/A' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Última actualización
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ company.updated_at || 'N/A' }}
                                </p>
                            </div>

                            <div v-if="company.deleted_at" class="rounded-2xl bg-red-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-red-500">
                                    Archivada
                                </p>
                                <p class="mt-1 text-sm font-black text-red-700">
                                    {{ company.deleted_at }}
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
                    {{ pendingAction === 'restore' ? 'Restaurar empresa' : 'Archivar empresa' }}
                </h2>

                <p class="mt-2 text-center text-sm leading-6 text-slate-500">
                    {{ pendingAction === 'restore'
                        ? 'La empresa volverá a estar disponible.'
                        : 'La empresa se archivará y no aparecerá como activa.'
                    }}
                </p>

                <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-center text-sm font-black text-slate-800">
                    {{ company.name }}
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

.sicem-btn-primary {
    background-color: #5b55a4 !important;
    color: #ffffff !important;
    border-color: #5b55a4 !important;
}

.sicem-btn-primary:hover {
    background-color: #4f4896 !important;
    color: #ffffff !important;
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

.sicem-status-inactive {
    border-color: #e2e8f0 !important;
    background-color: #f8fafc !important;
    color: #475569 !important;
}

.sicem-status-deleted {
    border-color: #fecaca !important;
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}
</style>