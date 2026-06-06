<script setup>
import {
  onMounted,
  ref,
} from 'vue'

const props = defineProps({
  delay: {
    type: Number,
    default: 0,
  },

  duration: {
    type: Number,
    default: 650,
  },

  direction: {
    type: String,
    default: 'up',

    validator: (value) => {
      return [
        'up',
        'down',
        'left',
        'right',
        'scale',
        'none',
      ].includes(value)
    },
  },
})

const visible = ref(false)

const enterFromClasses = {
  up: 'translate-y-5 opacity-0',
  down: '-translate-y-5 opacity-0',
  left: 'translate-x-5 opacity-0',
  right: '-translate-x-5 opacity-0',
  scale: 'scale-95 opacity-0',
  none: 'opacity-0',
}

const enterToClasses = {
  up: 'translate-y-0 opacity-100',
  down: 'translate-y-0 opacity-100',
  left: 'translate-x-0 opacity-100',
  right: 'translate-x-0 opacity-100',
  scale: 'scale-100 opacity-100',
  none: 'opacity-100',
}

onMounted(() => {
  requestAnimationFrame(() => {
    visible.value = true
  })
})
</script>

<template>
  <Transition
    appear
    enter-active-class="transition-all ease-out motion-reduce:transition-none"
    :enter-from-class="enterFromClasses[direction]"
    :enter-to-class="enterToClasses[direction]"
  >
    <div
      v-if="visible"
      :style="{
        transitionDelay: `${delay}ms`,
        transitionDuration: `${duration}ms`,
      }"
    >
      <slot />
    </div>
  </Transition>
</template>