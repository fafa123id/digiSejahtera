<script setup>
import {
  computed,
  onBeforeUnmount,
  ref,
  watch,
} from 'vue'

const props = defineProps({
  message: {
    type: String,
    default: '',
  },

  type: {
    type: String,
    default: 'success',

    validator: (value) => {
      return [
        'success',
        'error',
        'warning',
        'info',
      ].includes(value)
    },
  },

  duration: {
    type: Number,
    default: 3500,
  },
})

const emit = defineEmits([
  'close',
])

const visible = ref(false)

let timeoutId = null

const variants = {
  success: {
    wrapper: 'border-green-200 bg-white',
    iconWrapper: 'bg-green-100 text-green-600',
    icon: '✓',
    title: 'Berhasil',
    progress: 'bg-green-500',
  },

  error: {
    wrapper: 'border-red-200 bg-white',
    iconWrapper: 'bg-red-100 text-red-600',
    icon: '!',
    title: 'Terjadi Kesalahan',
    progress: 'bg-red-500',
  },

  warning: {
    wrapper: 'border-orange-200 bg-white',
    iconWrapper: 'bg-orange-100 text-orange-600',
    icon: '!',
    title: 'Perhatian',
    progress: 'bg-orange-500',
  },

  info: {
    wrapper: 'border-blue-200 bg-white',
    iconWrapper: 'bg-blue-100 text-[#1a6fbd]',
    icon: 'i',
    title: 'Informasi',
    progress: 'bg-[#1a6fbd]',
  },
}

const variant = computed(() => {
  return variants[props.type]
})

const clearTimer = () => {
  if (!timeoutId) {
    return
  }

  clearTimeout(timeoutId)
  timeoutId = null
}

const close = () => {
  visible.value = false

  clearTimer()

  setTimeout(() => {
    emit('close')
  }, 250)
}

const show = () => {
  if (!props.message) {
    return
  }

  clearTimer()

  visible.value = true

  timeoutId = setTimeout(() => {
    close()
  }, props.duration)
}

watch(
  () => props.message,
  (message) => {
    if (message) {
      show()

      return
    }

    visible.value = false
    clearTimer()
  },
  {
    immediate: true,
  }
)

onBeforeUnmount(() => {
  clearTimer()
})
</script>

<template>
  <Teleport to="body">
    <div
      class="pointer-events-none fixed right-4 top-4 z-[100] w-[calc(100%-2rem)] max-w-sm sm:right-6 sm:top-6"
    >
      <Transition
        enter-active-class="transition duration-300 ease-out"
        leave-active-class="transition duration-200 ease-in"
        enter-from-class="translate-x-6 opacity-0"
        leave-to-class="translate-x-6 opacity-0"
      >
        <div
          v-if="visible && message"
          class="pointer-events-auto relative overflow-hidden rounded-2xl border p-4 shadow-2xl shadow-slate-300/50"
          :class="variant.wrapper"
        >
          <div class="flex items-start gap-3">
            <div
              class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-black"
              :class="variant.iconWrapper"
            >
              {{ variant.icon }}
            </div>

            <div class="min-w-0 flex-1">
              <p class="text-sm font-black text-slate-700">
                {{ variant.title }}
              </p>

              <p class="mt-1 text-sm leading-5 text-slate-500">
                {{ message }}
              </p>
            </div>

            <button
              type="button"
              class="rounded-lg px-2 py-1 text-lg leading-none text-slate-300 transition hover:bg-slate-50 hover:text-slate-500"
              aria-label="Tutup notifikasi"
              @click="close"
            >
              ×
            </button>
          </div>

          <div
            class="toast-progress absolute bottom-0 left-0 h-1 w-full origin-left"
            :class="variant.progress"
            :style="{
              animationDuration: `${duration}ms`,
            }"
          />
        </div>
      </Transition>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-progress {
  animation-name: toast-progress;
  animation-timing-function: linear;
  animation-fill-mode: forwards;
}

@keyframes toast-progress {
  from {
    transform: scaleX(1);
  }

  to {
    transform: scaleX(0);
  }
}
</style>