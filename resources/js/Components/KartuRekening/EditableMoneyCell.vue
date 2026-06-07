<script setup>
import { computed, nextTick, ref, watch } from "vue";

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: "",
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

    placeholder: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["change"]);

const inputValue = ref("");

const isRawInteger = (value) => {
    return /^-?\d+$/.test(String(value));
};

const formatNumber = (value) => {
    if (value === null || value === undefined || value === "") {
        return "";
    }

    const text = String(value);

    if (text === "-" && props.allowNegative) {
        return "-";
    }

    if (!isRawInteger(text)) {
        return text;
    }

    const number = Number(text);

    if (number === 0 && !props.showZero) {
        return "";
    }

    return new Intl.NumberFormat("id-ID", {
        maximumFractionDigits: 0,
    }).format(number);
};

const parseNumber = (value) => {
    const text = String(value ?? "").trim();

    if (text === "") {
        return "";
    }

    if (text === "-") {
        return "-";
    }

    if (/^-?\d[\d.]*$/.test(text)) {
        return text.replaceAll(".", "");
    }

    return text;
};
const isNegative = computed(() => {
    const parsed = parseNumber(inputValue.value);

    if (parsed === "" || parsed === "-" || !isRawInteger(parsed)) {
        return false;
    }

    return Number(parsed) < 0;
});

watch(
    () => props.modelValue,
    (value) => {
        inputValue.value = formatNumber(value);
    },
    {
        immediate: true,
    },
);

const moveCursorToEnd = async (element) => {
    await nextTick();

    const length = element.value.length;

    element.setSelectionRange(length, length);
};

const handleInput = (event) => {
    const element = event.target;

    const parsed = parseNumber(element.value);

    inputValue.value = formatNumber(parsed);

    emit("change", parsed === "-" ? "" : parsed);

    moveCursorToEnd(element);
};

const handleFocus = (event) => {
    event.target.select();
};
</script>

<template>
    <input
        :value="inputValue"
        type="text"
        inputmode="numeric"
        :placeholder="placeholder"
        class="w-full min-w-[96px] rounded-md border px-2 py-1.5 text-right text-xs font-semibold tabular-nums outline-none transition"
        :class="
            dirty
                ? 'border-orange-300 bg-orange-50 text-orange-700 ring-2 ring-orange-100'
                : isNegative
                  ? 'border-transparent bg-red-50/70 text-red-600 hover:border-red-100 focus:border-red-300 focus:bg-white focus:ring-2 focus:ring-red-100'
                  : 'border-transparent bg-transparent text-slate-700 hover:border-blue-100 hover:bg-blue-50/50 focus:border-[#1a6fbd] focus:bg-white focus:ring-2 focus:ring-blue-100'
        "
        @input="handleInput"
        @focus="handleFocus"
    />
</template>
