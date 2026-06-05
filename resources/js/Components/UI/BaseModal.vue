<script setup>
import { onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  maxWidth: {
    type: String,
    default: 'max-w-md',
  },
  closeable: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['close'])

const close = () => {
  if (props.closeable) {
    emit('close')
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape' && props.show) {
    close()
  }
}

watch(
  () => props.show,
  (show) => {
    document.body.style.overflow = show ? 'hidden' : ''
  }
)

onMounted(() => {
  window.addEventListener('keydown', handleEscape)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      leave-active-class="transition duration-150 ease-in"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm"
        @click.self="close"
      >
        <Transition
          appear
          enter-active-class="transition duration-200 ease-out"
          leave-active-class="transition duration-150 ease-in"
          enter-from-class="translate-y-3 scale-95 opacity-0"
          leave-to-class="translate-y-3 scale-95 opacity-0"
        >
          <section
            v-if="show"
            class="w-full rounded-3xl bg-white p-6 shadow-2xl"
            :class="maxWidth"
          >
            <slot />
          </section>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>