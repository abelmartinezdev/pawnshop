<script setup lang="ts">
import { reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps<{ customer: any }>()

const form = reactive({
  name: props.customer.name ?? '',
  state: props.customer.state ?? '',
  city: props.customer.city ?? '',
  address: props.customer.address ?? '',
  phone: props.customer.phone ?? '',
  mobile: props.customer.mobile ?? '',
  email: props.customer.email ?? '',
  rfc: props.customer.rfc ?? '',
  inapam_code: props.customer.inapam_code ?? '',
})

const submit = () => router.put(`/customers/${props.customer.id}`, form)
</script>

<template>
  <div class="p-6 space-y-4 max-w-3xl">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold">Editar cliente</h1>
      <Link href="/customers" class="underline">Volver</Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
      <input v-model="form.name" class="rounded-lg border px-3 py-2" placeholder="Nombre *" />
      <input v-model="form.rfc" class="rounded-lg border px-3 py-2" placeholder="RFC" />
      <input v-model="form.email" class="rounded-lg border px-3 py-2" placeholder="Email" />
      <input v-model="form.mobile" class="rounded-lg border px-3 py-2" placeholder="Celular" />
      <input v-model="form.phone" class="rounded-lg border px-3 py-2" placeholder="Teléfono" />
      <input v-model="form.inapam_code" class="rounded-lg border px-3 py-2" placeholder="INAPAM" />
      <input v-model="form.state" class="rounded-lg border px-3 py-2" placeholder="Estado" />
      <input v-model="form.city" class="rounded-lg border px-3 py-2" placeholder="Ciudad" />
      <input v-model="form.address" class="rounded-lg border px-3 py-2 md:col-span-2" placeholder="Dirección" />
    </div>

    <button @click="submit" class="px-4 py-2 rounded-lg bg-zinc-900 text-white">
      Guardar cambios
    </button>
  </div>
</template>