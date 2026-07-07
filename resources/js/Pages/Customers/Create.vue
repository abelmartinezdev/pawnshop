<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    identificationTypes: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    name: '',
    state: '',
    city: '',
    address: '',
    phone: '',
    mobile: '',
    email: '',
    rfc: '',
    code_id: '',
    type_id: '',
    inapam_code: '',
})

const submit = () => {
    form.post(route('customers.store'), {
        preserveScroll: true,
    })
}

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const textareaClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        phone: 'M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6.4 6.4l1.2-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6a2 2 0 0 1 1.7 2Z',
        map: 'M9 18 3 21V6l6-3 6 3 6-3v15l-6 3-6-3ZM9 3v15M15 6v15',
        id: 'M4 5h16v14H4V5ZM8 9h4M8 13h8M8 17h6M15 9h1',
        save: 'M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2ZM7 21v-8h10v8M7 3v5h8',
    }

    return icons[icon] || icons.user
}
</script>

<template>
    <Head title="Nuevo cliente" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7">
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
                    Clientes
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                    Nuevo cliente
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    Captura la información general, contacto, dirección e identificación del cliente.
                </p>
            </div>

            <form class="grid gap-6 xl:grid-cols-[0.7fr_1.3fr]" @submit.prevent="submit">
                <section class="space-y-6">
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                        <div class="bg-gradient-to-br from-[#5b55a4] to-[#312d65] px-6 py-7 text-white">
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black">
                                    {{ form.name ? form.name.charAt(0).toUpperCase() : 'C' }}
                                </div>

                                <div>
                                    <p class="text-sm font-bold uppercase tracking-[0.22em] text-white/60">
                                        Cliente
                                    </p>
                                    <p class="mt-1 text-2xl font-black">
                                        {{ form.name || 'Sin nombre' }}
                                    </p>
                                    <p class="mt-1 text-sm text-white/70">
                                        {{ form.mobile || form.phone || 'Sin teléfono' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <p class="text-sm leading-6 text-slate-500">
                                Esta información será usada para contratos, tickets, historial de empeños y búsqueda rápida en operación.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="space-y-6">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#5b55a4] text-white">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('user')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Información personal
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Datos principales del cliente.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Nombre completo
                                </label>
                                <input v-model="form.name" type="text" :class="inputClass" placeholder="Nombre del cliente" />
                                <p v-if="form.errors.name" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    RFC
                                </label>
                                <input v-model="form.rfc" type="text" :class="inputClass" placeholder="RFC" maxlength="13" />
                                <p v-if="form.errors.rfc" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.rfc }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Clave INAPAM
                                </label>
                                <input v-model="form.inapam_code" type="text" :class="inputClass" placeholder="Opcional" />
                                <p v-if="form.errors.inapam_code" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.inapam_code }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('phone')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Contacto
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Teléfonos y correo electrónico.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Teléfono
                                </label>
                                <input v-model="form.phone" type="text" :class="inputClass" placeholder="Teléfono fijo" />
                                <p v-if="form.errors.phone" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.phone }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Celular
                                </label>
                                <input v-model="form.mobile" type="text" :class="inputClass" placeholder="Celular" />
                                <p v-if="form.errors.mobile" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.mobile }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Correo
                                </label>
                                <input v-model="form.email" type="email" :class="inputClass" placeholder="correo@ejemplo.com" />
                                <p v-if="form.errors.email" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.email }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('map')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Dirección
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Domicilio y ubicación del cliente.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Estado
                                </label>
                                <input v-model="form.state" type="text" :class="inputClass" placeholder="Estado" />
                                <p v-if="form.errors.state" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.state }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Ciudad
                                </label>
                                <input v-model="form.city" type="text" :class="inputClass" placeholder="Ciudad" />
                                <p v-if="form.errors.city" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.city }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-black text-slate-700">
                                    Dirección
                                </label>
                                <textarea v-model="form.address" rows="3" :class="textareaClass" placeholder="Calle, número, colonia, referencias..." />
                                <p v-if="form.errors.address" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.address }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-100 text-[#5b55a4]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path :d="iconPath('id')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    Identificación
                                </h2>
                                <p class="text-sm text-slate-500">
                                    Documento presentado por el cliente.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                              <label class="mb-2 block text-sm font-black text-slate-700">
                                  Tipo de identificación
                              </label>

                              <select
                                  v-model="form.type_id"
                                  class="h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100"
                              >
                                  <option class="bg-white text-slate-900" value="">
                                      Selecciona una opción
                                  </option>

                                  <option
                                      v-for="type in identificationTypes"
                                      :key="type.id"
                                      class="bg-white text-slate-900"
                                      :value="type.id"
                                  >
                                      {{ type.name }}
                                  </option>
                              </select>

                              <p v-if="form.errors.type_id" class="mt-2 text-sm font-bold text-red-600">
                                  {{ form.errors.type_id }}
                              </p>
                          </div>

                          <div>
                              <label class="mb-2 block text-sm font-black text-slate-700">
                                  Número / folio
                              </label>
                              <input v-model="form.code_id" type="text" :class="inputClass" placeholder="Número de identificación" />
                              <p v-if="form.errors.code_id" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.code_id }}</p>
                          </div>
                      </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <Link
                            :href="route('customers.index')"
                            class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-center text-sm font-black text-slate-700 transition hover:bg-slate-50"
                        >
                            Cancelar
                        </Link>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#5b55a4] px-6 py-3 text-sm font-black text-white shadow-lg shadow-violet-200 transition hover:-translate-y-0.5 hover:bg-[#4f4896] disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path :d="iconPath('save')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            {{ form.processing ? 'Guardando...' : 'Guardar cliente' }}
                        </button>
                    </div>
                </section>
            </form>
        </div>
    </AdminLayout>
</template>