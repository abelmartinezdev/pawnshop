<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps<{
  pawn: any
  can: { mayBeCanceled: boolean; canBeAuctioned: boolean }
}>()

function money(n: any) {
  const v = Number(n ?? 0)
  return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })
}

const statusLabel = computed(() => {
  if (props.pawn?.canceled_at) return 'Cancelado'
  if (props.pawn?.paid_at) return 'Liquidado'
  return 'Activo'
})
</script>

<template>
  <Head :title="`Empeño ${pawn.folio}`" />

  <div class="p-6 space-y-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-sm text-zinc-500">Empeño</div>
        <h1 class="text-2xl font-semibold">{{ pawn.folio }}</h1>
        <div class="mt-1 text-sm">
          <span class="text-zinc-500">Estado:</span>
          <span class="ml-2 font-medium">{{ statusLabel }}</span>
        </div>
      </div>

      <div class="flex flex-wrap gap-2">
        <Link
          v-if="!pawn.paid_at && !pawn.canceled_at"
          :href="route('pawns.payForm', pawn.id)"
          class="rounded-xl bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800"
        >
          Cobrar / Pagar
        </Link>

        <Link
          v-if="can.mayBeCanceled"
          :href="route('pawns.cancel', pawn.id)"
          method="post"
          as="button"
          class="rounded-xl border border-red-200 px-4 py-2 text-sm text-red-700 hover:bg-red-50"
        >
          Cancelar empeño
        </Link>

        <Link
          :href="route('pawns.index')"
          class="rounded-xl border border-zinc-200 px-4 py-2 text-sm hover:bg-zinc-50"
        >
          Volver
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
      <!-- Info -->
      <div class="rounded-2xl border border-zinc-200 bg-white p-4 space-y-2">
        <div class="text-sm font-semibold">Datos</div>
        <div class="text-sm"><span class="text-zinc-500">Cliente:</span> {{ pawn.customer?.name ?? '—' }}</div>
        <div class="text-sm"><span class="text-zinc-500">Total:</span> {{ money(pawn.total) }}</div>
        <div class="text-sm"><span class="text-zinc-500">Vence:</span> {{ pawn.date_expiration }}</div>
        <div class="text-sm"><span class="text-zinc-500">Remate:</span> {{ pawn.date_auction }}</div>
        <div class="text-sm"><span class="text-zinc-500">Bolsa:</span> {{ pawn.bag ?? '—' }}</div>
        <div class="text-sm"><span class="text-zinc-500">Beneficiario:</span> {{ pawn.beneficiary ?? '—' }}</div>

        <div class="pt-2 border-t text-sm">
          <div class="text-zinc-500 mb-1">Interés (menos 1 día)</div>
          <div class="font-semibold">{{ money(Math.round(pawn.interest2payminus1day ?? 0)) }}</div>
          <div class="text-zinc-500 mt-2 mb-1">Total a liquidar (menos 1 día)</div>
          <div class="font-semibold">{{ money(Math.round(pawn.amount2liquidateminus1day ?? 0)) }}</div>
        </div>
      </div>

      <!-- Items -->
      <div class="rounded-2xl border border-zinc-200 bg-white p-4 lg:col-span-2">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold">Artículos</div>
          <div class="text-xs text-zinc-500">{{ (pawn.items ?? []).length }} item(s)</div>
        </div>

        <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200">
          <table class="w-full text-sm">
            <thead class="bg-zinc-50 text-zinc-600">
              <tr>
                <th class="text-left p-2">Producto</th>
                <th class="text-left p-2">Descripción</th>
                <th class="text-right p-2">Cant.</th>
                <th class="text-right p-2">Valor</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="it in pawn.items" :key="it.id" class="border-t">
                <td class="p-2">{{ it.product?.name ?? `#${it.product_id}` }}</td>
                <td class="p-2 text-zinc-600">{{ it.description ?? '—' }}</td>
                <td class="p-2 text-right">{{ it.quantity }}</td>
                <td class="p-2 text-right">{{ money(it.value) }}</td>
              </tr>

              <tr v-if="(pawn.items ?? []).length === 0">
                <td colspan="4" class="p-4 text-center text-zinc-500">Sin artículos</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4">
          <div class="text-sm font-semibold">Transacciones</div>

          <div class="mt-2 overflow-hidden rounded-xl border border-zinc-200">
            <table class="w-full text-sm">
              <thead class="bg-zinc-50 text-zinc-600">
                <tr>
                  <th class="text-left p-2">Fecha</th>
                  <th class="text-left p-2">Tipo</th>
                  <th class="text-right p-2">Monto</th>
                  <th class="text-left p-2">Pago</th>
                  <th class="text-right p-2">Ver</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tx in (pawn.transactions ?? [])" :key="tx.id" class="border-t">
                  <td class="p-2">{{ tx.created_at }}</td>
                  <td class="p-2">{{ tx.type }}</td>
                  <td class="p-2 text-right">{{ money(tx.amount) }}</td>
                  <td class="p-2">{{ tx.payment_type ?? '—' }}</td>
                  <td class="p-2 text-right">
                    <Link :href="route('transactions.show', tx.id)" class="underline">Abrir</Link>
                  </td>
                </tr>

                <tr v-if="(pawn.transactions ?? []).length === 0">
                  <td colspan="5" class="p-4 text-center text-zinc-500">Sin transacciones</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-if="pawn.comments" class="mt-4 rounded-xl bg-zinc-50 p-3 text-sm">
          <div class="font-semibold mb-1">Comentarios</div>
          <div class="text-zinc-700 whitespace-pre-line">{{ pawn.comments }}</div>
        </div>
      </div>
    </div>
  </div>
</template>