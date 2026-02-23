<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps<{ mustChange: boolean }>()

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put('/account/password', {
    preserveScroll: true,
    onStart: () => form.clearErrors(),
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <Head title="Cambiar contraseña" />

  <AppLayout>
    <div class="w-full px-6 py-8 lg:px-10">
      <div class="max-w-xl">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">Cambiar contraseña</h1>

        <p v-if="props.mustChange" class="mt-2 text-sm text-amber-700 dark:text-amber-300">
          Por seguridad, debes cambiar tu contraseña antes de continuar.
        </p>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
          <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 space-y-3">
            <div>
              <label class="text-sm text-zinc-700 dark:text-zinc-300">Contraseña actual</label>
              <input
                v-model="form.current_password"
                type="password"
                autocomplete="current-password"
                class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
              />
              <p v-if="form.errors.current_password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.current_password }}
              </p>
            </div>

            <div>
              <label class="text-sm text-zinc-700 dark:text-zinc-300">Nueva contraseña</label>
              <input
                v-model="form.password"
                type="password"
                autocomplete="new-password"
                class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
              />
              <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.password }}
              </p>
            </div>

            <div>
              <label class="text-sm text-zinc-700 dark:text-zinc-300">Confirmar nueva contraseña</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                autocomplete="new-password"
                class="mt-1 w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-zinc-900 dark:text-zinc-100 outline-none"
              />
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="form.processing"
                class="rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 px-4 py-2 text-sm font-medium hover:opacity-90 disabled:opacity-60"
              >
                {{ form.processing ? 'Guardando…' : 'Actualizar contraseña' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>