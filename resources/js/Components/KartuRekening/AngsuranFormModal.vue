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

  loans: {
    type: Array,
    default: () => [],
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
      Input Angsuran
    </h3>

    <p class="mt-1 text-sm text-slate-500">
      Jasa dan sisa pinjaman dihitung otomatis oleh sistem.
    </p>

    <form
      class="mt-6 space-y-4"
      @submit.prevent="$emit('submit')"
    >
      <div>
        <label class="text-sm font-bold text-slate-600">
          Pinjaman Aktif
        </label>

        <select
          v-model="form.pinjaman_id"
          class="mt-2 w-full rounded-xl border border-blue-100 bg-[#f8fbff] px-4 py-3 text-sm outline-none focus:border-[#1a6fbd] focus:bg-white focus:ring-4 focus:ring-blue-100"
        >
          <option
            value=""
            disabled
          >
            Pilih pinjaman
          </option>

          <option
            v-for="loan in loans"
            :key="loan.id"
            :value="loan.id"
          >
            {{
              loan.jenis_pinjaman
                .charAt(0)
                .toUpperCase()
              + loan.jenis_pinjaman.slice(1)
            }}
            —
            Sisa Rp{{ Number(loan.sisa_pinjaman).toLocaleString('id-ID') }}
          </option>
        </select>
      </div>

      <FormInput
        v-model="form.periode"
        label="Periode"
        type="date"
        :error="form.errors.periode"
        required
      />

      <FormInput
        v-model="form.tanggal_pembayaran"
        label="Tanggal Pembayaran"
        type="date"
        :error="form.errors.tanggal_pembayaran"
        required
      />

      <FormInput
        v-model="form.nominal_angsuran"
        label="Nominal Angsuran Pokok"
        type="number"
        :error="form.errors.nominal_angsuran"
        required
      />

      <div class="rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-xs leading-5 text-orange-700">
        Untuk pembayaran jasa pinjaman sebrak tanpa pengurangan pokok,
        masukkan nominal angsuran sebesar 0.
      </div>

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
          :disabled="
            form.processing
            || !form.pinjaman_id
          "
          class="rounded-xl bg-gradient-to-r from-[#f07c1a] to-[#f9a54a] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-orange-200 disabled:opacity-60"
        >
          Simpan Angsuran
        </button>
      </div>
    </form>
  </BaseModal>
</template>