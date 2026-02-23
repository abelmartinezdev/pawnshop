<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  user: {
    id: number
    name: string
    email: string
    branch_id: number | null
    roles: string[]
    permissions: string[]
  }
  roles: Array<{ id: number; name: string }>
  permissions: Array<{ id: number; name: string }>
  branches: Array<{ id: number; name: string; code: string }>
}>()

const form = useForm({
  branch_id: props.user.branch_id,
  roles: [...(props.user.roles ?? [])],
  permissions: [...(props.user.permissions ?? [])], // direct perms
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Accesos', href: '/access/users' },
  { title: 'Usuarios', href: '/access/users' },
  { title: 'Editar', href: `/access/users/${props.user.id}/edit` },
]

const submit = () => {
  form.put(`/access/users/${props.user.id}`, {
    preserveScroll: true,
    onStart: () => form.clearErrors(),
  })
}
</script>

<template>
  <Head title="Accesos · Editar usuario" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full mx-auto p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ props.user.name }}</h1>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ props.user.email }}</p>
        </div>
        <Link href="/access/users" class="text-sm text-zinc-700 dark:text-zinc-300 hover:underline">Volver</Link>
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Sucursal por defecto</h2>
          <select v-model="form.branch_id"
                  class="mt-3 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none">
            <option :value="null">—</option>
            <option v-for="b in props.branches" :key="b.id" :value="b.id">
              {{ b.name }} ({{ b.code }})
            </option>
          </select>
          <p v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.branch_id }}
          </p>
        </div>

        <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Roles</h2>
          <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">Los roles aportan permisos. (syncRoles)</p>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2">
            <label v-for="r in props.roles" :key="r.id"
                   class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 px-3 py-2">
              <input type="checkbox" :value="r.name" v-model="form.roles" class="rounded border-zinc-300 dark:border-zinc-700" />
              <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ r.name }}</span>
            </label>
          </div>

          <p v-if="form.errors.roles" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.roles }}</p>
        </div>

        <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Permisos directos</h2>
          <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">Úsalos sólo cuando no quieras crear un rol. (syncPermissions)</p>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2">
            <label v-for="p in props.permissions" :key="p.id"
                   class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 px-3 py-2">
              <input type="checkbox" :value="p.name" v-model="form.permissions" class="rounded border-zinc-300 dark:border-zinc-700" />
              <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ p.name }}</span>
            </label>
          </div>

          <p v-if="form.errors.permissions" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.permissions }}</p>
        </div>

        <div class="flex justify-end">
          <button type="submit" :disabled="form.processing"
                  class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 text-sm font-medium hover:opacity-90 disabled:opacity-60">
            {{ form.processing ? 'Guardando…' : 'Guardar accesos' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>