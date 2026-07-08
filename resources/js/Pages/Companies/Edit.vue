<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
})

const form = useForm({
    name: props.company.name || '',
    code: props.company.code || '',
    rfc: props.company.rfc || '',
    phone: props.company.phone || '',
    email: props.company.email || '',
    address: props.company.address || '',
    website: props.company.website || '',
    is_active: Boolean(props.company.is_active),
    storage_commission: props.company.storage_commission ?? 0,
    marketing_commission: props.company.marketing_commission ?? 0,
    delayed_payment_commission: props.company.delayed_payment_commission ?? 0,
    replacement_contract_commission: props.company.replacement_contract_commission ?? 0,
})

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const textareaClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const submit = () => {
    form.put(route('companies.update', props.company.id), {
        preserveScroll: true,
    })
}

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        building: 'M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 8h2a2 2 0 0 1 2 2v11M8 7h4M8 11h4M8 15h4M3 21h18',
        cash: 'M3 7h18v10H3V7ZM7 11h.01M17 13h.01M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
        check: 'M20 6 9 17l-5-5',
    }

    return icons[icon] || icons.building
}
</script>

<template>
    <Head :title="`Editar ${company.name}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.24em] text-[#5b55a4]">
                        Administración / Empresas
                    </p>

                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Editar empresa
                    </h1>

                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        {{ company.name }}
                    </p>
                </div>

                <Link
                    :href="route('companies.show', company.id)"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('arrowLeft')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Regresar
                </Link>
            </div>

            <form class="grid gap-6 xl:grid-cols-[1fr_0.35fr]" @submit.prevent="submit">
                <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-5">
                        <h2 class="text-xl font-black text-slate-950">Datos generales</h2>
                        <p class="text-sm text-slate-500">Actualiza la información principal.</p>
                    </div>

                    <div class="grid gap-5 p-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">Nombre</label>
                            <input v-model="form.name" type="text" :class="inputClass" />
                            <p v-if="form.errors.name" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">Código</label>
                            <input v-model="form.code" type="text" :class="inputClass" />
                            <p v-if="form.errors.code" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.code }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">RFC</label>
                            <input v-model="form.rfc" type="text" :class="inputClass" />
                            <p v-if="form.errors.rfc" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.rfc }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">Teléfono</label>
                            <input v-model="form.phone" type="text" :class="inputClass" />
                            <p v-if="form.errors.phone" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-700">Correo electrónico</label>
                            <input v-model="form.email" type="email" :class="inputClass" />
                            <p v-if="form.errors.email" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">Dirección</label>
                            <textarea v-model="form.address" rows="4" :class="textareaClass" />
                            <p v-if="form.errors.address" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.address }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-black text-slate-700">Sitio web</label>
                            <input v-model="form.website" type="text" :class="inputClass" />
                            <p v-if="form.errors.website" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.website }}</p>
                        </div>
                    </div>
                </section>

                <aside class="space-y-6">
                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('building')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">Estatus</h2>
                                <p class="text-sm text-slate-500">Disponible en sistema.</p>
                            </div>
                        </div>

                        <label class="mt-5 flex cursor-pointer items-center justify-between rounded-2xl bg-slate-50 px-4 py-4">
                            <span>
                                <span class="block text-sm font-black text-slate-800">Empresa activa</span>
                                <span class="block text-xs font-semibold text-slate-400">Permitir operación.</span>
                            </span>

                            <input v-model="form.is_active" type="checkbox" class="h-5 w-5 rounded border-slate-300 text-[#5b55a4] focus:ring-[#5b55a4]" />
                        </label>
                    </section>

                    <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('cash')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">Comisiones</h2>
                                <p class="text-sm text-slate-500">Importes base.</p>
                            </div>
                        </div>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Almacenaje</label>
                                <input v-model="form.storage_commission" type="number" min="0" step="0.01" :class="inputClass" />
                                <p v-if="form.errors.storage_commission" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.storage_commission }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Comercialización</label>
                                <input v-model="form.marketing_commission" type="number" min="0" step="0.01" :class="inputClass" />
                                <p v-if="form.errors.marketing_commission" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.marketing_commission }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Desempeño extemporáneo</label>
                                <input v-model="form.delayed_payment_commission" type="number" min="0" step="0.01" :class="inputClass" />
                                <p v-if="form.errors.delayed_payment_commission" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.delayed_payment_commission }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">Reposición de contrato</label>
                                <input v-model="form.replacement_contract_commission" type="number" min="0" step="0.01" :class="inputClass" />
                                <p v-if="form.errors.replacement_contract_commission" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.replacement_contract_commission }}</p>
                            </div>
                        </div>
                    </section>

                    <button
                        type="submit"
                        class="sicem-btn-primary flex w-full items-center justify-center gap-2 rounded-2xl px-5 py-4 text-sm font-black shadow-lg shadow-violet-200 transition disabled:cursor-not-allowed disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path :d="iconPath('check')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </aside>
            </form>
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