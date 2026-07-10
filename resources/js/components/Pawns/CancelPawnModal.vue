<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    pawn: {
        type: Object,
        required: true,
    },
    url: {
        type: String,
        default: null,
    },
    cancellationOptions: {
        type: Array,
        default: () => [],
    },
})

const emit = defineEmits(['close'])

const form = useForm({
    cancellation_type: '',
    number_investigation: '',
    comments_cancel: '',
})

const shouldShowInvestigation = computed(() => form.cancellation_type === 'investigation')

const canSubmit = computed(() => {
    return Boolean(props.url)
        && Boolean(form.cancellation_type)
        && !form.processing
})

watch(
    () => props.show,
    (value) => {
        if (value) {
            form.clearErrors()
            return
        }

        form.reset()
        form.clearErrors()
    }
)

watch(
    () => form.cancellation_type,
    (value) => {
        if (value !== 'investigation') {
            form.number_investigation = ''
            form.clearErrors('number_investigation')
        }
    }
)

const close = () => {
    if (form.processing) {
        return
    }

    emit('close')
}

const submit = () => {
    if (!canSubmit.value) {
        return
    }

    form.put(props.url, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close')
        },
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
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
                @click.self="close"
            >
                <Transition
                    appear
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="translate-y-4 scale-95 opacity-0"
                    enter-to-class="translate-y-0 scale-100 opacity-100"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="translate-y-0 scale-100 opacity-100"
                    leave-to-class="translate-y-4 scale-95 opacity-0"
                >
                    <div class="w-full max-w-xl overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-slate-950/20">
                        <div class="bg-gradient-to-br from-rose-600 to-red-700 px-6 py-5 text-white">
                            <p class="text-xs font-black uppercase tracking-[0.22em] text-white/70">
                                Cancelación de empeño
                            </p>

                            <h2 class="mt-2 text-2xl font-black">
                                Cancelar folio {{ pawn.folio }}
                            </h2>

                            <p class="mt-2 text-sm font-semibold text-white/75">
                                Esta acción devolverá a caja el efectivo entregado y dejará registro de auditoría.
                            </p>
                        </div>

                        <form class="p-6" @submit.prevent="submit">
                            <div class="mb-5 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                                <p class="font-black">
                                    Importe a revertir: {{ money(pawn.total) }}
                                </p>
                                <p class="mt-1 font-semibold">
                                    Solo cancela si el empeño fue capturado por error o realmente debe anularse.
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Motivo de cancelación
                                    </label>

                                    <select
                                        v-model="form.cancellation_type"
                                        class="sicem-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100"
                                    >
                                        <option value="">
                                            Selecciona un motivo
                                        </option>

                                        <option
                                            v-for="option in cancellationOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>

                                    <p v-if="form.errors.cancellation_type" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.cancellation_type }}
                                    </p>
                                </div>

                                <div v-if="shouldShowInvestigation">
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Número de investigación
                                    </label>

                                    <input
                                        v-model="form.number_investigation"
                                        type="text"
                                        class="sicem-input w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100"
                                        placeholder="Ej. INV-2026-0001"
                                    >

                                    <p v-if="form.errors.number_investigation" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.number_investigation }}
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-black text-slate-700">
                                        Comentarios
                                    </label>

                                    <textarea
                                        v-model="form.comments_cancel"
                                        rows="4"
                                        class="sicem-input w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-sm focus:border-rose-500 focus:outline-none focus:ring-4 focus:ring-rose-100"
                                        placeholder="Describe brevemente por qué se cancela este empeño..."
                                    />

                                    <p v-if="form.errors.comments_cancel" class="mt-2 text-sm font-bold text-red-600">
                                        {{ form.errors.comments_cancel }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    class="sicem-btn-neutral rounded-2xl px-5 py-3 text-sm font-black transition"
                                    :disabled="form.processing"
                                    @click="close"
                                >
                                    No cancelar
                                </button>

                                <button
                                    type="submit"
                                    class="sicem-btn-rose rounded-2xl px-5 py-3 text-sm font-black transition"
                                    :disabled="!canSubmit"
                                >
                                    <span v-if="form.processing">Cancelando...</span>
                                    <span v-else>Sí, cancelar empeño</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.sicem-btn-rose {
    background-color: #e11d48 !important;
    color: #ffffff !important;
    border-color: #e11d48 !important;
}

.sicem-btn-rose:hover:not(:disabled) {
    background-color: #be123c !important;
    color: #ffffff !important;
}

.sicem-btn-rose:disabled {
    opacity: 0.5 !important;
    cursor: not-allowed !important;
}

.sicem-btn-neutral {
    background-color: #ffffff !important;
    color: #475569 !important;
    border: 1px solid #e2e8f0 !important;
}

.sicem-btn-neutral:hover:not(:disabled) {
    background-color: #f8fafc !important;
    color: #5b55a4 !important;
}

.sicem-input {
    color-scheme: light !important;
}
</style>