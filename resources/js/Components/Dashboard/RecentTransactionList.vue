<script setup>
defineProps({
  transactions: {
    type: Array,
    default: () => [],
  },
})

const variants = {
  saving: {
    icon: '+',
    wrapper: 'bg-green-50 text-[#3aab2e]',
  },

  withdrawal: {
    icon: '−',
    wrapper: 'bg-orange-50 text-[#f07c1a]',
  },

  loan: {
    icon: '↗',
    wrapper: 'bg-blue-50 text-[#1a6fbd]',
  },

  installment: {
    icon: '✓',
    wrapper: 'bg-teal-50 text-[#17a087]',
  },
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat(
    'id-ID',
    {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
    }
  ).format(value)
}

const formatDate = (value) => {
  return new Intl.DateTimeFormat(
    'id-ID',
    {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    }
  ).format(
    new Date(value)
  )
}
</script>

<template>
  <div>
    <div
      v-if="transactions.length === 0"
      class="py-12 text-center"
    >
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-2xl">
        📋
      </div>

      <p class="mt-4 text-sm font-bold text-slate-600">
        Belum ada transaksi
      </p>

      <p class="mt-1 text-xs text-slate-400">
        Aktivitas terbaru koperasi akan muncul di sini.
      </p>
    </div>

    <div
      v-else
      class="divide-y divide-blue-50"
    >
      <article
        v-for="transaction in transactions"
        :key="transaction.id"
        class="flex items-center gap-3 py-3 transition duration-200 hover:translate-x-1"
      >
        <div
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-base font-black"
          :class="
            variants[
              transaction.type
            ].wrapper
          "
        >
          {{
            variants[
              transaction.type
            ].icon
          }}
        </div>

        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-bold text-slate-700">
            {{ transaction.title }}
          </p>

          <p class="mt-0.5 truncate text-xs text-slate-400">
            {{
              transaction.member_name
            }}
            ·
            {{
              transaction.member_number
            }}
          </p>
        </div>

        <div class="shrink-0 text-right">
          <p class="text-xs font-black text-slate-700">
            {{
              formatCurrency(
                transaction.amount
              )
            }}
          </p>

          <p class="mt-1 text-[11px] text-slate-400">
            {{
              formatDate(
                transaction.date
              )
            }}
          </p>
        </div>
      </article>
    </div>
  </div>
</template>