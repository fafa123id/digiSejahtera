<script setup>
import { computed, nextTick, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },

  dirty: {
    type: Boolean,
    default: false,
  },

  showZero: {
    type: Boolean,
    default: false,
  },

  allowNegative: {
    type: Boolean,
    default: false,
  },

  readonly: {
    type: Boolean,
    default: false,
  },

  placeholder: {
    type: String,
    default: '-',
  },
})

const emit = defineEmits([
  'change',
])

const editing = ref(false)
const inputRef = ref(null)
const inputValue = ref('')

const isRawInteger = (value) => {
  return /^-?\d+$/.test(String(value))
}

const parseNumber = (value) => {
  const text = String(value ?? '').trim()

  if (text === '') {
    return ''
  }

  if (text === '-' && props.allowNegative) {
    return '-'
  }

  if (/^-?[\d.]+$/.test(text)) {
    return text.replaceAll('.', '')
  }

  return text
}

const formatNumber = (value) => {
  if (value === null || value === undefined || value === '') {
    return ''
  }

  const text = String(value)

  if (text === '-' && props.allowNegative) {
    return '-'
  }

  if (!isRawInteger(text)) {
    return text
  }

  const number = Number(text)

  if (number === 0 && !props.showZero) {
    return ''
  }

  return new Intl.NumberFormat('id-ID', {
    maximumFractionDigits: 0,
  }).format(number)
}

const displayValue = computed(() => {
  const formatted = formatNumber(props.modelValue)

  return formatted === ''
    ? props.placeholder
    : formatted
})

const isNegative = computed(() => {
  const parsed = parseNumber(inputValue.value)

  if (parsed === '' || parsed === '-' || !isRawInteger(parsed)) {
    return false
  }

  return Number(parsed) < 0
})

watch(
  () => props.modelValue,
  (value) => {
    if (!editing.value) {
      inputValue.value = formatNumber(value)
    }
  },
  {
    immediate: true,
  },
)

const startEditing = async () => {
  if (props.readonly) {
    return
  }

  editing.value = true
  inputValue.value = formatNumber(props.modelValue)

  await nextTick()

  inputRef.value?.focus()
  inputRef.value?.select()
}

const stopEditing = () => {
  if (inputValue.value === '-') {
    inputValue.value = formatNumber(props.modelValue)
  }

  editing.value = false
}

const cancelEditing = () => {
  inputValue.value = formatNumber(props.modelValue)
  editing.value = false
}

const handleInput = (event) => {
  const parsed = parseNumber(event.target.value)

  inputValue.value = formatNumber(parsed)

  if (parsed !== '-') {
    emit('change', parsed)
  }

  nextTick(() => {
    const length = inputRef.value?.value.length ?? 0

    inputRef.value?.setSelectionRange(length, length)
  })
}
</script>

<template>
  <span
    v-if="readonly"
    class="block w-full px-2 py-1.5 text-right text-xs font-semibold tabular-nums text-slate-700"
  >
    {{ displayValue }}
  </span>

  <input
    v-else-if="editing"
    ref="inputRef"
    :value="inputValue"
    type="text"
    inputmode="text"
    class="w-full min-w-[96px] rounded-md border px-2 py-1.5 text-right text-xs font-semibold tabular-nums outline-none transition"
    :class="
      dirty
        ? 'border-orange-300 bg-orange-50 text-orange-700 ring-2 ring-orange-100'
        : isNegative
          ? 'border-red-300 bg-white text-red-600 ring-2 ring-red-100'
          : 'border-[#1a6fbd] bg-white text-slate-700 ring-2 ring-blue-100'
    "
    @input="handleInput"
    @blur="stopEditing"
    @keydown.enter.prevent="$event.target.blur()"
    @keydown.esc.prevent="cancelEditing"
  >

  <button
    v-else
    type="button"
    class="group flex w-full min-w-[96px] items-center justify-end gap-1 rounded-md border px-2 py-1.5 text-right text-xs font-semibold tabular-nums transition"
    :class="
      dirty
        ? 'border-orange-300 bg-orange-50 text-orange-700 ring-2 ring-orange-100'
        : Number(modelValue ?? 0) < 0
          ? 'border-transparent bg-red-50/70 text-red-600 hover:border-red-200 hover:bg-red-50'
          : 'border-transparent bg-transparent text-slate-700 hover:border-blue-200 hover:bg-blue-50/60'
    "
    @click="startEditing"
  >
    <span>
      {{ displayValue }}
    </span>

    <svg
      xmlns="http://www.w3.org/2000/svg"
      class="h-3 w-3 opacity-0 transition group-hover:opacity-60"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      stroke-width="2"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="m16.862 3.487 3.651 3.651M5 19l4.2-.933L19.447 7.82a2.582 2.582 0 0 0-3.651-3.651L5.55 14.415 5 19Z"
      />
    </svg>
  </button>
</template>