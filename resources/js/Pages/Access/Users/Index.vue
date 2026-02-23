<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  users: any
  filters: { q?: string; perPage?: number }
}>()

const q = ref(props.filters.q ?? '')
const perPage = ref(props.filters.perPage ?? 10)

let t: any = null
watch([q, perPage], () => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get('/access/users', { q: q.value, perPage: perPage.value }, { preserveState: true, replace: true })
  }, 250)
})

const rows = computed(() => props.users?.data ?? [])

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Accesos', href: '/access/users' },
  { title: 'Usuarios', href: '/access/users' },
]
</script>

<template>
  <Head title="Accesos · Usuarios" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full mx-auto p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Usuarios</h1>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">Asigna roles y permisos directos.</p>
        </div>
        <Link
  href="/access/users/create"
  class="inline-flex items-center rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 text-sm font-medium hover:opacity-90"
>
  Nuevo usuario
</Link>
      </div>

      <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-3">
        <input v-model="q" placeholder="Buscar por nombre o email…"
          class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none" />
        <select v-model.number="perPage"
          class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>
      </div>

      <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        <table class="w-full text-sm">
          <thead class="bg-zinc-50 dark:bg-zinc-950/40 text-zinc-600 dark:text-zinc-400">
            <tr>
              <th class="text-left p-3">Usuario</th>
              <th class="text-left p-3">Roles</th>
              <th class="text-right p-3">Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="u in rows" :key="u.id" class="border-t border-zinc-100 dark:border-zinc-800">
              <td class="p-3">
                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ u.name }}</div>
                <div class="text-xs text-zinc-500 dark:text-zinc-500">{{ u.email }}</div>
              </td>

              <td class="p-3">
                <div class="flex flex-wrap gap-2">
                  <span v-for="r in (u.roles ?? [])" :key="r.id"
                        class="text-xs rounded-full bg-zinc-200 dark:bg-zinc-800 px-2 py-1 text-zinc-700 dark:text-zinc-200">
                    {{ r.name }}
                  </span>
                  <span v-if="(u.roles ?? []).length === 0" class="text-xs text-zinc-500">—</span>
                </div>
              </td>

              <td class="p-3 text-right">
                <Link :href="`/access/users/${u.id}/edit`"
                      class="rounded-lg border border-zinc-200 dark:border-zinc-700 px-3 py-1.5 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-950/40">
                  Editar accesos
                </Link>
              </td>
            </tr>

            <tr v-if="rows.length === 0">
              <td colspan="3" class="p-6 text-center text-zinc-600 dark:text-zinc-400">
                No hay usuarios.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex flex-wrap gap-2">
        <template v-for="(l, i) in props.users.links" :key="i">
          <button v-if="l.url" @click="router.get(l.url, {}, { preserveState: true, preserveScroll: true })"
                  class="px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm"
                  :class="l.active ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'text-zinc-700 dark:text-zinc-200'"
                  v-html="l.label" />
          <span v-else class="px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-400"
                v-html="l.label" />
        </template>
      </div>
    </div>
  </AppLayout>
</template>