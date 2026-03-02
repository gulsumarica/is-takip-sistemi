<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    show: Boolean,
    projects: Array // Backend'den gelen, müşterinin yetkili olduğu projeler listesi
});

const emit = defineEmits(['close']);

// Inertia Form nesnesi (Dosya yükleme desteği içerir)
const form = useForm({
    project_id: '',
    title: '',
    description: '',
    screenshot: null,
});

const imagePreview = ref(null);
const fileInput = ref(null);

// Modal kapandığında formu sıfırla
watch(() => props.show, (newVal) => {
    if (!newVal) {
        form.reset();
        imagePreview.value = null;
        form.clearErrors();
    }
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.screenshot = file;
        // Kullanıcıya seçtiği resmi anında önizletiyoruz (Kritik UX)
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submitTicket = () => {
    form.post(route('tickets.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            // Burada enerjik bir "Talebiniz Alındı" toast mesajı patlatabiliriz!
        }
    });
};
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <div v-if="show" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/50 backdrop-blur-sm sm:p-4">
            
            <div class="bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 max-h-[90vh] flex flex-col">
                
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h3 class="text-xl font-bold text-slate-800">Yeni Talep Oluştur</h3>
                    <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 custom-scrollbar">
                    <form @submit.prevent="submitTicket" class="space-y-5">
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">İlgili Proje</label>
                            <select v-model="form.project_id" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all">
                                <option value="" disabled>Lütfen bir proje seçin</option>
                                <option v-for="project in projects" :key="project.id" :value="project.id">
                                    {{ project.name }}
                                </option>
                            </select>
                            <span v-if="form.errors.project_id" class="text-red-500 text-xs mt-1">{{ form.errors.project_id }}</span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Konu Başlığı</label>
                            <input type="text" v-model="form.title" placeholder="Örn: Sisteme giriş yapamıyorum" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all">
                            <span v-if="form.errors.title" class="text-red-500 text-xs mt-1">{{ form.errors.title }}</span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Detaylı Açıklama</label>
                            <textarea v-model="form.description" rows="4" placeholder="Yaşadığınız sorunu detaylıca anlatın..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 transition-all resize-none"></textarea>
                            <span v-if="form.errors.description" class="text-red-500 text-xs mt-1">{{ form.errors.description }}</span>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Ekran Görüntüsü (İsteğe Bağlı)</label>
                            
                            <input 
                                type="file" 
                                ref="fileInput" 
                                @change="handleFileChange" 
                                accept="image/jpeg, image/png, image/webp" 
                                class="hidden"
                            >
                            
                            <div 
                                @click="$refs.fileInput.click()"
                                class="border-2 border-dashed border-slate-300 rounded-xl p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-slate-50 hover:border-indigo-400 transition-colors"
                                :class="{'border-indigo-500 bg-indigo-50': imagePreview}"
                            >
                                <template v-if="!imagePreview">
                                    <svg class="w-10 h-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <p class="text-sm font-medium text-slate-600">Fotoğraf çek veya galeriden seç</p>
                                    <p class="text-xs text-slate-400 mt-1">Sadece JPG, PNG, WEBP</p>
                                </template>
                                
                                <template v-else>
                                    <img :src="imagePreview" class="max-h-32 rounded-lg shadow-sm object-cover mb-2" />
                                    <p class="text-xs font-semibold text-indigo-600">Resmi değiştirmek için dokun</p>
                                </template>
                            </div>
                            <span v-if="form.errors.screenshot" class="text-red-500 text-xs mt-1">{{ form.errors.screenshot }}</span>
                        </div>
                    </form>
                </div>

                <div class="p-6 border-t border-slate-100 bg-white flex justify-end gap-3">
                    <button @click="$emit('close')" class="px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
                        İptal
                    </button>
                    <button 
                        @click="submitTicket" 
                        :disabled="form.processing"
                        class="px-6 py-3 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-500 hover:-translate-y-0.5 rounded-xl shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        Gönder
                    </button>
                </div>

            </div>
        </div>
    </Transition>
</template>