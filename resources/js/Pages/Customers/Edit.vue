<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
    identificationTypes: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    name: props.customer.name || '',
    state: props.customer.state || '',
    city: props.customer.city || '',
    address: props.customer.address || '',
    phone: props.customer.phone || '',
    mobile: props.customer.mobile || '',
    email: props.customer.email || '',
    rfc: props.customer.rfc || '',
    code_id: props.customer.code_id || '',
    type_id: props.customer.type_id || '',
    inapam_code: props.customer.inapam_code || '',
})

const submit = () => {
    form.put(route('customers.update', props.customer.id), {
        preserveScroll: true,
    })
}

const inputClass = 'h-12 w-full rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'
const textareaClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-[#5b55a4] focus:ring-4 focus:ring-violet-100'

const iconPath = (icon) => {
    const icons = {
        arrowLeft: 'M19 12H5M11 6l-6 6 6 6',
        user: 'M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0ZM4 21a8 8 0 0 1 16 0',
        save: 'M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2ZM7 21v-8h10v8M7 3v5h8',
        eye: 'M2.5 12S6 5 12 5s9.5 7 9.5 7S18 19 12 19s-9.5-7-9.5-7ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z',
    }

    return icons[icon] || icons.user
}
</script>

<template>
    <Head :title="`Editar ${customer.name}`" />

    <AdminLayout>
        <div class="px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <Link
                        :href="route('customers.show', customer.id)"
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
                        Editar cliente
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Actualiza la información de {{ customer.name }}.
                    </p>
                </div>

                <Link
                    :href="route('customers.show', customer.id)"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <path :d="iconPath('eye')" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Ver cliente
                </Link>
            </div>

            <form class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
                <div class="mb-6 flex items-center gap-4">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#5b55a4] text-2xl font-black text-white">
                        {{ form.name ? form.name.charAt(0).toUpperCase() : 'C' }}
                    </div>

                    <div>
                        <h2 class="text-xl font-black text-slate-950">
                            Datos del cliente
                        </h2>
                        <p class="text-sm text-slate-500">
                            Edita solo los campos necesarios.
                        </p>
                    </div>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-black text-slate-700">Nombre completo</label>
                        <input v-model="form.name" type="text" :class="inputClass" />
                        <p v-if="form.errors.name" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">Teléfono</label>
                        <input v-model="form.phone" type="text" :class="inputClass" />
                        <p v-if="form.errors.phone" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">Celular</label>
                        <input v-model="form.mobile" type="text" :class="inputClass" />
                        <p v-if="form.errors.mobile" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.mobile }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">Correo</label>
                        <input v-model="form.email" type="email" :class="inputClass" />
                        <p v-if="form.errors.email" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">RFC</label>
                        <input v-model="form.rfc" type="text" :class="inputClass" maxlength="13" />
                        <p v-if="form.errors.rfc" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.rfc }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">Estado</label>
                        <input v-model="form.state" type="text" :class="inputClass" />
                        <p v-if="form.errors.state" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.state }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-700">Ciudad</label>
                        <input v-model="form.city" type="text" :class="inputClass" />
                        <p v-if="form.errors.city" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.city }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-black text-slate-700">Dirección</label>
                        <textarea v-model="form.address" rows="3" :class="textareaClass" />
                        <p v-if="form.errors.address" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.address }}</p>
                    </div>

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
                      <label class="mb-2 block text-sm font-black text-slate-700">Número / folio identificación</label>
                      <input v-model="form.code_id" type="text" :class="inputClass" />
                      <p v-if="form.errors.code_id" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.code_id }}</p>
                  </div>

                  <div>
                      <label class="mb-2 block text-sm font-black text-slate-700">Clave INAPAM</label>
                      <input v-model="form.inapam_code" type="text" :class="inputClass" />
                      <p v-if="form.errors.inapam_code" class="mt-2 text-sm font-bold text-red-600">{{ form.errors.inapam_code }}</p>
                  </div>
                </div>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <Link
                        :href="route('customers.show', customer.id)"
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
                        {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>