<script setup lang="ts">
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  closure: any
  office: any
  company: any
  date: string
  user: any
}>()

function printNow() {
  window.print()
}
</script>

<template>
  <Head title="Ticket de corte" />

  <div class="p-6">
    <div class="max-w-sm mx-auto rounded-xl border border-zinc-200 p-4 print:border-0 print:p-0">
      <div class="text-center">
        <div class="text-lg font-semibold">{{ company?.name ?? 'Empresa' }}</div>
        <div class="text-sm text-zinc-600">{{ office?.name ?? 'Sucursal' }}</div>
      </div>

      <div class="mt-3 text-sm space-y-1">
        <div class="flex justify-between"><span>Folio corte</span><span>#{{ closure.id }}</span></div>
        <div class="flex justify-between"><span>Fecha</span><span>{{ date ?? closure.created_at }}</span></div>
        <div class="flex justify-between"><span>Cajero</span><span>{{ user?.name ?? closure.user_id }}</span></div>
      </div>

      <hr class="my-3" />

      <div class="text-sm text-zinc-700">
        <div class="font-semibold mb-1">Resumen</div>
        <div class="text-xs text-zinc-500">
          Aquí imprime lo que tu cierre realmente genera (totales por tipo, saldo final, etc.).
          Si tu `closeBox()` guarda un JSON/summary, pásalo como prop y lo muestras aquí.
        </div>
      </div>

      <hr class="my-3" />

      <div class="text-center text-xs text-zinc-500">
        Gracias
      </div>

      <div class="mt-4 print:hidden">
        <button @click="printNow" class="w-full rounded-xl bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800">
          Imprimir
        </button>
      </div>
    </div>
  </div>
</template>

<style>
@media print {
  body { background: white; }
}
</style>