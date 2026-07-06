<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    income: {
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

const formattedData = computed(() => {
    if (!props.income.data) {
        return null
    }

    if (typeof props.income.data === 'string') {
        return props.income.data
    }

    return JSON.stringify(props.income.data, null, 2)
})

const submitCancel = () => {
    cancelForm.post(route('incomes.cancel', props.income.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false
            cancelForm.reset()
        },
    })
}

const paymentBadgeClass = computed(() => {
    return props.income.payment_type === 'cash'
        ? 'border-cyan-200 bg-cyan-50 text-cyan-700'
        : 'border-violet-200 bg-violet-50 text-violet-700'
})

const statusBadgeClass = computed(() => {
    return props.income.is_cancelled
        ? 'border-red-200 bg-red-50 text-red-700'
        : 'border-emerald-200 bg-emerald-50 text-emerald-700'
})

const amountClass = computed(() => {
    return props.income.is_cancelled
        ? 'text-slate-300 line-through'
        : 'text-white'
})

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        income: 'M12 3v12M7 10l5 5 5-5M4 21h16M5 17h14',
        cancel: 'M18 6 6 18M6 6l12 12',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        building: 'M4 21V6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V21M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M9 21v-5h6v5',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        wallet: 'M4 7h15a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4V7ZM4 7V5a2 2 0 0 1 2-2h11v4M17 14h.01',
    }

    return icons[icon] || icons.income
}
</script>

<template>
    <Head :title="`Ingreso #${income.id}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="route('incomes.index')"
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
                        Ingreso #{{ income.id }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Detalle del ingreso manual registrado.
                    </p>
                </div>

                <button
                    v-if="!income.is_cancelled"
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
                    Cancelar ingreso
                </button>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="bg-emerald-500 px-6 py-6 text-white">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                                        <path
                                            :d="iconPath('income')"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-white/70">
                                        Monto del ingreso
                                    </p>

                                    <p class="mt-1 text-3xl font-black" :class="amountClass">
                                        {{ money(income.amount) }}
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
                                    {{ money(income.balance) }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                    :class="paymentBadgeClass"
                                >
                                    {{ income.payment_type_label }}
                                </span>

                                <span
                                    class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                    :class="statusBadgeClass"
                                >
                                    {{ income.is_cancelled ? 'Cancelado' : 'Activo' }}
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
                                        {{ income.created_at }}
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
                                        {{ income.user?.name || 'No especificado' }}
                                    </p>
                                    <p v-if="income.user?.email" class="text-xs text-slate-500">
                                        {{ income.user.email }}
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
                                        {{ income.office?.name || 'No especificada' }}
                                    </p>
                                    <p v-if="income.office?.company" class="text-xs text-slate-500">
                                        {{ income.office.company }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Detalle del ingreso
                        </h2>

                        <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Comentarios / motivo
                            </p>
                            <p class="mt-2 text-sm leading-6 text-slate-700">
                                {{ income.comments || 'Sin comentarios registrados.' }}
                            </p>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Método de pago
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ income.payment_type_label }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Caja de sucursal
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ money(income.office?.cash) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="income.is_cancelled" class="rounded-[2rem] border border-red-200 bg-red-50 p-6 shadow-sm">
                        <h2 class="text-lg font-black text-red-700">
                            Ingreso cancelado
                        </h2>

                        <div class="mt-4 rounded-2xl bg-white/70 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-red-400">
                                Fecha de cancelación
                            </p>
                            <p class="mt-1 text-sm font-black text-red-800">
                                {{ income.canceled_at }}
                            </p>
                        </div>

                        <div class="mt-4 rounded-2xl bg-white/70 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-red-400">
                                Motivo
                            </p>
                            <p class="mt-2 text-sm leading-6 text-red-800">
                                {{ income.comments_cancel || 'Sin motivo registrado.' }}
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
                        Cancelar ingreso
                    </h2>
                    <p class="mt-1 text-sm text-red-50">
                        Si fue en efectivo, se descontará el monto de la caja actual.
                    </p>
                </div>

                <form class="p-6" @submit.prevent="submitCancel">
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm leading-6 text-red-700">
                        Estás por cancelar el ingreso
                        <strong>#{{ income.id }}</strong>
                        por
                        <strong>{{ money(income.amount) }}</strong>.
                    </div>

                    <div class="mt-5">
                        <label class="mb-2 block text-sm font-black text-slate-700">
                            Motivo de cancelación
                        </label>

                        <textarea
                            v-model="cancelForm.comments_cancel"
                            rows="4"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-red-400 focus:bg-white focus:ring-4 focus:ring-red-100"
                            placeholder="Describe por qué se cancela este ingreso..."
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
                            {{ cancelForm.processing ? 'Cancelando...' : 'Sí, cancelar ingreso' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>