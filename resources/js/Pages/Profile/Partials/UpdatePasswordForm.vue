<script setup>
import FormInput from '@/Components/UI/FormInput.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const emit = defineEmits([
  'saved',
])

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const currentPasswordInput = ref(null)
const passwordInput = ref(null)

const submit = () => {
  form.put(
    route('password.update'),
    {
      preserveScroll: true,

      onSuccess: () => {
        form.reset()
        emit('saved')
      },

      onError: () => {
        if (form.errors.password) {
          form.reset(
            'password',
            'password_confirmation'
          )
        }

        if (form.errors.current_password) {
          form.reset('current_password')
        }
      },
    }
  )
}
</script>

<template>
  <section>
    <header class="flex items-start gap-4">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-green-50 text-[#3aab2e]">
        <svg
          class="h-6 w-6"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <rect x="3" y="11" width="18" height="10" rx="2" />
          <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
      </div>

      <div>
        <h2 class="text-lg font-black text-slate-800">
          Ubah Password
        </h2>

        <p class="mt-1 text-sm leading-6 text-slate-500">
          Gunakan password baru yang aman dan tidak mudah ditebak.
        </p>
      </div>
    </header>

    <form
      class="mt-7 space-y-5"
      @submit.prevent="submit"
    >
      <FormInput
        v-model="form.current_password"
        label="Password Saat Ini"
        type="password"
        placeholder="Masukkan password saat ini"
        autocomplete="current-password"
        :error="form.errors.current_password"
        required
      />

      <FormInput
        v-model="form.password"
        label="Password Baru"
        type="password"
        placeholder="Masukkan password baru"
        autocomplete="new-password"
        :error="form.errors.password"
        required
      />

      <FormInput
        v-model="form.password_confirmation"
        label="Konfirmasi Password Baru"
        type="password"
        placeholder="Ulangi password baru"
        autocomplete="new-password"
        :error="form.errors.password_confirmation"
        required
      />

      <div class="rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-xs leading-5 text-orange-700">
        Gunakan minimal 8 karakter. Kombinasikan huruf, angka, dan simbol agar
        password lebih aman.
      </div>

      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-xl bg-gradient-to-r from-[#3aab2e] to-[#268c1a] px-5 py-3 text-sm font-bold text-white shadow-md shadow-green-200 transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{
            form.processing
              ? 'Menyimpan...'
              : 'Ubah Password'
          }}
        </button>
      </div>
    </form>
  </section>
</template>