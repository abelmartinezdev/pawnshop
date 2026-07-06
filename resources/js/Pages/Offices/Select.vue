<script setup>
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    offices: {
        type: Array,
        default: () => [],
    },
    canSelectAnyOffice: {
        type: Boolean,
        default: false,
    },
    hasAssignedOffice: {
        type: Boolean,
        default: false,
    },
    user: {
        type: Object,
        default: () => ({}),
    },
})

const page = usePage()
const search = ref('')
const processingOfficeId = ref(null)

const appName = computed(() => {
    return import.meta.env.VITE_APP_NAME || 'SICEM'
})

const filteredOffices = computed(() => {
    const term = search.value.trim().toLowerCase()

    if (!term) {
        return props.offices
    }

    return props.offices.filter((office) => {
        const text = [
            office.name,
            office.code,
            office.serie,
            office.address,
            office.phone,
            office.company?.name,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase()

        return text.includes(term)
    })
})

const selectOffice = (office) => {
    processingOfficeId.value = office.id

    router.post(
        route('offices.select.store'),
        {
            office_id: office.id,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                processingOfficeId.value = null
            },
        },
    )
}

const money = (value) => {
    return Number(value || 0).toLocaleString('es-MX', {
        style: 'currency',
        currency: 'MXN',
    })
}

const initials = computed(() => {
    const name = props.user?.name || 'Usuario'

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('')
})
</script>

<template>
    <Head title="Seleccionar sucursal" />

    <div class="relative min-h-screen overflow-hidden bg-[#0f172a] text-white">
        <!-- Fondo -->
        <div class="absolute inset-0">
            <div class="absolute -left-40 -top-40 h-[30rem] w-[30rem] rounded-full bg-amber-400/20 blur-3xl"></div>
            <div class="absolute -bottom-48 -right-40 h-[36rem] w-[36rem] rounded-full bg-yellow-700/25 blur-3xl"></div>
            <div class="absolute left-1/2 top-1/2 h-[28rem] w-[28rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/5 blur-3xl"></div>

            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.14),transparent_30%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.08),transparent_30%)]"></div>

            <div
                class="absolute inset-0 opacity-[0.04]"
                style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 48px 48px;"
            ></div>
        </div>

        <div class="relative mx-auto flex min-h-screen max-w-7xl flex-col px-5 py-6 sm:px-8 lg:px-10">
            <!-- Header -->
            <header class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-400 text-slate-950 shadow-xl shadow-amber-500/25">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>

                    <div>
                        <p class="text-2xl font-black tracking-tight">
                            {{ appName }}
                        </p>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-200/80">
                            Control prendario
                        </p>
                    </div>
                </div>

                <div class="hidden items-center gap-3 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur sm:flex">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-400 text-sm font-black text-slate-950">
                        {{ initials }}
                    </div>

                    <div class="max-w-56">
                        <p class="truncate text-sm font-black">
                            {{ user?.name || 'Usuario' }}
                        </p>
                        <p class="truncate text-xs text-slate-300">
                            {{ user?.email }}
                        </p>
                    </div>
                </div>
            </header>

            <!-- Main -->
            <main class="flex flex-1 items-center py-10">
                <div class="grid w-full gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                    <!-- Info -->
                    <section>
                        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-4 py-2 text-sm font-semibold text-amber-100 backdrop-blur">
                            <span class="flex h-2 w-2 rounded-full bg-emerald-400"></span>
                            Acceso por sucursal
                        </div>

                        <h1 class="mt-6 max-w-xl text-4xl font-black leading-tight tracking-tight sm:text-5xl">
                            Selecciona la sucursal con la que vas a operar.
                        </h1>

                        <p class="mt-5 max-w-xl text-base leading-8 text-slate-300">
                            {{
                                canSelectAnyOffice
                                    ? 'Como administrador puedes elegir cualquier sucursal disponible para trabajar esta sesión.'
                                    : 'Tu usuario solo puede entrar a la sucursal que tiene asignada.'
                            }}
                        </p>

                        <div class="mt-8 grid max-w-xl gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <p class="text-2xl font-black text-amber-300">
                                    {{ offices.length }}
                                </p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-300">
                                    Sucursales
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <p class="text-2xl font-black text-amber-300">
                                    {{ canSelectAnyOffice ? 'Admin' : 'Usuario' }}
                                </p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-300">
                                    Tipo de acceso
                                </p>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <p class="text-2xl font-black text-amber-300">
                                    Caja
                                </p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-300">
                                    Por oficina
                                </p>
                            </div>
                        </div>

                        <div class="mt-8 rounded-2xl border border-amber-300/20 bg-amber-300/10 p-4 text-sm leading-6 text-amber-50">
                            Para proteger la operación, el menú administrativo se habilita únicamente después de seleccionar una sucursal.
                        </div>
                    </section>

                    <!-- Selector -->
                    <section class="overflow-hidden rounded-[2rem] border border-white/10 bg-white text-slate-900 shadow-2xl shadow-black/30">
                        <div class="border-b border-slate-100 px-6 py-6 sm:px-8">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <p class="text-sm font-black uppercase tracking-[0.25em] text-amber-600">
                                        Sucursal activa
                                    </p>

                                    <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                                        Elige una sucursal
                                    </h2>

                                    <p class="mt-2 text-sm leading-6 text-slate-500">
                                        Esta selección definirá la caja, folios, cortes y movimientos de la sesión.
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-100 px-4 py-3 text-center">
                                    <p class="text-xl font-black text-slate-950">
                                        {{ filteredOffices.length }}
                                    </p>
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                        visibles
                                    </p>
                                </div>
                            </div>

                            <div class="relative mt-5">
                                <div class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>

                                <input
                                    v-model="search"
                                    type="search"
                                    class="h-12 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:bg-white focus:ring-4 focus:ring-amber-100"
                                    placeholder="Buscar por empresa, sucursal, serie, dirección..."
                                />
                            </div>
                        </div>

                        <div
                            v-if="page.props.flash?.success"
                            class="mx-6 mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 sm:mx-8"
                        >
                            {{ page.props.flash.success }}
                        </div>

                        <div
                            v-if="page.props.flash?.warning"
                            class="mx-6 mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 sm:mx-8"
                        >
                            {{ page.props.flash.warning }}
                        </div>

                        <div
                            v-if="page.props.flash?.error"
                            class="mx-6 mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 sm:mx-8"
                        >
                            {{ page.props.flash.error }}
                        </div>

                        <div
                            v-if="page.props.errors?.office_id"
                            class="mx-6 mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 sm:mx-8"
                        >
                            {{ page.props.errors.office_id }}
                        </div>

                        <div class="max-h-[32rem] overflow-y-auto px-6 py-6 sm:px-8">
                            <div v-if="filteredOffices.length" class="grid gap-4">
                                <button
                                    v-for="office in filteredOffices"
                                    :key="office.id"
                                    type="button"
                                    class="group w-full rounded-3xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-xl hover:shadow-amber-100 disabled:cursor-not-allowed disabled:opacity-70"
                                    :disabled="processingOfficeId !== null"
                                    @click="selectOffice(office)"
                                >
                                    <div class="flex gap-4">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-amber-300 transition group-hover:bg-amber-400 group-hover:text-slate-950">
                                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                                                <path
                                                    d="M4 21V6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V21"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                />
                                                <path
                                                    d="M8 8h.01M12 8h.01M16 8h.01M8 12h.01M12 12h.01M16 12h.01M9 21v-5h6v5"
                                                    stroke="currentColor"
                                                    stroke-width="2.2"
                                                    stroke-linecap="round"
                                                />
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate text-xs font-black uppercase tracking-[0.18em] text-amber-600">
                                                        {{ office.company?.name || 'Empresa' }}
                                                    </p>

                                                    <h3 class="mt-1 truncate text-lg font-black text-slate-950">
                                                        {{ office.name }}
                                                    </h3>
                                                </div>

                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition group-hover:bg-amber-400 group-hover:text-slate-950">
                                                    <svg
                                                        v-if="processingOfficeId === office.id"
                                                        class="h-5 w-5 animate-spin"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                    >
                                                        <path
                                                            d="M12 3a9 9 0 1 1-9 9"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                        />
                                                    </svg>

                                                    <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                        <path
                                                            d="M5 12h14M13 6l6 6-6 6"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid gap-3 text-sm text-slate-500 sm:grid-cols-3">
                                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                                                        Serie
                                                    </p>
                                                    <p class="mt-1 truncate font-black text-slate-800">
                                                        {{ office.serie || office.code || 'Sin serie' }}
                                                    </p>
                                                </div>

                                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                                                        Teléfono
                                                    </p>
                                                    <p class="mt-1 truncate font-black text-slate-800">
                                                        {{ office.phone || 'Sin teléfono' }}
                                                    </p>
                                                </div>

                                                <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">
                                                        Caja
                                                    </p>
                                                    <p class="mt-1 truncate font-black text-slate-800">
                                                        {{ money(office.cash) }}
                                                    </p>
                                                </div>
                                            </div>

                                            <p class="mt-3 truncate text-sm font-medium text-slate-500">
                                                {{ office.address || 'Sin dirección registrada' }}
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </div>

                            <div
                                v-else
                                class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center"
                            >
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-slate-500 shadow-sm">
                                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                                        <path
                                            d="M6.5 4.5h11L21 9l-9 10L3 9l3.5-4.5Z"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M3 9h18M8 4.5 12 19l4-14.5M7.5 9 12 4.5 16.5 9"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-5 text-xl font-black text-slate-950">
                                    No hay sucursales disponibles
                                </h3>

                                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                                    {{
                                        search
                                            ? 'No encontramos sucursales con ese criterio de búsqueda.'
                                            : 'Tu usuario no tiene una sucursal disponible. Contacta al administrador.'
                                    }}
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>
</template>