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

  readonly: {
    type: Boolean,
    default: false,
  },

  inputType: {
    type: String,
    default: 'text',
  },

  nullable: {
    type: Boolean,
    default: false,
  },

  emptyLabel: {
    type: String,
    default: '-',
  },
  compact: {
  type: Boolean,
  default: false,
},
})

const emit = defineEmits([
  'change',
])

const editing = ref(false)
const inputRef = ref(null)
const inputValue = ref('')

const displayValue = computed(() => {
  if (
    props.modelValue === null
    || props.modelValue === undefined
    || props.modelValue === ''
  ) {
    return props.emptyLabel
  }

  return String(props.modelValue)
})

watch(
  () => props.modelValue,
  (value) => {
    if (!editing.value) {
      inputValue.value = String(value ?? '')
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
  inputValue.value = String(props.modelValue ?? '')

  await nextTick()

  inputRef.value?.focus()

  if (props.inputType === 'text') {
    inputRef.value?.select()
  }

  if (props.inputType === 'date') {
    inputRef.value?.showPicker?.()
  }
}

const stopEditing = () => {
  editing.value = false
}

const cancelEditing = () => {
  inputValue.value = String(props.modelValue ?? '')
  editing.value = false
}

const normalizeValue = (value) => {
  if (
    props.nullable
    && (
      value === null
      || value === undefined
      || String(value).trim() === ''
    )
  ) {
    return null
  }

  return value
}

const handleInput = (event) => {
  inputValue.value = event.target.value

  emit(
    'change',
    normalizeValue(inputValue.value),
  )
}
</script>

<template>
  <span
    v-if="readonly"
    :class="
      compact
        ? 'text-xs font-normal text-slate-400'
        : 'font-black text-slate-800'
    "
  >
    {{ displayValue }}
  </span>

  <input
    v-else-if="editing"
    ref="inputRef"
    :value="inputValue"
    :type="inputType"
    :class="
      compact
        ? 'w-[125px] rounded-md border border-[#1a6fbd] bg-white px-1.5 py-0.5 text-xs font-normal text-slate-600 outline-none ring-1 ring-blue-100'
        : 'w-full max-w-md rounded-lg border border-[#1a6fbd] bg-white px-2 py-1 text-xl font-black text-slate-800 outline-none ring-2 ring-blue-100'
    "
    @input="handleInput"
    @blur="stopEditing"
    @keydown.enter.prevent="$event.target.blur()"
    @keydown.esc.prevent="cancelEditing"
  >

  <button
    v-else
    type="button"
    class="group inline-flex items-center text-left transition"
    :class="
      compact
        ? [
            'gap-1 border-0 bg-transparent p-0 text-xs font-normal',
            dirty
              ? 'text-orange-600'
              : 'text-slate-400 hover:text-[#1a6fbd]',
          ]
        : [
            'max-w-md gap-2 rounded-lg border px-2 py-1',
            dirty
              ? 'border-orange-300 bg-orange-50 text-orange-700 ring-2 ring-orange-100'
              : 'border-transparent text-slate-800 hover:border-blue-200 hover:bg-white/70',
          ]
    "
    @click="startEditing"
  >
    <span
      :class="
        compact
          ? 'text-xs font-normal'
          : 'text-xl font-black'
      "
    >
      {{ displayValue }}
    </span>

    <svg
      xmlns="http://www.w3.org/2000/svg"
      :class="
        compact
          ? 'h-3 w-3 opacity-0 transition group-hover:opacity-60'
          : 'h-3.5 w-3.5 opacity-0 transition group-hover:opacity-60'
      "
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