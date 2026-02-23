<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useToast } from 'vue-toastification'

// ✅ Wayfinder action del controller (genera TS con php artisan wayfinder:generate)
import { store } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController'

const props = defineProps<{
  canResetPassword: boolean
  status?: string | null
}>()

const toast = useToast()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const emailHasError = computed(() => !!form.errors.email)
const passwordHasError = computed(() => !!form.errors.password)

const submit = () => {
  form.clearErrors()

  // ✅ Opción A (recomendada): submit con Wayfinder (Inertia infiere method+url)
  form.submit(store(), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Bienvenido 👋')
      form.reset('password')
    },
    onError: () => {
      toast.error('Revisa tus datos e intenta de nuevo.')
      form.reset('password')
    },
  })

  // ✅ Si por cualquier cosa tu versión no soporta submit(store()),
  // usa esto en su lugar:
  // form.post(store.url(), { ... })
}
</script>

<template>
  <Head title="Iniciar sesión" />

  <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="rounded-3xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm p-8">
        <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
          Iniciar sesión
        </h1>
        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
          Accede para administrar tu sucursal.
        </p>

        <div
          v-if="props.status"
          class="mt-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 p-3 text-sm text-emerald-700 dark:text-emerald-300"
        >
          {{ props.status }}
        </div>

        <form class="mt-6 space-y-4" @submit.prevent="submit">
          <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
              Correo
            </label>
            <input
              v-model="form.email"
              type="email"
              autocomplete="username"
              class="mt-1 w-full rounded-xl border px-3 py-2 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 outline-none transition
                     border-zinc-300 dark:border-zinc-700 focus:ring-2 focus:ring-zinc-300 dark:focus:ring-zinc-700"
              :class="emailHasError ? 'border-red-400 dark:border-red-500 focus:ring-red-200 dark:focus:ring-red-900/40' : ''"
            />
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
              Contraseña
            </label>
            <input
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              class="mt-1 w-full rounded-xl border px-3 py-2 bg-white dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100 outline-none transition
                     border-zinc-300 dark:border-zinc-700 focus:ring-2 focus:ring-zinc-300 dark:focus:ring-zinc-700"
              :class="passwordHasError ? 'border-red-400 dark:border-red-500 focus:ring-red-200 dark:focus:ring-red-900/40' : ''"
            />
            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.password }}
            </p>
          </div>

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
              <input v-model="form.remember" type="checkbox" class="rounded border-zinc-300 dark:border-zinc-700" />
              Recuérdame
            </label>

            <Link
              v-if="props.canResetPassword"
              href="/forgot-password"
              class="text-sm text-zinc-700 dark:text-zinc-300 hover:underline"
            >
              ¿Olvidaste tu contraseña?
            </Link>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 py-2.5 font-medium
                   hover:opacity-90 disabled:opacity-60 transition"
          >
            {{ form.processing ? 'Entrando...' : 'Entrar' }}
          </button>
        </form>
      </div>

      <p class="mt-4 text-center text-xs text-zinc-500">
        Casa de empeño · Sistema de Operación
      </p>
    </div>
  </div>
</template>