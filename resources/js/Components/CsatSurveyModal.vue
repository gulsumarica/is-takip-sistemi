<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    ticket: Object
});

const emit = defineEmits(['close']);

// 1'den 5'e kadar puanlama (1: Çok Kötü, 5: Harika)
const selectedScore = ref(null);
const isSubmitting = ref(false);

const emojis = [
    { score: 1, icon: '😡', label: 'Çok Kötü', color: 'bg-red-50 text-red-600 hover:bg-red-100 border-red-200' },
    { score: 2, icon: '🙁', label: 'Kötü', color: 'bg-orange-50 text-orange-600 hover:bg-orange-100 border-orange-200' },
    { score: 3, icon: '😐', label: 'Normal', color: 'bg-amber-50 text-amber-600 hover:bg-amber-100 border-amber-200' },
    { score: 4, icon: '🙂', label: 'İyi', color: 'bg-blue-50 text-blue-600 hover:bg-blue-100 border-blue-200' },
    { score: 5, icon: '🤩', label: 'Harika', color: 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border-emerald-200' }
];

watch(() => props.show, (newVal) => {
    if (newVal) selectedScore.value = null; // Modal açıldığında seçimi sıfırla
});

const submitSurvey = () => {
    if (!selectedScore.value || !props.ticket) return;
    
    isSubmitting.value = true;
    
    // Backend'e puanı gönder ve talebi kapat
    router.put(route('tickets.resolve', props.ticket.id), {
        csat_score: selectedScore.value
    }, {
        onSuccess: () => {
            emit('close');
            // Burada da ufak bir konfeti patlatabiliriz!
        },
        onFinish: () => { isSubmitting.value = false; },
        preserveScroll: true
    });
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm sm:p-4">
            
            <div class="bg-white rounded-t-[32px] sm:rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-100">
                
                <div class="bg-gradient-to-b from-indigo-50 to-white p-8 text-center border-b border-slate-100">
                    <div class="w-16 h-16 bg-white shadow-sm rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                        🎉
                    </div>
                    <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight">Talebiniz Çözüldü!</h3>
                    <p class="text-slate-500 mt-2 text-sm">
                        Bu sürecin çözümünden ne kadar memnun kaldınız? Değerlendirmeniz ekibimiz için çok değerli.
                    </p>
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-center gap-2">
                        <button 
                            v-for="emoji in emojis" 
                            :key="emoji.score"
                            @click="selectedScore = emoji.score"
                            class="flex flex-col items-center justify-center flex-1 py-4 rounded-2xl border-2 transition-all transform hover:scale-105"
                            :class="[
                                selectedScore === emoji.score 
                                    ? emoji.color + ' scale-110 shadow-md ring-2 ring-offset-1 ring-' + emoji.color.split('-')[1] + '-400' 
                                    : 'border-slate-100 bg-white hover:bg-slate-50 grayscale hover:grayscale-0'
                            ]"
                        >
                            <span class="text-3xl sm:text-4xl mb-1 filter drop-shadow-sm">{{ emoji.icon }}</span>
                            <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider mt-1" :class="selectedScore === emoji.score ? '' : 'text-slate-400'">
                                {{ emoji.label }}
                            </span>
                        </button>
                    </div>
                </div>

                <div class="p-6 pt-0">
                    <button 
                        @click="submitSurvey" 
                        :disabled="!selectedScore || isSubmitting"
                        class="w-full py-4 text-sm font-bold text-white bg-slate-800 hover:bg-slate-700 rounded-2xl shadow-lg transition-all disabled:opacity-40 disabled:hover:scale-100 hover:-translate-y-1 flex items-center justify-center gap-2"
                    >
                        <span v-if="isSubmitting" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        Gönder ve Kapat
                    </button>
                    <button @click="$emit('close')" class="w-full mt-3 py-2 text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors">
                        Değerlendirmeden Geç
                    </button>
                </div>

            </div>
        </div>
    </Transition>
</template>