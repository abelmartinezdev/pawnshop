<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps<{
  office: any
  company: any
  last_closure: any | null
  total_interest: number
  closures: any
  closing_pending: boolean
}>()

function money(n: any) {
  const v = Number(n ?? 0)
  return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })
}

const form = useForm({})
function submit() {
  form.post(route('closure.store'))
}
</script>

<template>
  <Head title="Corte de caja" />

  <div class="p-6 space-y-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold">Corte de caja</h1>
        <p class="text-sm text-zinc-500">
          Sucursal: <span class="font-medium">{{ office?.name ?? `#${office?.id}` }}</span>
        </p>
      </div>

      <button
        @click="submit"
        :disabled="form.processing"
        class="rounded-xl bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 disabled:opacity-60"
      >
        {{ last_closure && last_closure.created_at?.includes?.(new Date().toISOString().slice(0, 10)) ? 'Abrir caja' : 'Cerrar caja' }}
      </button>
    </div>

    <div v-if="closing_pending" class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
      Hay cierres pendientes (hubo días sin corte). El sistema hará cierres simulados antes de cerrar el día actual.
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
      <div class="rounded-2xl border border-zinc-200 bg-white p-4 space-y-2">
        <div class="text-sm font-semibold">Último corte</div>
        <div class="text-sm text-zinc-700">
          <span class="text-zinc-500">Fecha:</span> {{ last_closure?.created_at ?? '—' }}
        </div>
        <div class="text-sm">
          <span class="text-zinc-500">Interés (rango):</span>
          <span class="font-semibold ml-2">{{ money(total_interest) }}</span>
        </div>
      </div>

      <div class="rounded-2xl border border-zinc-200 bg-white p-4 lg:col-span-2">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold">Historial de cierres</div>
          <div class="text-xs text-zinc-500">Página {{ closures?.current_page ?? 1 }}</div>
        </div>

        <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200">
          <table class="w-full text-sm">
            <thead class="bg-zinc-50 text-zinc-600">
              <tr>
                <th class="text-left p-2">Fecha</th>
                <th class="text-left p-2">Usuario</th>
                <th class="text-right p-2">Ver</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in (closures?.data ?? [])" :key="c.id" class="border-t">
                <td class="p-2">{{ c.created_at }}</td>
                <td class="p-2">{{ c.user_id }}</td>
                <td class="p-2 text-right">
                  <Link :href="route('closure.show', c.id)" class="underline">Abrir</Link>
                </td>
              </tr>

              <tr v-if="(closures?.data ?? []).length === 0">
                <td colspan="3" class="p-4 text-center text-zinc-500">Sin cierres</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-3 flex justify-end gap-2 text-sm">
          <Link v-if="closures?.prev_page_url" :href="closures.prev_page_url" class="rounded-xl border border-zinc-200 px-3 py-1.5 hover:bg-zinc-50">
            Anterior
          </Link>
          <Link v-if="closures?.next_page_url" :href="closures.next_page_url" class="rounded-xl border border-zinc-200 px-3 py-1.5 hover:bg-zinc-50">
            Siguiente
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>