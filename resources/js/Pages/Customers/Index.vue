<script setup lang="ts">
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
  customers: any
  filters: { q: string; status: string; perPage: number }
}>()

const q = computed({
  get: () => props.filters.q ?? '',
  set: (v) => router.get('/customers', { ...props.filters, q: v }, { preserveState: true, replace: true }),
})

const setStatus = (status: string) => {
  router.get('/customers', { ...props.filters, status }, { preserveState: true, replace: true })
}

const destroyCustomer = (id: number) => {
  if (!confirm('¿Archivar cliente?')) return
  router.delete(`/customers/${id}`)
}

const restoreCustomer = (id: number) => {
  router.put(`/customers/${id}/restore`)
}
</script>

<template>
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-semibold">Clientes</h1>
        <p class="text-sm text-zinc-500">Administra tus clientes</p>
      </div>

      <Link href="/customers/create" class="px-3 py-2 rounded-lg bg-zinc-900 text-white">
        Nuevo
      </Link>
    </div>

    <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
      <input
        v-model="q"
        class="w-full md:w-96 rounded-lg border border-zinc-200 px-3 py-2"
        placeholder="Buscar por nombre, RFC, teléfono..."
      />

      <div class="flex gap-2">
        <button class="px-3 py-2 rounded-lg border" @click="setStatus('active')">Activos</button>
        <button class="px-3 py-2 rounded-lg border" @click="setStatus('all')">Todos</button>
        <button class="px-3 py-2 rounded-lg border" @click="setStatus('trashed')">Archivados</button>
      </div>
    </div>

    <div class="rounded-xl border border-zinc-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-zinc-50">
          <tr>
            <th class="text-left px-3 py-2">Nombre</th>
            <th class="text-left px-3 py-2">RFC</th>
            <th class="text-left px-3 py-2">Teléfono</th>
            <th class="text-left px-3 py-2">INAPAM</th>
            <th class="text-right px-3 py-2">Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="c in customers.data" :key="c.id" class="border-t">
            <td class="px-3 py-2">
              <div class="font-medium">{{ c.name }}</div>
              <div class="text-xs text-zinc-500">{{ c.email }}</div>
            </td>
            <td class="px-3 py-2">{{ c.rfc }}</td>
            <td class="px-3 py-2">{{ c.mobile || c.phone }}</td>
            <td class="px-3 py-2">{{ c.inapam_code ? 'Sí' : 'No' }}</td>
            <td class="px-3 py-2 text-right space-x-2">
              <Link :href="`/customers/${c.id}/edit`" class="underline">Editar</Link>

              <button
                v-if="!c.deleted_at"
                class="text-red-600 underline"
                @click="destroyCustomer(c.id)"
              >
                Archivar
              </button>

              <button
                v-else
                class="text-emerald-700 underline"
                @click="restoreCustomer(c.id)"
              >
                Restaurar
              </button>
            </td>
          </tr>

          <tr v-if="customers.data.length === 0">
            <td colspan="5" class="px-3 py-6 text-center text-zinc-500">
              Sin resultados
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- paginación simple -->
    <div class="flex gap-2">
      <Link
        v-for="(l, idx) in customers.links"
        :key="idx"
        :href="l.url || '#'"
        class="px-3 py-2 rounded-lg border"
        :class="l.active ? 'bg-zinc-900 text-white border-zinc-900' : ''"
        v-html="l.label"
        :disabled="!l.url"
      />
    </div>
  </div>
</template>