<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    transaction: {
        type: Object,
        required: true,
    },
})

const showCancelModal = ref(false)

const cancelForm = useForm({
    comments_cancel: '',
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const amountClass = computed(() => {
    if (props.transaction.is_cancelled) {
        return 'text-slate-400 line-through'
    }

    return Number(props.transaction.amount || 0) >= 0
        ? 'text-emerald-600'
        : 'text-red-600'
})

const formattedData = computed(() => {
    if (!props.transaction.data) {
        return null
    }

    if (typeof props.transaction.data === 'string') {
        return props.transaction.data
    }

    return JSON.stringify(props.transaction.data, null, 2)
})

const submitCancel = () => {
    cancelForm.post(route('transactions.cancel', props.transaction.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false
            cancelForm.reset()
        },
    })
}

const statusBadgeClass = computed(() => {
    return props.transaction.is_cancelled
        ? 'border-red-200 bg-red-50 text-red-700'
        : 'border-emerald-200 bg-emerald-50 text-emerald-700'
})

const paymentBadgeClass = computed(() => {
    return props.transaction.payment_type === 'cash'
        ? 'border-cyan-200 bg-cyan-50 text-cyan-700'
        : 'border-violet-200 bg-violet-50 text-violet-700'
})

const typeBadgeClass = computed(() => {
    const classes = {
        pawn: 'border-red-200 bg-red-50 text-red-700',
        countersign: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        liquidation: 'border-blue-200 bg-blue-50 text-blue-700',
        payment: 'border-amber-200 bg-amber-50 text-amber-700',
        payment_to_interest: 'border-orange-200 bg-orange-50 text-orange-700',
        manual_income: 'border-cyan-200 bg-cyan-50 text-cyan-700',
        manual_expense: 'border-rose-200 bg-rose-50 text-rose-700',
        sale: 'border-indigo-200 bg-indigo-50 text-indigo-700',
        aside: 'border-purple-200 bg-purple-50 text-purple-700',
        aside_payment: 'border-fuchsia-200 bg-fuchsia-50 text-fuchsia-700',
        adjustment: 'border-slate-200 bg-slate-50 text-slate-700',
    }

    return classes[props.transaction.type] || 'border-slate-200 bg-slate-50 text-slate-700'
})

const iconPath = (icon) => {
    const icons = {
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        cancel: 'M18 6 6 18M6 6l12 12',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        building: 'M4 21V6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V21M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M9 21v-5h6v5',
        gem: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        wallet: 'M4 7h15a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4V7ZM4 7V5a2 2 0 0 1 2-2h11v4M17 14h.01',
        info: 'M12 17v-6M12 7h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    }

    return icons[icon] || icons.info
}
</script>

<template>
    <Head :title="`Transacción #${transaction.id}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="route('transactions.index')"
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
                        Transacción #{{ transaction.id }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Detalle completo del movimiento registrado en caja.
                    </p>
                </div>

                <button
                    v-if="!transaction.is_cancelled"
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-red-200 transition hover:-translate-y-0.5 hover:bg-red-600"
                    @click="showCancelModal = true"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path
                            :d="iconPath('cancel')"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                    Cancelar transacción
                </button>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="bg-[#5b55a4] px-6 py-6 text-white">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                                        <path
                                            :d="iconPath('receipt')"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-white/70">
                                        Monto
                                    </p>

                                    <p class="mt-1 text-3xl font-black">
                                        {{ money(transaction.amount) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 p-6">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Saldo después del movimiento
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ money(transaction.balance) }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                    :class="typeBadgeClass"
                                >
                                    {{ transaction.type_label }}
                                </span>

                                <span
                                    class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                    :class="paymentBadgeClass"
                                >
                                    {{ transaction.payment_type_label }}
                                </span>

                                <span
                                    class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                    :class="statusBadgeClass"
                                >
                                    {{ transaction.is_cancelled ? 'Cancelada' : 'Activa' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-5 text-lg font-black text-slate-950">
                            Información general
                        </h2>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
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
                                        Fecha
                                    </p>
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ transaction.created_at }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
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
                                        Usuario
                                    </p>
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ transaction.user?.name || 'No especificado' }}
                                    </p>
                                    <p v-if="transaction.user?.email" class="text-xs text-slate-500">
                                        {{ transaction.user.email }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-slate-600">
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
                                    <p class="mt-1 text-sm font-black text-slate-800">
                                        {{ transaction.office?.name || 'No especificada' }}
                                    </p>
                                    <p v-if="transaction.office?.company" class="text-xs text-slate-500">
                                        {{ transaction.office.company }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Detalle del movimiento
                        </h2>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Tipo
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ transaction.type_label }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Método de pago
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ transaction.payment_type_label }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Descuento
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ transaction.discount_amount !== null ? money(transaction.discount_amount) : 'Sin descuento' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Tasa descuento
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ transaction.discount_rate !== null ? `${transaction.discount_rate}%` : 'No aplica' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Comentarios
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                {{ transaction.comments || 'Sin comentarios registrados.' }}
                            </p>
                        </div>
                    </div>

                    <div v-if="transaction.pawn" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('gem')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Empeño relacionado
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Información vinculada al movimiento.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Folio
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    #{{ transaction.pawn.folio }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Total
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ money(transaction.pawn.total) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 md:col-span-2">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Cliente
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ transaction.pawn.customer?.name || 'Sin cliente' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ transaction.pawn.customer?.mobile || transaction.pawn.customer?.phone || 'Sin teléfono' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="transaction.is_cancelled" class="rounded-[2rem] border border-red-200 bg-red-50 p-6 shadow-sm">
                        <h2 class="text-lg font-black text-red-700">
                            Transacción cancelada
                        </h2>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-white/70 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-red-400">
                                    Fecha de cancelación
                                </p>
                                <p class="mt-1 text-sm font-black text-red-800">
                                    {{ transaction.canceled_at }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white/70 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-red-400">
                                    Estado
                                </p>
                                <p class="mt-1 text-sm font-black text-red-800">
                                    Cancelada
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl bg-white/70 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-red-400">
                                Motivo
                            </p>
                            <p class="mt-2 text-sm leading-6 text-red-800">
                                {{ transaction.comments_cancel || 'Sin motivo registrado.' }}
                            </p>
                        </div>
                    </div>

                    <div v-if="formattedData" class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Datos técnicos
                        </h2>

                        <pre class="mt-4 max-h-96 overflow-auto rounded-2xl bg-slate-950 p-5 text-xs leading-6 text-slate-100">{{ formattedData }}</pre>
                    </div>
                </section>
            </div>
        </div>

        <div
            v-if="showCancelModal"
            class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/60 px-4 backdrop-blur-sm"
        >
            <div class="w-full max-w-lg overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="bg-red-500 px-6 py-5 text-white">
                    <h2 class="text-xl font-black">
                        Cancelar transacción
                    </h2>
                    <p class="mt-1 text-sm text-red-50">
                        Esta acción ajustará la caja si el movimiento fue en efectivo.
                    </p>
                </div>

                <form class="p-6" @submit.prevent="submitCancel">
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-700">
                        Estás por cancelar la transacción
                        <strong>#{{ transaction.id }}</strong>
                        por
                        <strong>{{ money(transaction.amount) }}</strong>.
                    </div>

                    <div class="mt-5">
                        <label class="mb-2 block text-sm font-black text-slate-700">
                            Motivo de cancelación
                        </label>

                        <textarea
                            v-model="cancelForm.comments_cancel"
                            rows="4"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold outline-none transition focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                            placeholder="Describe por qué se cancela esta transacción..."
                        />

                        <p v-if="cancelForm.errors.comments_cancel" class="mt-2 text-sm font-bold text-red-600">
                            {{ cancelForm.errors.comments_cancel }}
                        </p>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            @click="showCancelModal = false"
                        >
                            No cancelar
                        </button>

                        <button
                            type="submit"
                            class="rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-red-200 transition hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="cancelForm.processing"
                        >
                            {{ cancelForm.processing ? 'Cancelando...' : 'Sí, cancelar transacción' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>