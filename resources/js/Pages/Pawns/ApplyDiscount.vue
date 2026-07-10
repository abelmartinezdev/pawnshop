<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    pawn: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        required: true,
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const form = useForm({
    days: '',
    comments: '',
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const requestedDays = computed(() => {
    const value = Number(form.days || 0)

    if (value < 0) {
        return 0
    }

    return Math.floor(value)
})

const safeDays = computed(() => {
    return Math.min(
        requestedDays.value,
        Number(props.summary.max_discount_days || 0)
    )
})

const discountAmount = computed(() => {
    return safeDays.value * Number(props.summary.daily_interest || 0)
})

const finalDays = computed(() => {
    return Math.max(
        Number(props.summary.days_to_pay || 0)
            - Number(props.summary.discounted_days || 0)
            - safeDays.value,
        0
    )
})

const finalInterest = computed(() => {
    return Math.max(
        Number(props.summary.interest_current || 0) - discountAmount.value,
        0
    )
})

const finalTotal = computed(() => {
    return Number(props.pawn.total || 0) + finalInterest.value
})

const canSubmit = computed(() => {
    return Boolean(props.urls.store)
        && safeDays.value > 0
        && safeDays.value <= Number(props.summary.max_discount_days || 0)
        && !form.processing
})

const submit = () => {
    if (!canSubmit.value) {
        return
    }

    form.days = safeDays.value

    form.post(props.urls.store, {
        preserveScroll: true,
    })
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        check: 'M20 6 9 17l-5-5',
    }

    return icons[icon] || icons.percent
}
</script>

<template>
    <Head :title="`Aplicar descuento ${pawn.folio}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="urls.show || route('pawns.show', pawn.id)"
                        class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Regresar al empeño
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Descuento por días
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Aplicar descuento · Folio {{ pawn.folio }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Descuenta días de interés al empeño. Esta acción no mueve caja, solo registra el ajuste.
                    </p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.42fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="sicem-hero p-6 text-white">
                            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.22em] text-white/60">
                                        Total actual a liquidar
                                    </p>

                                    <p class="mt-2 text-5xl font-black tracking-tight text-white">
                                        {{ money(summary.amount_current) }}
                                    </p>

                                    <p class="mt-3 text-sm text-white/70">
                                        Cliente:
                                        <strong class="text-white">{{ pawn.customer?.name || 'Sin cliente' }}</strong>
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                                        Días disponibles
                                    </p>
                                    <p class="mt-1 text-3xl font-black text-white">
                                        {{ summary.max_discount_days }}
                                    </p>
                                    <p class="mt-1 text-xs text-white/60">
                                        Ya descontados: {{ summary.discounted_days }} día(s)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form class="p-6" @submit.prevent="submit">
                            <div class="grid gap-4 md:grid-cols-4">
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Préstamo
                                    </p>
                                    <p class="mt-1 text-xl font-black text-slate-950">
                                        {{ money(pawn.total) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-blue-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                        Días actuales
                                    </p>
                                    <p class="mt-1 text-xl font-black text-blue-700">
                                        {{ summary.days_to_pay }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-emerald-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                        Interés diario
                                    </p>
                                    <p class="mt-1 text-xl font-black text-emerald-600">
                                        {{ money(summary.daily_interest) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-orange-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-500">
                                        Interés actual
                                    </p>
                                    <p class="mt-1 text-xl font-black text-orange-600">
                                        {{ money(summary.interest_current) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-5 lg:grid-cols-[0.45fr_1fr]">
                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Días a descontar
                                    </label>

                                    <input
                                        v-model="form.days"
                                        type="number"
                                        min="1"
                                        :max="summary.max_discount_days"
                                        step="1"
                                        class="sicem-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-[#5b55a4] focus:outline-none focus:ring-4 focus:ring-violet-100"
                                        placeholder="Ej. 5"
                                    >

                                    <p class="mt-2 text-xs font-semibold text-slate-400">
                                        Máximo permitido: {{ summary.max_discount_days }} día(s).
                                    </p>

                                    <p v-if="form.errors.days" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.days }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Comentarios
                                    </label>

                                    <textarea
                                        v-model="form.comments"
                                        rows="4"
                                        class="sicem-input w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-[#5b55a4] focus:outline-none focus:ring-4 focus:ring-violet-100"
                                        placeholder="Ej. Autorizado por gerencia..."
                                    />

                                    <p v-if="form.errors.comments" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.comments }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-4">
                                <div class="rounded-2xl bg-rose-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-500">
                                        Días descontados
                                    </p>
                                    <p class="mt-1 text-xl font-black text-rose-600">
                                        {{ safeDays }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-emerald-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                        Descuento
                                    </p>
                                    <p class="mt-1 text-xl font-black text-emerald-600">
                                        -{{ money(discountAmount) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-blue-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                        Días finales
                                    </p>
                                    <p class="mt-1 text-xl font-black text-blue-700">
                                        {{ finalDays }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-violet-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                        Total final
                                    </p>
                                    <p class="mt-1 text-xl font-black text-[#5b55a4]">
                                        {{ money(finalTotal) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-700">
                                Este descuento quedará registrado como movimiento de auditoría. No aumenta ni disminuye el efectivo de caja.
                            </div>

                            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <Link
                                    :href="urls.show || route('pawns.show', pawn.id)"
                                    class="sicem-btn-neutral inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-black transition"
                                >
                                    Cancelar
                                </Link>

                                <button
                                    type="submit"
                                    class="sicem-btn-primary inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black transition"
                                    :disabled="!canSubmit"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                        <path :d="iconPath('check')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span v-if="form.processing">Aplicando...</span>
                                    <span v-else>Aplicar descuento</span>
                                </button>
                            </div>
                        </form>
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
                                    Fecha
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ pawn.created_at }}
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
                            Cliente
                        </h2>

                        <div class="mt-5 flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('user')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
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

.sicem-btn-primary:hover:not(:disabled) {
    background-color: #49438d !important;
    color: #ffffff !important;
}

.sicem-btn-primary:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
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
</style>