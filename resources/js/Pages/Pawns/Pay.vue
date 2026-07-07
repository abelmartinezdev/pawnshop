<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    pawn: {
        type: Object,
        required: true,
    },
    payment: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({
            transactions: [],
            payment_types: [],
        }),
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const showConfirmModal = ref(false)
const showErrorModal = ref(false)
const errorMessage = ref('')

const form = useForm({
    transaction: 'liquidation',
    discount: 0,
    pay_extra: 0,
    payment: 0,
    amount_due: 0,
    amount_paid: 0,
    payment_type: 'cash',
    change: 0,
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const interestAfterDiscount = computed(() => {
    const discount = Number(form.discount || 0)

    if (discount <= 0 || form.transaction !== 'liquidation') {
        return Number(props.payment.interest_to_pay || 0)
    }

    if (discount > 100) {
        return Number(props.payment.interest_to_pay || 0)
    }

    return Number(props.payment.interest_to_pay || 0) * (1 - (discount / 100))
})

const discountAmount = computed(() => {
    return Math.max(Number(props.payment.interest_to_pay || 0) - interestAfterDiscount.value, 0)
})

const liquidationDue = computed(() => {
    return Number(props.pawn.total || 0) + interestAfterDiscount.value
})

const countersignDue = computed(() => {
    return Number(props.payment.interest_to_pay || 0) + Number(form.pay_extra || 0)
})

const interestPaymentDue = computed(() => {
    return Number(form.payment || 0)
})

const selectedDue = computed(() => {
    if (form.transaction === 'countersign') {
        return countersignDue.value
    }

    if (form.transaction === 'interest_payment') {
        return interestPaymentDue.value
    }

    return liquidationDue.value
})

const change = computed(() => {
    return Math.max(Number(form.amount_paid || 0) - Number(selectedDue.value || 0), 0)
})

const transactionDescription = computed(() => {
    return props.options.transactions.find((item) => item.value === form.transaction)?.description || ''
})

const selectedTransactionLabel = computed(() => {
    return props.options.transactions.find((item) => item.value === form.transaction)?.label || 'Transacción'
})

const showDiscount = computed(() => {
    return form.transaction === 'liquidation' && props.payment.can_apply_discount
})

const showPayExtra = computed(() => {
    return form.transaction === 'countersign'
})

const showInterestPayment = computed(() => {
    return form.transaction === 'interest_payment'
})

const showReadonlyAmount = computed(() => {
    return form.transaction === 'liquidation' || form.transaction === 'countersign'
})

const canOpenPayment = computed(() => {
    return selectedDue.value > 0 && !form.processing
})

const canConfirmPayment = computed(() => {
    return Number(form.amount_paid || 0) >= Number(selectedDue.value || 0) && selectedDue.value > 0 && !form.processing
})

const validateOperation = () => {
    if (form.transaction === 'countersign') {
        const payExtra = Number(form.pay_extra || 0)

        if (payExtra < 0 || payExtra >= Number(props.pawn.total || 0)) {
            openError('El abono a capital es incorrecto. Debe ser menor al préstamo original.')
            return false
        }
    }

    if (form.transaction === 'interest_payment') {
        const payment = Number(form.payment || 0)
        const interest = Number(props.payment.interest_to_pay || 0)

        if (payment <= 0 || payment >= interest) {
            openError('La cantidad de abono es incorrecta. Debe ser mayor a cero y menor al interés total.')
            return false
        }
    }

    if (selectedDue.value <= 0) {
        openError('El total a pagar debe ser mayor a cero.')
        return false
    }

    return true
}

const openPaymentModal = () => {
    if (!validateOperation()) {
        return
    }

    form.amount_due = roundMoney(selectedDue.value)
    form.amount_paid = roundMoney(selectedDue.value)
    form.change = 0

    showConfirmModal.value = true
}

const submit = () => {
    if (!canConfirmPayment.value) {
        openError('El pago recibido no cubre el total.')
        return
    }

    form.amount_due = roundMoney(selectedDue.value)
    form.change = roundMoney(change.value)

    form.post(props.urls.pay || route('pawns.pay', props.pawn.id), {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmModal.value = false
        },
    })
}

const openError = (message) => {
    errorMessage.value = message
    showErrorModal.value = true
}

const resetDynamicFields = () => {
    form.discount = 0
    form.pay_extra = 0
    form.payment = 0
    form.amount_due = 0
    form.amount_paid = 0
    form.change = 0
}

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const roundMoney = (value) => {
    return Math.round(Number(value || 0) * 100) / 100
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        x: 'M18 6 6 18M6 6l12 12',
        check: 'M20 6 9 17l-5-5',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        refresh: 'M21 12a9 9 0 1 1-3-6.7M21 4v6h-6',
        clock: 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    }

    return icons[icon] || icons.cash
}
</script>

<template>
    <Head :title="`Pago ${pawn.folio}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-5xl">
                <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50 px-6 py-5">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.22em] text-[#5b55a4]">
                                Pago de empeño
                            </p>

                            <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-950">
                                Folio {{ pawn.folio }}
                            </h1>
                        </div>

                        <Link
                            :href="urls.show || route('pawns.show', pawn.id)"
                            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Salir
                        </Link>
                    </div>

                    <div class="p-6">
                        <div class="mb-6 rounded-[1.75rem] border-l-4 border-[#5b55a4] bg-slate-50 p-6">
                            <p class="text-lg font-black uppercase leading-8 text-slate-900">
                                {{ pawn.customer?.name || 'Cliente no especificado' }}
                            </p>

                            <p class="mt-1 text-base font-semibold uppercase leading-7 text-slate-700">
                                Préstamo de <span class="font-black text-slate-950">{{ money(pawn.total) }}</span><br>
                                Efectuado el {{ pawn.created_at_long || pawn.created_at }}<br>
                                --
                            </p>

                            <p
                                v-if="pawn.inapam_rate"
                                class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-700"
                            >
                                Tiene descuento de INAPAM del {{ Number(pawn.inapam_rate * 100).toFixed(2) }}%.
                            </p>

                            <p class="mt-3 text-base font-semibold uppercase leading-7 text-slate-700">
                                Son {{ payment.days_to_pay }} días, con interés diario de
                                <span class="font-black text-slate-950">{{ money(payment.daily_interest) }}</span><br>
                                Total de interés a pagar
                                <span class="font-black text-slate-950">{{ money(payment.interest_to_pay) }}</span><br>
                                --
                            </p>

                            <div class="mt-5">
                                <p class="text-sm font-black uppercase tracking-[0.18em] text-slate-400">
                                    Se debe un total de
                                </p>
                                <p class="mt-1 text-4xl font-black text-slate-950">
                                    {{ money(payment.amount_to_liquidate) }}
                                </p>
                            </div>
                        </div>

                        <form @submit.prevent="openPaymentModal">
                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Transacción
                                    </label>

                                    <select
                                        v-model="form.transaction"
                                        :class="selectClass"
                                        @change="resetDynamicFields"
                                    >
                                        <option
                                            v-for="transaction in options.transactions"
                                            :key="transaction.value"
                                            class="bg-white text-slate-900"
                                            :value="transaction.value"
                                        >
                                            {{ transaction.label }}
                                        </option>
                                    </select>

                                    <p class="mt-2 text-xs font-semibold text-slate-400">
                                        {{ transactionDescription }}
                                    </p>
                                </div>

                                <div v-if="showDiscount">
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        % descuento a interés
                                    </label>
                                    <input
                                        v-model.number="form.discount"
                                        type="number"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        :class="inputClass"
                                    />
                                    <p class="mt-2 text-xs font-semibold text-emerald-600">
                                        Descuento: {{ money(discountAmount) }}
                                    </p>
                                </div>

                                <div v-if="showReadonlyAmount">
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Cantidad
                                    </label>
                                    <input
                                        :value="money(selectedDue)"
                                        type="text"
                                        :class="inputClass"
                                        readonly
                                    />
                                </div>

                                <div v-if="showPayExtra">
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Abono a capital
                                    </label>
                                    <input
                                        v-model.number="form.pay_extra"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        :class="inputClass"
                                    />
                                    <p class="mt-2 text-xs font-semibold text-slate-400">
                                        El nuevo préstamo será de {{ money(Number(pawn.total || 0) - Number(form.pay_extra || 0)) }}.
                                    </p>
                                </div>

                                <div v-if="showInterestPayment">
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Cantidad
                                    </label>
                                    <input
                                        v-model.number="form.payment"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        :class="inputClass"
                                    />
                                    <p class="mt-2 text-xs font-semibold text-slate-400">
                                        Debe ser menor a {{ money(payment.interest_to_pay) }}.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-3">
                                <div class="rounded-2xl bg-violet-50 p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                        Operación
                                    </p>
                                    <p class="mt-1 text-lg font-black text-[#5b55a4]">
                                        {{ selectedTransactionLabel }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-emerald-50 p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                        Total a pagar
                                    </p>
                                    <p class="mt-1 text-2xl font-black text-emerald-600">
                                        {{ money(selectedDue) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-5">
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                        Interés base
                                    </p>
                                    <p class="mt-1 text-lg font-black text-slate-950">
                                        {{ money(payment.interest_to_pay) }}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="submit"
                                class="sicem-btn-blue mt-6 flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-4 text-sm font-black uppercase shadow-lg shadow-blue-100 transition disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="!canOpenPayment"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('check')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Pagar
                            </button>
                        </form>
                    </div>
                </section>
            </div>
        </div>

        <div
            v-if="showConfirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-black text-slate-950">
                            Confirmar pago
                        </h2>
                        <p class="text-sm text-slate-500">
                            Captura el pago recibido y el tipo de pago.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="showConfirmModal = false"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('x')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 p-6">
                    <div class="sicem-total-box rounded-2xl p-5">
                        <p class="sicem-total-label text-xs font-black uppercase tracking-[0.22em]">
                            Debe
                        </p>
                        <p class="sicem-total-amount mt-2 text-4xl font-black">
                            {{ money(selectedDue) }}
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Pago
                            </label>
                            <input
                                v-model.number="form.amount_paid"
                                type="number"
                                min="0"
                                step="0.01"
                                :class="inputClass"
                            />
                            <p v-if="Number(form.amount_paid || 0) < selectedDue" class="mt-2 text-xs font-black text-red-600">
                                El pago no cubre el total.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Tipo de pago
                            </label>
                            <select v-model="form.payment_type" :class="selectClass">
                                <option
                                    v-for="paymentType in options.payment_types"
                                    :key="paymentType.value"
                                    class="bg-white text-slate-900"
                                    :value="paymentType.value"
                                >
                                    {{ paymentType.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Debe
                            </label>
                            <input :value="money(selectedDue)" type="text" :class="inputClass" readonly />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Cambio
                            </label>
                            <input :value="money(change)" type="text" :class="inputClass" readonly />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="showConfirmModal = false"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="sicem-btn-blue rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-blue-100 transition disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="!canConfirmPayment"
                        @click="submit"
                    >
                        {{ form.processing ? 'Registrando...' : 'Pagar' }}
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="showErrorModal"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-[2rem] bg-white p-8 text-center shadow-2xl">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-4 border-red-200 text-red-500">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('x')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </div>

                <h2 class="mt-6 text-2xl font-black text-slate-950">
                    ¡Error!
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    {{ errorMessage }}
                </p>

                <button
                    type="button"
                    class="sicem-btn-blue mt-6 rounded-2xl px-6 py-3 text-sm font-black shadow-lg shadow-blue-100 transition"
                    @click="showErrorModal = false"
                >
                    OK
                </button>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.sicem-btn-blue {
    background-color: #2563eb !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
}

.sicem-btn-blue:hover {
    background-color: #1d4ed8 !important;
    color: #ffffff !important;
}

.sicem-total-box {
    background-color: #202020 !important;
    color: #ffffff !important;
}

.sicem-total-label {
    color: #34d399 !important;
}

.sicem-total-amount {
    color: #34d399 !important;
}
</style>