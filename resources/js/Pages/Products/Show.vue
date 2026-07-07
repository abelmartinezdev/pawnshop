<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
})

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
        minimumFractionDigits: 3,
    })
}

const percent = (value) => {
    return `${Number(value || 0).toFixed(3)}%`
}

const deleteProduct = () => {
    if (!confirm(`¿Eliminar el producto "${props.product.description}"?`)) {
        return
    }

    router.delete(route('products.destroy', props.product.id), {
        preserveScroll: true,
    })
}

const restoreProduct = () => {
    if (!confirm(`¿Restaurar el producto "${props.product.description}"?`)) {
        return
    }

    router.post(route('products.restore', props.product.id), {}, {
        preserveScroll: true,
    })
}

const statusLabel = () => {
    if (props.product.is_deleted) return 'Eliminado'
    if (!props.product.is_active) return 'Inactivo'

    return 'Activo'
}

const statusBadgeClass = () => {
    if (props.product.is_deleted) {
        return 'border-red-200 bg-red-50 text-red-700'
    }

    if (!props.product.is_active) {
        return 'border-amber-200 bg-amber-50 text-amber-700'
    }

    return 'border-emerald-200 bg-emerald-50 text-emerald-700'
}

const pawnStatusClass = (status) => {
    return {
        active: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        expired: 'border-amber-200 bg-amber-50 text-amber-700',
        paid: 'border-blue-200 bg-blue-50 text-blue-700',
        cancelled: 'border-red-200 bg-red-50 text-red-700',
        countersigned: 'border-violet-200 bg-violet-50 text-[#5b55a4]',
    }[status] || 'border-slate-200 bg-slate-50 text-slate-700'
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        restore: 'M3 12a9 9 0 1 0 3-6.7M3 4v6h6',
        box: 'M21 8l-9-5-9 5 9 5 9-5ZM3 8v8l9 5 9-5V8M12 13v8',
        tag: 'M20.5 13.5 13.5 20.5a2 2 0 0 1-2.8 0L3 12.8V3h9.8l7.7 7.7a2 2 0 0 1 0 2.8ZM7.5 7.5h.01',
        percent: 'M19 5 5 19M7 7h.01M17 17h.01',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        gem: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        empty: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
    }

    return icons[icon] || icons.box
}
</script>

<template>
    <Head :title="product.description" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="route('products.index')"
                        class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Volver
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Producto
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        {{ product.description }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Detalle del producto, departamento y uso reciente en empeños.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <Link
                        v-if="!product.is_deleted"
                        :href="route('products.edit', product.id)"
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
                v-if="product.is_deleted"
                class="mb-6 rounded-[1.75rem] border border-red-200 bg-red-50 p-5 text-red-700"
            >
                <p class="font-black">
                    Producto eliminado
                </p>
                <p class="mt-1 text-sm">
                    Este producto fue eliminado el {{ product.deleted_at }}. Puedes restaurarlo si necesitas volver a usarlo.
                </p>
            </div>

            <div class="grid gap-6 xl:grid-cols-[0.75fr_1.25fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div
                            class="px-6 py-7 text-white"
                            :style="{ background: `linear-gradient(135deg, ${product.department?.color || '#5b55a4'}, #312d65)` }"
                        >
                            <div class="flex items-center gap-4">
                                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 text-3xl font-black">
                                    {{ product.code?.charAt(0) || 'P' }}
                                </div>

                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-white/60">
                                        Código
                                    </p>
                                    <p class="mt-1 text-2xl font-black">
                                        {{ product.code }}
                                    </p>
                                    <p class="mt-1 text-sm text-white/70">
                                        {{ product.description }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 p-6 sm:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Unidad
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ product.unit }}
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
                            Rango de precio
                        </h2>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Precio mínimo
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ money(product.min_price) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Precio máximo
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ money(product.max_price) }}
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
                                v-if="!product.is_deleted"
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-black text-red-600 transition hover:bg-red-500 hover:text-white"
                                @click="deleteProduct"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('trash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Eliminar producto
                            </button>

                            <button
                                v-else
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-600"
                                @click="restoreProduct"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('restore')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Restaurar producto
                            </button>
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Departamento
                            </h2>
                            <p class="text-sm text-slate-500">
                                Reglas financieras heredadas por este producto.
                            </p>
                        </div>

                        <div v-if="product.department" class="grid gap-4 p-6 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 p-4 md:col-span-3">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Departamento
                                </p>
                                <Link
                                    :href="route('departments.show', product.department.id)"
                                    class="mt-1 inline-flex text-sm font-black text-[#5b55a4] hover:underline"
                                >
                                    {{ product.department.display_name }}
                                </Link>
                            </div>

                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    Préstamo
                                </p>
                                <p class="mt-1 text-xl font-black text-[#5b55a4]">
                                    {{ percent(product.department.loan_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Interés diario
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ percent(product.department.daily_interest_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Interés mensual
                                </p>
                                <p class="mt-1 text-xl font-black text-emerald-600">
                                    {{ percent(product.department.monthly_interest_rate) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">
                                    Plazo
                                </p>
                                <p class="mt-1 text-xl font-black text-blue-700">
                                    {{ product.department.term || 0 }} días
                                </p>
                            </div>

                            <div class="rounded-2xl bg-amber-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-500">
                                    Remate
                                </p>
                                <p class="mt-1 text-xl font-black text-amber-700">
                                    {{ product.department.auction || 0 }} días
                                </p>
                            </div>
                        </div>

                        <div v-else class="p-10 text-center text-sm font-semibold text-slate-500">
                            Este producto no tiene departamento asignado.
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <h2 class="text-lg font-black text-slate-950">
                                Uso reciente
                            </h2>
                            <p class="text-sm text-slate-500">
                                Últimos empeños donde se utilizó este producto.
                            </p>
                        </div>

                        <div v-if="product.recent_items?.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Folio
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Cliente
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Descripción
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Valor
                                        </th>
                                        <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">
                                            Estado
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr
                                        v-for="item in product.recent_items"
                                        :key="item.id"
                                        class="transition hover:bg-slate-50/80"
                                    >
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-950">
                                                {{ item.pawn?.folio || 'N/A' }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                                {{ item.pawn?.created_at || item.created_at }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-800">
                                                {{ item.pawn?.customer?.name || 'Sin cliente' }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                                {{ item.pawn?.customer?.phone || 'Sin teléfono' }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="text-sm font-semibold text-slate-700">
                                                {{ item.description }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                                Cantidad: {{ item.quantity }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black text-slate-950">
                                            {{ money(item.value) }}
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <span
                                                class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                                :class="pawnStatusClass(item.pawn?.status)"
                                            >
                                                {{ item.pawn?.status_label || 'N/A' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <Link
                                                v-if="item.pawn"
                                                :href="route('pawns.show', item.pawn.id)"
                                                class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-3 py-2 text-xs font-black text-white transition hover:bg-blue-600"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                    <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                Detalles
                                            </Link>
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
                                Sin uso registrado
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Este producto aún no ha sido usado en empeños.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>