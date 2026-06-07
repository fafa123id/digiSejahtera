<script setup>
defineProps({
  rows: {
    type: Array,
    default: () => [],
  },

  totals: {
    type: Object,
    default: () => ({}),
  },
})

const formatNumber = (value) => {
  const number =
    Number(value ?? 0)

  if (number === 0) {
    return '-'
  }

  return new Intl.NumberFormat(
    'id-ID',
    {
      maximumFractionDigits: 0,
    }
  ).format(number)
}

const nominalClass = (value) => {
  return Number(value ?? 0) < 0
    ? 'text-red-600'
    : 'text-slate-700'
}
</script>

<template>
  <div class="overflow-x-auto rounded-2xl border border-blue-100">
    <table class="min-w-[1450px] w-full border-collapse text-xs">
      <thead>
        <tr class="bg-gradient-to-r from-[#0f4f8e] via-[#1a6fbd] to-[#3aab2e] text-white">
          <th
            rowspan="2"
            class="border border-white/20 px-3 py-3 text-left"
          >
            Bulan
          </th>

          <th
            colspan="6"
            class="border border-white/20 px-3 py-3 text-center"
          >
            Simpanan
          </th>

          <th
            colspan="4"
            class="border border-white/20 px-3 py-3 text-center"
          >
            Pinjaman Reguler
          </th>

          <th
            colspan="4"
            class="border border-white/20 px-3 py-3 text-center"
          >
            Pinjaman Sebrak
          </th>

          <th
            rowspan="2"
            class="border border-white/20 px-3 py-3 text-right"
          >
            Jumlah Tagihan
          </th>
        </tr>

        <tr class="bg-[#1a6fbd] text-white">
          <th class="border border-white/20 px-3 py-2">SIMPOK</th>
          <th class="border border-white/20 px-3 py-2">SIMWA</th>
          <th class="border border-white/20 px-3 py-2">SSR</th>
          <th class="border border-white/20 px-3 py-2">SHR</th>
          <th class="border border-white/20 px-3 py-2">SREK</th>
          <th class="border border-white/20 px-3 py-2">Jumlah</th>

          <th class="border border-white/20 px-3 py-2">Ke</th>
          <th class="border border-white/20 px-3 py-2">Jumlah</th>
          <th class="border border-white/20 px-3 py-2">Sisa</th>
          <th class="border border-white/20 px-3 py-2">Jasa</th>

          <th class="border border-white/20 px-3 py-2">Ke</th>
          <th class="border border-white/20 px-3 py-2">Jumlah</th>
          <th class="border border-white/20 px-3 py-2">Sisa</th>
          <th class="border border-white/20 px-3 py-2">Jasa</th>
        </tr>
      </thead>

      <tbody>
        <tr
          v-for="row in rows"
          :key="row.periode"
          class="transition hover:bg-blue-50/70"
        >
          <td class="border border-blue-100 bg-blue-50/40 px-3 py-2 font-black text-[#1a6fbd]">
            {{ row.bulan }}
          </td>

          <td
            class="border border-blue-100 px-3 py-2 text-right"
            :class="nominalClass(row.simpanan.pokok)"
          >
            {{ formatNumber(row.simpanan.pokok) }}
          </td>

          <td
            class="border border-blue-100 px-3 py-2 text-right"
            :class="nominalClass(row.simpanan.wajib)"
          >
            {{ formatNumber(row.simpanan.wajib) }}
          </td>

          <td
            class="border border-blue-100 px-3 py-2 text-right"
            :class="nominalClass(row.simpanan.sukarela)"
          >
            {{ formatNumber(row.simpanan.sukarela) }}
          </td>

          <td
            class="border border-blue-100 px-3 py-2 text-right"
            :class="nominalClass(row.simpanan.hari_raya)"
          >
            {{ formatNumber(row.simpanan.hari_raya) }}
          </td>

          <td
            class="border border-blue-100 px-3 py-2 text-right"
            :class="nominalClass(row.simpanan.rekreasi)"
          >
            {{ formatNumber(row.simpanan.rekreasi) }}
          </td>

          <td
            class="border border-blue-100 bg-green-50/40 px-3 py-2 text-right font-bold"
            :class="nominalClass(row.simpanan.jumlah)"
          >
            {{ formatNumber(row.simpanan.jumlah) }}
          </td>

          <td class="border border-blue-100 px-3 py-2 text-center">
            {{ row.reguler?.ke ?? '-' }}
          </td>

          <td class="border border-blue-100 px-3 py-2 text-right">
            {{ formatNumber(row.reguler?.jumlah) }}
          </td>

          <td class="border border-blue-100 px-3 py-2 text-right">
            {{ formatNumber(row.reguler?.sisa) }}
          </td>

          <td class="border border-blue-100 px-3 py-2 text-right">
            {{ formatNumber(row.reguler?.jasa) }}
          </td>

          <td class="border border-blue-100 px-3 py-2 text-center">
            {{ row.sebrak?.ke ?? '-' }}
          </td>

          <td class="border border-blue-100 px-3 py-2 text-right">
            {{ formatNumber(row.sebrak?.jumlah) }}
          </td>

          <td class="border border-blue-100 px-3 py-2 text-right">
            {{ formatNumber(row.sebrak?.sisa) }}
          </td>

          <td class="border border-blue-100 px-3 py-2 text-right">
            {{ formatNumber(row.sebrak?.jasa) }}
          </td>

          <td
            class="border border-blue-100 bg-orange-50/50 px-3 py-2 text-right font-black"
            :class="nominalClass(row.jumlah_tagihan)"
          >
            {{ formatNumber(row.jumlah_tagihan) }}
          </td>
        </tr>
      </tbody>

      <tfoot>
        <tr class="bg-slate-50 font-black text-slate-700">
          <td class="border border-blue-100 px-3 py-3">
            JUMLAH
          </td>

          <td class="border border-blue-100 px-3 py-3 text-right">
            {{ formatNumber(totals.simpanan_pokok) }}
          </td>

          <td class="border border-blue-100 px-3 py-3 text-right">
            {{ formatNumber(totals.simpanan_wajib) }}
          </td>

          <td class="border border-blue-100 px-3 py-3 text-right">
            {{ formatNumber(totals.simpanan_sukarela) }}
          </td>

          <td class="border border-blue-100 px-3 py-3 text-right">
            {{ formatNumber(totals.simpanan_hari_raya) }}
          </td>

          <td class="border border-blue-100 px-3 py-3 text-right">
            {{ formatNumber(totals.simpanan_rekreasi) }}
          </td>

          <td class="border border-blue-100 bg-green-100/60 px-3 py-3 text-right">
            {{ formatNumber(totals.total_simpanan) }}
          </td>

          <td
            colspan="3"
            class="border border-blue-100 px-3 py-3 text-center"
          >
            Jumlah Jasa Reguler
          </td>

          <td class="border border-blue-100 px-3 py-3 text-right">
            {{ formatNumber(totals.jasa_reguler) }}
          </td>

          <td
            colspan="3"
            class="border border-blue-100 px-3 py-3 text-center"
          >
            Jumlah Jasa Sebrak
          </td>

          <td class="border border-blue-100 px-3 py-3 text-right">
            {{ formatNumber(totals.jasa_sebrak) }}
          </td>

          <td class="border border-blue-100" />
        </tr>
      </tfoot>
    </table>
  </div>
</template>