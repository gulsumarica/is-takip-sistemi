<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    task: Object
});

const emit = defineEmits(['close', 'submit']);

// Süre state'i
const timeSpent = ref('');

// Modal her açıldığında süreyi sıfırlıyoruz ki önceki task'ın süresi kalmasın
watch(() => props.show, (newVal) => {
    if (newVal) {
        timeSpent.value = '';
    }
});

const handleSubmit = () => {
    // Sadece geçerli bir rakam girildiyse gönder (Basic validation)
    if (timeSpent.value && timeSpent.value > 0) {
        emit('submit', { time_spent_minutes: parseInt(timeSpent.value) });
    }
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4">
            
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-100">
                
                <div class="bg-emerald-50 p-6 text-center border-b border-emerald-100">
                    <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-800">Harika İş Çıkardın!</h3>
                    <p class="text-sm text-emerald-600 mt-1" v-if="task">
                        <span class="font-semibold">#TASK-{{ task.id }}</span> numaralı görevi tamamlıyorsun.
                    </p>
                </div>

                <div class="p-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Bu görev için kaç dakika harcadın?
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            v-model="timeSpent" 
                            placeholder="Örn: 45"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-slate-700 text-lg"
                            @keyup.enter="handleSubmit"
                            autofocus
                        >
                        <span class="absolute right-4 top-3.5 text-slate-400 font-medium">Dakika</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Bu veri, proje maliyet ve efor raporlarına yansıyacaktır.</p>
                </div>

                <div class="px-6 py-4 bg-slate-50 flex gap-3 justify-end border-t border-slate-100">
                    <button 
                        @click="$emit('close')" 
                        class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-xl transition-colors"
                    >
                        İptal Et
                    </button>
                    <button 
                        @click="handleSubmit" 
                        :disabled="!timeSpent || timeSpent <= 0"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 hover:-translate-y-0.5 transform transition-all rounded-xl shadow-md disabled:opacity-50 disabled:hover:translate-y-0"
                    >
                        Kaydet ve Tamamla
                    </button>
                </div>

            </div>
        </div>
    </Transition>
</template>