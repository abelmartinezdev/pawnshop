<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const initials = computed(() => {
    return props.customer.name
        ? props.customer.name
            .split(' ')
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part.charAt(0).toUpperCase())
            .join('')
        : 'C'
})

const pawns = computed(() => props.customer.pawns || [])
const movements = computed(() => props.customer.movements || [])

const deleteCustomer = () => {
    if (!confirm(`¿Eliminar al cliente "${props.customer.name}"?`)) {
        return
    }

    router.delete(route('customers.destroy', props.customer.id), {
        preserveScroll: true,
    })
}

const restoreCustomer = () => {
    if (!confirm(`¿Restaurar al cliente "${props.customer.name}"?`)) {
        return
    }

    router.post(route('customers.restore', props.customer.id), {}, {
        preserveScroll: true,
    })
}

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const pawnStatusClass = (status) => {
    return {
        active: 'border-emerald-200 bg-emerald-50 text-emerald-700',
        expired: 'border-amber-200 bg-amber-50 text-amber-700',
        paid: 'border-blue-200 bg-blue-50 text-blue-700',
        cancelled: 'border-red-200 bg-red-50 text-red-700',
    }[status] || 'border-slate-200 bg-slate-50 text-slate-700'
}

const movementAmountClass = (amount) => {
    return Number(amount || 0) >= 0 ? 'text-emerald-600' : 'text-red-600'
}

const movementRowClass = (movement) => {
    if (movement.is_cancelled) {
        return 'bg-red-50/70'
    }

    if (movement.type === 'pawn') {
        return 'bg-amber-50/60'
    }

    return 'bg-white'
}

const movementSign = (amount) => {
    return Number(amount || 0) >= 0 ? '+' : '-'
}

const absMoney = (amount) => {
    return money(Math.abs(Number(amount || 0)))
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        restore: 'M3 12a9 9 0 1 0 3-6.7M3 4v6h6',
        phone: 'M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6.4 6.4l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2Z',
        mail: 'M4 6h16v12H4V6ZM4 7l8 6 8-6',
        map: 'M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3ZM9 3v15M15 6v15',
        id: 'M4 5h16v14H4V5ZM8 9h4M8 13h8M8 17h6M15 9h1',
        gem: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        plus: 'M12 5v14M5 12h14',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        activity: 'M22 12h-4l-3 8-6-16-3 8H2',
        empty: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
    }

    return icons[icon] || icons.user
}
</script>

<template>
    <Head :title="customer.name" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="route('customers.index')"
                        class="mb-4 inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Volver
                    </Link>

                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Cliente
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        {{ customer.name }}
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Perfil del cliente, empeños registrados y últimos movimientos.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a
                        v-if="!customer.is_deleted && urls.new_pawn"
                        :href="urls.new_pawn"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:bg-blue-600"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('plus')" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Empeñar
                    </a>

                    <Link
                        v-if="!customer.is_deleted"
                        :href="route('customers.edit', customer.id)"
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
                v-if="customer.is_deleted"
                class="mb-6 rounded-[1.75rem] border border-red-200 bg-red-50 p-5 text-red-700"
            >
                <p class="font-black">
                    Cliente eliminado
                </p>
                <p class="mt-1 text-sm">
                    Este cliente fue eliminado el {{ customer.deleted_at }}. Puedes restaurarlo si necesitas volver a usarlo.
                </p>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="bg-gradient-to-br from-[#5b55a4] to-[#312d65] px-6 py-7 text-white">
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/15 text-3xl font-black">
                                        {{ initials }}
                                    </div>

                                    <div>
                                        <p class="text-sm font-bold uppercase tracking-[0.22em] text-white/60">
                                            Cliente
                                        </p>
                                        <p class="mt-1 text-2xl font-black">
                                            {{ customer.name }}
                                        </p>
                                        <p class="mt-1 text-sm text-white/70">
                                            {{ customer.display_phone || 'Sin teléfono' }}
                                        </p>
                                    </div>
                                </div>

                                <span
                                    class="inline-flex self-start rounded-full border px-3 py-1 text-xs font-black sm:self-center"
                                    :class="customer.is_deleted ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'"
                                >
                                    {{ customer.is_deleted ? 'Eliminado' : 'Activo' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-4 p-6 md:grid-cols-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Empeños
                                </p>
                                <p class="mt-1 text-2xl font-black text-[#5b55a4]">
                                    {{ customer.pawns_count || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">
                                    Activos
                                </p>
                                <p class="mt-1 text-2xl font-black text-emerald-600">
                                    {{ customer.active_pawns_count || 0 }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Monto empeñado
                                </p>
                                <p class="mt-1 text-xl font-black text-slate-950">
                                    {{ money(customer.total_pawned) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Registro
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-800">
                                    {{ customer.created_at || 'No disponible' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-lg font-black text-slate-950">
                                        Empeños del cliente
                                    </h2>
                                    <p class="text-sm text-slate-500">
                                        Operaciones prendarias registradas.
                                    </p>
                                </div>

                                <a
                                    v-if="!customer.is_deleted && urls.new_pawn"
                                    :href="urls.new_pawn"
                                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-500 px-4 py-2 text-sm font-black text-white transition hover:bg-blue-600"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                        <path :d="iconPath('plus')" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                    </svg>
                                    Empeñar
                                </a>
                            </div>
                        </div>

                        <div v-if="pawns.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Folio
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Fecha
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Vencimiento
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Monto
                                        </th>
                                        <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">
                                            Estatus
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr
                                        v-for="pawn in pawns"
                                        :key="pawn.id"
                                        class="transition hover:bg-slate-50/80"
                                    >
                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-950">
                                                {{ pawn.folio || `#${pawn.id}` }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                                ID interno: {{ pawn.id }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-sm font-bold text-slate-700">
                                            {{ pawn.date || pawn.created_at || 'No disponible' }}
                                        </td>

                                        <td class="px-5 py-4 text-sm font-bold text-slate-700">
                                            {{ pawn.date_expiration || 'No especificado' }}
                                        </td>

                                        <td class="px-5 py-4 text-right text-sm font-black text-slate-950">
                                            {{ money(pawn.total) }}
                                        </td>

                                        <td class="px-5 py-4 text-center">
                                            <span
                                                class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                                :class="pawnStatusClass(pawn.status)"
                                            >
                                                {{ pawn.status_label }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <a
                                                v-if="pawn.show_url"
                                                :href="pawn.show_url"
                                                class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-3 py-2 text-xs font-black text-white transition hover:bg-blue-600"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                    <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                Detalles
                                            </a>
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
                                Sin empeños registrados
                            </h3>

                            <p class="mt-2 text-sm text-slate-500">
                                Este cliente aún no tiene operaciones prendarias.
                            </p>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path :d="iconPath('activity')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>

                                <div>
                                    <h2 class="text-lg font-black text-slate-950">
                                        Últimos movimientos
                                    </h2>
                                    <p class="text-sm text-slate-500">
                                        Historial reciente relacionado con sus empeños.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-if="movements.length" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Fecha
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Movimiento
                                        </th>
                                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                            Folio
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Importe
                                        </th>
                                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="movement in movements"
                                        :key="movement.id"
                                        :class="movementRowClass(movement)"
                                    >
                                        <td class="px-5 py-4 text-sm font-bold text-slate-700">
                                            {{ movement.created_at }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <p class="text-sm font-black text-slate-800">
                                                {{ movement.type_label }}
                                                <span
                                                    v-if="movement.is_cancelled"
                                                    class="ml-2 rounded bg-red-500 px-2 py-0.5 text-[10px] font-black uppercase text-white"
                                                >
                                                    Cancelado
                                                </span>
                                            </p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                                {{ movement.comments || 'Sin comentarios' }}
                                            </p>
                                        </td>

                                        <td class="px-5 py-4 text-sm font-black text-slate-700">
                                            {{ movement.pawn?.folio || 'N/A' }}
                                        </td>

                                        <td
                                            class="px-5 py-4 text-right text-sm font-black"
                                            :class="movementAmountClass(movement.amount)"
                                        >
                                            {{ movementSign(movement.amount) }}{{ absMoney(movement.amount) }}
                                        </td>

                                        <td class="px-5 py-4 text-right">
                                            <a
                                                v-if="movement.pawn?.show_url"
                                                :href="movement.pawn.show_url"
                                                class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-3 py-2 text-xs font-black text-white transition hover:bg-blue-600"
                                            >
                                                Detalles
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="p-10 text-center text-sm font-semibold text-slate-500">
                            No hay movimientos registrados para este cliente.
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Datos generales
                                </h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    Información registrada del cliente.
                                </p>
                            </div>

                            <Link
                                v-if="!customer.is_deleted"
                                :href="route('customers.edit', customer.id)"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b55a4] text-white shadow-lg shadow-violet-200 transition hover:bg-[#4f4896]"
                                title="Editar"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('edit')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </Link>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div class="grid grid-cols-[130px_1fr] gap-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-black text-slate-500">Nombre</p>
                                <p class="text-sm font-black text-slate-900">{{ customer.name }}</p>
                            </div>

                            <div class="grid grid-cols-[130px_1fr] gap-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-black text-slate-500">{{ customer.type_label || 'Identificación' }}</p>
                                <p class="text-sm font-black text-slate-900">{{ customer.code_id || 'No capturado' }}</p>
                            </div>

                            <div class="grid grid-cols-[130px_1fr] gap-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-black text-slate-500">RFC</p>
                                <p class="text-sm font-black text-slate-900">{{ customer.rfc || 'No capturado' }}</p>
                            </div>

                            <div class="grid grid-cols-[130px_1fr] gap-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-black text-slate-500">Email</p>
                                <p class="text-sm font-black text-slate-900">{{ customer.email || 'No capturado' }}</p>
                            </div>

                            <div class="grid grid-cols-[130px_1fr] gap-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-black text-slate-500">Teléfono</p>
                                <p class="text-sm font-black text-slate-900">{{ customer.phone || 'No capturado' }}</p>
                            </div>

                            <div class="grid grid-cols-[130px_1fr] gap-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-black text-slate-500">Celular</p>
                                <p class="text-sm font-black text-slate-900">{{ customer.mobile || 'No capturado' }}</p>
                            </div>

                            <div class="grid grid-cols-[130px_1fr] gap-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-black text-slate-500">Ciudad</p>
                                <p class="text-sm font-black text-slate-900">{{ customer.city || 'No capturado' }}</p>
                            </div>

                            <div class="grid grid-cols-[130px_1fr] gap-4 rounded-2xl bg-slate-50 p-4">
                                <p class="text-sm font-black text-slate-500">Dirección</p>
                                <p class="text-sm font-black text-slate-900">{{ customer.address || 'No capturado' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Acciones
                        </h2>

                        <div class="mt-5 space-y-3">
                            <button
                                v-if="!customer.is_deleted"
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-black text-red-600 transition hover:bg-red-500 hover:text-white"
                                @click="deleteCustomer"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('trash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Eliminar cliente
                            </button>

                            <button
                                v-else
                                type="button"
                                class="flex w-full items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-600"
                                @click="restoreCustomer"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('restore')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Restaurar cliente
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AdminLayout>
</template>