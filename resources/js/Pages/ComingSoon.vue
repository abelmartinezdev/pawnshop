<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Hammer, ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
    title: {
        type: String,
        default: 'Módulo en construcción',
    },
    description: {
        type: String,
        default: 'Este módulo será implementado próximamente.',
    },
})
</script>

<template>
    <Head :title="title" />

    <AdminLayout>
        <div class="mx-auto flex min-h-[60vh] max-w-3xl items-center justify-center px-4 py-12">
            <div class="w-full rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                    <Hammer class="h-8 w-8" />
                </div>

                <h1 class="text-2xl font-bold text-slate-900">
                    {{ title }}
                </h1>

                <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500">
                    {{ description }}
                </p>

                <button
                    type="button"
                    class="mx-auto mt-8 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                    @click="history.back()"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Regresar
                </button>
            </div>
        </div>
    </AdminLayout>
</template>