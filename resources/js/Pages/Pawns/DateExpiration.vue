<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const props = defineProps({
    pawn: {
        type: Object,
        required: true,
    },
    urls: {
        type: Object,
        default: () => ({}),
    },
})

const showConfirmModal = ref(false)

const form = useForm({
    date_expiration: props.pawn.date_expiration_input || '',
    date_auction: props.pawn.date_auction_input || '',
    comments: '',
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const textareaClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const expirationChanged = computed(() => {
    return form.date_expiration !== props.pawn.date_expiration_input
})

const auctionChanged = computed(() => {
    return form.date_auction !== props.pawn.date_auction_input
})

const hasChanges = computed(() => {
    return expirationChanged.value || auctionChanged.value || form.comments.trim() !== ''
})

const expirationDiff = computed(() => {
    return diffDays(props.pawn.date_expiration_input, form.date_expiration)
})

const auctionDiff = computed(() => {
    return diffDays(props.pawn.date_auction_input, form.date_auction)
})

const canSubmit = computed(() => {
    return form.date_expiration && form.date_auction && !form.processing
})

const openConfirm = () => {
    if (!canSubmit.value) {
        return
    }

    showConfirmModal.value = true
}

const submit = () => {
    form.put(props.urls.update || route('pawns.date-expiration.update', props.pawn.id), {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmModal.value = false
        },
    })
}

const diffDays = (oldDate, newDate) => {
    if (!oldDate || !newDate) {
        return 0
    }

    const oldValue = new Date(`${oldDate}T00:00:00`)
    const newValue = new Date(`${newDate}T00:00:00`)

    return Math.round((newValue - oldValue) / (1000 * 60 * 60 * 24))
}

const diffLabel = (value) => {
    if (value === 0) {
        return 'Sin cambio'
    }

    if (value > 0) {
        return `+${value} día(s)`
    }

    return `${value} día(s)`
}

const diffClass = (value) => {
    if (value > 0) {
        return 'text-emerald-600 bg-emerald-50 border-emerald-100'
    }

    if (value < 0) {
        return 'text-red-600 bg-red-50 border-red-100'
    }

    return 'text-slate-500 bg-slate-50 border-slate-100'
}

const formatDate = (value) => {
    if (!value) {
        return 'N/A'
    }

    const date = new Date(`${value}T00:00:00`)

    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    })
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        calendar: 'M7 3v4M17 3v4M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z',
        clock: 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        alert: 'M12 9v4M12 17h.01M10.3 4.3 2.7 18a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z',
        check: 'M20 6 9 17l-5-5',
        x: 'M18 6 6 18M6 6l12 12',
    }

    return icons[icon] || icons.calendar
}
</script>

<template>
    <Head :title="`Cambiar fecha ${pawn.folio}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Empeños / Fecha de espera
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Cambiar fecha de espera
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Folio <span class="font-black text-slate-800">{{ pawn.folio }}</span>
                        <span v-if="pawn.customer?.name"> · {{ pawn.customer.name }}</span>
                    </p>
                </div>

                <Link
                    :href="urls.show || route('pawns.show', pawn.id)"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Regresar
                </Link>
            </div>

            <div class="grid gap-6 xl:grid-cols-[1fr_0.36fr]">
                <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h2 class="text-xl font-black text-slate-950">
                            Fechas del contrato
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Actualiza la expiración y la fecha de remate del empeño.
                        </p>
                    </div>

                    <form class="p-6" @submit.prevent="openConfirm">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                                <div class="mb-5 flex items-center gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-200 text-slate-600">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                            <path :d="iconPath('clock')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="text-lg font-black text-slate-950">
                                            Fechas actuales
                                        </h3>
                                        <p class="text-sm text-slate-500">
                                            Valores registrados actualmente.
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-black text-slate-700">
                                            Expiración
                                        </label>

                                        <input
                                            :value="pawn.date_expiration || 'N/A'"
                                            type="text"
                                            :class="inputClass"
                                            disabled
                                        />
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-black text-slate-700">
                                            Remate
                                        </label>

                                        <input
                                            :value="pawn.date_auction || 'N/A'"
                                            type="text"
                                            :class="inputClass"
                                            disabled
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-[1.75rem] border border-[#5b55a4]/30 bg-white p-5">
                                <div class="mb-5 flex items-center gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                            <path :d="iconPath('calendar')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="text-lg font-black text-slate-950">
                                            Fechas nuevas
                                        </h3>
                                        <p class="text-sm text-slate-500">
                                            Captura las nuevas fechas.
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-black text-slate-700">
                                            Expiración
                                        </label>

                                        <input
                                            v-model="form.date_expiration"
                                            type="date"
                                            :class="inputClass"
                                        />

                                        <p v-if="form.errors.date_expiration" class="mt-2 text-sm font-bold text-red-600">
                                            {{ form.errors.date_expiration }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-black text-slate-700">
                                            Remate
                                        </label>

                                        <input
                                            v-model="form.date_auction"
                                            type="date"
                                            :class="inputClass"
                                        />

                                        <p v-if="form.errors.date_auction" class="mt-2 text-sm font-bold text-red-600">
                                            {{ form.errors.date_auction }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div
                                class="rounded-2xl border px-5 py-4"
                                :class="diffClass(expirationDiff)"
                            >
                                <p class="text-xs font-black uppercase tracking-[0.18em]">
                                    Cambio expiración
                                </p>

                                <p class="mt-1 text-xl font-black">
                                    {{ diffLabel(expirationDiff) }}
                                </p>

                                <p class="mt-1 text-sm font-semibold opacity-80">
                                    {{ formatDate(form.date_expiration) }}
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border px-5 py-4"
                                :class="diffClass(auctionDiff)"
                            >
                                <p class="text-xs font-black uppercase tracking-[0.18em]">
                                    Cambio remate
                                </p>

                                <p class="mt-1 text-xl font-black">
                                    {{ diffLabel(auctionDiff) }}
                                </p>

                                <p class="mt-1 text-sm font-semibold opacity-80">
                                    {{ formatDate(form.date_auction) }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="mb-2 block text-sm font-black text-slate-700">
                                Comentarios
                            </label>

                            <textarea
                                v-model="form.comments"
                                rows="4"
                                :class="textareaClass"
                                placeholder="Motivo del cambio de fecha..."
                            />

                            <p v-if="form.errors.comments" class="mt-2 text-sm font-bold text-red-600">
                                {{ form.errors.comments }}
                            </p>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
                            <Link
                                :href="urls.show || route('pawns.show', pawn.id)"
                                class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                Cancelar
                            </Link>

                            <button
                                type="submit"
                                class="sicem-btn-primary rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition disabled:cursor-not-allowed disabled:opacity-60"
                                :disabled="!canSubmit"
                            >
                                Cambiar fecha
                            </button>
                        </div>
                    </form>
                </section>

                <aside class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-black text-slate-950">
                            Resumen
                        </h2>

                        <div class="mt-5 space-y-3">
                            <div class="rounded-2xl bg-violet-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                                    Folio
                                </p>

                                <p class="mt-1 text-lg font-black text-[#5b55a4]">
                                    {{ pawn.folio }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Cliente
                                </p>

                                <p class="mt-1 text-sm font-black text-slate-900">
                                    {{ pawn.customer?.name || 'Sin cliente' }}
                                </p>

                                <p class="mt-1 text-xs font-semibold text-slate-400">
                                    {{ pawn.customer?.phone || 'Sin teléfono' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                    Sucursal
                                </p>

                                <p class="mt-1 text-sm font-black text-slate-900">
                                    {{ pawn.office?.name || 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6 text-amber-800 shadow-sm">
                        <div class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('alert')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>

                            <div>
                                <p class="font-black">
                                    Importante
                                </p>

                                <p class="mt-1 text-sm leading-6">
                                    Este cambio no mueve caja. Solo actualiza las fechas del empeño y deja un movimiento de historial.
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>

        <div
            v-if="showConfirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        >
            <div class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
                    <div>
                        <h2 class="text-xl font-black text-slate-950">
                            Confirmar cambio
                        </h2>

                        <p class="text-sm text-slate-500">
                            Revisa las nuevas fechas antes de guardar.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="flex h-10 w-10 items-center justify-center rounded-2xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="showConfirmModal = false"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('x')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4 p-6">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Nueva expiración
                            </p>

                            <p class="mt-1 text-lg font-black text-slate-950">
                                {{ formatDate(form.date_expiration) }}
                            </p>

                            <p class="mt-1 text-sm font-semibold" :class="expirationDiff > 0 ? 'text-emerald-600' : expirationDiff < 0 ? 'text-red-600' : 'text-slate-400'">
                                {{ diffLabel(expirationDiff) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                                Nuevo remate
                            </p>

                            <p class="mt-1 text-lg font-black text-slate-950">
                                {{ formatDate(form.date_auction) }}
                            </p>

                            <p class="mt-1 text-sm font-semibold" :class="auctionDiff > 0 ? 'text-emerald-600' : auctionDiff < 0 ? 'text-red-600' : 'text-slate-400'">
                                {{ diffLabel(auctionDiff) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="form.comments" class="rounded-2xl bg-violet-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-[#5b55a4]">
                            Comentarios
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ form.comments }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button
                        type="button"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        @click="showConfirmModal = false"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="sicem-btn-primary rounded-2xl px-5 py-3 text-sm font-black shadow-lg shadow-violet-200 transition disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{ form.processing ? 'Guardando...' : 'Confirmar cambio' }}
                    </button>
                </div>
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
</style>