<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'

const props = defineProps({
    companies: {
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
        }),
    },
})

const records = computed(() => props.companies?.data || [])

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || 'active',
    per_page: props.filters.per_page || 10,
})

const confirmModal = ref(false)
const pendingCompany = ref(null)
const pendingAction = ref('archive')

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const selectClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const cleanFilters = () => {
    return Object.fromEntries(
        Object.entries(form).filter(([_, value]) => value !== '' && value !== null && value !== undefined),
    )
}

const submitFilters = () => {
    router.get(route('companies.index'), cleanFilters(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const clearFilters = () => {
    form.search = ''
    form.status = 'active'
    form.per_page = 10

    router.get(route('companies.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const openArchive = (company) => {
    pendingCompany.value = company
    pendingAction.value = 'archive'
    confirmModal.value = true
}

const openRestore = (company) => {
    pendingCompany.value = company
    pendingAction.value = 'restore'
    confirmModal.value = true
}

const executeAction = () => {
    if (!pendingCompany.value) return

    if (pendingAction.value === 'restore') {
        router.post(route('companies.restore', pendingCompany.value.id), {}, {
            preserveScroll: true,
            onFinish: () => closeConfirm(),
        })

        return
    }

    router.delete(route('companies.destroy', pendingCompany.value.id), {
        preserveScroll: true,
        onFinish: () => closeConfirm(),
    })
}

const closeConfirm = () => {
    confirmModal.value = false
    pendingCompany.value = null
    pendingAction.value = 'archive'
}

const statusClass = (company) => {
    if (company.is_deleted) return 'sicem-status-deleted'
    if (company.is_active) return 'sicem-status-active'

    return 'sicem-status-inactive'
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
        plus: 'M12 5v14M5 12h14',
        search: 'M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z',
        filter: 'M4 5h16M7 12h10M10 19h4',
        building: 'M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 8h2a2 2 0 0 1 2 2v11M8 7h4M8 11h4M8 15h4M3 21h18',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        edit: 'M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z',
        trash: 'M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3',
        refresh: 'M21 12a9 9 0 1 1-3-6.7M21 4v6h-6',
        office: 'M3 21h18M5 21V7l8-4v18M19 21V11l-6-3M9 9h.01M9 13h.01M9 17h.01M15 13h.01M15 17h.01',
        empty: 'M4 7h16M4 12h16M4 17h16',
        x: 'M18 6 6 18M6 6l12 12',
    }

    return icons[icon] || icons.building
}
</script>

<template>
    <Head title="Empresas" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Administración
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Empresas
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Administra las empresas, sus datos fiscales, comisiones y sucursales.
                    </p>
                </div>

                <Link
                    :href="route('companies.create')"
                    class="sicem-btn-primary inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('plus')" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    Nueva empresa
                </Link>
            </div>

            <div class="mb-6 grid gap-5 md:grid-cols-2 xl:grid-cols-5">
                <div class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm xl:col-span-2">
                    <div class="sicem-card-hero p-5 text-white">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-white/60">
                            Empresas registradas
                        </p>
                        <p class="mt-2 text-3xl font-black">
                            {{ summary.total || 0 }}
                        </p>
                    </div>
                    <div class="p-5 text-sm font-semibold text-slate-500">
                        Total incluyendo empresas archivadas.
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-emerald-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Activas</p>
                    <p class="mt-2 text-3xl font-black text-emerald-600">{{ summary.active || 0 }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">En operación</p>
                </div>

                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Inactivas</p>
                    <p class="mt-2 text-3xl font-black text-slate-600">{{ summary.inactive || 0 }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Pausadas</p>
                </div>

                <div class="rounded-[1.75rem] border border-violet-100 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Sucursales</p>
                    <p class="mt-2 text-3xl font-black text-[#5b55a4]">{{ summary.offices || 0 }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-500">Totales</p>
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
                            <h2 class="text-lg font-black text-slate-950">Filtros</h2>
                            <p class="text-sm text-slate-500">
                                Busca por nombre, código, RFC, correo o teléfono.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">Buscar</label>

                            <div class="relative">
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('search')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                                </svg>

                                <input
                                    v-model="form.search"
                                    type="search"
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                                    placeholder="Nombre, RFC, correo..."
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">Estado</label>

                            <select v-model="form.status" :class="selectClass">
                                <option
                                    v-for="status in options.statuses"
                                    :key="status.value"
                                    class="bg-white text-slate-900"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">Por página</label>

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
                            class="sicem-btn-primary rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition"
                        >
                            Aplicar filtros
                        </button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-5 sm:px-6">
                    <h2 class="text-xl font-black text-slate-950">Listado de empresas</h2>
                    <p class="text-sm text-slate-500">Empresas disponibles en el sistema.</p>
                </div>

                <div v-if="records.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Empresa</th>
                                <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Contacto</th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">Sucursales</th>
                                <th class="px-5 py-4 text-center text-xs font-black uppercase tracking-wider text-slate-500">Estado</th>
                                <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr
                                v-for="company in records"
                                :key="company.id"
                                class="transition hover:bg-slate-50/80"
                            >
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('building')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </div>

                                        <div>
                                            <Link
                                                :href="route('companies.show', company.id)"
                                                class="text-sm font-black text-slate-950 hover:text-[#5b55a4] hover:underline"
                                            >
                                                {{ company.name }}
                                            </Link>

                                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                                Código: {{ company.code || 'N/A' }} · RFC: {{ company.rfc || 'N/A' }}
                                            </p>

                                            <p v-if="company.address" class="mt-1 max-w-xl text-xs font-semibold text-slate-500">
                                                {{ company.address }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="text-sm font-black text-slate-800">{{ company.phone || 'Sin teléfono' }}</p>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">{{ company.email || 'Sin correo' }}</p>
                                    <p v-if="company.website" class="mt-1 text-xs font-semibold text-[#5b55a4]">{{ company.website }}</p>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <div class="inline-flex items-center gap-2 rounded-full border border-violet-100 bg-violet-50 px-3 py-1 text-sm font-black text-[#5b55a4]">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <path :d="iconPath('office')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        {{ company.offices_count }}
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <span class="sicem-status-pill" :class="statusClass(company)">
                                        {{ company.status_label }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="route('companies.show', company.id)"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-700 transition hover:bg-[#5b55a4] hover:text-white"
                                            title="Ver detalle"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </Link>

                                        <Link
                                            v-if="!company.is_deleted"
                                            :href="route('companies.edit', company.id)"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-blue-200 bg-white text-blue-600 transition hover:bg-blue-500 hover:text-white"
                                            title="Editar"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('edit')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </Link>

                                        <button
                                            v-if="company.is_deleted"
                                            type="button"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-white text-emerald-600 transition hover:bg-emerald-500 hover:text-white"
                                            title="Restaurar"
                                            @click="openRestore(company)"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('refresh')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>

                                        <button
                                            v-else
                                            type="button"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-red-200 bg-white text-red-600 transition hover:bg-red-500 hover:text-white"
                                            title="Archivar"
                                            @click="openArchive(company)"
                                        >
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path :d="iconPath('trash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
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

                    <h3 class="mt-5 text-xl font-black text-slate-950">No hay empresas</h3>
                    <p class="mt-2 text-sm text-slate-500">Registra la primera empresa para iniciar.</p>

                    <Link
                        :href="route('companies.create')"
                        class="sicem-btn-primary mt-6 inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition"
                    >
                        Nueva empresa
                    </Link>
                </div>

                <div
                    v-if="companies.links?.length"
                    class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <p class="text-sm font-semibold text-slate-500">
                        Mostrando {{ companies.from || 0 }} a {{ companies.to || 0 }} de {{ companies.total || 0 }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="link in companies.links"
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

        <div
            v-if="confirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-md rounded-[2rem] bg-white p-8 shadow-2xl">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full" :class="pendingAction === 'restore' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600'">
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath(pendingAction === 'restore' ? 'refresh' : 'trash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <h2 class="mt-6 text-center text-2xl font-black text-slate-950">
                    {{ pendingAction === 'restore' ? 'Restaurar empresa' : 'Archivar empresa' }}
                </h2>

                <p class="mt-2 text-center text-sm leading-6 text-slate-500">
                    {{ pendingAction === 'restore' ? 'La empresa volverá a estar disponible.' : 'La empresa se archivará y no aparecerá como activa.' }}
                </p>

                <p class="mt-4 rounded-2xl bg-slate-50 p-4 text-center text-sm font-black text-slate-800">
                    {{ pendingCompany?.name }}
                </p>

                <div class="mt-6 flex justify-center gap-3">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="closeConfirm"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="rounded-2xl px-5 py-3 text-sm font-black text-white transition"
                        :class="pendingAction === 'restore' ? 'sicem-btn-green' : 'sicem-btn-danger'"
                        @click="executeAction"
                    >
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.sicem-card-hero {
    background: linear-gradient(135deg, #5b55a4, #312d65) !important;
}

.sicem-btn-primary {
    background-color: #5b55a4 !important;
    color: #ffffff !important;
    border-color: #5b55a4 !important;
}

.sicem-btn-primary:hover {
    background-color: #4f4896 !important;
    color: #ffffff !important;
}

.sicem-btn-danger {
    background-color: #dc2626 !important;
    color: #ffffff !important;
}

.sicem-btn-green {
    background-color: #10b981 !important;
    color: #ffffff !important;
}

.sicem-status-pill {
    display: inline-flex;
    border-radius: 9999px;
    border-width: 1px;
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 900;
}

.sicem-status-active {
    border-color: #bbf7d0 !important;
    background-color: #f0fdf4 !important;
    color: #15803d !important;
}

.sicem-status-inactive {
    border-color: #e2e8f0 !important;
    background-color: #f8fafc !important;
    color: #475569 !important;
}

.sicem-status-deleted {
    border-color: #fecaca !important;
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}
</style>