<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

const props = defineProps({
    transactions: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    options: {
        type: Object,
        default: () => ({
            types: [],
            paymentTypes: [],
            statuses: [],
        }),
    },
})

const page = usePage()

const form = reactive({
    search: props.filters.search || '',
    type: props.filters.type || '',
    payment_type: props.filters.payment_type || '',
    status: props.filters.status || 'active',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    per_page: props.filters.per_page || 15,
})

const activeOffice = computed(() => {
    return page.props.activeOffice || null
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const submitFilters = () => {
    router.get(route('transactions.index'), cleanFilters(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const clearFilters = () => {
    form.search = ''
    form.type = ''
    form.payment_type = ''
    form.status = 'active'
    form.date_from = ''
    form.date_to = ''
    form.per_page = 15

    router.get(route('transactions.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const cleanFilters = () => {
    return Object.fromEntries(
        Object.entries(form).filter(([_, value]) => value !== '' && value !== null && value !== undefined),
    )
}

const amountClass = (amount, isCancelled = false) => {
    if (isCancelled) {
        return 'text-slate-400 line-through'
    }

    return Number(amount || 0) >= 0
        ? 'text-emerald-600'
        : 'text-red-600'
}

const statusBadgeClass = (transaction) => {
    if (transaction.is_cancelled) {
        return 'border-red-200 bg-red-50 text-red-700'
    }

    return 'border-emerald-200 bg-emerald-50 text-emerald-700'
}

const paymentBadgeClass = (paymentType) => {
    return paymentType === 'cash'
        ? 'border-cyan-200 bg-cyan-50 text-cyan-700'
        : 'border-violet-200 bg-violet-50 text-violet-700'
}

const typeBadgeClass = (type) => {
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

    return classes[type] || 'border-slate-200 bg-slate-50 text-slate-700'
}

const iconPath = (icon) => {
    const icons = {
        search: 'M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z',
        receipt: 'M7 3h10a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 0 1 2-2ZM8 8h8M8 12h8M8 16h5',
        filter: 'M4 5h16M7 12h10M10 19h4',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        empty: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
    }

    return icons[icon] || icons.receipt
}

const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:bg-white focus:ring-4 focus:ring-violet-100'
const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:bg-white focus:ring-4 focus:ring-violet-100'
</script>

<template>
    <Head title="Transacciones" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Caja
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Transacciones
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Consulta los movimientos de caja registrados en
                        <strong class="font-black text-slate-700">
                            {{ activeOffice?.name || 'la sucursal activa' }}
                        </strong>.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Registros
                    </p>
                    <p class="mt-1 text-2xl font-black text-slate-950">
                        {{ transactions.total || 0 }}
                    </p>
                </div>
            </div>

            <section class="mb-6 rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <form class="p-5 sm:p-6" @submit.prevent="submitFilters">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path
                                    :d="iconPath('filter')"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-black text-slate-950">
                                Filtros
                            </h2>
                            <p class="text-sm text-slate-500">
                                Busca por folio, cliente, tipo, monto o comentario.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                        <div class="xl:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Buscar
                            </label>

                            <div class="relative">
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath('search')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    />
                                </svg>

                                <input
                                    v-model="form.search"
                                    type="search"
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:bg-white focus:ring-4 focus:ring-violet-100"
                                    placeholder="ID, cliente, comentario..."
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Tipo
                            </label>

                            <select
                                v-model="form.type"
                                :class="selectClass"
                            >
                                <option class="bg-white text-slate-900" value="">Todos</option>
                                <option
                                    v-for="type in options.types"
                                    :key="type.value"
                                    :value="type.value"
                                    class="bg-white text-slate-900"
                                >
                                    {{ type.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Pago
                            </label>

                            <select
                                v-model="form.payment_type"
                                :class="selectClass"
                            >
                                <option class="bg-white text-slate-900" value="">Todos</option>
                                <option
                                    v-for="paymentType in options.paymentTypes"
                                    :key="paymentType.value"
                                    :value="paymentType.value"
                                    class="bg-white text-slate-900"
                                >
                                    {{ paymentType.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Estado
                            </label>

                            <select
                                v-model="form.status"
                                :class="selectClass"
                            >
                                <option
                                    v-for="status in options.statuses"
                                    :key="status.value"
                                    :value="status.value"
                                    class="bg-white text-slate-900"
                                >
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Por página
                            </label>

                            <select
                                v-model="form.per_page"
                                :class="selectClass"
                            >
                                <option class="bg-white text-slate-900" :value="10">10</option>
                                <option class="bg-white text-slate-900" :value="15">15</option>
                                <option class="bg-white text-slate-900" :value="25">25</option>
                                <option class="bg-white text-slate-900" :value="50">50</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Desde
                            </label>

                            <input
                                v-model="form.date_from"
                                type="date"
                                :class="inputClass"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Hasta
                            </label>

                            <input
                                v-model="form.date_to"
                                type="date"
                                :class="inputClass"
                            />
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            @click="clearFilters"
                        >
                            Limpiar
                        </button>

                        <button
                            type="submit"
                            class="rounded-2xl bg-[#5b55a4] px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5 hover:bg-[#4f4896]"
                        >
                            Aplicar filtros
                        </button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-950">
                                Movimientos registrados
                            </h2>
                            <p class="text-sm text-slate-500">
                                Historial de transacciones de la sucursal activa.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="transactions.data?.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Movimiento
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Detalle
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Pago
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Monto
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Saldo
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Estado
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Acción
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr
                                v-for="transaction in transactions.data"
                                :key="transaction.id"
                                class="transition hover:bg-slate-50/80"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-950 text-white">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
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
                                            <p class="text-sm font-black text-slate-950">
                                                #{{ transaction.id }}
                                            </p>
                                            <p class="text-xs font-semibold text-slate-500">
                                                {{ transaction.created_at }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="max-w-sm">
                                        <span
                                            class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                            :class="typeBadgeClass(transaction.type)"
                                        >
                                            {{ transaction.type_label }}
                                        </span>

                                        <p class="mt-2 line-clamp-2 text-sm font-medium text-slate-600">
                                            {{ transaction.comments || 'Sin comentarios' }}
                                        </p>

                                        <p v-if="transaction.pawn" class="mt-1 text-xs font-bold text-slate-400">
                                            Empeño #{{ transaction.pawn.folio }}
                                            <span v-if="transaction.pawn.customer">
                                                · {{ transaction.pawn.customer }}
                                            </span>
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                        :class="paymentBadgeClass(transaction.payment_type)"
                                    >
                                        {{ transaction.payment_type_label }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <p class="text-base font-black" :class="amountClass(transaction.amount, transaction.is_cancelled)">
                                        {{ money(transaction.amount) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <p class="text-sm font-black text-slate-700">
                                        {{ money(transaction.balance) }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                        :class="statusBadgeClass(transaction)"
                                    >
                                        {{ transaction.is_cancelled ? 'Cancelada' : 'Activa' }}
                                    </span>

                                    <p v-if="transaction.canceled_at" class="mt-1 text-xs text-slate-400">
                                        {{ transaction.canceled_at }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="route('transactions.show', transaction.id)"
                                        class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-950 hover:text-white"
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
                        No hay transacciones
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Cuando registres movimientos de caja aparecerán en esta sección.
                    </p>
                </div>

                <div
                    v-if="transactions.links?.length"
                    class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <p class="text-sm font-semibold text-slate-500">
                        Mostrando {{ transactions.from || 0 }} a {{ transactions.to || 0 }} de {{ transactions.total || 0 }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="link in transactions.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            preserve-scroll
                            preserve-state
                            class="rounded-xl border px-3 py-2 text-sm font-black transition"
                            :class="[
                                link.active
                                    ? 'border-[#5b55a4] bg-[#5b55a4] text-white'
                                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                                !link.url ? 'pointer-events-none opacity-40' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>