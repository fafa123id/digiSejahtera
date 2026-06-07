<script setup>
import EditableMoneyCell from '@/Components/KartuRekening/EditableMoneyCell.vue'
import {
  computed,
  onMounted,
  onUnmounted,
  ref,
} from 'vue'

const props = defineProps({
  loan: {
    type: Object,
    required: true,
  },

  anggotaId: {
    type: Number,
    required: true,
  },

  periode: {
    type: String,
    required: true,
  },

  section: {
    type: String,
    required: true,
  },

  dirtyKeys: {
    type: Object,
    default: () => ({}),
  },

  printMode: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits([
  'change',
  'discard',
])

const INPUT_MENU_EVENT =
  'digisejahtera:loan-entry-menu-opened'


const menuId = [
  props.anggotaId,
  props.periode,
  props.section,
  Math.random()
    .toString(36)
    .slice(2),
].join('-')

const menuOpen =
  ref(false)

const makeClientKey = () => {
  if (
    typeof crypto !== 'undefined'
    && typeof crypto.randomUUID
      === 'function'
  ) {
    return crypto.randomUUID()
  }

  return [
    Date.now(),
    Math.random()
      .toString(36)
      .slice(2),
  ].join('-')
}

const makeDirtyKey = (
  entry
) => {
  return [
    props.anggotaId,
    props.periode,
    props.section,
    'jumlah',
    entry.client_key,
  ].join('|')
}

const isDirty = (
  entry
) => {
  return Boolean(
    props.dirtyKeys[
      makeDirtyKey(
        entry
      )
    ]
  )
}

const canAddAngsuran =
  computed(() => {
    return (
      props.loan
        .can_add_angsuran
      && !props.loan
        .entries
        .some(
          (entry) =>
            entry.entry_type
            === 'angsuran'
        )
    )
  })


const handleOtherMenuOpened = (
  event
) => {
  if (
    event.detail.menuId
    !== menuId
  ) {
    menuOpen.value =
      false
  }
}

const toggleMenu = () => {
  if (menuOpen.value) {
    menuOpen.value =
      false

    return
  }

  menuOpen.value =
    true

  window.dispatchEvent(
    new CustomEvent(
      INPUT_MENU_EVENT,
      {
        detail: {
          menuId,
        },
      }
    )
  )
}

const closeMenu = () => {
  menuOpen.value =
    false
}

const addEntry = (
  entryType
) => {
  if (
    entryType === 'angsuran'
    && !canAddAngsuran.value
  ) {
    return
  }

  const clientKey =
    makeClientKey()

  props.loan.entries.push({
    client_key:
      clientKey,

    entry_id:
      null,

    entry_type:
      entryType,

    loan_label:
      entryType
      === 'angsuran'
        ? 'angsuran'
        : (
            props.loan.entries
              .some(
                (entry) =>
                  entry.entry_type
                  === 'pinjaman'
              )
            || props.loan
              .has_angsuran
              ? 'pinjaman_tambahan'
              : 'pinjaman'
          ),

    action:
      entryType
      === 'angsuran'
        ? 'create_angsuran'
        : 'create_pinjaman',

    jumlah:
      '',
  })

  closeMenu()
}

const updateEntry = (
  entry,
  value
) => {
  entry.jumlah =
    value

  emit(
    'change',
    {
      anggota_id:
        props.anggotaId,

      periode:
        props.periode,

      section:
        props.section,

      field:
        'jumlah',

      value,

      action:
        entry.action,

      entry_id:
        entry.entry_id,

      client_key:
        entry.client_key,
    }
  )
}

const discardEntry = (
  entry
) => {

  if (entry.entry_id) {
    return
  }

  const index =
    props.loan
      .entries
      .findIndex(
        (item) =>
          item.client_key
          === entry.client_key
      )

  if (index >= 0) {
    props.loan
      .entries
      .splice(
        index,
        1
      )
  }

  emit(
    'discard',
    entry.client_key
  )
}

const formatNumber = (
  value
) => {
  if (
    value === null
    || value === undefined
    || value === ''
  ) {
    return '-'
  }

  return new Intl.NumberFormat(
    'id-ID',
    {
      maximumFractionDigits: 0,
    }
  ).format(
    Number(value)
  )
}

const labelText = (
  entry
) => {
  return {
    angsuran:
      'Angsuran',

    pinjaman:
      'Pinjaman',

    pinjaman_tambahan:
      'Pinjaman Tambahan',
  }[
    entry.loan_label
  ] ?? 'Transaksi'
}

const labelClass = (
  entry
) => {
  return {
    angsuran:
      'text-[#3aab2e]',

    pinjaman:
      'text-[#1a6fbd]',

    pinjaman_tambahan:
      'text-[#f07c1a]',
  }[
    entry.loan_label
  ] ?? 'text-slate-400'
}


onMounted(() => {
  window.addEventListener(
    INPUT_MENU_EVENT,
    handleOtherMenuOpened
  )
})

onUnmounted(() => {
  window.removeEventListener(
    INPUT_MENU_EVENT,
    handleOtherMenuOpened
  )
})
</script>

<template>
  <div class="min-w-[132px] space-y-2">
    <!-- Entri pinjaman atau angsuran yang sudah tersedia -->
    <div
      v-for="entry in loan.entries"
      :key="entry.client_key"
      class="rounded-lg border border-blue-50 bg-white/70 p-1"
    >
      <template v-if="printMode">
        <p class="px-1 text-right text-xs font-semibold tabular-nums text-slate-700">
          {{ formatNumber(entry.jumlah) }}
        </p>
      </template>

      <template v-else>
        <div class="flex items-center gap-1">
          <EditableMoneyCell
            :model-value="entry.jumlah"
            :dirty="isDirty(entry)"
            :show-zero="
              entry.entry_type
              === 'angsuran'
            "
            @change="
              updateEntry(
                entry,
                $event
              )
            "
          />

          <button
            v-if="!entry.entry_id"
            type="button"
            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-sm font-black text-red-400 transition hover:bg-red-50 hover:text-red-600"
            title="Batalkan input baru"
            @click="
              discardEntry(
                entry
              )
            "
          >
            ×
          </button>
        </div>
      </template>

      <p
        class="mt-0.5 px-1 text-[9px] font-black uppercase tracking-wide"
        :class="
          labelClass(
            entry
          )
        "
      >
        {{
          labelText(
            entry
          )
        }}
      </p>
    </div>

    <!-- Menu tambah transaksi -->
    <div
      v-if="!printMode"
      class="relative"
    >
      <button
        type="button"
        class="w-full rounded-lg border border-dashed px-2 py-1.5 text-[10px] font-black uppercase tracking-wide transition"
        :class="
          menuOpen
            ? 'border-red-200 bg-red-50 text-red-500 hover:bg-red-100'
            : 'border-blue-200 bg-blue-50/50 text-[#1a6fbd] hover:border-blue-300 hover:bg-blue-100'
        "
        @click="toggleMenu"
      >
        {{
          menuOpen
            ? '× Tutup'
            : '+ Input'
        }}
      </button>

      <div
        v-if="menuOpen"
        class="absolute right-0 top-full z-20 mt-1 w-48 overflow-hidden rounded-xl border border-blue-100 bg-white p-1 shadow-xl shadow-blue-100/80"
      >
        <button
          type="button"
          class="w-full rounded-lg px-3 py-2 text-left text-xs font-bold text-[#1a6fbd] transition hover:bg-blue-50"
          @click="
            addEntry(
              'pinjaman'
            )
          "
        >
          + Tambah Pinjaman
        </button>

        <button
          type="button"
          :disabled="
            !canAddAngsuran
          "
          class="mt-1 w-full rounded-lg px-3 py-2 text-left text-xs font-bold transition disabled:cursor-not-allowed disabled:text-slate-300"
          :class="
            canAddAngsuran
              ? 'text-[#3aab2e] hover:bg-green-50'
              : ''
          "
          @click="
            addEntry(
              'angsuran'
            )
          "
        >
          + Tambah Angsuran
        </button>

        <p
          v-if="
            !canAddAngsuran
          "
          class="px-3 py-1.5 text-[10px] leading-4 text-slate-400"
        >
          Angsuran hanya dapat dicatat jika terdapat saldo dari bulan
          sebelumnya.
        </p>

      </div>
    </div>
  </div>
</template>