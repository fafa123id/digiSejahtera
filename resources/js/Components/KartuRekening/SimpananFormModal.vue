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
    max-width="max-w-2xl"
    @close="$emit('close')"
  >
    <h3 class="text-xl font-black text-slate-800">
      Input Simpanan
    </h3>

    <p class="mt-1 text-sm text-slate-500">
      Gunakan nilai negatif untuk mencatat penarikan simpanan.
    </p>

    <form
      class="mt-6"
      @submit.prevent="$emit('submit')"
    >
      <div class="grid gap-4 sm:grid-cols-2">
        <FormInput
          v-model="form.periode"
          label="Periode"
          type="date"
          :error="form.errors.periode"
          required
        />

        <FormInput
          v-model="form.simpanan_pokok"
          label="Simpanan Pokok"
          type="number"
          :error="form.errors.simpanan_pokok"
        />

        <FormInput
          v-model="form.simpanan_wajib"
          label="Simpanan Wajib"
          type="number"
          :error="form.errors.simpanan_wajib"
        />

        <FormInput
          v-model="form.simpanan_sukarela"
          label="Simpanan Suka Rela"
          type="number"
          :error="form.errors.simpanan_sukarela"
        />

        <FormInput
          v-model="form.simpanan_hari_raya"
          label="Simpanan Hari Raya"
          type="number"
          :error="form.errors.simpanan_hari_raya"
        />

        <FormInput
          v-model="form.simpanan_rekreasi"
          label="Simpanan Rekreasi"
          type="number"
          :error="form.errors.simpanan_rekreasi"
        />
      </div>

      <div class="mt-6 flex justify-end gap-2">
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
          class="rounded-xl bg-gradient-to-r from-[#3aab2e] to-[#268c1a] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-green-200 disabled:opacity-60"
        >
          Simpan Simpanan
        </button>
      </div>
    </form>
  </BaseModal>
</template>