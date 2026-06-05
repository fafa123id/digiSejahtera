<script setup>
import BaseModal from '@/Components/UI/BaseModal.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  credential: {
    type: Object,
    default: null,
  },
})

defineEmits(['close'])

const copied = ref(false)

const credentialText = computed(() => {
  if (!props.credential) {
    return ''
  }

  return [
    `Nama: ${props.credential.name}`,
    `Username: ${props.credential.username}`,
    `Password sementara: ${props.credential.password}`,
  ].join('\n')
})

watch(
  () => props.show,
  () => {
    copied.value = false
  }
)

const copyCredential = async () => {
  try {
    await navigator.clipboard.writeText(credentialText.value)
    copied.value = true
  } catch {
    copied.value = false
  }
}
</script>

<template>
  <BaseModal
    :show="show"
    :closeable="false"
    @close="$emit('close')"
  >
    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-50 text-2xl font-black text-[#3aab2e]">
      ✓
    </div>

    <h3 class="mt-5 text-xl font-black text-slate-800">
      Password Sementara
    </h3>

    <p class="mt-2 text-sm leading-6 text-slate-500">
      Salin password berikut dan berikan kepada pengurus.
      Password tidak dapat ditampilkan kembali setelah popup ditutup.
    </p>

    <div class="mt-5 space-y-3 rounded-2xl border border-blue-100 bg-[#f8fbff] p-4">
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
          Nama
        </p>

        <p class="mt-1 text-sm font-bold text-slate-700">
          {{ credential?.name }}
        </p>
      </div>

      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
          Username
        </p>

        <p class="mt-1 text-sm font-bold text-[#1a6fbd]">
          {{ credential?.username }}
        </p>
      </div>

      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
          Password
        </p>

        <p class="mt-1 break-all font-mono text-lg font-black tracking-wide text-[#f07c1a]">
          {{ credential?.password }}
        </p>
      </div>
    </div>

    <p
      v-if="copied"
      class="mt-3 text-xs font-bold text-[#3aab2e]"
    >
      Username dan password berhasil disalin.
    </p>

    <div class="mt-6 flex justify-end gap-2">
      <button
        type="button"
        class="rounded-xl border border-blue-100 px-4 py-2.5 text-sm font-bold text-[#1a6fbd] transition hover:bg-blue-50"
        @click="copyCredential"
      >
        {{ copied ? 'Tersalin ✓' : 'Salin' }}
      </button>

      <button
        type="button"
        class="rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-blue-200 transition hover:-translate-y-0.5"
        @click="$emit('close')"
      >
        Selesai
      </button>
    </div>
  </BaseModal>
</template>