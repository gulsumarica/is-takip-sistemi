<script setup>
const props = defineProps({
    description: {
        type: String,
        default: '',
    },
    imageUrl: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['image-click']);

const handleImageClick = () => {
    if (!props.imageUrl) return;
    emit('image-click', props.imageUrl);
};
</script>

<template>
    <div
        class="relative overflow-hidden rounded-2xl border border-slate-200/70 bg-slate-50/40 px-4 py-4 sm:px-5 sm:py-5 backdrop-blur-md shadow-sm"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-sky-400/5 to-fuchsia-500/5"
        />

        <div class="relative flex flex-col gap-3 sm:gap-4">
            <p class="text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">
                {{ description }}
            </p>

            <div
                v-if="imageUrl"
                class="group relative mt-1 inline-flex max-w-full cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-white/70 px-2.5 py-2 shadow-sm hover:border-indigo-300 hover:bg-white transition-all"
                @click="handleImageClick"
            >
                <div class="relative h-14 w-20 flex-shrink-0 overflow-hidden rounded-xl bg-slate-100">
                    <img
                        :src="imageUrl"
                        alt="Talep ana görseli"
                        class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105"
                        loading="lazy"
                    />
                    <div
                        class="pointer-events-none absolute inset-0 rounded-xl bg-slate-900/20 opacity-0 group-hover:opacity-100 transition-opacity"
                    />
                </div>

                <div class="flex flex-1 flex-col">
                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                        Talep Özeti
                    </span>
                    <span class="mt-0.5 text-xs font-medium text-slate-700 line-clamp-2">
                        Ekran görüntüsünü büyütmek için tıklayın.
                    </span>
                </div>

                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900/80 text-slate-100 text-xs shadow-md">
                    ⤢
                </div>
            </div>
        </div>
    </div>
</template>

