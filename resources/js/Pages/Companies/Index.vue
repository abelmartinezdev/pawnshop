<!-- resources/js/Pages/Companies/Index.vue -->
<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { useToast } from 'vue-toastification'
import { type BreadcrumbItem } from '@/types'

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
  filters: { q?: string; status?: string; perPage?: number }
}>()

const page = usePage()
const toast = useToast()

const flash = computed(() => (page.props as any)?.flash)
watch(
  flash,
  (v) => {
    if (!v) return
    v.type === 'success' ? toast.success(v.message) : toast.error(v.message)
  },
  { immediate: true },
)

const q = ref(props.filters.q ?? '')
const status = ref(props.filters.status ?? 'active')
const perPage = ref(props.filters.perPage ?? 10)

let t: any = null
watch([q, status, perPage], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get(
      route('companies.index'),
      { q: q.value, status: status.value, perPage: perPage.value },
      { preserveState: true, replace: true, preserveScroll: true },
    )
  }, 250)
})

const isTrashedView = computed(() => status.value === 'trashed')

const archiveCompany = (id: number) => {
  router.delete(route('companies.destroy', id), {
    preserveScroll: true,
    onBefore: () => confirm('¿Archivar empresa?'),
  })
}

const restoreCompany = (id: number) => {
  router.post(
    route('companies.restore', id),
    {},
    {
      preserveScroll: true,
      onBefore: () => confirm('¿Restaurar empresa?'),
    },
  )
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Empresas', href: route('companies.index') },
]
</script>

<template>
  <Head title="Empresas" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full px-6 py-6 lg:px-8">
      <!-- Header -->
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Empresas</h1>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Gestiona empresas (soft delete incluido).
          </p>
        </div>

        <Link
          :href="route('companies.create')"
          class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 text-sm font-medium hover:opacity-90"
        >
          Nueva empresa
        </Link>
      </div>

      <!-- Filtros -->
      <div class="mt-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Buscar</label>
            <input
              v-model="q"
              type="text"
              placeholder="Nombre o código…"
              class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950
                     px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado</label>
            <select
              v-model="status"
              class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950
                     px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
            >
              <option value="active">Activas</option>
              <option value="all">Todas</option>
              <option value="trashed">Archivadas</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Por página</label>
            <select
              v-model.number="perPage"
              class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950
                     px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
            >
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
          </div>
        </div>

        <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-500">
          Vista actual: <span class="font-medium">{{ isTrashedView ? 'Archivadas' : 'No archivadas' }}</span>
        </p>
      </div>

      <!-- Tabla -->
      <div class="mt-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-950/50">
              <tr class="text-left text-zinc-700 dark:text-zinc-300">
                <th class="px-6 py-4 font-semibold">Nombre</th>
                <th class="px-6 py-4 font-semibold">Código</th>
                <th class="px-6 py-4 font-semibold">Contacto</th>
                <th class="px-6 py-4 font-semibold">Estado</th>
                <th class="px-6 py-4 font-semibold w-60">Acciones</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
              <tr v-if="!props.companies.data.length">
                <td colspan="5" class="px-6 py-10 text-center text-sm text-zinc-600 dark:text-zinc-400">
                  Sin resultados.
                </td>
              </tr>

              <tr
                v-for="c in props.companies.data"
                :key="c.id"
                class="text-zinc-900 dark:text-zinc-100"
              >
                <td class="px-6 py-4">
                  <div class="font-medium">{{ c.name }}</div>
                  <div v-if="c.address" class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ c.address }}
                  </div>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="inline-flex rounded-lg bg-zinc-100 dark:bg-zinc-800 px-2 py-1 text-xs text-zinc-800 dark:text-zinc-100"
                  >
                    {{ c.code }}
                  </span>
                </td>

                <td class="px-6 py-4">
                  <div class="text-xs" v-if="c.phone">📞 {{ c.phone }}</div>
                  <div class="text-xs" v-if="c.email">✉️ {{ c.email }}</div>
                  <div v-if="!c.phone && !c.email" class="text-xs text-zinc-500 dark:text-zinc-400">—</div>
                </td>

                <td class="px-6 py-4">
                  <span
                    class="inline-flex rounded-full px-2 py-1 text-xs"
                    :class="
                      c.is_active
                        ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-200'
                        : 'bg-zinc-200 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200'
                    "
                  >
                    {{ c.is_active ? 'Activa' : 'Inactiva' }}
                  </span>

                  <span
                    v-if="c.deleted_at"
                    class="ml-2 inline-flex rounded-full px-2 py-1 text-xs bg-rose-100 text-rose-900 dark:bg-rose-900/30 dark:text-rose-200"
                  >
                    Archivada
                  </span>
                </td>

                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-2">
                    <Link
                      :href="route('companies.edit', c.id)"
                      class="rounded-lg border border-zinc-200 dark:border-zinc-800 px-3 py-1.5 text-xs
                             hover:bg-zinc-50 dark:hover:bg-zinc-950/30"
                    >
                      Editar
                    </Link>

                    <button
                      v-if="!c.deleted_at"
                      type="button"
                      class="rounded-lg border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/20
                             px-3 py-1.5 text-xs text-rose-900 dark:text-rose-200 hover:opacity-90"
                      @click="archiveCompany(c.id)"
                    >
                      Archivar
                    </button>

                    <button
                      v-else
                      type="button"
                      class="rounded-lg border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/20
                             px-3 py-1.5 text-xs text-emerald-900 dark:text-emerald-200 hover:opacity-90"
                      @click="restoreCompany(c.id)"
                    >
                      Restaurar
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="props.companies.links?.length" class="p-4 border-t border-zinc-200 dark:border-zinc-800">
          <div class="flex flex-wrap gap-2">
            <Link
              v-for="(l, idx) in props.companies.links"
              :key="idx"
              :href="l.url ?? ''"
              class="rounded-lg border border-zinc-200 dark:border-zinc-800 px-3 py-1 text-xs
                     text-zinc-800 dark:text-zinc-200"
              :class="[
                !l.url ? 'opacity-50 pointer-events-none' : 'hover:bg-zinc-50 dark:hover:bg-zinc-950/30',
                l.active ? 'bg-zinc-900 text-white border-zinc-900 dark:bg-white dark:text-zinc-900 dark:border-white' : '',
              ]"
              v-html="l.label"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>