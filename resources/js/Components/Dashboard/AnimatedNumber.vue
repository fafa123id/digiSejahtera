<script setup>
import {
  computed,
  onMounted,
  onUnmounted,
  ref,
  watch,
} from 'vue'

const props = defineProps({
  value: {
    type: Number,
    default: 0,
  },

  format: {
    type: String,
    default: 'number',

    validator: (value) => {
      return [
        'number',
        'currency',
      ].includes(value)
    },
  },

  duration: {
    type: Number,
    default: 900,
  },
})

const displayedValue = ref(0)

let animationFrame = null

const formattedValue = computed(() => {
  const formatter = new Intl.NumberFormat(
    'id-ID',
    props.format === 'currency'
      ? {
          style: 'currency',
          currency: 'IDR',
          maximumFractionDigits: 0,
        }
      : {
          maximumFractionDigits: 0,
        }
  )

  return formatter.format(
    displayedValue.value
  )
})

const animate = () => {
  if (animationFrame) {
    cancelAnimationFrame(
      animationFrame
    )
  }

  const startValue =
    displayedValue.value

  const endValue =
    Number(props.value ?? 0)

  const startedAt =
    performance.now()

  const step = (currentTime) => {
    const progress = Math.min(
      (
        currentTime
        - startedAt
      )
      / props.duration,
      1
    )

    const eased =
      1
      - Math.pow(
          1 - progress,
          3
        )

    displayedValue.value =
      startValue
      + (
        endValue
        - startValue
      )
      * eased

    if (progress < 1) {
      animationFrame =
        requestAnimationFrame(step)
    }
  }

  animationFrame =
    requestAnimationFrame(step)
}

watch(
  () => props.value,
  () => {
    animate()
  }
)

onMounted(() => {
  animate()
})

onUnmounted(() => {
  if (animationFrame) {
    cancelAnimationFrame(
      animationFrame
    )
  }
})
</script>

<template>
  <span>
    {{ formattedValue }}
  </span>
</template>