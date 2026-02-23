<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import OfficeForm from './Partials/OfficeForm.vue'

const props = defineProps<{
  companies: Array<{ id: number; name: string; code: string }>
}>()
</script>

<template>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold">Nueva sucursal</h1>
        <p class="text-sm text-zinc-500">Crea una sucursal (Indeco, Loreto, etc.).</p>
      </div>

      <Link :href="route('offices.index')" class="text-sm text-zinc-700 hover:underline">
        Volver
      </Link>
    </div>

    <div class="rounded-lg border p-4">
      <OfficeForm :companies="props.companies" :submit-url="route('offices.store')" submit-text="Crear sucursal" />
    </div>
  </div>
</template>