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
    items: {
        type: Array,
        default: () => [],
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const form = useForm({
    confirmation: false,
})

const canSubmit = computed(() => {
    return Boolean(props.urls.store)
        && form.confirmation
        && !form.processing
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

const quantity = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    })
}

const modeLabel = (mode) => {
    return {
        sellable: 'Vendible',
        grouped: 'Agrupado',
        not_sell: 'No vendible',
    }[mode] || 'Vendible'
}

const modeClass = (mode) => {
    return {
        sellable: 'sicem-mode-sellable',
        grouped: 'sicem-mode-grouped',
        not_sell: 'sicem-mode-blocked',
    }[mode] || 'sicem-mode-sellable'
}

const submit = () => {
    if (!canSubmit.value) {
        return
    }

    form.post(props.urls.store, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head :title="`Mandar a remate - ${pawn.folio}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div
                    class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div>
                        <Link
                            v-if="urls.show"
                            :href="urls.show"
                            class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        >
                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M19 12H5M11 6l-6 6 6 6"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>

                            Volver al empeño
                        </Link>

                        <p
                            class="text-sm font-black uppercase tracking-[0.24em] text-amber-600"
                        >
                            Proceso de remate
                        </p>

                        <h1
                            class="mt-2 text-3xl font-black tracking-tight text-slate-950"
                        >
                            Mandar a remate · {{ pawn.folio }}
                        </h1>

                        <p class="mt-2 text-sm text-slate-500">
                            Revisa el cálculo y las prendas antes de
                            confirmar esta operación.
                        </p>
                    </div>

                    <div
                        class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-800"
                    >
                        <p
                            class="text-xs font-black uppercase tracking-[0.18em] text-amber-600"
                        >
                            Fecha programada
                        </p>

                        <p class="mt-1 text-lg font-black">
                            {{ pawn.date_auction || 'No especificada' }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-6 xl:grid-cols-[1fr_0.38fr]">
                    <section class="space-y-6">
                        <div
                            class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm"
                        >
                            <div class="sicem-auction-hero p-6 text-white">
                                <div
                                    class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                                >
                                    <div>
                                        <p
                                            class="text-xs font-black uppercase tracking-[0.2em] text-white/60"
                                        >
                                            Cliente
                                        </p>

                                        <p
                                            class="mt-2 text-2xl font-black text-white"
                                        >
                                            {{ pawn.customer }}
                                        </p>

                                        <p
                                            class="mt-2 text-sm font-semibold text-white/70"
                                        >
                                            {{ pawn.office }}
                                            · Empeñado el
                                            {{ pawn.created_at }}
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-2xl bg-white/10 px-5 py-4 backdrop-blur"
                                    >
                                        <p
                                            class="text-xs font-black uppercase tracking-[0.18em] text-white/60"
                                        >
                                            Total para remate
                                        </p>

                                        <p
                                            class="mt-1 text-3xl font-black text-white"
                                        >
                                            {{ money(summary.total) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4"
                            >
                                <div class="rounded-2xl bg-slate-50 p-4">
                                    <p
                                        class="text-xs font-black uppercase tracking-[0.16em] text-slate-400"
                                    >
                                        Préstamo
                                    </p>

                                    <p
                                        class="mt-1 text-xl font-black text-slate-950"
                                    >
                                        {{ money(summary.principal) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-emerald-50 p-4">
                                    <p
                                        class="text-xs font-black uppercase tracking-[0.16em] text-emerald-500"
                                    >
                                        Interés
                                    </p>

                                    <p
                                        class="mt-1 text-xl font-black text-emerald-700"
                                    >
                                        {{ money(summary.interest) }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-blue-50 p-4">
                                    <p
                                        class="text-xs font-black uppercase tracking-[0.16em] text-blue-500"
                                    >
                                        Días transcurridos
                                    </p>

                                    <p
                                        class="mt-1 text-xl font-black text-blue-700"
                                    >
                                        {{ summary.days_elapsed }}
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-amber-50 p-4">
                                    <p
                                        class="text-xs font-black uppercase tracking-[0.16em] text-amber-600"
                                    >
                                        Días cobrados
                                    </p>

                                    <p
                                        class="mt-1 text-xl font-black text-amber-700"
                                    >
                                        {{ summary.days_charged }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="summary.interest_is_capped"
                            class="rounded-[1.75rem] border border-blue-200 bg-blue-50 p-5 text-blue-800"
                        >
                            <div class="flex gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <path
                                        d="M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                                <div>
                                    <p class="font-black">
                                        Se aplicó el límite de 72 días.
                                    </p>

                                    <p class="mt-1 text-sm">
                                        Aunque han transcurrido
                                        {{ summary.days_elapsed }} días, el
                                        interés para remate se calculó sobre
                                        72 días.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm"
                        >
                            <div
                                class="border-b border-slate-100 px-6 py-5"
                            >
                                <h2
                                    class="text-lg font-black text-slate-950"
                                >
                                    Prendas que pasarán a remate
                                </h2>

                                <p class="text-sm text-slate-500">
                                    El préstamo y el interés están
                                    distribuidos proporcionalmente, sin
                                    duplicar importes.
                                </p>
                            </div>

                            <div class="overflow-x-auto">
                                <table
                                    class="min-w-full divide-y divide-slate-100"
                                >
                                    <thead class="bg-[#172331] text-white">
                                        <tr>
                                            <th
                                                class="px-5 py-4 text-left text-xs font-black uppercase"
                                            >
                                                Artículo
                                            </th>

                                            <th
                                                class="px-5 py-4 text-left text-xs font-black uppercase"
                                            >
                                                Modalidad
                                            </th>

                                            <th
                                                class="px-5 py-4 text-right text-xs font-black uppercase"
                                            >
                                                Cantidad
                                            </th>

                                            <th
                                                class="px-5 py-4 text-right text-xs font-black uppercase"
                                            >
                                                Préstamo
                                            </th>

                                            <th
                                                class="px-5 py-4 text-right text-xs font-black uppercase"
                                            >
                                                Interés
                                            </th>

                                            <th
                                                class="px-5 py-4 text-right text-xs font-black uppercase"
                                            >
                                                Total
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody
                                        class="divide-y divide-slate-100 bg-white"
                                    >
                                        <tr
                                            v-for="item in items"
                                            :key="`${item.pawn_item_id}-${item.unit_number}`"
                                        >
                                            <td class="px-5 py-4">
                                                <p
                                                    class="text-sm font-black text-slate-950"
                                                >
                                                    {{ item.product_name }}
                                                </p>

                                                <p
                                                    class="mt-1 text-xs font-semibold text-slate-400"
                                                >
                                                    {{
                                                        item.product_code
                                                        || 'Sin código'
                                                    }}

                                                    <span
                                                        v-if="item.unit_number > 1"
                                                    >
                                                        · Unidad
                                                        {{ item.unit_number }}
                                                    </span>
                                                </p>

                                                <p
                                                    class="mt-2 max-w-md text-xs leading-5 text-slate-500"
                                                >
                                                    {{ item.description }}
                                                </p>
                                            </td>

                                            <td class="px-5 py-4">
                                                <span
                                                    class="sicem-mode-pill"
                                                    :class="
                                                        modeClass(
                                                            item.auction_mode
                                                        )
                                                    "
                                                >
                                                    {{
                                                        modeLabel(
                                                            item.auction_mode
                                                        )
                                                    }}
                                                </span>
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-black text-slate-700"
                                            >
                                                {{
                                                    quantity(item.quantity)
                                                }}
                                                {{ item.unit || '' }}
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-black text-slate-700"
                                            >
                                                {{ money(item.value) }}
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-black text-emerald-600"
                                            >
                                                {{
                                                    money(
                                                        item.interest_amount
                                                    )
                                                }}
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-black text-slate-950"
                                            >
                                                {{ money(item.total) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <aside class="space-y-6">
                        <div
                            class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <h2
                                class="text-lg font-black text-slate-950"
                            >
                                Resumen del cálculo
                            </h2>

                            <div class="mt-5 space-y-3">
                                <div
                                    class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"
                                >
                                    <span
                                        class="text-sm font-bold text-slate-500"
                                    >
                                        Interés diario
                                    </span>

                                    <span
                                        class="text-sm font-black text-slate-900"
                                    >
                                        {{
                                            money(
                                                summary.daily_interest
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3"
                                >
                                    <span
                                        class="text-sm font-bold text-slate-500"
                                    >
                                        Interés bruto
                                    </span>

                                    <span
                                        class="text-sm font-black text-slate-900"
                                    >
                                        {{
                                            money(
                                                summary.gross_interest
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between rounded-2xl bg-violet-50 px-4 py-3"
                                >
                                    <span
                                        class="text-sm font-bold text-[#5b55a4]"
                                    >
                                        Descuentos
                                    </span>

                                    <span
                                        class="text-sm font-black text-[#5b55a4]"
                                    >
                                        -{{
                                            money(
                                                summary.discount_amount
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="flex items-center justify-between rounded-2xl bg-blue-50 px-4 py-3"
                                >
                                    <span
                                        class="text-sm font-bold text-blue-600"
                                    >
                                        Abonos a interés
                                    </span>

                                    <span
                                        class="text-sm font-black text-blue-700"
                                    >
                                        -{{
                                            money(
                                                summary.paid_amount
                                            )
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <form
                            class="rounded-[2rem] border border-amber-200 bg-white p-6 shadow-sm"
                            @submit.prevent="submit"
                        >
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700"
                            >
                                <svg
                                    class="h-6 w-6"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <path
                                        d="M14 5l5 5M4 20l8-8M12 3l9 9-3 3-9-9 3-3ZM6 18l-2 2"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <h2
                                class="mt-5 text-lg font-black text-slate-950"
                            >
                                Confirmar pase a remate
                            </h2>

                            <p
                                class="mt-2 text-sm leading-6 text-slate-500"
                            >
                                Se crearán {{ items.length }} registro(s) de
                                remate y el empeño quedará bloqueado para
                                pagos, descuentos y cancelaciones.
                            </p>

                            <label
                                class="mt-5 flex cursor-pointer gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <input
                                    v-model="form.confirmation"
                                    type="checkbox"
                                    class="mt-0.5 h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                />

                                <span
                                    class="text-sm font-bold leading-5 text-slate-700"
                                >
                                    Confirmo que revisé las prendas y los
                                    importes del remate.
                                </span>
                            </label>

                            <p
                                v-if="form.errors.confirmation"
                                class="mt-3 text-sm font-bold text-red-600"
                            >
                                {{ form.errors.confirmation }}
                            </p>

                            <button
                                type="submit"
                                class="sicem-btn-orange mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black transition"
                                :disabled="!canSubmit"
                            >
                                <svg
                                    v-if="form.processing"
                                    class="h-4 w-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        class="opacity-25"
                                    />

                                    <path
                                        d="M21 12a9 9 0 0 0-9-9"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                </svg>

                                {{
                                    form.processing
                                        ? 'Procesando...'
                                        : 'Sacar a remate'
                                }}
                            </button>

                            <Link
                                v-if="urls.show"
                                :href="urls.show"
                                class="mt-3 inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                Cancelar y volver
                            </Link>
                        </form>
                    </aside>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.sicem-auction-hero {
    background: linear-gradient(
        135deg,
        #d97706,
        #92400e
    ) !important;
}

.sicem-btn-orange {
    border: 1px solid #d97706 !important;
    background-color: #d97706 !important;
    color: #ffffff !important;
}

.sicem-btn-orange:hover:not(:disabled) {
    border-color: #b45309 !important;
    background-color: #b45309 !important;
    color: #ffffff !important;
}

.sicem-btn-orange:disabled {
    cursor: not-allowed !important;
    opacity: 0.45 !important;
}

.sicem-mode-pill {
    display: inline-flex;
    border-radius: 9999px;
    border-width: 1px;
    padding: 0.3rem 0.7rem;
    font-size: 0.7rem;
    font-weight: 900;
}

.sicem-mode-sellable {
    border-color: #a7f3d0 !important;
    background-color: #ecfdf5 !important;
    color: #047857 !important;
}

.sicem-mode-grouped {
    border-color: #bfdbfe !important;
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
}

.sicem-mode-blocked {
    border-color: #fecaca !important;
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}
</style>