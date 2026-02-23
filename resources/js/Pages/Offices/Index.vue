<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

type OfficeRow = {
  id: number
  name: string
  code: string
  company_id: number
  company?: { id: number; name: string; code: string }
  phone?: string | null
  address?: string | null
  serie?: string | null
  schedule?: string | null
  is_active: boolean
  deleted_at?: string | null
}

const props = defineProps<{
  offices: {
    data: OfficeRow[]
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: { q: string; status: string; perPage: number; company_id?: number | null }
  companies: Array<{ id: number; name: string; code: string }>
}>()

const page = usePage()

const state = reactive({
  q: props.filters.q ?? '',
  status: props.filters.status ?? 'active',
  perPage: props.filters.perPage ?? 10,
  company_id: (props.filters.company_id ?? '') as any,
})

watch(
  () => [state.q, state.status, state.perPage, state.company_id],
  () => {
    router.get(
      route('offices.index'),
      { q: state.q, status: state.status, perPage: state.perPage, company_id: state.company_id || null },
      { preserveState: true, replace: true, preserveScroll: true },
    )
  },
)

const flash = computed(() => (page.props as any)?.flash)
</script>

<template>
  <div class="p-6 space-y-4">
    <div v-if="flash" class="rounded-lg border p-3 text-sm"
         :class="flash?.type === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-900' : 'border-rose-200 bg-rose-50 text-rose-900'">
      {{ flash?.message }}
    </div>

    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-semibold">Sucursales</h1>
        <p class="text-sm text-zinc-500">Sucursales pertenecen a una empresa.</p>
      </div>

      <Link :href="route('offices.create')" class="rounded-lg bg-zinc-900 text-white px-4 py-2 text-sm hover:bg-zinc-800">
        Nueva sucursal
      </Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <input
        v-model="state.q"
        type="text"
        placeholder="Buscar por nombre/código..."
        class="w-full rounded-lg border px-3 py-2 text-sm"
      />

      <select v-model="state.company_id" class="w-full rounded-lg border px-3 py-2 text-sm">
        <option value="">Todas las empresas</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">
          {{ c.name }} ({{ c.code }})
        </option>
      </select>

      <select v-model="state.status" class="w-full rounded-lg border px-3 py-2 text-sm">
        <option value="active">Activas</option>
        <option value="all">Todas</option>
        <option value="trashed">Archivadas</option>
      </select>

      <select v-model.number="state.perPage" class="w-full rounded-lg border px-3 py-2 text-sm">
        <option :value="10">10</option>
        <option :value="25">25</option>
        <option :value="50">50</option>
      </select>
    </div>

    <div class="overflow-x-auto rounded-lg border">
      <table class="min-w-full text-sm">
        <thead class="bg-zinc-50">
          <tr class="text-left">
            <th class="px-4 py-3">Sucursal</th>
            <th class="px-4 py-3">Código</th>
            <th class="px-4 py-3">Empresa</th>
            <th class="px-4 py-3">Info</th>
            <th class="px-4 py-3 w-56">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="!offices.data.length">
            <td colspan="5" class="px-4 py-6 text-center text-zinc-500">Sin resultados.</td>
          </tr>

          <tr v-for="o in offices.data" :key="o.id" class="border-t">
            <td class="px-4 py-3">
              <div class="font-medium">{{ o.name }}</div>
              <div class="text-xs text-zinc-500" v-if="o.address">{{ o.address }}</div>
            </td>

            <td class="px-4 py-3">
              <span class="inline-flex rounded-md bg-zinc-100 px-2 py-1 text-xs">
                {{ o.code }}
              </span>
              <span v-if="o.serie" class="ml-2 text-xs text-zinc-500">Serie: {{ o.serie }}</span>
            </td>

            <td class="px-4 py-3">
              <div class="text-sm">
                {{ o.company?.name ?? `#${o.company_id}` }}
              </div>
              <div class="text-xs text-zinc-500" v-if="o.company?.code">{{ o.company.code }}</div>
            </td>

            <td class="px-4 py-3">
              <div class="text-xs" v-if="o.phone">📞 {{ o.phone }}</div>
              <div class="text-xs" v-if="o.schedule">🕒 {{ o.schedule }}</div>
              <div v-if="!o.phone && !o.schedule" class="text-xs text-zinc-500">—</div>

              <div class="mt-1">
                <span
                  class="inline-flex rounded-full px-2 py-1 text-xs"
                  :class="o.is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-zinc-200 text-zinc-800'"
                >
                  {{ o.is_active ? 'Activa' : 'Inactiva' }}
                </span>
                <span v-if="o.deleted_at" class="ml-2 inline-flex rounded-full px-2 py-1 text-xs bg-rose-100 text-rose-900">
                  Archivada
                </span>
              </div>
            </td>

            <td class="px-4 py-3 flex gap-2">
              <Link
                :href="route('offices.edit', o.id)"
                class="rounded-lg border px-3 py-1.5 text-xs hover:bg-zinc-50"
              >
                Editar
              </Link>

              <button
                v-if="!o.deleted_at"
                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs text-rose-900 hover:bg-rose-100"
                @click="
                  router.delete(route('offices.destroy', o.id), {
                    preserveScroll: true,
                    onBefore: () => confirm('¿Archivar sucursal?'),
                  })
                "
              >
                Archivar
              </button>

              <button
                v-else
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs text-emerald-900 hover:bg-emerald-100"
                @click="
                  router.post(route('offices.restore', o.id), {}, {
                    preserveScroll: true,
                    onBefore: () => confirm('¿Restaurar sucursal?'),
                  })
                "
              >
                Restaurar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex flex-wrap gap-2">
      <Link
        v-for="(l, idx) in offices.links"
        :key="idx"
        :href="l.url ?? ''"
        class="rounded border px-3 py-1 text-xs"
        :class="[
          !l.url ? 'opacity-50 pointer-events-none' : 'hover:bg-zinc-50',
          l.active ? 'bg-zinc-900 text-white border-zinc-900' : '',
        ]"
        v-html="l.label"
      />
    </div>
  </div>
</template>