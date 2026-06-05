<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import ToastAlert from '@/Components/UI/ToastAlert.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import { Head } from '@inertiajs/vue3'
import { ref } from 'vue'

defineProps({
  mustVerifyEmail: {
    type: Boolean,
    default: false,
  },

  status: {
    type: String,
    default: '',
  },
})

const toast = ref(null)

let toastCounter = 0

const showToast = (
  message,
  type = 'success'
) => {
  toastCounter += 1

  toast.value = {
    id: toastCounter,
    message,
    type,
  }
}

const closeToast = () => {
  toast.value = null
}
</script>

<template>
  <Head title="Profil Saya — DigiSejahtera" />

  <AuthenticatedLayout>
    <template #title>
      Profil Saya
    </template>

    <ToastAlert
      v-if="toast"
      :key="toast.id"
      :message="toast.message"
      :type="toast.type"
      @close="closeToast"
    />

    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0f4f8e] via-[#1a6fbd] to-[#3aab2e] px-6 py-7 text-white shadow-xl shadow-blue-200/70 sm:px-8">
      <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-white/10" />
      <div class="absolute -bottom-20 right-28 h-44 w-44 rounded-full bg-white/5" />

      <div class="relative">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-100">
          Pengaturan Akun
        </p>

        <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
          Kelola Profil Anda
        </h2>

        <p class="mt-2 max-w-xl text-sm leading-6 text-blue-50/90">
          Perbarui informasi akun dan gunakan password yang aman untuk menjaga
          akses ke sistem DigiSejahtera.
        </p>
      </div>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
      <section class="rounded-3xl border border-blue-100 bg-white p-6 shadow-[0_8px_28px_rgba(26,111,189,0.08)]">
        <UpdateProfileInformationForm
          :must-verify-email="mustVerifyEmail"
          :status="status"
          @saved="showToast('Informasi profil berhasil diperbarui.')"
        />
      </section>

      <section class="rounded-3xl border border-blue-100 bg-white p-6 shadow-[0_8px_28px_rgba(26,111,189,0.08)]">
        <UpdatePasswordForm
          @saved="showToast('Password berhasil diperbarui.')"
        />
      </section>
    </div>
  </AuthenticatedLayout>
</template>