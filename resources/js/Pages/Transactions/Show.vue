<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps<{
  transaction: any
  pawn: any
  data: Record<string, any> | null
  can: { mayBeCanceled: boolean }
}>()

function money(n: any) {
  const v = Number(n ?? 0)
  return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })
}

const cancelForm = useForm({
  comments_cancel: '',
})

const showCancel = ref(false)

function submitCancel() {
  cancelForm.post(route('transactions.cancel', props.transaction.id), {
    preserveScroll: true,
    onSuccess: () => (showCancel.value = false),
  })
}

const infoRows = computed(() => {
  const d = props.data ?? {}
  return Object.keys(d).map((k) => ({ key: k, val: d[k] }))
})
</script>

<template>
  <Head :title="`Transacción #${transaction.id}`" />

  <div class="p-6 space-y-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-sm text-zinc-500">Transacción</div>
        <h1 class="text-2xl font-semibold">#{{ transaction.id }}</h1>
        <div class="text-sm text-zinc-500 mt-1">
          Empeño:
          <Link :href="route('pawns.show', pawn.id)" class="underline">{{ pawn.folio }}</Link>
        </div>
      </div>

      <div class="flex gap-2">
        <button
          v-if="can.mayBeCanceled"
          @click="showCancel = !showCancel"
          class="rounded-xl border border-red-200 px-4 py-2 text-sm text-red-700 hover:bg-red-50"
        >
          Cancelar
        </button>

        <Link :href="route('pawns.show', pawn.id)" class="rounded-xl border border-zinc-200 px-4 py-2 text-sm hover:bg-zinc-50">
          Volver
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
      <div class="rounded-2xl border border-zinc-200 bg-white p-4 space-y-2">
        <div class="text-sm font-semibold">Detalle</div>
        <div class="text-sm"><span class="text-zinc-500">Tipo:</span> {{ transaction.type }}</div>
        <div class="text-sm"><span class="text-zinc-500">Monto:</span> {{ money(transaction.amount) }}</div>
        <div class="text-sm"><span class="text-zinc-500">Pago:</span> {{ transaction.payment_type ?? '—' }}</div>
        <div class="text-sm"><span class="text-zinc-500">Caja:</span> {{ transaction.balance ?? '—' }}</div>
        <div class="text-sm"><span class="text-zinc-500">Usuario:</span> {{ transaction.user?.name ?? '—' }}</div>
        <div class="text-sm"><span class="text-zinc-500">Fecha:</span> {{ transaction.created_at }}</div>
      </div>

      <div class="rounded-2xl border border-zinc-200 bg-white p-4 lg:col-span-2">
        <div class="text-sm font-semibold">Data (JSON)</div>

        <div v-if="infoRows.length" class="mt-3 overflow-hidden rounded-xl border border-zinc-200">
          <table class="w-full text-sm">
            <thead class="bg-zinc-50 text-zinc-600">
              <tr>
                <th class="text-left p-2">Campo</th>
                <th class="text-left p-2">Valor</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in infoRows" :key="r.key" class="border-t">
                <td class="p-2 font-medium">{{ r.key }}</td>
                <td class="p-2 text-zinc-700">
                  <span v-if="typeof r.val !== 'object'">{{ r.val }}</span>
                  <pre v-else class="text-xs bg-zinc-50 p-2 rounded-lg overflow-auto">{{ JSON.stringify(r.val, null, 2) }}</pre>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="mt-3 text-sm text-zinc-500">Sin data</div>

        <div v-if="showCancel" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4">
          <div class="text-sm font-semibold text-red-800 mb-2">Cancelar transacción</div>

          <textarea
            v-model="cancelForm.comments_cancel"
            rows="3"
            class="w-full rounded-xl border border-red-200 px-3 py-2 text-sm"
            placeholder="Motivo de cancelación..."
          />
          <div v-if="cancelForm.errors.comments_cancel" class="text-xs text-red-700 mt-1">{{ cancelForm.errors.comments_cancel }}</div>

          <div class="mt-3 flex justify-end gap-2">
            <button @click="showCancel = false" class="rounded-xl border border-red-200 px-3 py-2 text-sm text-red-800 hover:bg-red-100">
              Cerrar
            </button>
            <button
              @click="submitCancel"
              :disabled="cancelForm.processing"
              class="rounded-xl bg-red-700 px-3 py-2 text-sm text-white hover:bg-red-800 disabled:opacity-60"
            >
              Confirmar cancelación
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>