<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps<{
  pawns: any
  filters: { search?: string }
}>()

const search = ref(props.filters?.search ?? '')
const rows = computed(() => props.pawns?.data ?? [])

let t: any = null
watch(search, () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(route('pawns.index'), { search: search.value }, { preserveState: true, replace: true })
  }, 300)
})

function money(n: any) {
  const v = Number(n ?? 0)
  return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })
}
</script>

<template>
  <Head title="Empeños" />

  <div class="p-6 space-y-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold">Empeños</h1>
        <p class="text-sm text-zinc-500">Búsqueda por folio</p>
      </div>

      <div class="w-full max-w-md">
        <input
          v-model="search"
          type="text"
          class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm outline-none focus:ring"
          placeholder="Buscar folio..."
        />
      </div>
    </div>

    <div class="rounded-2xl border border-zinc-200 bg-white overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-zinc-50 text-zinc-600">
          <tr>
            <th class="text-left p-3">Folio</th>
            <th class="text-left p-3">Cliente</th>
            <th class="text-left p-3">Total</th>
            <th class="text-left p-3">Vence</th>
            <th class="text-left p-3">Estado</th>
            <th class="text-right p-3">Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in rows" :key="p.id" class="border-t">
            <td class="p-3 font-medium">{{ p.folio }}</td>
            <td class="p-3">{{ p.customer?.name ?? '—' }}</td>
            <td class="p-3">{{ money(p.total) }}</td>
            <td class="p-3">{{ p.date_expiration }}</td>
            <td class="p-3">
              <span v-if="p.canceled_at" class="text-red-600">Cancelado</span>
              <span v-else-if="p.paid_at" class="text-emerald-700">Liquidado</span>
              <span v-else class="text-zinc-700">Activo</span>
            </td>
            <td class="p-3 text-right">
              <Link
                :href="route('pawns.show', p.id)"
                class="inline-flex items-center rounded-xl border border-zinc-200 px-3 py-1.5 hover:bg-zinc-50"
              >
                Ver
              </Link>
            </td>
          </tr>

          <tr v-if="rows.length === 0">
            <td colspan="6" class="p-6 text-center text-zinc-500">Sin resultados</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación simple -->
    <div class="flex items-center justify-between text-sm">
      <div class="text-zinc-500">
        Página {{ props.pawns?.current_page ?? 1 }} de {{ props.pawns?.last_page ?? 1 }}
      </div>

      <div class="flex gap-2">
        <Link
          v-if="props.pawns?.prev_page_url"
          :href="props.pawns.prev_page_url"
          class="rounded-xl border border-zinc-200 px-3 py-1.5 hover:bg-zinc-50"
        >
          Anterior
        </Link>
        <Link
          v-if="props.pawns?.next_page_url"
          :href="props.pawns.next_page_url"
          class="rounded-xl border border-zinc-200 px-3 py-1.5 hover:bg-zinc-50"
        >
          Siguiente
        </Link>
      </div>
    </div>
  </div>
</template>