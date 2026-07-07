<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
    department: {
        type: Object,
        required: true,
    },
})

const percent = (value) => {
    return `${Number(value || 0).toFixed(3)}%`
}

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 3,
    })
}

const deleteDepartment = () => {
    if (!confirm(`¿Eliminar el departamento "${props.department.description}"?`)) {
        return
    }

    router.delete(route('departments.destroy', props.department.id), {
        preserveScroll: true,
    })
}

const restoreDepartment = () => {
    if (!confirm(`¿Restaurar el departamento "${props.department.description}"?`)) {
        return
    }

    router.post(route('departments.restore', props.department.id), {}, {
        preserveScroll: true,
    })
}

const statusLabel = () => {
    if (props.department.is_deleted) return 'Eliminado'
    if (!props.department.is_active) return 'Inactivo'

    return 'Activo'
}

const statusBadgeClass = () => {
    if (props.department.is_deleted) {
        return 'border-red-200 bg-red-50 text-red-700'
    }

    if (!props.department.is_active) {
        return 'border-amber-200 bg-amber-50 text-amber-700'
    }

    return 'border-emerald-200 bg-emerald-50 text-emerald-700'
}

const productStatusClass = (product) => {
    return product.is_active
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700'
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        restore: 'M3 12a9 9 0 1 0 3-6.7M3 4v6h6',
        box: 'M21 8l-9-5-9 5 9 5 9-5ZM3 8v8l9 5 9-5V8M12 13v8',
        clock: 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        chart: 'M4 19V5M4 19h16M8 16v-5M12 16V8M16 16v-8',
        empty: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
    }

    return icons[icon] || icons.box
}
</script>

<template>
    <Head :title="department.description" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="route('departments.index')"
                        class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Volver
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Departamento
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        {{ department.description }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Reglas financieras y productos asociados.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link
                        v-if="!department.is_deleted"
                        :href="route('departments.edit', department.id)"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#5b55a4] px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5 hover:bg-[#4f4896]"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('edit')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Editar
                    </Link>
                </div>
            </div>

            <div
                v-if="department.is_deleted"
                class="mb-6 rounded-[1.75rem] border border-red-200 bg-red-50 p-5 text-red-700"
            >
                <p class="font-black">
                    Departamento eliminado
                </p>
                <p class="mt-1 text-sm">
                    Este departamento fue eliminado el {{ department.deleted_at }}. Puedes restaurarlo si necesitas volver a usarlo.
                </p>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div
                            class="px-6 py-7 text-white"
                            :style="{ background: `linear-gradient(135deg, ${department.color || '#5b55a4'}, #312d65)` }"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 text-3xl font-black">
                                    {{ department.code?.charAt(0) || 'D' }}
                                </div>

                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-white/60">
                                        Código
                                    </p>
                                    <p class="mt-1 text-2xl font-black">
                                        {{ department.code }}
                                    </p>
                                    <p class="mt-1 text-sm text-white/70">
                                        {{ department.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-6 sm:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Productos
                                </p>
                                <p class="mt-1 text-2xl font-black text-[#5b55a4]">
                                    {{ department.products_count || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Estado
                                </p>
                                <span
                                    class="mt-2 inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                    :class="statusBadgeClass()"
                                >
                                    {{ statusLabel() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Plazos
                        </h2>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                    Días de plazo
                                </p>
                                <p class="mt-1 text-2xl font-black text-blue-700">
                                    {{ department.term || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-amber-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-500">
                                    Días para remate
                                </p>
                                <p class="mt-1 text-2xl font-black text-amber-700">
                                    {{ department.auction || 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Acciones
                        </h2>

                        <div class="mt-5 space-y-3">
                            <button
                                v-if="!department.is_deleted"
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-black text-red-600 transition hover:bg-red-500 hover:text-white"
                                @click="deleteDepartment"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('trash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Eliminar departamento
                            </button>

                            <button
                                v-else
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-600"
                                @click="restoreDepartment"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('restore')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Restaurar departamento
                            </button>
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Reglas financieras
                            </h2>
                            <p class="text-sm text-slate-500">
                                Parámetros usados para calcular préstamos e intereses.
                            </p>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-3">
                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    Préstamo
                                </p>
                                <p class="mt-1 text-xl font-black text-[#5b55a4]">
                                    {{ percent(department.loan_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Interés diario
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ percent(department.daily_interest_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Interés mensual
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ percent(department.monthly_interest_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    IVA
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ percent(department.iva_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    CAT anual
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ percent(department.cat_annual) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    CAT sin IVA
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ percent(department.cat_annual_noiva) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Productos relacionados
                            </h2>
                            <p class="text-sm text-slate-500">
                                Últimos productos configurados dentro de este departamento.
                            </p>
                        </div>

                        <div v-if="department.products?.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Código
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Descripción
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Unidad
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Precio
                                        </th>
                                        <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">
                                            Estado
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr
                                        v-for="product in department.products"
                                        :key="product.id"
                                        class="transition hover:bg-slate-50/80"
                                    >
                                        <td class="px-5 py-4 text-sm font-black text-slate-950">
                                            {{ product.code }}
                                        </td>

                                        <td class="px-5 py-4 text-sm font-semibold text-slate-700">
                                            {{ product.description }}
                                        </td>

                                        <td class="px-5 py-4 text-sm font-semibold text-slate-500">
                                            {{ product.unit }}
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black text-slate-900">
                                            {{ money(product.min_price) }} - {{ money(product.max_price) }}
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <span
                                                class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                                :class="productStatusClass(product)"
                                            >
                                                {{ product.is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="p-10 text-center">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-[#5b55a4]">
                                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('empty')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <h3 class="mt-5 text-xl font-black text-slate-950">
                                Sin productos
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Este departamento aún no tiene productos asociados.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>