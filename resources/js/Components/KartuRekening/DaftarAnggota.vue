<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  anggotas: {
    type: Array,
    default: () => [],
  },

  selectedId: {
    type: Number,
    default: null,
  },
})

defineEmits([
  'select',
  'add',
])

const search = ref('')

const filteredAnggotas = computed(() => {
  const keyword =
    search.value
      .toLowerCase()
      .trim()

  if (!keyword) {
    return props.anggotas
  }

  return props.anggotas.filter(
    (anggota) => {
      return (
        anggota.nama
          .toLowerCase()
          .includes(keyword)
        || anggota.nomor_anggota
          .toLowerCase()
          .includes(keyword)
      )
    }
  )
})
</script>

<template>
  <aside class="flex h-full flex-col rounded-3xl border border-blue-100 bg-white shadow-[0_8px_28px_rgba(26,111,189,0.08)]">
    <div class="border-b border-blue-50 p-4">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="text-xs font-black uppercase tracking-[0.14em] text-[#3aab2e]">
            Anggota
          </p>

          <h3 class="mt-1 text-base font-black text-slate-800">
            Daftar Anggota
          </h3>
        </div>

        <button
          type="button"
          class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] text-lg font-black text-white shadow-md shadow-blue-200 transition hover:-translate-y-0.5"
          @click="$emit('add')"
        >
          +
        </button>
      </div>

      <input
        v-model="search"
        type="search"
        placeholder="Cari anggota..."
        class="mt-4 w-full rounded-xl border border-blue-100 bg-[#f8fbff] px-3 py-2.5 text-sm outline-none transition focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100"
      />
    </div>

    <div class="max-h-[680px] flex-1 space-y-1 overflow-y-auto p-3">
      <button
        v-for="anggota in filteredAnggotas"
        :key="anggota.id"
        type="button"
        class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition"
        :class="
          anggota.id === selectedId
            ? 'bg-gradient-to-r from-blue-50 to-green-50 ring-1 ring-blue-100'
            : 'hover:bg-blue-50/70'
        "
        @click="$emit('select', anggota)"
      >
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#1a6fbd] to-[#3aab2e] text-xs font-black text-white">
          {{ anggota.nama.charAt(0).toUpperCase() }}
        </div>

        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-bold text-slate-700">
            {{ anggota.nama }}
          </p>

          <p class="mt-0.5 text-xs text-slate-400">
            No. {{ anggota.nomor_anggota }}
          </p>
        </div>

        <span
          class="h-2 w-2 shrink-0 rounded-full"
          :class="
            anggota.status === 'aktif'
              ? 'bg-green-500'
              : 'bg-slate-300'
          "
        />
      </button>

      <div
        v-if="filteredAnggotas.length === 0"
        class="px-3 py-10 text-center text-xs text-slate-400"
      >
        Data anggota belum tersedia.
      </div>
    </div>
  </aside>
</template>