<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

type CompanyRow = {
  id: number
  name: string
  code: string
  phone?: string | null
  email?: string | null
  address?: string | null
  is_active: boolean
  deleted_at?: string | null
}

const props = defineProps<{
  companies: {
    data: CompanyRow[]
    links: Array<{ url: string | null; label: string; active: boolean }>
    meta?: any
  }
  filters: { q: string; status: string; perPage: number }
}>()

const page = usePage()

const state = reactive({
  q: props.filters.q ?? '',
  status: props.filters.status ?? 'active',
  perPage: props.filters.perPage ?? 10,
})

watch(
  () => [state.q, state.status, state.perPage],
  () => {
    router.get(
      route('companies.index'),
      { q: state.q, status: state.status, perPage: state.perPage },
      { preserveState: true, replace: true, preserveScroll: true },
    )
  },
)

const isTrashedView = computed(() => state.status === 'trashed')

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
        <h1 class="text-xl font-semibold">Empresas</h1>
        <p class="text-sm text-zinc-500">Gestiona empresas (soft delete incluido).</p>
      </div>

      <Link :href="route('companies.create')" class="rounded-lg bg-zinc-900 text-white px-4 py-2 text-sm hover:bg-zinc-800">
        Nueva empresa
      </Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <input
        v-model="state.q"
        type="text"
        placeholder="Buscar por nombre o código..."
        class="w-full rounded-lg border px-3 py-2 text-sm"
      />

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
            <th class="px-4 py-3">Nombre</th>
            <th class="px-4 py-3">Código</th>
            <th class="px-4 py-3">Contacto</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3 w-56">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-if="!companies.data.length">
            <td colspan="5" class="px-4 py-6 text-center text-zinc-500">Sin resultados.</td>
          </tr>

          <tr v-for="c in companies.data" :key="c.id" class="border-t">
            <td class="px-4 py-3">
              <div class="font-medium">{{ c.name }}</div>
              <div class="text-xs text-zinc-500" v-if="c.address">{{ c.address }}</div>
            </td>

            <td class="px-4 py-3">
              <span class="inline-flex rounded-md bg-zinc-100 px-2 py-1 text-xs">
                {{ c.code }}
              </span>
            </td>

            <td class="px-4 py-3">
              <div class="text-xs" v-if="c.phone">📞 {{ c.phone }}</div>
              <div class="text-xs" v-if="c.email">✉️ {{ c.email }}</div>
              <div v-if="!c.phone && !c.email" class="text-xs text-zinc-500">—</div>
            </td>

            <td class="px-4 py-3">
              <span
                class="inline-flex rounded-full px-2 py-1 text-xs"
                :class="c.is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-zinc-200 text-zinc-800'"
              >
                {{ c.is_active ? 'Activa' : 'Inactiva' }}
              </span>
              <span v-if="c.deleted_at" class="ml-2 inline-flex rounded-full px-2 py-1 text-xs bg-rose-100 text-rose-900">
                Archivada
              </span>
            </td>

            <td class="px-4 py-3 flex gap-2">
              <Link
                :href="route('companies.edit', c.id)"
                class="rounded-lg border px-3 py-1.5 text-xs hover:bg-zinc-50"
              >
                Editar
              </Link>

              <button
                v-if="!c.deleted_at"
                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs text-rose-900 hover:bg-rose-100"
                @click="
                  router.delete(route('companies.destroy', c.id), {
                    preserveScroll: true,
                    onBefore: () => confirm('¿Archivar empresa?'),
                  })
                "
              >
                Archivar
              </button>

              <button
                v-else
                class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs text-emerald-900 hover:bg-emerald-100"
                @click="
                  router.post(route('companies.restore', c.id), {}, {
                    preserveScroll: true,
                    onBefore: () => confirm('¿Restaurar empresa?'),
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
        v-for="(l, idx) in companies.links"
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