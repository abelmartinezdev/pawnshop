<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  roles: Array<{ id: number; name: string }>
  permissions: Array<{ id: number; name: string }>
  branches: Array<{ id: number; name: string; code: string }>
}>()

const form = useForm({
  name: '',
  email: '',
  branch_id: props.branches?.[0]?.id ?? null,
  password: '',
  roles: [] as string[],
  permissions: [] as string[],
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Accesos', href: '/access/users' },
  { title: 'Usuarios', href: '/access/users' },
  { title: 'Nuevo', href: '/access/users/create' },
]

const submit = () => {
  form.post('/access/users', {
    preserveScroll: true,
    onStart: () => form.clearErrors(),
  })
}
</script>

<template>
  <Head title="Accesos · Nuevo usuario" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full px-6 py-6 lg:px-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Nuevo usuario</h1>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">Crea un usuario y asígnale accesos.</p>
        </div>
        <Link href="/access/users" class="text-sm text-zinc-700 dark:text-zinc-300 hover:underline">Volver</Link>
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Datos</h2>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
              <label class="text-sm text-zinc-700 dark:text-zinc-300">Nombre</label>
              <input v-model="form.name"
                class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none" />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.name }}</p>
            </div>

            <div>
              <label class="text-sm text-zinc-700 dark:text-zinc-300">Email</label>
              <input v-model="form.email" type="email"
                class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none" />
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.email }}</p>
            </div>

            <div>
              <label class="text-sm text-zinc-700 dark:text-zinc-300">Sucursal</label>
              <select v-model="form.branch_id"
                class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none">
                <option v-for="b in props.branches" :key="b.id" :value="b.id">
                  {{ b.name }} ({{ b.code }})
                </option>
              </select>
              <p v-if="form.errors.branch_id" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.branch_id }}</p>
            </div>

            <div>
              <label class="text-sm text-zinc-700 dark:text-zinc-300">
                Contraseña (opcional)
              </label>
              <input v-model="form.password" type="password" autocomplete="new-password"
                placeholder="Si lo dejas vacío, se genera una temporal"
                class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none" />
              <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ form.errors.password }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Roles</h2>
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
            {{ form.processing ? 'Creando…' : 'Crear usuario' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>