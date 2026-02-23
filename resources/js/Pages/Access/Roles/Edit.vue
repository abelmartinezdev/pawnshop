<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  role: { id: number; name: string; permissions: string[] }
  permissions: Array<{ id: number; name: string }>
}>()

const form = useForm({
  permissions: [...(props.role.permissions ?? [])],
})

const q = ref('')

const filtered = computed(() => {
  const s = q.value.trim().toLowerCase()
  if (!s) return props.permissions
  return props.permissions.filter((p) => p.name.toLowerCase().includes(s))
})

const toggleAllFiltered = (value: boolean) => {
  const names = filtered.value.map((p) => p.name)
  const current = new Set(form.permissions)

  if (value) {
    names.forEach((n) => current.add(n))
  } else {
    names.forEach((n) => current.delete(n))
  }

  form.permissions = Array.from(current)
}

const submit = () => {
  form.put(`/access/roles/${props.role.id}`, { preserveScroll: true, onStart: () => form.clearErrors() })
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Accesos', href: '/access/users' },
  { title: 'Roles', href: '/access/roles' },
  { title: props.role.name, href: `/access/roles/${props.role.id}/edit` },
]
</script>

<template>
  <Head :title="`Accesos · Rol · ${props.role.name}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full mx-auto p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Rol: {{ props.role.name }}</h1>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">Selecciona permisos para este rol.</p>
        </div>
        <div class="flex items-center gap-2">
          <Link href="/access/roles" class="text-sm text-zinc-700 dark:text-zinc-300 hover:underline">Volver</Link>
        </div>
      </div>

      <form class="mt-6 space-y-4" @submit.prevent="submit">
        <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6">
          <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
            <div class="w-full md:max-w-md">
              <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Buscar permiso</label>
              <input
                v-model="q"
                placeholder="ej: branches."
                class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950
                       px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
              />
            </div>

            <div class="flex gap-2">
              <button
                type="button"
                @click="toggleAllFiltered(true)"
                class="rounded-xl border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-sm
                       text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-950/40"
              >
                Marcar filtrados
              </button>
              <button
                type="button"
                @click="toggleAllFiltered(false)"
                class="rounded-xl border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-sm
                       text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-950/40"
              >
                Desmarcar filtrados
              </button>
            </div>
          </div>

          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            <label
              v-for="p in filtered"
              :key="p.id"
              class="flex items-center gap-2 rounded-xl border border-zinc-200 dark:border-zinc-800 px-3 py-2"
            >
              <input
                type="checkbox"
                :value="p.name"
                v-model="form.permissions"
                class="rounded border-zinc-300 dark:border-zinc-700"
              />
              <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ p.name }}</span>
            </label>
          </div>

          <p v-if="form.errors.permissions" class="mt-2 text-sm text-red-600 dark:text-red-400">
            {{ form.errors.permissions }}
          </p>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="form.processing"
            class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 text-sm font-medium
                   hover:opacity-90 disabled:opacity-60"
          >
            {{ form.processing ? 'Guardando…' : 'Guardar permisos' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>