<script setup>
import FormInput from '@/Components/UI/FormInput.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  mustVerifyEmail: {
    type: Boolean,
    default: false,
  },

  status: {
    type: String,
    default: '',
  },
})

const emit = defineEmits([
  'saved',
])

const page = usePage()

const user = computed(() => {
  return page.props.auth.user
})

const form = useForm({
  name: user.value.name,
  username: user.value.username,
  email: user.value.email,
})

const submit = () => {
  form.patch(
    route('profile.update'),
    {
      preserveScroll: true,

      onSuccess: () => {
        emit('saved')
      },
    }
  )
}
</script>

<template>
  <section>
    <header class="flex items-start gap-4">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-[#1a6fbd]">
        <svg
          class="h-6 w-6"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <circle cx="12" cy="8" r="4" />
          <path d="M4 21a8 8 0 0 1 16 0" />
        </svg>
      </div>

      <div>
        <h2 class="text-lg font-black text-slate-800">
          Informasi Profil
        </h2>

        <p class="mt-1 text-sm leading-6 text-slate-500">
          Ubah nama, username, dan email akun yang digunakan pada sistem.
        </p>
      </div>
    </header>

    <form
      class="mt-7 space-y-5"
      @submit.prevent="submit"
    >
      <FormInput
        v-model="form.name"
        label="Nama"
        placeholder="Masukkan nama"
        autocomplete="name"
        :error="form.errors.name"
        required
      />

      <FormInput
        v-model="form.username"
        label="Username"
        placeholder="Masukkan username"
        autocomplete="username"
        :error="form.errors.username"
        required
      />

      <FormInput
        v-model="form.email"
        label="Email"
        type="email"
        placeholder="Masukkan alamat email"
        autocomplete="email"
        :error="form.errors.email"
        required
      />

      <div
        v-if="
          mustVerifyEmail
          && user.email_verified_at === null
        "
        class="rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-xs leading-5 text-orange-700"
      >
        <p>
          Alamat email belum diverifikasi.
        </p>

        <Link
          :href="route('verification.send')"
          method="post"
          as="button"
          class="mt-2 font-bold text-[#1a6fbd] underline underline-offset-2"
        >
          Kirim ulang tautan verifikasi
        </Link>

        <p
          v-if="status === 'verification-link-sent'"
          class="mt-2 font-bold text-[#3aab2e]"
        >
          Tautan verifikasi baru telah dikirim ke alamat email Anda.
        </p>
      </div>

      <div class="rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-3 text-xs leading-5 text-slate-500">
        Username digunakan untuk login. Email disimpan sebagai informasi akun
        dan dapat digunakan untuk kebutuhan verifikasi atau pemulihan akun.
      </div>

      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-xl bg-gradient-to-r from-[#1a6fbd] to-[#0f4f8e] px-5 py-3 text-sm font-bold text-white shadow-md shadow-blue-200 transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{
            form.processing
              ? 'Menyimpan...'
              : 'Simpan Perubahan'
          }}
        </button>
      </div>
    </form>
  </section>
</template>