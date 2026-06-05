<script setup>
import BaseModal from '@/Components/UI/BaseModal.vue'

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Konfirmasi',
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin ingin melanjutkan?',
  },
  confirmText: {
    type: String,
    default: 'Lanjutkan',
  },
  cancelText: {
    type: String,
    default: 'Batal',
  },
  processing: {
    type: Boolean,
    default: false,
  },
  variant: {
    type: String,
    default: 'danger',
    validator: (value) => ['danger', 'warning', 'primary'].includes(value),
  },
})

defineEmits(['close', 'confirm'])

const buttonStyles = {
  danger: 'bg-red-500 shadow-red-100 hover:bg-red-600',
  warning: 'bg-[#f07c1a] shadow-orange-100 hover:bg-orange-600',
  primary: 'bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] shadow-blue-100',
}
</script>

<template>
  <BaseModal
    :show="show"
    @close="$emit('close')"
  >
    <div
      class="flex h-14 w-14 items-center justify-center rounded-full text-2xl font-black"
      :class="
        variant === 'danger'
          ? 'bg-red-50 text-red-500'
          : variant === 'warning'
            ? 'bg-orange-50 text-[#f07c1a]'
            : 'bg-blue-50 text-[#1a6fbd]'
      "
    >
      !
    </div>

    <h3 class="mt-5 text-xl font-black text-slate-800">
      {{ title }}
    </h3>

    <p class="mt-2 text-sm leading-6 text-slate-500">
      {{ message }}
    </p>

    <div class="mt-6 flex justify-end gap-2">
      <button
        type="button"
        class="rounded-xl px-4 py-2.5 text-sm font-bold text-slate-400 transition hover:bg-slate-50 hover:text-slate-600"
        @click="$emit('close')"
      >
        {{ cancelText }}
      </button>

      <button
        type="button"
        :disabled="processing"
        class="rounded-xl px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60"
        :class="buttonStyles[variant]"
        @click="$emit('confirm')"
      >
        {{ processing ? 'Memproses...' : confirmText }}
      </button>
    </div>
  </BaseModal>
</template>