<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import CompanyForm from './Partials/CompanyForm.vue'
</script>

<template>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold">Nueva empresa</h1>
        <p class="text-sm text-zinc-500">Crea una empresa (ej: La Joya, Prestabaja).</p>
      </div>

      <Link :href="route('companies.index')" class="text-sm text-zinc-700 hover:underline">
        Volver
      </Link>
    </div>

    <div class="rounded-lg border p-4">
      <CompanyForm :submit-url="route('companies.store')" submit-text="Crear empresa" />
    </div>
  </div>
</template>