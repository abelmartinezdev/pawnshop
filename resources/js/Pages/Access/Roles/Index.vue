<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  roles: Array<{ id: number; name: string; users_count: number }>
}>()

const form = useForm({ name: '' })

const submit = () => {
  form.post('/access/roles', { preserveScroll: true, onStart: () => form.clearErrors() })
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Accesos', href: '/access/users' },
  { title: 'Roles', href: '/access/roles' },
]
</script>

<template>
  <Head title="Accesos · Roles" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full mx-auto p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Roles</h1>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">Crea roles y define permisos.</p>
        </div>

        <Link
          href="/access/permissions"
          class="rounded-xl border border-zinc-200 dark:border-zinc-700 px-4 py-2 text-sm
                 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-950/40"
        >
          Ver permisos
        </Link>
      </div>

      <form class="mt-6 rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6"
            @submit.prevent="submit">
        <div class="flex flex-col md:flex-row gap-3 items-start md:items-end">
          <div class="w-full md:flex-1">
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nuevo rol</label>
            <input
              v-model="form.name"
              placeholder="ej: auditor"
              class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950
                     px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
              :class="form.errors.name ? 'border-red-400 dark:border-red-500' : ''"
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 text-sm font-medium
                   hover:opacity-90 disabled:opacity-60"
          >
            {{ form.processing ? 'Creando…' : 'Crear rol' }}
          </button>
        </div>
      </form>

      <div class="mt-6 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
        <table class="w-full text-sm">
          <thead class="bg-zinc-50 dark:bg-zinc-950/40 text-zinc-600 dark:text-zinc-400">
            <tr>
              <th class="text-left p-3">Rol</th>
              <th class="text-left p-3">Usuarios</th>
              <th class="text-right p-3">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in props.roles" :key="r.id" class="border-t border-zinc-100 dark:border-zinc-800">
              <td class="p-3 font-medium text-zinc-900 dark:text-zinc-100">{{ r.name }}</td>
              <td class="p-3 text-zinc-700 dark:text-zinc-300">{{ r.users_count }}</td>
              <td class="p-3 text-right">
                <Link
                  :href="`/access/roles/${r.id}/edit`"
                  class="rounded-lg border border-zinc-200 dark:border-zinc-700 px-3 py-1.5 text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-950/40"
                >
                  Editar permisos
                </Link>
              </td>
            </tr>

            <tr v-if="props.roles.length === 0">
              <td colspan="3" class="p-6 text-center text-zinc-600 dark:text-zinc-400">No hay roles.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>