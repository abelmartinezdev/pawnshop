<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    departments: {
        type: Array,
        default: () => [],
    },
    units: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    department_id: props.product.department_id || '',
    code: props.product.code || '',
    description: props.product.description || '',
    unit: props.product.unit || 'PIEZA',
    min_price: props.product.min_price ?? '',
    max_price: props.product.max_price ?? '',
    is_active: props.product.is_active ?? true,
})

const selectedDepartment = computed(() => {
    return props.departments.find((department) => Number(department.id) === Number(form.department_id)) || null
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 3,
    })
}

const submit = () => {
    form.put(route('products.update', props.product.id), {
        preserveScroll: true,
    })
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        save: 'M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2ZM7 21v-8h10v8M7 3v5h8',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        box: 'M21 8l-9-5-9 5 9 5 9-5ZM3 8v8l9 5 9-5V8M12 13v8',
        tag: 'M20.5 13.5 13.5 20.5a2 2 0 0 1-2.8 0L3 12.8V3h9.8l7.7 7.7a2 2 0 0 1 0 2.8ZM7.5 7.5h.01',
    }

    return icons[icon] || icons.box
}
</script>

<template>
    <Head :title="`Editar ${product.description}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="route('products.show', product.id)"
                        class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Volver
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Productos
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Editar producto
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Actualiza el catálogo para {{ product.description }}.
                    </p>
                </div>

                <Link
                    :href="route('products.show', product.id)"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Ver producto
                </Link>
            </div>

            <form class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]" @submit.prevent="submit">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div
                            class="px-6 py-7 text-white"
                            :style="{ background: `linear-gradient(135deg, ${selectedDepartment?.color || '#5b55a4'}, #312d65)` }"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 text-3xl font-black">
                                    {{ form.code ? form.code.charAt(0).toUpperCase() : 'P' }}
                                </div>

                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-white/60">
                                        Producto
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
                                    Precio mínimo
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ money(form.min_price) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Precio máximo
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ money(form.max_price) }}
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
                                    Código, descripción, unidad y departamento.
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
                                <label class="mb-2 block text-sm font-black text-slate-700">Unidad</label>
                                <select v-model="form.unit" :class="selectClass">
                                    <option
                                        v-for="unit in units"
                                        :key="unit"
                                        class="bg-white text-slate-900"
                                        :value="unit"
                                    >
                                        {{ unit }}
                                    </option>
                                </select>
                                <p v-if="form.errors.unit" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.unit }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-black text-slate-700">Descripción</label>
                                <input v-model="form.description" type="text" :class="inputClass" />
                                <p v-if="form.errors.description" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.description }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-black text-slate-700">Departamento</label>
                                <select v-model="form.department_id" :class="selectClass">
                                    <option
                                        v-for="department in departments"
                                        :key="department.id"
                                        class="bg-white text-slate-900"
                                        :value="department.id"
                                    >
                                        {{ department.display_name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.department_id" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.department_id }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-slate-300 text-[#5b55a4] focus:ring-[#5b55a4]"
                                    />
                                    <span>
                                        <span class="block text-sm font-black text-slate-800">Producto activo</span>
                                        <span class="block text-xs font-semibold text-slate-500">Disponible para nuevas operaciones de empeño.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('tag')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Rango de precio
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Valores de referencia para calcular préstamos.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Precio mínimo</label>
                                <input v-model="form.min_price" type="number" min="0" step="0.001" :class="inputClass" />
                                <p v-if="form.errors.min_price" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.min_price }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Precio máximo</label>
                                <input v-model="form.max_price" type="number" min="0" step="0.001" :class="inputClass" />
                                <p v-if="form.errors.max_price" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.max_price }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <Link
                            :href="route('products.show', product.id)"
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