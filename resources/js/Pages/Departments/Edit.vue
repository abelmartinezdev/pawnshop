<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    department: {
        type: Object,
        required: true,
    },
    dayOptions: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    code: props.department.code || '',
    description: props.department.description || '',
    auction: props.department.auction || '',
    term: props.department.term || '',
    loan_rate: props.department.loan_rate ?? '',
    daily_interest_rate: props.department.daily_interest_rate ?? '',
    monthly_interest_rate: props.department.monthly_interest_rate ?? '',
    iva_rate: props.department.iva_rate ?? '',
    cat_annual: props.department.cat_annual ?? '',
    cat_annual_noiva: props.department.cat_annual_noiva ?? '',
    color: props.department.color || '#5b55a4',
    icon: props.department.icon || '',
    is_active: props.department.is_active ?? true,
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const previewInitial = computed(() => {
    return form.code ? form.code.charAt(0).toUpperCase() : 'D'
})

const submit = () => {
    form.put(route('departments.update', props.department.id), {
        preserveScroll: true,
    })
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        save: 'M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2ZM7 21v-8h10v8M7 3v5h8',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        box: 'M21 8l-9-5-9 5 9 5 9-5ZM3 8v8l9 5 9-5V8M12 13v8',
        clock: 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        palette: 'M12 3a9 9 0 0 0 0 18h1.5a1.5 1.5 0 0 0 0-3H12a2 2 0 0 1 0-4h2a7 7 0 0 0 0-14h-2Z',
    }

    return icons[icon] || icons.box
}
</script>

<template>
    <Head :title="`Editar ${department.description}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="route('departments.show', department.id)"
                        class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Volver
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Departamentos
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Editar departamento
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Actualiza las reglas financieras de {{ department.description }}.
                    </p>
                </div>

                <Link
                    :href="route('departments.show', department.id)"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Ver departamento
                </Link>
            </div>

            <form class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]" @submit.prevent="submit">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="px-6 py-7 text-white" :style="{ background: `linear-gradient(135deg, ${form.color || '#5b55a4'}, #312d65)` }">
                            <div class="flex items-center gap-4">
                                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 text-3xl font-black">
                                    {{ previewInitial }}
                                </div>

                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-white/60">
                                        Departamento
                                    </p>
                                    <p class="mt-1 text-2xl font-black">
                                        {{ form.code || 'SIN CÓDIGO' }}
                                    </p>
                                    <p class="mt-1 text-sm text-white/70">
                                        {{ form.description || 'Sin descripción' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-6 sm:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Plazo
                                </p>
                                <p class="mt-1 text-2xl font-black text-slate-950">
                                    {{ form.term || 0 }} días
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Remate
                                </p>
                                <p class="mt-1 text-2xl font-black text-slate-950">
                                    {{ form.auction || 0 }} días
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('box')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Información general
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Código, descripción y estado.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Código</label>
                                <input v-model="form.code" type="text" :class="inputClass" />
                                <p v-if="form.errors.code" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.code }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Descripción</label>
                                <input v-model="form.description" type="text" :class="inputClass" />
                                <p v-if="form.errors.description" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.description }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-slate-300 text-[#5b55a4] focus:ring-[#5b55a4]"
                                    />
                                    <span>
                                        <span class="block text-sm font-black text-slate-800">Departamento activo</span>
                                        <span class="block text-xs font-semibold text-slate-500">Disponible para productos y operaciones nuevas.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('clock')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Plazos
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Vencimiento y remate.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Días de plazo</label>
                                <select v-model="form.term" :class="selectClass">
                                    <option class="bg-white text-slate-900" value="">Seleccione</option>
                                    <option v-for="day in dayOptions" :key="`term-${day}`" class="bg-white text-slate-900" :value="day">
                                        {{ day }}
                                    </option>
                                </select>
                                <p v-if="form.errors.term" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.term }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Días para remate</label>
                                <select v-model="form.auction" :class="selectClass">
                                    <option class="bg-white text-slate-900" value="">Seleccione</option>
                                    <option v-for="day in dayOptions" :key="`auction-${day}`" class="bg-white text-slate-900" :value="day">
                                        {{ day }}
                                    </option>
                                </select>
                                <p v-if="form.errors.auction" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.auction }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('percent')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Tasas e intereses
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Reglas financieras del departamento.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Porcentaje de préstamo</label>
                                <input v-model="form.loan_rate" type="number" step="0.001" :class="inputClass" />
                                <p v-if="form.errors.loan_rate" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.loan_rate }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Interés diario</label>
                                <input v-model="form.daily_interest_rate" type="number" step="0.001" :class="inputClass" />
                                <p v-if="form.errors.daily_interest_rate" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.daily_interest_rate }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Interés mensual</label>
                                <input v-model="form.monthly_interest_rate" type="number" step="0.001" :class="inputClass" />
                                <p v-if="form.errors.monthly_interest_rate" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.monthly_interest_rate }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">% de I.V.A.</label>
                                <input v-model="form.iva_rate" type="number" step="0.001" :class="inputClass" />
                                <p v-if="form.errors.iva_rate" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.iva_rate }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">CAT anual</label>
                                <input v-model="form.cat_annual" type="number" step="0.001" :class="inputClass" />
                                <p v-if="form.errors.cat_annual" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.cat_annual }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">CAT anual sin IVA</label>
                                <input v-model="form.cat_annual_noiva" type="number" step="0.001" :class="inputClass" />
                                <p v-if="form.errors.cat_annual_noiva" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.cat_annual_noiva }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('palette')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Apariencia
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Color e ícono opcional.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Color</label>
                                <input v-model="form.color" type="text" :class="inputClass" />
                                <p v-if="form.errors.color" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.color }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Ícono</label>
                                <input v-model="form.icon" type="text" :class="inputClass" />
                                <p v-if="form.errors.icon" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.icon }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <Link
                            :href="route('departments.show', department.id)"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancelar
                        </Link>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#5b55a4] px-6 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5 hover:bg-[#4f4896] disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('save')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </AdminLayout>
</template>