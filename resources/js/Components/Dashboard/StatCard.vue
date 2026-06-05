<script setup>
import AnimatedNumber from '@/Components/Dashboard/AnimatedNumber.vue'

defineProps({
  title: {
    type: String,
    required: true,
  },

  value: {
    type: Number,
    default: 0,
  },

  format: {
    type: String,
    default: 'number',
  },

  description: {
    type: String,
    default: '',
  },

  variant: {
    type: String,
    default: 'blue',

    validator: (value) => {
      return [
        'blue',
        'green',
        'orange',
        'teal',
      ].includes(value)
    },
  },
})

const variants = {
  blue: {
    wrapper:
      'border-blue-200 bg-blue-100/80',
    title:
      'text-blue-700/70',
    value:
      'text-slate-900',
    description:
      'text-green-600',
  },

  green: {
    wrapper:
      'border-green-200 bg-green-100/80',
    title:
      'text-blue-700/70',
    value:
      'text-slate-900',
    description:
      'text-green-600',
  },

  orange: {
    wrapper:
      'border-orange-200 bg-orange-100/80',
    title:
      'text-blue-700/70',
    value:
      'text-slate-900',
    description:
      'text-green-600',
  },

  teal: {
    wrapper:
      'border-teal-200 bg-teal-100/80',
    title:
      'text-blue-700/70',
    value:
      'text-slate-900',
    description:
      'text-green-600',
  },
}
</script>

<template>
  <article
    class="group rounded-2xl border px-4 py-4 transition duration-300 hover:-translate-y-1 hover:shadow-md"
    :class="variants[variant].wrapper"
  >
    <p
      class="text-xs font-medium uppercase tracking-wide"
      :class="variants[variant].title"
    >
      {{ title }}
    </p>

    <p
      class="mt-2 text-base font-black tracking-tight"
      :class="variants[variant].value"
    >
      <AnimatedNumber
        :value="value"
        :format="format"
      />
    </p>

    <p
      v-if="description"
      class="mt-2 text-xs font-medium"
      :class="variants[variant].description"
    >
      {{ description }}
    </p>
  </article>
</template>