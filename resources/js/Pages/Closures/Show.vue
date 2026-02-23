<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps<{
  closure: any
  office: any
  company: any
}>()
</script>

<template>
  <Head title="Detalle de corte" />

  <div class="p-6 space-y-4">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-sm text-zinc-500">Corte</div>
        <h1 class="text-2xl font-semibold">#{{ closure.id }}</h1>
        <div class="text-sm text-zinc-500 mt-1">{{ closure.created_at }}</div>
      </div>

      <div class="flex gap-2">
        <Link :href="route('closure.ticket', closure.id)" class="rounded-xl bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800">
          Imprimir ticket
        </Link>
        <Link :href="route('closure.create')" class="rounded-xl border border-zinc-200 px-4 py-2 text-sm hover:bg-zinc-50">
          Volver
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
      <div class="rounded-2xl border border-zinc-200 bg-white p-4 space-y-2">
        <div class="text-sm font-semibold">Sucursal</div>
        <div class="text-sm text-zinc-700">{{ office?.name ?? `#${office?.id}` }}</div>
        <div class="text-sm text-zinc-500">Company: {{ company?.name ?? `#${company?.id}` }}</div>
      </div>

      <div class="rounded-2xl border border-zinc-200 bg-white p-4 lg:col-span-2">
        <div class="text-sm font-semibold">Información</div>
        <div class="mt-2 text-sm text-zinc-700">
          Este detalle lo puedes enriquecer con los totales que genere tu `Pawnshop::closeBox()` (si guardas resumen en closure o en otra tabla).
        </div>
      </div>
    </div>
  </div>
</template>