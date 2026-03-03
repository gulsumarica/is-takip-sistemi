<script setup>
const props = defineProps({
    label: {
        type: String,
        default: 'Çözüldü Olarak İşaretle',
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    size: {
        type: String,
        default: 'md', // sm | md
    },
});

const emit = defineEmits(['click']);

const handleClick = () => {
    if (props.disabled || props.loading) return;
    emit('click');
};
/* eslint-disable vue/no-dupe-keys */
</script>

<template>
    <button
        type="button"
        @click="handleClick"
        :disabled="disabled || loading"
        class="inline-flex items-center gap-2 rounded-full border border-indigo-500/40 bg-gradient-to-r from-indigo-600 via-indigo-500 to-sky-400 text-white shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/40 transition-all duration-200 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-60 disabled:hover:translate-y-0 disabled:cursor-not-allowed"
        :class="[
            size === 'sm'
                ? 'px-3 py-1.5 text-xs'
                : 'px-4 py-2 text-sm',
        ]"
    >
        <span
            class="relative flex h-5 w-5 items-center justify-center rounded-full bg-white/10 ring-1 ring-indigo-300/60 shadow-inner"
        >
            <span
                v-if="loading"
                class="h-3 w-3 rounded-full border-2 border-white border-t-transparent animate-spin"
            />
            <span
                v-else
                class="h-2.5 w-2.5 rounded-full bg-emerald-300 shadow-[0_0_12px_rgba(52,211,153,0.9)]"
            />
        </span>

        <span class="font-semibold tracking-tight">
            {{ label }}
        </span>
    </button>
</template>

