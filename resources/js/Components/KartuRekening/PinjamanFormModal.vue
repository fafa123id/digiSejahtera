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
      Input Pinjaman
    </h3>

    <p class="mt-1 text-sm text-slate-500">
      Jasa reguler 1,5% dan jasa sebrak 2%.
    </p>

    <form
      class="mt-6 space-y-4"
      @submit.prevent="$emit('submit')"
    >
      <FormInput
        v-model="form.tanggal_pinjaman"
        label="Tanggal Pinjaman"
        type="date"
        :error="form.errors.tanggal_pinjaman"
        required
      />

      <div>
        <label class="text-sm font-bold text-slate-600">
          Jenis Pinjaman
        </label>

        <select
          v-model="form.jenis_pinjaman"
          class="mt-2 w-full rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm outline-none focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100"
        >
          <option value="reguler">
            Pinjaman Reguler
          </option>

          <option value="sebrak">
            Pinjaman Sebrak
          </option>
        </select>

        <p
          v-if="form.errors.jenis_pinjaman"
          class="mt-1 text-xs font-semibold text-red-500"
        >
          {{ form.errors.jenis_pinjaman }}
        </p>
      </div>

      <FormInput
        v-model="form.nominal_pinjaman"
        label="Nominal Pinjaman"
        type="number"
        :error="form.errors.nominal_pinjaman"
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
          Simpan Pinjaman
        </button>
      </div>
    </form>
  </BaseModal>
</template>