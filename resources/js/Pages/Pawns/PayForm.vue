<script setup lang="ts">
import { computed, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineOptions({ layout: AppLayout })

const props = defineProps<{
  pawn: any
  amounts: {
    interest_total: number
    liquidate_total: number
    countersign_interest: number
  }
  defaults: {
    payment_type: 'cash' | 'card' | 'transfer'
    transaction: 'liquidate' | 'countersign' | 'interest_payment'
  }
}>()

function money(n: any) {
  const v = Number(n ?? 0)
  return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })
}

const form = useForm({
  transaction: props.defaults.transaction,
  payment_type: props.defaults.payment_type,
  amount_paid: '',
  payment: '',
  pay_extra: 0,
  discount: 0,
  change: '0',
})

// auto sugerencias
const suggestedToPay = computed(() => {
  if (form.transaction === 'liquidate') return props.amounts.liquidate_total
  if (form.transaction === 'countersign') return props.amounts.countersign_interest
  return 0
})

watch(
  () => form.amount_paid,
  () => {
    // Cambio = amount_paid - requerido
    const paid = Number(form.amount_paid || 0)
    const need =
      form.transaction === 'interest_payment'
        ? Number(form.payment || 0)
        : Number(suggestedToPay.value || 0) + Number(form.transaction === 'countersign' ? form.pay_extra || 0 : 0)

    const change = paid - need
    form.change = String(isFinite(change) ? Math.max(0, Math.round(change)) : 0)
  }
)

function submit() {
  form.post(route('pawns.pay', props.pawn.id))
}
</script>

<template>
  <Head :title="`Cobro ${pawn.folio}`" />

  <div class="p-6 space-y-4">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-sm text-zinc-500">Cobro</div>
        <h1 class="text-2xl font-semibold">{{ pawn.folio }}</h1>
        <div class="text-sm text-zinc-500 mt-1">Cliente: {{ pawn.customer?.name ?? '—' }}</div>
      </div>

      <Link :href="route('pawns.show', pawn.id)" class="rounded-xl border border-zinc-200 px-4 py-2 text-sm hover:bg-zinc-50">
        Volver
      </Link>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
      <!-- Form -->
      <div class="rounded-2xl border border-zinc-200 bg-white p-4 space-y-4 lg:col-span-2">
        <div class="text-sm font-semibold">Tipo de operación</div>

        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
          <label class="rounded-xl border border-zinc-200 p-3 text-sm cursor-pointer">
            <input v-model="form.transaction" type="radio" value="liquidate" class="mr-2" />
            Liquidación
            <div class="text-xs text-zinc-500 mt-1">Total: {{ money(amounts.liquidate_total) }}</div>
          </label>

          <label class="rounded-xl border border-zinc-200 p-3 text-sm cursor-pointer">
            <input v-model="form.transaction" type="radio" value="countersign" class="mr-2" />
            Refrendo
            <div class="text-xs text-zinc-500 mt-1">Interés: {{ money(amounts.countersign_interest) }}</div>
          </label>

          <label class="rounded-xl border border-zinc-200 p-3 text-sm cursor-pointer">
            <input v-model="form.transaction" type="radio" value="interest_payment" class="mr-2" />
            Abono a interés
            <div class="text-xs text-zinc-500 mt-1">Manual</div>
          </label>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div>
            <div class="text-sm font-medium mb-1">Forma de pago</div>
            <select v-model="form.payment_type" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm">
              <option value="cash">Efectivo</option>
              <option value="card">Tarjeta</option>
              <option value="transfer">Transferencia</option>
            </select>
            <div v-if="form.errors.payment_type" class="text-xs text-red-600 mt-1">{{ form.errors.payment_type }}</div>
          </div>

          <div>
            <div class="text-sm font-medium mb-1">Cantidad recibida (amount_paid)</div>
            <input v-model="form.amount_paid" type="number" step="0.01" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" />
            <div v-if="form.errors.amount_paid" class="text-xs text-red-600 mt-1">{{ form.errors.amount_paid }}</div>
          </div>

          <div v-if="form.transaction === 'interest_payment'">
            <div class="text-sm font-medium mb-1">Abono a interés (payment)</div>
            <input v-model="form.payment" type="number" step="0.01" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" />
            <div v-if="form.errors.payment" class="text-xs text-red-600 mt-1">{{ form.errors.payment }}</div>
          </div>

          <div v-if="form.transaction === 'countersign'">
            <div class="text-sm font-medium mb-1">Abono a capital (pay_extra)</div>
            <input v-model="form.pay_extra" type="number" step="0.01" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" />
            <div v-if="form.errors.pay_extra" class="text-xs text-red-600 mt-1">{{ form.errors.pay_extra }}</div>
          </div>

          <div v-if="form.transaction === 'liquidate'">
            <div class="text-sm font-medium mb-1">Descuento (%)</div>
            <input v-model="form.discount" type="number" step="1" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-sm" />
            <div v-if="form.errors.discount" class="text-xs text-red-600 mt-1">{{ form.errors.discount }}</div>
          </div>

          <div>
            <div class="text-sm font-medium mb-1">Cambio (calculado)</div>
            <input v-model="form.change" type="text" readonly class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm" />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
          <button
            type="button"
            @click="submit"
            :disabled="form.processing"
            class="rounded-xl bg-zinc-900 px-4 py-2 text-sm text-white hover:bg-zinc-800 disabled:opacity-60"
          >
            Registrar
          </button>
        </div>
      </div>

      <!-- Summary -->
      <div class="rounded-2xl border border-zinc-200 bg-white p-4 space-y-3">
        <div class="text-sm font-semibold">Resumen</div>

        <div class="text-sm">
          <div class="text-zinc-500">Interés (menos 1 día)</div>
          <div class="font-semibold">{{ money(amounts.interest_total) }}</div>
        </div>

        <div class="text-sm">
          <div class="text-zinc-500">Total liquidación (menos 1 día)</div>
          <div class="font-semibold">{{ money(amounts.liquidate_total) }}</div>
        </div>

        <div class="text-sm">
          <div class="text-zinc-500">Sugerido a cobrar</div>
          <div class="font-semibold">{{ money(suggestedToPay) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>