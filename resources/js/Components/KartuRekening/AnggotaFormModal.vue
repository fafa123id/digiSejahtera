<script setup>
import BaseModal from '@/Components/UI/BaseModal.vue'
import FormInput from '@/Components/UI/FormInput.vue'

defineProps({
  show: {
    type: Boolean,
    default: false,
  },

  form: {
    type: Object,
    required: true,
  },
})

defineEmits([
  'close',
  'submit',
])
</script>

<template>
  <BaseModal
    :show="show"
    @close="$emit('close')"
  >
    <h3 class="text-xl font-black text-slate-800">
      Tambah Anggota
    </h3>

    <p class="mt-1 text-sm text-slate-500">
      Nomor anggota dibuat otomatis oleh sistem.
    </p>

    <form
      class="mt-6 space-y-4"
      @submit.prevent="$emit('submit')"
    >
      <FormInput
        v-model="form.nama"
        label="Nama Anggota"
        placeholder="Masukkan nama anggota"
        :error="form.errors.nama"
        required
      />

      <div>
        <p class="text-sm font-bold text-slate-700">
          Agama
        </p>

        <div class="mt-2 grid grid-cols-2 gap-3">
          <label
            class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition"
            :class="
              form.agama === 'islam'
                ? 'border-[#1a6fbd] bg-blue-50 text-[#1a6fbd] ring-2 ring-blue-100'
                : 'border-slate-200 bg-white text-slate-500 hover:border-blue-200 hover:bg-blue-50/40'
            "
          >
            <input
              v-model="form.agama"
              type="radio"
              value="islam"
              class="h-4 w-4 border-slate-300 text-[#1a6fbd] focus:ring-[#1a6fbd]"
            >

            <span class="text-sm font-bold">
              Islam
            </span>
          </label>

          <label
            class="flex cursor-pointer items-center gap-3 rounded-xl border px-4 py-3 transition"
            :class="
              form.agama === 'nonislam'
                ? 'border-[#1a6fbd] bg-blue-50 text-[#1a6fbd] ring-2 ring-blue-100'
                : 'border-slate-200 bg-white text-slate-500 hover:border-blue-200 hover:bg-blue-50/40'
            "
          >
            <input
              v-model="form.agama"
              type="radio"
              value="nonislam"
              class="h-4 w-4 border-slate-300 text-[#1a6fbd] focus:ring-[#1a6fbd]"
            >

            <span class="text-sm font-bold">
              Non-Islam
            </span>
          </label>
        </div>

        <p
          v-if="form.errors.agama"
          class="mt-2 text-xs font-semibold text-red-500"
        >
          {{ form.errors.agama }}
        </p>
      </div>

      <FormInput
        v-model="form.tanggal_masuk"
        label="Tanggal Bergabung"
        type="date"
        :error="form.errors.tanggal_masuk"
        required
      />

      <div class="flex justify-end gap-2 pt-2">
        <button
          type="button"
          class="rounded-xl px-4 py-2.5 text-sm font-bold text-slate-400 hover:bg-slate-50"
          @click="$emit('close')"
        >
          Batal
        </button>

        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-200 disabled:opacity-60"
        >
          Simpan
        </button>
      </div>
    </form>
  </BaseModal>
</template>