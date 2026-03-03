<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Paylaş',
    },
    description: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

const modalRef = ref(null);
const closeButtonRef = ref(null);

const handleClose = () => {
    emit('close');
};

const handleKeydown = (event) => {
    if (event.key === 'Escape' && props.show) {
        handleClose();
    }
};

onMounted(() => {
    // Global klavye dinleyicisi
    window.addEventListener('keydown', handleKeydown);

    // Güvenli buton event'i (eski share-modal.js'teki addEventListener hatasını engellemek için guard'lı)
    if (closeButtonRef.value) {
        closeButtonRef.value.addEventListener('click', handleClose);
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);

    if (closeButtonRef.value) {
        closeButtonRef.value.removeEventListener('click', handleClose);
    }
});

// Modal açıldığında body scroll kilitle (opsiyonel UX)
watch(
    () => props.show,
    (visible) => {
        if (typeof document === 'undefined') return;
        if (visible) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    },
    { immediate: false },
);
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
            ref="modalRef"
            @click.self="handleClose"
        >
            <div
                class="w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/10 p-6 sm:p-7"
            >
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">
                            {{ title }}
                        </h2>
                        <p v-if="description" class="mt-1 text-sm text-slate-500">
                            {{ description }}
                        </p>
                    </div>
                    <button
                        ref="closeButtonRef"
                        type="button"
                        class="inline-flex items-center justify-center rounded-full p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                        aria-label="Kapat"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>

                <!-- Slot ile içerik esnek bırakıldı (örn. paylaşım linki, sosyal butonlar vs.) -->
                <div class="space-y-3">
                    <slot />
                </div>
            </div>
        </div>
    </Teleport>
</template>

