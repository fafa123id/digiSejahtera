<script setup>
const props = defineProps({
  kitir: {
    type: Object,
    required: true,
  },
})

const formatter = new Intl.NumberFormat(
  'id-ID',
  {
    maximumFractionDigits: 0,
  },
)

const formatNominal = (
  value
) => {
  if (
    value === null
    || value === undefined
  ) {
    return '-'
  }

  return formatter.format(
    Number(
      value
    )
  )
}

const formatAngsuranLabel = (
  label,
  value
) => {
  if (
    value === null
    || value === undefined
  ) {
    return label
  }

  return `${label} ${value}`
}
</script>

<template>
  <article
    class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-[0_8px_22px_rgba(15,79,142,0.08)]"
  >
    <header
      class="border-b-2 border-slate-800 px-4 py-3 text-center"
    >
      <p
        class="text-sm font-black tracking-wide text-slate-900"
      >
        KOPERASI SEJAHTERA
      </p>
    </header>

    <div class="px-4 py-3">
      <div
        class="grid grid-cols-[112px_12px_minmax(0,1fr)_auto] items-center gap-1 border-b border-slate-200 pb-2 text-xs"
      >
        <span class="font-semibold text-slate-700">
          Nama/ No Angt.
        </span>

        <span>:</span>

        <span
          class="truncate font-black uppercase text-slate-900"
        >
          {{ kitir.nama }}
        </span>

        <span
          class="font-black text-slate-900"
        >
          {{ kitir.nomor_anggota }}
        </span>
      </div>

      <p
        class="mt-3 border-b border-slate-200 pb-2 text-xs font-bold text-slate-700"
      >
        Rincian Potongan
      </p>

      <dl class="divide-y divide-slate-100 text-xs">
        <div
          class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 py-1.5"
        >
          <dt>1. Simp.Wajib</dt>
          <dd>:</dd>
          <dd class="text-right font-semibold tabular-nums">
            {{ formatNominal(kitir.simpanan_wajib) }}
          </dd>
        </div>

        <div
          class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 py-1.5"
        >
          <dt>2. Simp. Suka Rela</dt>
          <dd>:</dd>
          <dd class="text-right font-semibold tabular-nums">
            {{ formatNominal(kitir.simpanan_sukarela) }}
          </dd>
        </div>

        <div
          class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 py-1.5"
        >
          <dt>3. Simp. Hari Raya</dt>
          <dd>:</dd>
          <dd class="text-right font-semibold tabular-nums">
            {{ formatNominal(kitir.simpanan_hari_raya) }}
          </dd>
        </div>

        <div
          class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 py-1.5"
        >
          <dt>
            {{
              formatAngsuranLabel(
                '4. Angsuran ke',
                kitir.reguler.angsuran_ke,
              )
            }}
          </dt>

          <dd>:</dd>

          <dd class="text-right font-semibold tabular-nums">
            {{
              formatNominal(
                kitir.reguler.nominal_angsuran,
              )
            }}
          </dd>
        </div>

        <div
          class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 py-1.5"
        >
          <dt>5. Jasa 1,5%</dt>
          <dd>:</dd>
          <dd class="text-right font-semibold tabular-nums">
            {{
              formatNominal(
                kitir.reguler.jasa_pinjaman,
              )
            }}
          </dd>
        </div>

        <div
          class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 py-1.5"
        >
          <dt>
            {{
              formatAngsuranLabel(
                '6. Angs.sebrak ke',
                kitir.sebrak.angsuran_ke,
              )
            }}
          </dt>

          <dd>:</dd>

          <dd class="text-right font-semibold tabular-nums">
            {{
              formatNominal(
                kitir.sebrak.nominal_angsuran,
              )
            }}
          </dd>
        </div>

        <div
          class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 py-1.5"
        >
          <dt>7. Jasa sebrak 2%</dt>
          <dd>:</dd>
          <dd class="text-right font-semibold tabular-nums">
            {{
              formatNominal(
                kitir.sebrak.jasa_pinjaman,
              )
            }}
          </dd>
        </div>

        <div
          class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 py-1.5"
        >
          <dt>8. Simp.Rekreasi</dt>
          <dd>:</dd>
          <dd class="text-right font-semibold tabular-nums">
            {{ formatNominal(kitir.simpanan_rekreasi) }}
          </dd>
        </div>
      </dl>

      <div
        class="mt-1 grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 border-t-2 border-slate-800 py-2 text-xs"
      >
        <span class="font-black">Jumlah</span>
        <span>:</span>
        <span
          class="text-right font-black tabular-nums text-slate-900"
        >
          {{ formatNominal(kitir.jumlah) }}
        </span>
      </div>

      <div
        class="grid grid-cols-[minmax(0,1fr)_12px_96px] items-center gap-1 border-t border-slate-200 py-2 text-xs"
      >
        <span class="font-bold">Sisa Pinjaman</span>
        <span>:</span>
        <span
          class="text-right font-bold tabular-nums text-slate-900"
        >
          {{ formatNominal(kitir.sisa_pinjaman) }}
        </span>
      </div>
    </div>
  </article>
</template>