<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketCreateModal from '@/Components/TicketCreateModal.vue';

const props = defineProps({
    tickets: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] } // Talep açma modalına göndermek için
});

const showCreateModal = ref(false);

// Enum değerlerimize göre Vue tarafında renk ve metin eşleştirmesi
const getStatusConfig = (status) => {
    const configs = {
        'opened': { text: 'Açık', color: 'bg-slate-100 text-slate-700' },
        'in_progress': { text: 'İşlemde', color: 'bg-amber-100 text-amber-700' },
        'waiting_customer': { text: 'Yanıt Bekliyor', color: 'bg-purple-100 text-purple-700' },
        'resolved': { text: 'Çözüldü', color: 'bg-emerald-100 text-emerald-700' }
    };
    return configs[status] || { text: 'Bilinmiyor', color: 'bg-gray-100 text-gray-700' };
};

// Tarih formatlama (Örn: 12 Eki 2026)
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<template>
    <Head title="Taleplerim" />

    <AppLayout>
        <div class="py-6 sm:py-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Taleplerim</h1>
                    <p class="text-sm text-slate-500 mt-1">Açtığınız tüm destek taleplerini buradan takip edebilirsiniz.</p>
                </div>
                
                <button 
                    @click="showCreateModal = true"
                    class="w-full sm:w-auto px-6 py-3.5 sm:py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-2xl sm:rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Yeni Talep Aç
                </button>
            </div>

            <div v-if="tickets.length === 0" class="bg-white rounded-3xl p-10 text-center border border-slate-100 shadow-sm mt-10">
                <div class="w-24 h-24 bg-indigo-50 text-indigo-300 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Harika! Hiçbir sorun yok.</h3>
                <p class="text-slate-500 mt-2 text-sm max-w-sm mx-auto">Şu anda açık bir talebiniz bulunmuyor. Bir sorun yaşarsanız veya desteğe ihtiyacınız olursa buradayız.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div 
                    v-for="ticket in tickets" 
                    :key="ticket.id"
                    class="bg-white/90 backdrop-blur-sm border border-slate-100 rounded-[24px] p-5 sm:p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col relative overflow-hidden group"
                >
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50 px-2 py-1 rounded-lg">
                            {{ ticket.project.name }}
                        </span>
                        
                        <span 
                            class="px-3 py-1 rounded-full text-xs font-bold"
                            :class="getStatusConfig(ticket.status).color"
                        >
                            {{ getStatusConfig(ticket.status).text }}
                        </span>
                    </div>

                    <div class="flex-1">
                        <h2 class="text-lg font-bold text-slate-800 mb-2 leading-tight">
                            {{ ticket.title }}
                        </h2>
                        <p class="text-sm text-slate-500 line-clamp-2">
                            {{ ticket.description }}
                        </p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-50 flex justify-between items-center">
                        <div class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ formatDate(ticket.created_at) }}
                        </div>
                        
                        <Link 
                            :href="route('tickets.show', ticket.id)" 
                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 group-hover:translate-x-1 transition-transform"
                        >
                            Detaylar
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <TicketCreateModal 
            :show="showCreateModal" 
            :projects="projects"
            @close="showCreateModal = false" 
        />
    </AppLayout>
</template>

<style scoped>
/* Kartlardaki açıklamanın 2 satırdan sonrasını '...' ile kesmesi için Tailwind eklentisi alternatifi */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>