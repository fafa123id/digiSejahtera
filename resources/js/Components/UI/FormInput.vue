<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  label: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: '',
  },
  error: {
    type: String,
    default: '',
  },
  autocomplete: {
    type: String,
    default: 'off',
  },
  required: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div>
    <label class="text-sm font-bold text-slate-600">
      {{ label }}

      <span
        v-if="required"
        class="text-red-500"
      >
        *
      </span>
    </label>

    <input
      :value="modelValue"
      :type="type"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      class="mt-2 w-full rounded-xl border bg-[#f8fbff] px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-4"
      :class="
        error
          ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
          : 'border-blue-100 focus:border-[#1a6fbd] focus:ring-blue-100'
      "
      @input="emit('update:modelValue', $event.target.value)"
    />

    <p
      v-if="error"
      class="mt-1 text-xs font-semibold text-red-500"
    >
      {{ error }}
    </p>
  </div>
</template>