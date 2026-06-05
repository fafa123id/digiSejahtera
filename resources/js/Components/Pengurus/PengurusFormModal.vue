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
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value),
  },
})

defineEmits(['close', 'submit'])
</script>

<template>
  <BaseModal
    :show="show"
    @close="$emit('close')"
  >
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-xs font-black uppercase tracking-[0.18em] text-[#3aab2e]">
          Data Pengurus
        </p>

        <h3 class="mt-1 text-xl font-black text-slate-800">
          {{
            mode === 'create'
              ? 'Tambah Pengurus'
              : 'Ubah Pengurus'
          }}
        </h3>
      </div>

      <button
        type="button"
        class="rounded-full px-2 py-1 text-xl text-slate-400 transition hover:bg-slate-50 hover:text-slate-600"
        @click="$emit('close')"
      >
        ×
      </button>
    </div>

    <form @submit.prevent="$emit('submit')">
      <div class="mt-6 space-y-4">
        <FormInput
          v-model="form.name"
          label="Nama Pengurus"
          placeholder="Masukkan nama pengurus"
          :error="form.errors.name"
          required
        />

        <FormInput
          v-model="form.username"
          label="Username"
          placeholder="Contoh: sulastri"
          :error="form.errors.username"
          required
        />

        <div
          v-if="mode === 'create'"
          class="rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-xs leading-5 text-orange-700"
        >
          Sistem akan menghasilkan password sementara secara otomatis.
          Password hanya ditampilkan satu kali setelah akun berhasil dibuat.
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button
          type="button"
          class="rounded-xl px-4 py-2.5 text-sm font-bold text-slate-400 transition hover:bg-slate-50 hover:text-slate-600"
          @click="$emit('close')"
        >
          Batal
        </button>

        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-200 transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{
            form.processing
              ? 'Menyimpan...'
              : mode === 'create'
                ? 'Simpan'
                : 'Simpan Perubahan'
          }}
        </button>
      </div>
    </form>
  </BaseModal>
</template>