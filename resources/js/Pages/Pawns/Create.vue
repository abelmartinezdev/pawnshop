<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { computed, reactive, ref, watch } from 'vue'
import PawnPhotoCapture from '@/components/Pawns/PawnPhotoCapture.vue'

const props = defineProps({
    customers: {
        type: Array,
        default: () => [],
    },
    departments: {
        type: Array,
        default: () => [],
    },
    products: {
        type: Array,
        default: () => [],
    },
    selectedCustomer: {
        type: Object,
        default: null,
    },
    selectedDepartment: {
        type: Object,
        default: null,
    },
    quotationDraft: {
        type: Object,
        default: null,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    cash: {
        type: Object,
        default: () => ({
            office_cash: 0,
        }),
    },
})

const customerSearch = ref('')
const showItemModal = ref(false)
const showFinishModal = ref(false)
const showErrorModal = ref(false)
const errorMessage = ref('')

const selectedCustomer = ref(props.selectedCustomer)
const selectedDepartment = ref(props.selectedDepartment)

const itemForm = reactive({
    product_id: '',
    quantity: '1',
    value: '',
    description: '',
})

const initialQuotationItems = Array.isArray(props.quotationDraft?.items)
    ? props.quotationDraft.items.map((item, index) => ({
        uid: item.uid || `quotation-${item.product_id}-${index}`,
        product_id: Number(item.product_id),
        product_code: item.product_code || '',
        product_name: item.product_name || '',
        unit: item.unit || '',
        quantity: Number(item.quantity || 0),
        description: item.description || item.product_name || '',
        value: Number(item.value || 0),
        min_price: Number(item.min_price || 0),
        max_price: Number(item.max_price || 0),
        unit_min_price: Number(item.unit_min_price || 0),
        unit_max_price: Number(item.unit_max_price || 0),
    }))
    : []

const form = useForm({
    customer_id: props.selectedCustomer?.id || '',
    department_id: props.selectedDepartment?.id || '',
    beneficiary: props.selectedCustomer?.name || '',
    bag: '',
    comments: '',
    items: initialQuotationItems,
    photos: [],
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const hasQuotationDraft = computed(() => {
    return Boolean(
        props.quotationDraft
        && props.quotationDraft.source === 'gold_quotation'
        && Number(props.quotationDraft.department_id) === Number(selectedDepartment.value?.id)
    )
})

const currentStep = computed(() => {
    if (!selectedCustomer.value) return 1
    if (!selectedDepartment.value) return 2

    return 3
})

const filteredCustomers = computed(() => {
    const search = customerSearch.value.trim().toLowerCase()

    if (!search) {
        return props.customers
    }

    return props.customers.filter((customer) => {
        return [
            customer.name,
            customer.city,
            customer.state,
            customer.phone,
            customer.email,
            customer.rfc,
            customer.code_id,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase()
            .includes(search)
    })
})

const filteredProducts = computed(() => {
    if (!selectedDepartment.value) {
        return []
    }

    return props.products.filter((product) => {
        return Number(product.department_id) === Number(selectedDepartment.value.id)
    })
})

const selectedProduct = computed(() => {
    return filteredProducts.value.find((product) => Number(product.id) === Number(itemForm.product_id)) || null
})

const itemQuantity = computed(() => {
    return Number(itemForm.quantity || 0)
})

const calculatedMinPrice = computed(() => {
    if (!selectedProduct.value) return 0

    return Number(selectedProduct.value.min_price || 0) * itemQuantity.value
})

const calculatedMaxPrice = computed(() => {
    if (!selectedProduct.value) return 0

    return Number(selectedProduct.value.max_price || 0) * itemQuantity.value
})

const hasCalculatedPriceRange = computed(() => {
    return calculatedMinPrice.value > 0 || calculatedMaxPrice.value > 0
})

const itemPriceOutsideRange = computed(() => {
    const value = Number(itemForm.value || 0)

    if (calculatedMinPrice.value > 0 && value < calculatedMinPrice.value) {
        return true
    }

    if (calculatedMaxPrice.value > 0 && value > calculatedMaxPrice.value) {
        return true
    }

    return false
})

const total = computed(() => {
    return form.items.reduce((sum, item) => sum + Number(item.value || 0), 0)
})

const officeCashAfter = computed(() => {
    return Number(props.cash.office_cash || 0) - Number(total.value || 0)
})

const hasNegativeCash = computed(() => {
    return officeCashAfter.value < 0
})

const ivaFactor = computed(() => {
    const raw = Number(selectedDepartment.value?.iva_rate || 0)

    return raw > 1 ? raw / 100 : raw
})

const dailyInterestAmount = computed(() => {
    const rate = Number(selectedDepartment.value?.daily_interest_rate || 0) / 100

    return Number(total.value || 0) * rate
})

const settlementRows = computed(() => {
    if (!selectedDepartment.value || total.value <= 0) {
        return [
            {
                number: 1,
                principal: 0,
                interest: 0,
                storage: 0,
                iva: 0,
                refinanceTotal: 0,
                liquidateTotal: 0,
                date: '-',
            },
            {
                number: 2,
                principal: 0,
                interest: 0,
                storage: 0,
                iva: 0,
                refinanceTotal: 0,
                liquidateTotal: 0,
                date: '-',
            },
        ]
    }

    const term = Number(selectedDepartment.value.term || 0)
    const auction = Number(selectedDepartment.value.auction || 0)

    return [
        makeSettlementRow(1, term),
        makeSettlementRow(2, term + auction),
    ]
})

const canFinish = computed(() => {
    return form.customer_id && form.department_id && form.items.length > 0 && total.value > 0
})

const selectCustomer = (customer) => {
    selectedCustomer.value = customer
    form.customer_id = customer.id
    form.beneficiary = customer.name || ''

    const params = {
        customer_id: customer.id,
    }

    if (hasQuotationDraft.value) {
        params.department_id = selectedDepartment.value.id
        params.from_quotation = 1
    }

    router.get(route('pawns.create'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const selectDepartment = (department) => {
    if (Number(form.department_id) !== Number(department.id)) {
        form.items = []
    }

    selectedDepartment.value = department
    form.department_id = department.id

    router.get(route('pawns.create'), {
        customer_id: form.customer_id,
        department_id: department.id,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const resetCustomer = () => {
    selectedCustomer.value = null
    form.customer_id = ''
    form.beneficiary = ''

    if (hasQuotationDraft.value) {
        router.get(route('pawns.create'), {
            department_id: selectedDepartment.value.id,
            from_quotation: 1,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        })

        return
    }

    selectedDepartment.value = null
    form.department_id = ''
    form.items = []

    router.get(route('pawns.create'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const resetDepartment = () => {
    selectedDepartment.value = null
    form.department_id = ''
    form.items = []

    router.get(route('pawns.create'), {
        customer_id: form.customer_id,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const openItemModal = () => {
    if (!selectedDepartment.value) {
        openError('Primero selecciona un departamento.')
        return
    }

    if (!filteredProducts.value.length) {
        openError('Este departamento no tiene productos activos. Registra productos antes de empeñar.')
        return
    }

    resetItemForm()
    showItemModal.value = true
}

const resetItemForm = () => {
    itemForm.product_id = ''
    itemForm.quantity = '1'
    itemForm.value = ''
    itemForm.description = ''
}

const addItem = () => {
    if (!selectedProduct.value) {
        openError('Selecciona un producto.')
        return
    }

    if (!Number(itemForm.quantity || 0)) {
        openError('Captura la cantidad del artículo.')
        return
    }

    if (!Number(itemForm.value || 0)) {
        openError('Captura el valor del artículo.')
        return
    }

    form.items.push({
        uid: globalThis.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`,
        product_id: selectedProduct.value.id,
        product_code: selectedProduct.value.code,
        product_name: selectedProduct.value.description,
        unit: selectedProduct.value.unit,
        quantity: Number(itemForm.quantity),
        description: itemForm.description.trim() || selectedProduct.value.description,
        value: Number(itemForm.value),
        min_price: Number(calculatedMinPrice.value),
        max_price: Number(calculatedMaxPrice.value),
        unit_min_price: Number(selectedProduct.value.min_price || 0),
        unit_max_price: Number(selectedProduct.value.max_price || 0),
    })

    showItemModal.value = false
    resetItemForm()
}

const removeItem = (index) => {
    form.items.splice(index, 1)
}

const openFinish = () => {
    if (!form.items.length) {
        openError('Por favor, agrega algún artículo a empeñar.')
        return
    }

    showFinishModal.value = true
}

const submit = () => {
    if (!canFinish.value) {
        openError('Revisa que tengas cliente, departamento y al menos un artículo.')
        return
    }

    form.post(route('pawns.store'), {
        preserveScroll: true,
        onError: () => {
            showFinishModal.value = false
        },
    })
}

const openError = (message) => {
    errorMessage.value = message
    showErrorModal.value = true
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

const percent = (value) => {
    return `${Number(value || 0).toFixed(3)}%`
}

const todayPlusDays = (days) => {
    const date = new Date()
    date.setDate(date.getDate() + Number(days || 0))

    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).toUpperCase()
}

const makeSettlementRow = (number, days) => {
    const principal = Number(total.value || 0)
    const interestBase = Number(dailyInterestAmount.value || 0) * Number(days || 0)
    const iva = interestBase * ivaFactor.value
    const interestWithIva = interestBase + iva

    return {
        number,
        principal,
        interest: interestBase,
        storage: 0,
        iva,
        refinanceTotal: interestWithIva,
        liquidateTotal: principal + interestWithIva,
        date: todayPlusDays(days),
    }
}

const pendingLabel = (count) => {
    return count > 0
        ? `Tiene ${count} préstamo(s) pendiente(s)`
        : 'Sin préstamos pendientes'
}

const pendingClass = (count) => {
    return count > 0
        ? 'border-amber-200 bg-amber-50 text-amber-700'
        : 'border-emerald-200 bg-emerald-50 text-emerald-700'
}

watch(selectedProduct, (product) => {
    if (!product) {
        itemForm.value = ''
        itemForm.description = ''
        return
    }

    itemForm.description = product.description || ''
    itemForm.value = ''
})

const iconPath = (icon) => {
    const icons = {
        search: 'M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z',
        plus: 'M12 5v14M5 12h14',
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        gem: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        box: 'M21 8l-9-5-9 5 9 5 9-5ZM3 8v8l9 5 9-5V8M12 13v8',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        check: 'M20 6 9 17l-5-5',
        x: 'M18 6 6 18M6 6l12 12',
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z',
    }

    return icons[icon] || icons.box
}
</script>

<template>
    <Head title="Nuevo empeño" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Caja / empeños
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Nuevo empeño
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Selecciona cliente, departamento, artículos y confirma la operación.
                    </p>
                </div>

                <Link
                    :href="route('pawns.index')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Cancelar
                </Link>
            </div>

            <div
                v-if="hasQuotationDraft"
                class="sicem-quotation-banner mb-6 rounded-[1.75rem] border p-5 shadow-sm"
            >
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex gap-3">
                        <div class="sicem-quotation-icon flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('gem')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div>
                            <p class="font-black">Cotización de oro cargada</p>
                            <p class="mt-1 text-sm font-semibold leading-6">
                                Préstamo solicitado: {{ money(quotationDraft.total_import) }} ·
                                Rango sugerido: {{ money(quotationDraft.suggested_minimum) }} - {{ money(quotationDraft.suggested_maximum) }}.
                            </p>
                            <p v-if="quotationDraft.warning" class="mt-1 text-sm font-black">
                                {{ quotationDraft.warning }} Puedes continuar con este importe.
                            </p>
                            <p v-else class="mt-1 text-sm font-black">
                                El importe se encuentra dentro del rango sugerido.
                            </p>
                        </div>
                    </div>

                    <div class="sicem-quotation-total rounded-2xl px-5 py-3 text-right">
                        <p class="text-[11px] font-black uppercase tracking-[0.16em]">Artículos preparados</p>
                        <p class="mt-1 text-lg font-black">{{ form.items.length }} · {{ money(total) }}</p>
                    </div>
                </div>
            </div>

            <div class="mb-6 grid gap-4 md:grid-cols-3">
                <div
                    class="rounded-[1.5rem] border p-5 shadow-sm"
                    :class="currentStep >= 1 ? 'border-[#5b55a4] bg-white' : 'border-slate-200 bg-white'"
                >
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Paso 1</p>
                    <p class="mt-1 text-lg font-black text-slate-950">Cliente</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        {{ selectedCustomer?.name || 'Pendiente' }}
                    </p>
                </div>

                <div
                    class="rounded-[1.5rem] border p-5 shadow-sm"
                    :class="currentStep >= 2 ? 'border-[#5b55a4] bg-white' : 'border-slate-200 bg-white'"
                >
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Paso 2</p>
                    <p class="mt-1 text-lg font-black text-slate-950">Departamento</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        {{ selectedDepartment?.description || 'Pendiente' }}
                    </p>
                </div>

                <div
                    class="rounded-[1.5rem] border p-5 shadow-sm"
                    :class="currentStep >= 3 ? 'border-[#5b55a4] bg-white' : 'border-slate-200 bg-white'"
                >
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Paso 3</p>
                    <p class="mt-1 text-lg font-black text-slate-950">Artículos</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        {{ form.items.length }} artículo(s) · {{ money(total) }}
                    </p>
                </div>
            </div>

            <section v-if="currentStep === 1" class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-950">Selecciona / registra el cliente</h2>
                        <p class="text-sm text-slate-500">Busca al cliente para iniciar el empeño.</p>
                    </div>

                    <Link
                        :href="route('customers.create', { where: 'box' })"
                        class="sicem-btn-primary inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('plus')" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Nuevo cliente
                    </Link>
                </div>

                <div class="p-6">
                    <div class="relative mb-6">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('search')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>

                        <input
                            v-model="customerSearch"
                            type="search"
                            class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                            placeholder="Buscar por nombre, ciudad, teléfono, RFC o identificación..."
                        />
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Cliente</th>
                                    <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Ciudad</th>
                                    <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">Estatus</th>
                                    <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Acción</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr
                                    v-for="customer in filteredCustomers"
                                    :key="customer.id"
                                    class="transition hover:bg-slate-50/80"
                                >
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-black text-slate-950">{{ customer.name }}</p>
                                        <p class="mt-1 text-xs font-semibold text-slate-400">
                                            {{ customer.type_label || 'Identificación' }} {{ customer.code_id || '' }}
                                            <span v-if="customer.inapam_code" class="ml-2 rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-black text-emerald-700">
                                                INAPAM
                                            </span>
                                        </p>
                                    </td>

                                    <td class="px-5 py-4">
                                        <p class="text-sm font-black text-slate-800">
                                            {{ customer.city || 'Sin ciudad' }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-slate-400">
                                            {{ customer.phone || 'Sin teléfono' }}
                                        </p>
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <span
                                            class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                            :class="pendingClass(customer.active_pawns_count)"
                                        >
                                            {{ pendingLabel(customer.active_pawns_count) }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <button
                                            type="button"
                                            class="sicem-btn-blue rounded-2xl px-4 py-2 text-xs font-black shadow-sm transition"
                                            @click="selectCustomer(customer)"
                                        >
                                            Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section v-else-if="currentStep === 2" class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-950">Selecciona el departamento</h2>
                        <p class="text-sm text-slate-500">
                            Cliente: <span class="font-black text-slate-800">{{ selectedCustomer.name }}</span>
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="resetCustomer"
                    >
                        Cambiar cliente
                    </button>
                </div>

                <div class="grid gap-5 p-6 md:grid-cols-2 xl:grid-cols-3">
                    <button
                        v-for="department in departments"
                        :key="department.id"
                        type="button"
                        class="group overflow-hidden rounded-[2rem] border border-slate-200 bg-white text-left shadow-sm transition hover:-translate-y-1 hover:shadow-xl"
                        @click="selectDepartment(department)"
                    >
                        <div
                            class="p-7 text-white"
                            :style="{ background: `linear-gradient(135deg, ${department.color || '#5b55a4'}, #312d65)` }"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-white/15 text-2xl font-black">
                                    {{ department.code?.charAt(0) || 'D' }}
                                </div>

                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">{{ department.code }}</p>
                                    <p class="mt-1 text-xl font-black">{{ department.description }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 p-5">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Plazo</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ department.term }} días</p>
                            </div>

                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Remate</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ department.auction }} días</p>
                            </div>

                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-400">Productos</p>
                                <p class="mt-1 text-sm font-black text-slate-800">{{ department.active_products_count }}</p>
                            </div>
                        </div>
                    </button>
                </div>
            </section>

            <section v-else class="grid gap-6 xl:grid-cols-[1fr_0.45fr]">
                <div class="space-y-6">
                    <div
                        v-if="hasNegativeCash"
                        class="rounded-[1.75rem] border border-amber-200 bg-amber-50 p-5 text-amber-800"
                    >
                        <div class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('alert')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                            <div>
                                <p class="font-black">La caja quedará en saldo negativo.</p>
                                <p class="mt-1 text-sm">
                                    Saldo actual: {{ money(cash.office_cash) }} · Después del empeño: {{ money(officeCashAfter) }}.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-xl font-black text-slate-950">
                                    Empeñar - {{ selectedCustomer.name }}
                                </h2>
                                <p class="text-sm text-slate-500">
                                    {{ selectedDepartment.description }} · {{ selectedDepartment.term }} días de plazo
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                                    @click="resetCustomer"
                                >
                                    Cambiar cliente
                                </button>

                                <button
                                    type="button"
                                    class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                                    @click="resetDepartment"
                                >
                                    Cambiar departamento
                                </button>

                                <button
                                    type="button"
                                    class="sicem-btn-rose rounded-2xl px-4 py-2 text-xs font-black shadow-sm shadow-rose-100 transition"
                                    @click="openItemModal"
                                >
                                    + Agregar artículo
                                </button>
                            </div>
                        </div>

                        <div class="p-6">
                            <div v-if="form.items.length" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-100">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500"></th>
                                            <th class="px-4 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Cant.</th>
                                            <th class="px-4 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Unidad</th>
                                            <th class="px-4 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Artículo</th>
                                            <th class="px-4 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Descripción</th>
                                            <th class="px-4 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Rango calculado</th>
                                            <th class="px-4 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Valor</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        <tr v-for="(item, index) in form.items" :key="item.uid">
                                            <td class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    class="rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-black text-red-600 transition hover:bg-red-500 hover:text-white"
                                                    @click="removeItem(index)"
                                                >
                                                    Quitar
                                                </button>
                                            </td>

                                            <td class="px-4 py-4 text-right text-sm font-black text-slate-950">
                                                {{ numberFormat(item.quantity) }}
                                            </td>

                                            <td class="px-4 py-4 text-sm font-semibold text-slate-600">
                                                {{ item.unit }}
                                            </td>

                                            <td class="px-4 py-4">
                                                <p class="text-sm font-black text-slate-900">{{ item.product_name }}</p>
                                                <p class="text-xs font-semibold text-slate-400">{{ item.product_code }}</p>
                                            </td>

                                            <td class="px-4 py-4 text-sm text-slate-600">
                                                {{ item.description }}
                                            </td>

                                            <td class="px-4 py-4 text-right text-sm font-semibold text-slate-500">
                                                {{ money(item.min_price) }} - {{ money(item.max_price) }}
                                            </td>

                                            <td class="px-4 py-4 text-right text-sm font-black text-slate-950">
                                                {{ money(item.value) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div v-else class="rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-rose-600 shadow-sm">
                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                        <path :d="iconPath('plus')" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                </div>

                                <h3 class="mt-5 text-xl font-black text-slate-950">
                                    Agrega artículos al empeño
                                </h3>

                                <p class="mt-2 text-sm text-slate-500">
                                    Selecciona producto, cantidad, valor y descripción.
                                </p>

                                <button
                                    type="button"
                                    class="sicem-btn-rose mt-6 rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-rose-100 transition"
                                    @click="openItemModal"
                                >
                                    + Agregar artículo
                                </button>
                            </div>
                        </div>
                    </div>

                    <PawnPhotoCapture v-model="form.photos" />

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Opciones de pago para refrendo o desempeño
                            </h2>
                            <p class="text-sm text-slate-500">
                                Vista previa calculada con las reglas del departamento.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-[#172331] text-white">
                                    <tr>
                                        <th class="px-4 py-4 text-left text-xs font-black uppercase">Número</th>
                                        <th class="px-4 py-4 text-right text-xs font-black uppercase">Importe del mutuo</th>
                                        <th class="px-4 py-4 text-right text-xs font-black uppercase">Interés</th>
                                        <th class="px-4 py-4 text-right text-xs font-black uppercase">Almacenaje</th>
                                        <th class="px-4 py-4 text-right text-xs font-black uppercase">I.V.A.</th>
                                        <th class="px-4 py-4 text-right text-xs font-black uppercase">Por refrendo</th>
                                        <th class="px-4 py-4 text-right text-xs font-black uppercase">Por desempeño</th>
                                        <th class="px-4 py-4 text-right text-xs font-black uppercase">Cuando se realizan los pagos</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr v-for="row in settlementRows" :key="row.number">
                                        <td class="px-4 py-4 text-sm font-black text-slate-900">{{ row.number }}</td>
                                        <td class="px-4 py-4 text-right text-sm font-semibold text-slate-700">{{ money(row.principal) }}</td>
                                        <td class="px-4 py-4 text-right text-sm font-semibold text-slate-700">{{ money(row.interest) }}</td>
                                        <td class="px-4 py-4 text-right text-sm font-semibold text-slate-700">{{ money(row.storage) }}</td>
                                        <td class="px-4 py-4 text-right text-sm font-semibold text-slate-700">{{ money(row.iva) }}</td>
                                        <td class="px-4 py-4 text-right text-sm font-black text-emerald-600">{{ money(row.refinanceTotal) }}</td>
                                        <td class="px-4 py-4 text-right text-sm font-black text-[#5b55a4]">{{ money(row.liquidateTotal) }}</td>
                                        <td class="px-4 py-4 text-right text-sm font-semibold text-slate-700">{{ row.date }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="grid divide-y divide-slate-100 border-t border-slate-100 md:grid-cols-2 md:divide-x md:divide-y-0">
                            <div class="p-5 text-center">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Costo mensual total</p>
                                <p class="mt-1 text-sm font-semibold text-slate-600">
                                    Para fines informativos y de comparación
                                </p>
                                <p class="mt-1 text-lg font-black text-slate-950">
                                    {{ percent(selectedDepartment.monthly_interest_rate) }} fijo sin I.V.A.
                                </p>
                            </div>

                            <div class="p-5 text-center">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Costo diario total</p>
                                <p class="mt-1 text-sm font-semibold text-slate-600">
                                    Para fines informativos y de comparación
                                </p>
                                <p class="mt-1 text-lg font-black text-slate-950">
                                    {{ percent(selectedDepartment.daily_interest_rate) }} fijo sin I.V.A.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="bg-[#202020] p-6 text-white">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-emerald-400">
                                Total
                            </p>
                            <p class="mt-2 text-4xl font-black tracking-tight text-emerald-400">
                                {{ money(total) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="sicem-btn-orange sicem-btn-disabled flex w-full items-center justify-center gap-2 px-6 py-4 text-sm font-black uppercase transition"
                            :disabled="!canFinish || form.processing"
                            @click="openFinish"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('cash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Empeñar
                        </button>

                        <div class="p-6">
                            <div class="mb-6 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">
                                    <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                        <path :d="iconPath('user')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>

                                <p class="mt-3 text-lg font-black text-slate-950">{{ selectedCustomer.name }}</p>
                                <p class="text-xs font-bold text-slate-400">{{ selectedCustomer.type_label || 'Identificación' }} {{ selectedCustomer.code_id || '' }}</p>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                    <span class="font-bold text-slate-400">RFC</span>
                                    <span class="font-black text-slate-800">{{ selectedCustomer.rfc || 'N/A' }}</span>
                                </div>

                                <div class="flex justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                    <span class="font-bold text-slate-400">Email</span>
                                    <span class="font-black text-slate-800">{{ selectedCustomer.email || 'N/A' }}</span>
                                </div>

                                <div class="flex justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                    <span class="font-bold text-slate-400">Teléfono</span>
                                    <span class="font-black text-slate-800">{{ selectedCustomer.phone || 'N/A' }}</span>
                                </div>

                                <div class="flex justify-between rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                    <span class="font-bold text-slate-400">Ciudad</span>
                                    <span class="font-black text-slate-800">{{ selectedCustomer.city || 'N/A' }}</span>
                                </div>

                                <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                    <span class="font-bold text-slate-400">Dirección</span>
                                    <p class="mt-1 font-black text-slate-800">{{ selectedCustomer.full_address || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </section>
        </div>

        <div
            v-if="showItemModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-2xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-black text-slate-950">Agregar artículo</h2>
                        <p class="text-sm text-slate-500">Captura los datos del producto empeñado.</p>
                    </div>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="showItemModal = false"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('x')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-black text-slate-700">Producto</label>
                        <select v-model="itemForm.product_id" :class="selectClass">
                            <option class="bg-white text-slate-900" value="">Seleccione</option>
                            <option
                                v-for="product in filteredProducts"
                                :key="product.id"
                                class="bg-white text-slate-900"
                                :value="product.id"
                            >
                                {{ product.description }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">
                            Cantidad <span v-if="selectedProduct">({{ selectedProduct.unit }})</span>
                        </label>
                        <input v-model="itemForm.quantity" type="number" min="0" step="0.001" :class="inputClass" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">
                            Precio mínimo calculado
                        </label>
                        <input
                            :value="selectedProduct ? money(calculatedMinPrice) : ''"
                            type="text"
                            :class="inputClass"
                            disabled
                        />
                        <p v-if="selectedProduct" class="mt-1 text-xs font-semibold text-slate-400">
                            {{ money(selectedProduct.min_price) }} × {{ itemQuantity || 0 }} {{ selectedProduct.unit }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">
                            Precio máximo calculado
                        </label>
                        <input
                            :value="selectedProduct ? money(calculatedMaxPrice) : ''"
                            type="text"
                            :class="inputClass"
                            disabled
                        />
                        <p v-if="selectedProduct" class="mt-1 text-xs font-semibold text-slate-400">
                            {{ money(selectedProduct.max_price) }} × {{ itemQuantity || 0 }} {{ selectedProduct.unit }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">
                            Valor del préstamo
                        </label>
                        <input
                            v-model="itemForm.value"
                            type="number"
                            min="0"
                            step="0.01"
                            :class="[
                                inputClass,
                                itemPriceOutsideRange ? 'border-red-300 bg-red-50 focus:border-red-500 focus:ring-red-100' : '',
                            ]"
                            :placeholder="selectedProduct ? `${money(calculatedMinPrice)} - ${money(calculatedMaxPrice)}` : '0.00'"
                        />

                        <div v-if="selectedProduct" class="mt-2 flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 transition hover:bg-slate-50"
                                @click="itemForm.value = calculatedMinPrice"
                            >
                                Usar mínimo
                            </button>

                            <button
                                type="button"
                                class="sicem-btn-primary rounded-xl px-3 py-1.5 text-xs font-black transition"
                                @click="itemForm.value = calculatedMaxPrice"
                            >
                                Usar máximo
                            </button>
                        </div>

                        <p v-if="itemPriceOutsideRange" class="mt-2 text-xs font-black text-red-600">
                            El valor está fuera del rango calculado.
                        </p>
                    </div>

                    <div class="md:col-span-3">
                        <label class="mb-2 block text-sm font-black text-slate-700">Descripción extra</label>
                        <textarea
                            v-model="itemForm.description"
                            rows="4"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                            placeholder="Características, estado, marca, serie, detalles..."
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="showItemModal = false"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="sicem-btn-blue rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-blue-100 transition"
                        @click="addItem"
                    >
                        Agregar
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="showFinishModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-black text-slate-950">Confirmar empeño</h2>
                        <p class="text-sm text-slate-500">Completa los últimos datos antes de finalizar.</p>
                    </div>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="showFinishModal = false"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('x')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 p-6">
                    <div class="sicem-total-box rounded-2xl p-5">
                        <p class="sicem-total-label text-xs font-black uppercase tracking-[0.22em]">
                            Total a entregar
                        </p>
                        <p class="sicem-total-amount mt-2 text-4xl font-black">
                            {{ money(total) }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                            Fotografías
                        </p>

                        <p class="mt-1 text-sm font-black text-slate-800">
                            {{ form.photos.length }} foto(s) capturada(s)
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">Beneficiario</label>
                        <input v-model="form.beneficiary" type="text" :class="inputClass" />
                        <p v-if="form.errors.beneficiary" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.beneficiary }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">Bolsa</label>
                        <input v-model="form.bag" type="text" :class="inputClass" />
                        <p v-if="form.errors.bag" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.bag }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">Comentarios</label>
                        <textarea
                            v-model="form.comments"
                            rows="4"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                        />
                        <p v-if="form.errors.comments" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.comments }}</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="showFinishModal = false"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="sicem-btn-orange sicem-btn-disabled rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-orange-100 transition"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{ form.processing ? 'Registrando...' : 'Terminar' }}
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

                <h2 class="mt-6 text-2xl font-black text-slate-950">¡Error!</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">{{ errorMessage }}</p>

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
.sicem-btn-primary {
    background-color: #5b55a4 !important;
    color: #ffffff !important;
    border-color: #5b55a4 !important;
}

.sicem-btn-primary:hover {
    background-color: #4f4896 !important;
    color: #ffffff !important;
}

.sicem-btn-rose {
    background-color: #e11d48 !important;
    color: #ffffff !important;
    border-color: #e11d48 !important;
}

.sicem-btn-rose:hover {
    background-color: #be123c !important;
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

.sicem-btn-orange {
    background-color: #f97316 !important;
    color: #ffffff !important;
    border-color: #f97316 !important;
}

.sicem-btn-orange:hover {
    background-color: #ea580c !important;
    color: #ffffff !important;
}

.sicem-btn-disabled:disabled {
    opacity: 0.55 !important;
    cursor: not-allowed !important;
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

.sicem-quotation-banner {
    background-color: #fffbeb !important;
    border-color: #fcd34d !important;
    color: #78350f !important;
}

.sicem-quotation-icon {
    background-color: #fef3c7 !important;
    color: #b45309 !important;
}

.sicem-quotation-total {
    background-color: #78350f !important;
    color: #ffffff !important;
}
</style>
