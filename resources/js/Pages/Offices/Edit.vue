<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import OfficeForm from './Partials/OfficeForm.vue'

const props = defineProps<{
  office: {
    id: number
    name: string
    code: string
    company_id: number
    phone?: string | null
    address?: string | null
    serie?: string | null
    schedule?: string | null
    bank_account?: string | null
    daily_interest_rate?: number | null
    monthly_interest_rate?: number | null
    is_active: boolean
  }
  companies: Array<{ id: number; name: string; code: string }>
}>()
</script>

<template>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-xl font-semibold">Editar sucursal</h1>
        <p class="text-sm text-zinc-500">{{ props.office.name }} ({{ props.office.code }})</p>
      </div>

      <Link :href="route('offices.index')" class="text-sm text-zinc-700 hover:underline">
        Volver
      </Link>
    </div>

    <div class="rounded-lg border p-4">
      <OfficeForm
        method="put"
        :companies="props.companies"
        :initial="props.office"
        :submit-url="route('offices.update', props.office.id)"
        submit-text="Guardar cambios"
      />
    </div>
  </div>
</template>