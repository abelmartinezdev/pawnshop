<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    office: {
        type: Object,
        required: true,
    },
    paymentTypes: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    amount: '',
    payment_type: 'cash',
    comments: '',
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const submit = () => {
    form.post(route('expenses.store'), {
        preserveScroll: true,
    })
}

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:bg-white focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:bg-white focus:ring-4 focus:ring-violet-100'

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        expense: 'M12 21V9M7 14l5-5 5 5M4 3h16M5 7h14',
        wallet: 'M4 7h15a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4V7ZM4 7V5a2 2 0 0 1 2-2h11v4M17 14h.01',
    }

    return icons[icon] || icons.expense
}
</script>

<template>
    <Head title="Nuevo gasto" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7">
                <Link
                    :href="route('expenses.index')"
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
                    Nuevo gasto
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Registra una salida manual de dinero para la sucursal activa.
                </p>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.7fr_1.3fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="bg-red-500 px-6 py-6 text-white">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                                        <path
                                            :d="iconPath('wallet')"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-white/70">
                                        Caja actual
                                    </p>

                                    <p class="mt-1 text-3xl font-black">
                                        {{ money(office.cash) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Sucursal
                            </p>
                            <p class="mt-1 text-lg font-black text-slate-950">
                                {{ office.name }}
                            </p>

                            <p class="mt-4 text-sm leading-6 text-slate-500">
                                Si el gasto es en efectivo, disminuirá la caja física de la sucursal.
                                Si es tarjeta, quedará registrado como transacción pero no modificará el efectivo disponible.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path
                                    :d="iconPath('expense')"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-black text-slate-950">
                                Datos del gasto
                            </h2>
                            <p class="text-sm text-slate-500">
                                Captura el monto y motivo del gasto.
                            </p>
                        </div>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Monto
                            </label>

                            <input
                                v-model="form.amount"
                                type="number"
                                min="0"
                                step="0.01"
                                :class="inputClass"
                                placeholder="0.00"
                            />

                            <p v-if="form.errors.amount" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.amount }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Tipo de pago
                            </label>

                            <select v-model="form.payment_type" :class="selectClass">
                                <option
                                    v-for="paymentType in paymentTypes"
                                    :key="paymentType.value"
                                    :value="paymentType.value"
                                    class="bg-white text-slate-900"
                                >
                                    {{ paymentType.label }}
                                </option>
                            </select>

                            <p v-if="form.errors.payment_type" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.payment_type }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Motivo del gasto
                            </label>

                            <textarea
                                v-model="form.comments"
                                rows="5"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:bg-white focus:ring-4 focus:ring-violet-100"
                                placeholder="Ej. Pago de servicio, compra de insumos, ajuste de caja..."
                            />

                            <p v-if="form.errors.comments" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.comments }}
                            </p>
                        </div>

                        <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                            <Link
                                :href="route('expenses.index')"
                                class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                Cancelar
                            </Link>

                            <button
                                type="submit"
                                class="rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-red-200 transition hover:-translate-y-0.5 hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Guardando...' : 'Registrar gasto' }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>