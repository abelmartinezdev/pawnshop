<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  companies: Array<{ id: number; name: string; code: string }>
  initial?: {
    name?: string
    code?: string
    company_id?: number
    phone?: string | null
    address?: string | null
    serie?: string | null
    schedule?: string | null
    bank_account?: string | null
    daily_interest_rate?: number | null
    monthly_interest_rate?: number | null
    is_active?: boolean
  }
  submitUrl: string
  method?: 'post' | 'put'
  submitText?: string
}>()

const form = useForm({
  name: props.initial?.name ?? '',
  code: props.initial?.code ?? '',
  company_id: props.initial?.company_id ?? (props.companies[0]?.id ?? null),
  phone: props.initial?.phone ?? '',
  address: props.initial?.address ?? '',
  serie: props.initial?.serie ?? '',
  schedule: props.initial?.schedule ?? '',
  bank_account: props.initial?.bank_account ?? '',
  daily_interest_rate: props.initial?.daily_interest_rate ?? null,
  monthly_interest_rate: props.initial?.monthly_interest_rate ?? null,
  is_active: props.initial?.is_active ?? true,
})

const submit = () => {
  if ((props.method ?? 'post') === 'put') {
    form.put(props.submitUrl)
  } else {
    form.post(props.submitUrl)
  }
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Nombre</label>
        <input v-model="form.name" class="w-full rounded-lg border px-3 py-2 text-sm" />
        <div v-if="form.errors.name" class="text-xs text-rose-600 mt-1">{{ form.errors.name }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Código</label>
        <input v-model="form.code" class="w-full rounded-lg border px-3 py-2 text-sm" />
        <div v-if="form.errors.code" class="text-xs text-rose-600 mt-1">{{ form.errors.code }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Empresa</label>
        <select v-model.number="form.company_id" class="w-full rounded-lg border px-3 py-2 text-sm">
          <option v-for="c in companies" :key="c.id" :value="c.id">
            {{ c.name }} ({{ c.code }})
          </option>
        </select>
        <div v-if="form.errors.company_id" class="text-xs text-rose-600 mt-1">{{ form.errors.company_id }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Teléfono</label>
        <input v-model="form.phone" class="w-full rounded-lg border px-3 py-2 text-sm" />
        <div v-if="form.errors.phone" class="text-xs text-rose-600 mt-1">{{ form.errors.phone }}</div>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium mb-1">Dirección</label>
        <input v-model="form.address" class="w-full rounded-lg border px-3 py-2 text-sm" />
        <div v-if="form.errors.address" class="text-xs text-rose-600 mt-1">{{ form.errors.address }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Serie (folios)</label>
        <input v-model="form.serie" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Ej: IND, LTO..." />
        <div v-if="form.errors.serie" class="text-xs text-rose-600 mt-1">{{ form.errors.serie }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Horario</label>
        <input v-model="form.schedule" class="w-full rounded-lg border px-3 py-2 text-sm" placeholder="Ej: Lun-Vie 9-6" />
        <div v-if="form.errors.schedule" class="text-xs text-rose-600 mt-1">{{ form.errors.schedule }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Cuenta bancaria</label>
        <input v-model="form.bank_account" class="w-full rounded-lg border px-3 py-2 text-sm" />
        <div v-if="form.errors.bank_account" class="text-xs text-rose-600 mt-1">{{ form.errors.bank_account }}</div>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div>
          <label class="block text-sm font-medium mb-1">Interés diario (%)</label>
          <input v-model.number="form.daily_interest_rate" type="number" step="0.01"
                 class="w-full rounded-lg border px-3 py-2 text-sm" />
          <div v-if="form.errors.daily_interest_rate" class="text-xs text-rose-600 mt-1">{{ form.errors.daily_interest_rate }}</div>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Interés mensual (%)</label>
          <input v-model.number="form.monthly_interest_rate" type="number" step="0.01"
                 class="w-full rounded-lg border px-3 py-2 text-sm" />
          <div v-if="form.errors.monthly_interest_rate" class="text-xs text-rose-600 mt-1">{{ form.errors.monthly_interest_rate }}</div>
        </div>
      </div>

      <div class="md:col-span-2 flex items-center gap-2">
        <input id="is_active_office" type="checkbox" v-model="form.is_active" class="rounded border" />
        <label for="is_active_office" class="text-sm">Activa</label>
        <div v-if="form.errors.is_active" class="text-xs text-rose-600">{{ form.errors.is_active }}</div>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button
        type="submit"
        class="rounded-lg bg-zinc-900 text-white px-4 py-2 text-sm hover:bg-zinc-800 disabled:opacity-50"
        :disabled="form.processing"
      >
        {{ submitText ?? 'Guardar' }}
      </button>

      <span v-if="form.processing" class="text-xs text-zinc-500">Guardando…</span>
    </div>
  </form>
</template>