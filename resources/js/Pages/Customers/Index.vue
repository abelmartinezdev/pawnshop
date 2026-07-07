<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'

const props = defineProps({
    customers: {
        type: Object,
        required: true,
    },
    summary: {
        type: Object,
        default: () => ({}),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    options: {
        type: Object,
        default: () => ({
            statuses: [],
            states: [],
            cities: [],
        }),
    },
})

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'active',
    state: props.filters.state || '',
    city: props.filters.city || '',
    per_page: props.filters.per_page || 15,
})

const records = computed(() => props.customers?.data || [])

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const cleanFilters = () => {
    return Object.fromEntries(
        Object.entries(form).filter(([_, value]) => value !== '' && value !== null && value !== undefined),
    )
}

const submitFilters = () => {
    router.get(route('customers.index'), cleanFilters(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const clearFilters = () => {
    form.search = ''
    form.status = 'active'
    form.state = ''
    form.city = ''
    form.per_page = 15

    router.get(route('customers.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const deleteCustomer = (customer) => {
    if (!confirm(`¿Eliminar al cliente "${customer.name}"?`)) {
        return
    }

    router.delete(route('customers.destroy', customer.id), {
        preserveScroll: true,
    })
}

const restoreCustomer = (customer) => {
    if (!confirm(`¿Restaurar al cliente "${customer.name}"?`)) {
        return
    }

    router.post(route('customers.restore', customer.id), {}, {
        preserveScroll: true,
    })
}

const statusBadgeClass = (customer) => {
    return customer.is_deleted
        ? 'border-red-200 bg-red-50 text-red-700'
        : 'border-emerald-200 bg-emerald-50 text-emerald-700'
}

const iconPath = (icon) => {
    const icons = {
        plus: 'M12 5v14M5 12h14',
        users: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        search: 'M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z',
        filter: 'M4 5h16M7 12h10M10 19h4',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        restore: 'M3 12a9 9 0 1 0 3-6.7M3 4v6h6',
        phone: 'M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6.4 6.4l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2Z',
        id: 'M4 5h16v14H4V5ZM8 9h4M8 13h8M8 17h6M15 9h1',
        empty: 'M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5ZM3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9',
    }

    return icons[icon] || icons.users
}
</script>

<template>
    <Head title="Clientes" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Operación
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Clientes
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Administra el padrón de clientes, datos de contacto, identificación y relación con empeños.
                    </p>
                </div>

                <Link
                    :href="route('customers.create')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#5b55a4] px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5 hover:bg-[#4f4896]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('plus')" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    Nuevo cliente
                </Link>
            </div>

            <div class="mb-6 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm">
                    <div class="bg-gradient-to-br from-[#5b55a4] to-[#312d65] p-5 text-white">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                            Total clientes
                        </p>
                        <p class="mt-2 text-3xl font-black">
                            {{ summary.total || 0 }}
                        </p>
                    </div>
                    <div class="p-5 text-sm font-semibold text-slate-500">
                        Registrados en el sistema
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Activos
                    </p>
                    <p class="mt-2 text-3xl font-black text-emerald-600">
                        {{ summary.active || 0 }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        Disponibles para operación
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-red-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Eliminados
                    </p>
                    <p class="mt-2 text-3xl font-black text-red-600">
                        {{ summary.deleted || 0 }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        En papelera
                    </p>
                </div>

                <div class="rounded-[1.75rem] border border-violet-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                        Con empeños
                    </p>
                    <p class="mt-2 text-3xl font-black text-[#5b55a4]">
                        {{ summary.with_pawns || 0 }}
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">
                        Tienen historial prendario
                    </p>
                </div>
            </div>

            <section class="mb-6 rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <form class="p-5 sm:p-6" @submit.prevent="submitFilters">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('filter')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-lg font-black text-slate-950">
                                Filtros
                            </h2>
                            <p class="text-sm text-slate-500">
                                Busca por nombre, teléfono, correo, RFC, ciudad o identificación.
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
                                    <path :d="iconPath('search')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>

                                <input
                                    v-model="form.search"
                                    type="search"
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                                    placeholder="Nombre, RFC, teléfono..."
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Estado del registro
                            </label>

                            <select v-model="form.status" :class="selectClass">
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
                                Estado
                            </label>

                            <select v-model="form.state" :class="selectClass">
                                <option class="bg-white text-slate-900" value="">Todos</option>
                                <option
                                    v-for="state in options.states"
                                    :key="state"
                                    :value="state"
                                    class="bg-white text-slate-900"
                                >
                                    {{ state }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Ciudad
                            </label>

                            <select v-model="form.city" :class="selectClass">
                                <option class="bg-white text-slate-900" value="">Todas</option>
                                <option
                                    v-for="city in options.cities"
                                    :key="city"
                                    :value="city"
                                    class="bg-white text-slate-900"
                                >
                                    {{ city }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Por página
                            </label>

                            <select v-model="form.per_page" :class="selectClass">
                                <option class="bg-white text-slate-900" :value="10">10</option>
                                <option class="bg-white text-slate-900" :value="15">15</option>
                                <option class="bg-white text-slate-900" :value="25">25</option>
                                <option class="bg-white text-slate-900" :value="50">50</option>
                            </select>
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
                    <h2 class="text-xl font-black text-slate-950">
                        Padrón de clientes
                    </h2>
                    <p class="text-sm text-slate-500">
                        Clientes registrados para operaciones de empeño.
                    </p>
                </div>

                <div v-if="records.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Cliente
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Contacto
                                </th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                                    Ubicación
                                </th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">
                                    Empeños
                                </th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">
                                    Estado
                                </th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr
                                v-for="customer in records"
                                :key="customer.id"
                                class="transition hover:bg-slate-50/80"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#5b55a4] text-sm font-black text-white">
                                            {{ customer.name?.charAt(0)?.toUpperCase() || 'C' }}
                                        </div>

                                        <div>
                                            <p class="text-sm font-black text-slate-950">
                                                {{ customer.name }}
                                            </p>

                                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                                RFC: {{ customer.rfc || 'No capturado' }}
                                            </p>

                                            <p class="text-xs font-semibold text-slate-400">
                                                ID: {{ customer.code_id || 'No capturado' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm font-black text-slate-700">
                                        {{ customer.display_phone || 'Sin teléfono' }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ customer.email || 'Sin correo' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm font-black text-slate-700">
                                        {{ customer.city || 'Sin ciudad' }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ customer.state || 'Sin estado' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex rounded-full border border-violet-200 bg-violet-50 px-3 py-1 text-xs font-black text-[#5b55a4]">
                                        {{ customer.pawns_count || 0 }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-full border px-3 py-1 text-xs font-black"
                                        :class="statusBadgeClass(customer)"
                                    >
                                        {{ customer.is_deleted ? 'Eliminado' : 'Activo' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="route('customers.show', customer.id)"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-950 hover:text-white"
                                            title="Ver"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </Link>

                                        <Link
                                            v-if="!customer.is_deleted"
                                            :href="route('customers.edit', customer.id)"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-[#5b55a4] hover:text-white"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('edit')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </Link>

                                        <button
                                            v-if="!customer.is_deleted"
                                            type="button"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-red-200 bg-white text-red-600 transition hover:bg-red-500 hover:text-white"
                                            title="Eliminar"
                                            @click="deleteCustomer(customer)"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('trash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>

                                        <button
                                            v-else
                                            type="button"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-white text-emerald-600 transition hover:bg-emerald-500 hover:text-white"
                                            title="Restaurar"
                                            @click="restoreCustomer(customer)"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('restore')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
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
                        No hay clientes
                    </h3>

                    <p class="mt-2 text-sm text-slate-500">
                        Registra tu primer cliente para comenzar operaciones.
                    </p>

                    <Link
                        :href="route('customers.create')"
                        class="mt-6 inline-flex items-center justify-center rounded-2xl bg-[#5b55a4] px-5 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:bg-[#4f4896]"
                    >
                        Nuevo cliente
                    </Link>
                </div>

                <div
                    v-if="customers.links?.length"
                    class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <p class="text-sm font-semibold text-slate-500">
                        Mostrando {{ customers.from || 0 }} a {{ customers.to || 0 }} de {{ customers.total || 0 }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="link in customers.links"
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