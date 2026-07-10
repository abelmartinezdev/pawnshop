<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    pawn: {
        type: Object,
        required: true,
    },
    url: {
        type: String,
        default: null,
    },
    paymentTypes: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['close'])

const form = useForm({
    discount: 0,
    amount_paid: '',
    payment_type: 'cash',
})

const principal = computed(() => Number(props.pawn.discount_preview?.principal || props.pawn.total || 0))
const originalInterest = computed(() => Number(props.pawn.discount_preview?.interest_original || 0))
const originalTotal = computed(() => Number(props.pawn.discount_preview?.amount_original || 0))

const safeDiscount = computed(() => {
    const value = Number(form.discount || 0)

    if (value < 0) {
        return 0
    }

    if (value > 100) {
        return 100
    }

    return value
})

const discountAmount = computed(() => {
    return originalInterest.value * (safeDiscount.value / 100)
})

const discountedInterest = computed(() => {
    return Math.max(originalInterest.value - discountAmount.value, 0)
})

const totalToPay = computed(() => {
    return principal.value + discountedInterest.value
})

const amountPaid = computed(() => Number(form.amount_paid || 0))

const change = computed(() => {
    return Math.max(amountPaid.value - totalToPay.value, 0)
})

const canSubmit = computed(() => {
    return Boolean(props.url)
        && safeDiscount.value > 0
        && amountPaid.value >= totalToPay.value
        && Boolean(form.payment_type)
        && !form.processing
})

watch(
    () => props.show,
    (value) => {
        if (value) {
            form.clearErrors()
            form.discount = 0
            form.amount_paid = ''
            form.payment_type = 'cash'
            return
        }

        form.reset()
        form.clearErrors()
    }
)

watch(totalToPay, (value) => {
    if (!form.amount_paid || Number(form.amount_paid) < value) {
        form.amount_paid = value.toFixed(2)
    }
})

const close = () => {
    if (form.processing) {
        return
    }

    emit('close')
}

const submit = () => {
    if (!canSubmit.value) {
        return
    }

    form.discount = safeDiscount.value
    form.amount_paid = amountPaid.value.toFixed(2)

    form.post(props.url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close')
        },
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
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
                @click.self="close"
            >
                <Transition
                    appear
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-4 scale-95 opacity-0"
                    enter-to-class="translate-y-0 scale-100 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 scale-100 opacity-100"
                    leave-to-class="translate-y-4 scale-95 opacity-0"
                >
                    <div class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-slate-950/20">
                        <div class="bg-gradient-to-br from-violet-700 to-indigo-900 px-6 py-5 text-white">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-white/70">
                                Liquidación con descuento
                            </p>

                            <h2 class="mt-2 text-2xl font-black">
                                Aplicar descuento · Folio {{ pawn.folio }}
                            </h2>

                            <p class="mt-2 text-sm font-semibold text-white/75">
                                El descuento se aplica únicamente al interés. Al confirmar, el empeño quedará liquidado.
                            </p>
                        </div>

                        <form class="p-6" @submit.prevent="submit">
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Préstamo
                                    </p>
                                    <p class="mt-1 text-xl font-black text-slate-950">
                                        {{ money(principal) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-orange-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-orange-500">
                                        Interés original
                                    </p>
                                    <p class="mt-1 text-xl font-black text-orange-600">
                                        {{ money(originalInterest) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-violet-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                        Total original
                                    </p>
                                    <p class="mt-1 text-xl font-black text-[#5b55a4]">
                                        {{ money(originalTotal) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        % descuento a interés
                                    </label>

                                    <input
                                        v-model="form.discount"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        class="sicem-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-[#5b55a4] focus:outline-none focus:ring-4 focus:ring-violet-100"
                                    >

                                    <p v-if="form.errors.discount" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.discount }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Tipo de pago
                                    </label>

                                    <select
                                        v-model="form.payment_type"
                                        class="sicem-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-[#5b55a4] focus:outline-none focus:ring-4 focus:ring-violet-100"
                                    >
                                        <option
                                            v-for="type in paymentTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </option>
                                    </select>

                                    <p v-if="form.errors.payment_type" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.payment_type }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Monto recibido
                                    </label>

                                    <input
                                        v-model="form.amount_paid"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="sicem-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-[#5b55a4] focus:outline-none focus:ring-4 focus:ring-violet-100"
                                    >

                                    <p v-if="form.errors.amount_paid" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.amount_paid }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-4">
                                <div class="rounded-2xl bg-emerald-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                        Descuento
                                    </p>
                                    <p class="mt-1 text-lg font-black text-emerald-600">
                                        -{{ money(discountAmount) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-blue-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                        Interés final
                                    </p>
                                    <p class="mt-1 text-lg font-black text-blue-700">
                                        {{ money(discountedInterest) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-violet-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                        Total a pagar
                                    </p>
                                    <p class="mt-1 text-lg font-black text-[#5b55a4]">
                                        {{ money(totalToPay) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Cambio
                                    </p>
                                    <p class="mt-1 text-lg font-black text-slate-950">
                                        {{ money(change) }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-700">
                                Esta acción liquidará el empeño con descuento. El descuento quedará guardado en la transacción de liquidación.
                            </div>

                            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    class="sicem-btn-neutral rounded-2xl px-5 py-3 text-sm font-black transition"
                                    :disabled="form.processing"
                                    @click="close"
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="submit"
                                    class="sicem-btn-primary rounded-2xl px-5 py-3 text-sm font-black transition"
                                    :disabled="!canSubmit"
                                >
                                    <span v-if="form.processing">Aplicando...</span>
                                    <span v-else>Liquidar con descuento</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
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

.sicem-btn-neutral:hover:not(:disabled) {
    background-color: #f8fafc !important;
    color: #5b55a4 !important;
}

.sicem-input {
    color-scheme: light !important;
}
</style>