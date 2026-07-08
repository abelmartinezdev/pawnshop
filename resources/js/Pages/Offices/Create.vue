<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    companies: {
        type: Array,
        default: () => [],
    },
    selected_company_id: {
        type: [Number, String, null],
        default: null,
    },
})

const form = useForm({
    company_id: props.selected_company_id || '',
    name: '',
    code: '',
    serie: '',
    phone: '',
    address: '',
    schedule: '',
    bank_account: '',
    daily_interest_rate: 0,
    monthly_interest_rate: 0,
    cash: 0,
})

const selectedCompany = computed(() => {
    return props.companies.find((company) => Number(company.id) === Number(form.company_id)) || null
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const textareaClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const submit = () => {
    form.post(route('offices.store'), {
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

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        office: 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M9 9h.01M9 13h.01M9 17h.01M15 13h.01M15 17h.01',
        building: 'M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 8h2a2 2 0 0 1 2 2v11M8 7h4M8 11h4M8 15h4M3 21h18',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        check: 'M20 6 9 17l-5-5',
    }

    return icons[icon] || icons.office
}
</script>

<template>
    <Head title="Nueva sucursal" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Administración / Sucursales
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Nueva sucursal
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Registra datos operativos, caja inicial y tasas de interés de la sucursal.
                    </p>
                </div>

                <Link
                    :href="route('offices.index')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Regresar
                </Link>
            </div>

            <form class="grid gap-6 xl:grid-cols-[1fr_0.35fr]" @submit.prevent="submit">
                <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h2 class="text-xl font-black text-slate-950">
                            Datos de la sucursal
                        </h2>
                        <p class="text-sm text-slate-500">
                            Información principal para operación y tickets.
                        </p>
                    </div>

                    <div class="grid gap-5 p-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Empresa
                            </label>

                            <select v-model="form.company_id" :class="selectClass">
                                <option class="bg-white text-slate-900" value="">
                                    Selecciona empresa
                                </option>
                                <option
                                    v-for="company in companies"
                                    :key="company.id"
                                    class="bg-white text-slate-900"
                                    :value="company.id"
                                >
                                    {{ company.name }} {{ company.code ? `· ${company.code}` : '' }}
                                </option>
                            </select>

                            <p v-if="form.errors.company_id" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.company_id }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Nombre
                            </label>

                            <input v-model="form.name" type="text" :class="inputClass" placeholder="Ej. Sucursal Centro" />

                            <p v-if="form.errors.name" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Código
                            </label>

                            <input v-model="form.code" type="text" :class="inputClass" placeholder="Ej. CENTRO" />

                            <p v-if="form.errors.code" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.code }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Serie
                            </label>

                            <input v-model="form.serie" type="text" :class="inputClass" placeholder="Ej. A" />

                            <p v-if="form.errors.serie" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.serie }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Teléfono
                            </label>

                            <input v-model="form.phone" type="text" :class="inputClass" placeholder="Teléfono de sucursal" />

                            <p v-if="form.errors.phone" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Cuenta bancaria
                            </label>

                            <input v-model="form.bank_account" type="text" :class="inputClass" placeholder="Banco / cuenta / CLABE" />

                            <p v-if="form.errors.bank_account" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.bank_account }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Dirección
                            </label>

                            <textarea v-model="form.address" rows="4" :class="textareaClass" placeholder="Dirección de la sucursal" />

                            <p v-if="form.errors.address" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.address }}
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Horario
                            </label>

                            <textarea v-model="form.schedule" rows="3" :class="textareaClass" placeholder="Ej. Lunes a viernes de 9:00 a 18:00" />

                            <p v-if="form.errors.schedule" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.schedule }}
                            </p>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('building')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Empresa seleccionada
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Relación de sucursal.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Empresa
                            </p>
                            <p class="mt-1 text-sm font-black text-slate-900">
                                {{ selectedCompany?.name || 'Sin seleccionar' }}
                            </p>
                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                {{ selectedCompany?.code || 'Sin código' }}
                            </p>
                        </div>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('cash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Caja inicial
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Efectivo inicial.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Efectivo
                            </label>

                            <input v-model="form.cash" type="number" step="0.01" :class="inputClass" />

                            <p v-if="form.errors.cash" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.cash }}
                            </p>

                            <p class="mt-3 rounded-2xl bg-emerald-50 p-4 text-xl font-black text-emerald-600">
                                {{ money(form.cash) }}
                            </p>
                        </div>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('percent')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Tasas
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Interés por sucursal.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Interés diario
                                </label>

                                <input v-model="form.daily_interest_rate" type="number" min="0" step="0.0001" :class="inputClass" />

                                <p v-if="form.errors.daily_interest_rate" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.daily_interest_rate }}
                                </p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Interés mensual
                                </label>

                                <input v-model="form.monthly_interest_rate" type="number" min="0" step="0.0001" :class="inputClass" />

                                <p v-if="form.errors.monthly_interest_rate" class="mt-2 text-sm font-bold text-red-600">
                                    {{ form.errors.monthly_interest_rate }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <button
                        type="submit"
                        class="sicem-btn-primary flex w-full items-center justify-center gap-2 rounded-2xl px-5 py-4 text-sm font-black shadow-lg shadow-violet-200 transition disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('check')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ form.processing ? 'Guardando...' : 'Guardar sucursal' }}
                    </button>
                </aside>
            </form>
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
</style>