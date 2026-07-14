<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

const props = defineProps({
    department: {
        type: Object,
        required: true,
    },
    office: {
        type: Object,
        required: true,
    },
    products: {
        type: Array,
        default: () => [],
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const weights = reactive(
    Object.fromEntries(props.products.map((product) => [product.id, '']))
)

const form = useForm({
    department_id: props.department.id,
    total_import: '',
    items: [],
})

const selectedLines = computed(() => {
    return props.products
        .map((product) => {
            const quantity = Math.max(Number(weights[product.id] || 0), 0)

            return {
                product,
                quantity,
                minimum: Number(product.min_price || 0) * quantity,
                maximum: Number(product.max_price || 0) * quantity,
            }
        })
        .filter((line) => line.quantity > 0)
})

const totalWeight = computed(() => {
    return selectedLines.value.reduce((sum, line) => sum + line.quantity, 0)
})

const suggestedMinimum = computed(() => {
    return selectedLines.value.reduce((sum, line) => sum + line.minimum, 0)
})

const suggestedMaximum = computed(() => {
    return selectedLines.value.reduce((sum, line) => sum + line.maximum, 0)
})

const suggestedAverage = computed(() => {
    return (suggestedMinimum.value + suggestedMaximum.value) / 2
})

const loanAmount = computed(() => Math.max(Number(form.total_import || 0), 0))

const ivaFactor = computed(() => {
    const raw = Number(props.department.iva_rate || 0)

    return raw > 1 ? raw / 100 : raw
})

const dailyInterestBase = computed(() => {
    return loanAmount.value * (Number(props.department.daily_interest_rate || 0) / 100)
})

const dailyIva = computed(() => dailyInterestBase.value * ivaFactor.value)
const dailyInterestTotal = computed(() => dailyInterestBase.value + dailyIva.value)

const monthlyInterestBase = computed(() => {
    return loanAmount.value * (Number(props.department.monthly_interest_rate || 0) / 100)
})

const monthlyIva = computed(() => monthlyInterestBase.value * ivaFactor.value)
const monthlyInterestTotal = computed(() => monthlyInterestBase.value + monthlyIva.value)

const termInterestTotal = computed(() => {
    return dailyInterestTotal.value * Number(props.department.term || 0)
})

const amountAtTerm = computed(() => loanAmount.value + termInterestTotal.value)

const rangeStatus = computed(() => {
    if (!selectedLines.value.length || loanAmount.value <= 0) {
        return 'empty'
    }

    if (suggestedMinimum.value > 0 && loanAmount.value < suggestedMinimum.value) {
        return 'below'
    }

    if (suggestedMaximum.value > 0 && loanAmount.value > suggestedMaximum.value) {
        return 'above'
    }

    return 'within'
})

const rangeMessage = computed(() => {
    if (rangeStatus.value === 'below') {
        return `El préstamo está ${money(suggestedMinimum.value - loanAmount.value)} por debajo del mínimo sugerido.`
    }

    if (rangeStatus.value === 'above') {
        return `El préstamo está ${money(loanAmount.value - suggestedMaximum.value)} por encima del máximo sugerido.`
    }

    if (rangeStatus.value === 'within') {
        return 'El préstamo se encuentra dentro del rango sugerido.'
    }

    return 'Captura el peso de al menos un producto y el importe que deseas prestar.'
})

const canContinue = computed(() => {
    return Boolean(
        props.urls.continue
        && selectedLines.value.length
        && loanAmount.value > 0
        && !form.processing
    )
})

const setSuggestedLoan = (type) => {
    const values = {
        minimum: suggestedMinimum.value,
        average: suggestedAverage.value,
        maximum: suggestedMaximum.value,
    }

    form.total_import = Number(values[type] || 0).toFixed(2)
    form.clearErrors('total_import')
}

const clearQuotation = () => {
    props.products.forEach((product) => {
        weights[product.id] = ''
    })

    form.total_import = ''
    form.items = []
    form.clearErrors()
}

const submit = () => {
    if (!canContinue.value) {
        return
    }

    form.items = selectedLines.value.map((line) => ({
        product_id: line.product.id,
        quantity: Number(line.quantity.toFixed(3)),
    }))

    form.post(props.urls.continue, {
        preserveScroll: true,
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

const numberFormat = (value, decimals = 3) => {
    return Number(value || 0).toLocaleString('es-MX', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    })
}

const percent = (value) => `${Number(value || 0).toFixed(3)}%`

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        scale: 'M12 3v18M5 6h14M7 6l-4 7h8L7 6Zm10 0-4 7h8l-4-7ZM8 21h8',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        check: 'M20 6 9 17l-5-5',
        arrowRight: 'M5 12h14M13 6l6 6-6 6',
        refresh: 'M20 7v5h-5M4 17v-5h5M6.1 9A7 7 0 0 1 18 6l2 1M4 17l2 1a7 7 0 0 0 11.9-3',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
    }

    return icons[icon] || icons.scale
}
</script>

<template>
    <Head title="Cotización de oro" />

    <AdminLayout>
        <form class="px-4 py-6 sm:px-6 lg:px-8" @submit.prevent="submit">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-amber-700">
                        Caja / cotizaciones
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Cotización de oro
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Captura el peso por producto, revisa el rango sugerido y define libremente el préstamo.
                    </p>
                </div>

                <Link
                    v-if="urls.exit"
                    :href="urls.exit"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Salir
                </Link>

                <button
                    v-else
                    type="button"
                    class="cursor-not-allowed rounded-2xl border border-slate-200 bg-slate-100 px-5 py-3 text-sm font-black text-slate-400"
                    disabled
                >
                    Salir
                </button>
            </div>

            <div class="mb-6 grid gap-4 lg:grid-cols-3">
                <div class="rounded-[1.5rem] border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-700">Sucursal</p>
                    <p class="mt-2 text-lg font-black text-slate-950">{{ office.name }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Serie {{ office.serie || 'Sin serie' }}</p>
                </div>

                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Departamento</p>
                    <p class="mt-2 text-lg font-black text-slate-950">{{ department.description }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ department.code }} · {{ department.term }} días</p>
                </div>

                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Peso capturado</p>
                    <p class="mt-2 text-2xl font-black text-slate-950">{{ numberFormat(totalWeight) }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">gramos en {{ selectedLines.length }} producto(s)</p>
                </div>
            </div>

            <div
                v-if="form.hasErrors"
                class="mb-6 rounded-[1.5rem] border border-red-200 bg-red-50 p-5 text-red-800"
            >
                <p class="font-black">Revisa la información capturada.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm font-semibold">
                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                </ul>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.48fr]">
                <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-slate-950">Peso de las prendas</h2>
                            <p class="text-sm text-slate-500">Deja en blanco los productos que no se utilizarán.</p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                            @click="clearQuotation"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('refresh')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Limpiar
                        </button>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <div
                            v-for="product in products"
                            :key="product.id"
                            class="grid gap-4 px-6 py-5 transition hover:bg-slate-50/70 md:grid-cols-[1fr_12rem] md:items-center"
                        >
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-[11px] font-black uppercase tracking-wider text-amber-800">
                                        {{ product.code }}
                                    </span>
                                    <p class="text-base font-black text-slate-950">{{ product.description }}</p>
                                </div>

                                <p class="mt-2 text-sm font-semibold text-slate-500">
                                    Rango por {{ product.unit || 'gramo' }}:
                                    <span class="text-slate-800">{{ money(product.min_price) }} - {{ money(product.max_price) }}</span>
                                </p>

                                <p v-if="Number(weights[product.id] || 0) > 0" class="mt-1 text-xs font-black text-amber-700">
                                    Sugerido: {{ money(Number(product.min_price) * Number(weights[product.id])) }} -
                                    {{ money(Number(product.max_price) * Number(weights[product.id])) }}
                                </p>
                            </div>

                            <label class="block">
                                <span class="mb-2 block text-xs font-black uppercase tracking-[0.16em] text-slate-500">
                                    Peso / cantidad
                                </span>
                                <div class="relative">
                                    <input
                                        v-model="weights[product.id]"
                                        type="number"
                                        min="0"
                                        step="0.001"
                                        inputmode="decimal"
                                        class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 pr-16 text-right text-sm font-black text-slate-950 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
                                        placeholder="0.000"
                                    />
                                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs font-black text-slate-400">
                                        {{ product.unit || 'g' }}
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div v-if="!products.length" class="p-10 text-center text-sm font-semibold text-slate-500">
                        No hay productos activos disponibles para cotizar.
                    </div>
                </section>

                <aside class="space-y-6">
                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-800">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('scale')" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-950">Rango sugerido</h2>
                                <p class="text-xs font-semibold text-slate-500">Calculado con precios de productos.</p>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-left transition hover:bg-emerald-100 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="!selectedLines.length"
                                @click="setSuggestedLoan('minimum')"
                            >
                                <p class="text-[11px] font-black uppercase tracking-wider text-emerald-700">Mínimo</p>
                                <p class="mt-1 text-lg font-black text-emerald-900">{{ money(suggestedMinimum) }}</p>
                            </button>

                            <button
                                type="button"
                                class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-left transition hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="!selectedLines.length"
                                @click="setSuggestedLoan('maximum')"
                            >
                                <p class="text-[11px] font-black uppercase tracking-wider text-blue-700">Máximo</p>
                                <p class="mt-1 text-lg font-black text-blue-900">{{ money(suggestedMaximum) }}</p>
                            </button>
                        </div>

                        <button
                            type="button"
                            class="mt-3 w-full rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-black text-amber-900 transition hover:bg-amber-100 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!selectedLines.length"
                            @click="setSuggestedLoan('average')"
                        >
                            Usar promedio: {{ money(suggestedAverage) }}
                        </button>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <label for="total_import" class="block text-sm font-black text-slate-900">
                            Cantidad a prestar
                        </label>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            El importe es libre. Puedes continuar aunque quede fuera del rango sugerido.
                        </p>

                        <div class="relative mt-4">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-lg font-black text-slate-400">$</span>
                            <input
                                id="total_import"
                                v-model="form.total_import"
                                type="number"
                                min="0.01"
                                step="0.01"
                                inputmode="decimal"
                                class="h-14 w-full rounded-2xl border border-slate-200 bg-white pl-9 pr-4 text-right text-xl font-black text-slate-950 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100"
                                placeholder="0.00"
                            />
                        </div>

                        <p v-if="form.errors.total_import" class="mt-2 text-xs font-black text-red-600">
                            {{ form.errors.total_import }}
                        </p>

                        <div
                            class="mt-4 rounded-2xl border p-4"
                            :class="{
                                'border-amber-200 bg-amber-50 text-amber-900': rangeStatus === 'below' || rangeStatus === 'above',
                                'border-emerald-200 bg-emerald-50 text-emerald-900': rangeStatus === 'within',
                                'border-slate-200 bg-slate-50 text-slate-600': rangeStatus === 'empty',
                            }"
                        >
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="iconPath(rangeStatus === 'within' ? 'check' : 'alert')"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                                <div>
                                    <p class="text-sm font-black">
                                        {{ rangeStatus === 'within' ? 'Importe dentro del rango' : rangeStatus === 'empty' ? 'Cotización pendiente' : 'Advertencia de rango' }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold leading-5">{{ rangeMessage }}</p>
                                    <p v-if="rangeStatus === 'below' || rangeStatus === 'above'" class="mt-2 text-xs font-black">
                                        Esta advertencia no impide continuar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('cash')" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>
                            <h2 class="text-lg font-black text-slate-950">Interés estimado</h2>
                        </div>

                        <dl class="mt-5 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="font-semibold text-slate-500">Tasa diaria</dt>
                                <dd class="font-black text-slate-900">{{ percent(department.daily_interest_rate) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="font-semibold text-slate-500">Interés diario</dt>
                                <dd class="font-black text-slate-900">{{ money(dailyInterestBase) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="font-semibold text-slate-500">IVA diario</dt>
                                <dd class="font-black text-slate-900">{{ money(dailyIva) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 border-t border-slate-100 pt-3">
                                <dt class="font-black text-slate-700">Interés diario con IVA</dt>
                                <dd class="font-black text-emerald-700">{{ money(dailyInterestTotal) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="font-semibold text-slate-500">Interés mensual con IVA</dt>
                                <dd class="font-black text-slate-900">{{ money(monthlyInterestTotal) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="font-semibold text-slate-500">Interés por {{ department.term }} días</dt>
                                <dd class="font-black text-slate-900">{{ money(termInterestTotal) }}</dd>
                            </div>
                        </dl>

                        <div class="sicem-total-box mt-5 rounded-2xl p-4">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-300">Total al plazo</p>
                            <p class="mt-1 text-2xl font-black text-white">{{ money(amountAtTerm) }}</p>
                        </div>
                    </section>

                    <button
                        type="submit"
                        class="sicem-btn-gold sicem-btn-disabled inline-flex w-full items-center justify-center gap-2 rounded-2xl px-6 py-4 text-sm font-black shadow-lg shadow-amber-200 transition"
                        :disabled="!canContinue"
                    >
                        <span>{{ form.processing ? 'Preparando empeño...' : 'Continuar al empeño' }}</span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('arrowRight')" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </aside>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
.sicem-btn-gold {
    background-color: #b7791f !important;
    border-color: #b7791f !important;
    color: #ffffff !important;
}

.sicem-btn-gold:hover:not(:disabled) {
    background-color: #975a16 !important;
    border-color: #975a16 !important;
    color: #ffffff !important;
}

.sicem-btn-disabled:disabled {
    cursor: not-allowed !important;
    opacity: 0.5 !important;
}

.sicem-total-box {
    background-color: #172331 !important;
    color: #ffffff !important;
}
</style>
