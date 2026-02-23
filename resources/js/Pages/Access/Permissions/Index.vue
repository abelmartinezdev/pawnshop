<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { useToast } from 'vue-toastification'

const props = defineProps<{
  permissions: Array<{ id: number; name: string }>
  grouped: Record<string, Array<{ id: number; name: string }>>
  filters: { q?: string }
}>()

const page = usePage()
const toast = useToast()

// flash toast global
const flash = computed(() => (page.props as any).flash)
watch(
  flash,
  (v) => {
    if (!v) return
    v.type === 'success' ? toast.success(v.message) : toast.error(v.message)
  },
  { immediate: true },
)

const q = ref(props.filters.q ?? '')
let t: any = null
watch(q, () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get('/access/permissions', { q: q.value }, { preserveState: true, replace: true })
  }, 250)
})

const groups = computed(() => {
  const entries = Object.entries(props.grouped ?? {})
  entries.sort((a, b) => {
    if (a[0] === 'misc') return 1
    if (b[0] === 'misc') return -1
    return a[0].localeCompare(b[0])
  })
  return entries
})

const canManage = computed(() => {
  const map = ((page.props as any)?.auth?.can ?? {}) as Record<string, boolean>
  // usas roles.manage para administrar permisos
  return !!map['roles.manage']
})

const newPerm = ref('')

const createPermission = () => {
  const name = newPerm.value.trim()
  if (!name) return

  router.post(
    '/access/permissions',
    { name },
    {
      preserveScroll: true,
      onSuccess: () => (newPerm.value = ''),
    },
  )
}

const deletePermission = (p: { id: number; name: string }) => {
  if (!confirm(`¿Eliminar permiso "${p.name}"? Esto lo quitará de roles/usuarios que lo tengan.`)) return
  router.delete(`/access/permissions/${p.id}`, { preserveScroll: true })
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Accesos', href: '/access/users' },
  { title: 'Permisos', href: '/access/permissions' },
]
</script>

<template>
  <Head title="Accesos · Permisos" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full px-6 py-6 lg:px-8">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Permisos</h1>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Catálogo de permisos (guard web). Crea permisos y asígnalos a roles.
          </p>
        </div>
      </div>

      <!-- Crear permiso -->
      <div
        v-if="canManage"
        class="mt-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6"
      >
        <div class="flex flex-col md:flex-row gap-3 md:items-end">
          <div class="w-full md:flex-1">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
              Nuevo permiso
            </label>
            <input
              v-model="newPerm"
              placeholder="ej: access.permissions.manage"
              class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950
                     px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-500">
              Convención recomendada: <span class="font-medium">modulo.accion</span> (ej: branches.manage)
            </p>
          </div>

          <button
            type="button"
            @click="createPermission"
            class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 text-sm font-medium hover:opacity-90"
          >
            Crear
          </button>
        </div>
      </div>

      <!-- Buscar -->
      <div class="mt-6">
        <input
          v-model="q"
          placeholder="Buscar permiso… (ej: branches.)"
          class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950
                 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
        />
      </div>

      <!-- Agrupado -->
      <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div
          v-for="[group, items] in groups"
          :key="group"
          class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6"
        >
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ group }}</h2>
            <span class="text-xs text-zinc-500 dark:text-zinc-500">{{ items.length }}</span>
          </div>

          <div class="mt-3 space-y-2">
            <div
              v-for="p in items"
              :key="p.id"
              class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 dark:border-zinc-800 px-3 py-2"
            >
              <span class="text-xs text-zinc-800 dark:text-zinc-200">{{ p.name }}</span>

              <button
                v-if="canManage"
                type="button"
                @click="deletePermission(p)"
                class="text-xs rounded-lg border border-red-200 dark:border-red-900/50 px-2 py-1 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-950/20"
              >
                Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="props.permissions.length === 0" class="mt-10 text-center text-sm text-zinc-600 dark:text-zinc-400">
        No hay permisos.
      </div>
    </div>
  </AppLayout>
</template>