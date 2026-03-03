<script setup>
import { computed, ref, nextTick, onMounted, watch } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TicketCreateModal from '@/Components/TicketCreateModal.vue';
import EnergyButton from '@/Components/EnergyButton.vue';
import CsatSurveyModal from '@/Components/CsatSurveyModal.vue';

const props = defineProps({
    tickets: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    stats: {
        type: Object,
        default: () => ({ total: 0, in_progress: 0, waiting_customer: 0, resolved: 0 }),
    },
});

const showCreateModal = ref(false);
const search = ref('');
const statusFilter = ref('all'); // all | open | closed

const user = computed(() => usePage().props.auth.user);

const showDetailModal = ref(false);
const activeTicket = ref(null);
const resolving = ref(false);
const showCsatModal = ref(false);
const detailScrollContainer = ref(null);

const getStatusConfig = (status) => {
    const configs = {
        opened: { text: 'Açık', color: 'bg-slate-100 text-slate-700' },
        in_progress: { text: 'İşlemde', color: 'bg-amber-100 text-amber-700' },
        waiting_customer: { text: 'Yanıt Bekliyor', color: 'bg-purple-100 text-purple-700' },
        resolved: { text: 'Çözüldü', color: 'bg-emerald-100 text-emerald-700' },
    };
    return configs[status] || { text: 'Bilinmiyor', color: 'bg-gray-100 text-gray-700' };
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

// KPI kartları
const kpiCards = computed(() => [
    {
        key: 'total',
        label: 'Toplam Talep',
        value: props.stats.total,
        color: 'bg-indigo-50 text-indigo-700',
        accent: 'bg-indigo-500',
    },
    {
        key: 'in_progress',
        label: 'İşlemde',
        value: props.stats.in_progress,
        color: 'bg-amber-50 text-amber-700',
        accent: 'bg-amber-500',
    },
    {
        key: 'waiting_customer',
        label: 'Dönüş Beklenenler',
        value: props.stats.waiting_customer,
        color: 'bg-purple-50 text-purple-700',
        accent: 'bg-purple-500',
    },
    {
        key: 'resolved',
        label: 'Çözülenler',
        value: props.stats.resolved,
        color: 'bg-emerald-50 text-emerald-700',
        accent: 'bg-emerald-500',
    },
]);

// Arama + durum filtresi
const filteredTickets = computed(() => {
    let list = [...props.tickets];

    if (statusFilter.value === 'open') {
        list = list.filter((t) => t.status !== 'resolved');
    } else if (statusFilter.value === 'closed') {
        list = list.filter((t) => t.status === 'resolved');
    }

    if (search.value.trim().length > 0) {
        const q = search.value.toLowerCase();
        list = list.filter((t) => t.title.toLowerCase().includes(q));
    }

    return list;
});

// Seçili talep için chat mesajları
const publicComments = computed(() => {
    if (!activeTicket.value?.comments) return [];
    return [...activeTicket.value.comments].sort(
        (a, b) => new Date(a.created_at) - new Date(b.created_at),
    );
});

const isResolvedModal = computed(() => activeTicket.value?.status === 'resolved');

// Yorum formu
const commentForm = useForm({
    body: '',
    attachment: null,
});

const attachmentPreview = ref(null);
const attachmentInput = ref(null);

const handleAttachmentChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    commentForm.attachment = file;
    attachmentPreview.value = URL.createObjectURL(file);
};

const resetCommentForm = () => {
    commentForm.reset('body', 'attachment');
    attachmentPreview.value = null;
    if (attachmentInput.value) {
        attachmentInput.value.value = '';
    }
};

const submitComment = () => {
    if (!activeTicket.value?.id) return;

    commentForm.post(route('tickets.comments.store', activeTicket.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            resetCommentForm();
            scrollToBottomModal();
        },
    });
};

const scrollToBottomModal = async () => {
    await nextTick();
    if (detailScrollContainer.value) {
        detailScrollContainer.value.scrollTop = detailScrollContainer.value.scrollHeight;
    }
};

// Talep detay modali
const openDetails = (ticket) => {
    if (!ticket) return;
    activeTicket.value = ticket;
    showDetailModal.value = true;
};

const closeDetails = () => {
    showDetailModal.value = false;
    activeTicket.value = null;
    resolving.value = false;
};

// Talebi çözüldü olarak işaretleme (müşteri)
const markResolved = () => {
    if (!activeTicket.value?.id || resolving.value) return;
    resolving.value = true;

    router.put(
        route('tickets.resolve', activeTicket.value.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showCsatModal.value = true;
                if (activeTicket.value) {
                    activeTicket.value.status = 'resolved';
                }
                scrollToBottomModal();
            },
            onFinish: () => {
                resolving.value = false;
            },
        },
    );
};

onMounted(() => {
    scrollToBottomModal();
});

watch(
    publicComments,
    () => {
        scrollToBottomModal();
    },
    { deep: true },
);

watch(showDetailModal, (val) => {
    if (val) {
        scrollToBottomModal();
    }
});
</script>

<template>
    <Head title="Taleplerim" />

    <AppLayout>
        <div class="py-6 sm:py-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <!-- Başlık + Yeni Talep -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Müşteri Paneli</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Tüm destek taleplerinizi, durumlarını ve iletişim geçmişinizi buradan yönetebilirsiniz.
                    </p>
                </div>

                <button
                    @click="showCreateModal = true"
                    class="w-full sm:w-auto px-6 py-3.5 sm:py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-2xl sm:rounded-xl shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Yeni Talep Aç
                </button>
            </div>

            <!-- KPI kartları -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
                <div
                    v-for="card in kpiCards"
                    :key="card.key"
                    class="rounded-2xl px-4 py-3 sm:px-5 sm:py-4 flex items-center justify-between border border-slate-100 shadow-sm"
                    :class="card.color"
                >
                    <div>
                        <p class="text-[11px] uppercase tracking-wide font-semibold opacity-70">
                            {{ card.label }}
                        </p>
                        <p class="mt-1 text-xl sm:text-2xl font-extrabold">
                            {{ card.value }}
                        </p>
                    </div>
                    <div
                        class="w-9 h-9 rounded-full flex items-center justify-center bg-white/70 shadow-sm"
                        :class="card.accent"
                    >
                        <span class="text-xs font-bold text-white">#</span>
                    </div>
                </div>
            </div>

            <!-- Filtreler + Arama -->
            <div
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 mb-6 bg-white/80 backdrop-blur border border-slate-100 rounded-2xl px-4 py-3"
            >
                <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-semibold text-slate-500">
                    <button
                        type="button"
                        class="px-3 py-1.5 rounded-lg transition-all"
                        :class="statusFilter === 'all' ? 'bg-white shadow text-slate-900' : ''"
                        @click="statusFilter = 'all'"
                    >
                        Tümü
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 rounded-lg transition-all"
                        :class="statusFilter === 'open' ? 'bg-white shadow text-slate-900' : ''"
                        @click="statusFilter = 'open'"
                    >
                        Açık
                    </button>
                    <button
                        type="button"
                        class="px-3 py-1.5 rounded-lg transition-all"
                        :class="statusFilter === 'closed' ? 'bg-white shadow text-slate-900' : ''"
                        @click="statusFilter = 'closed'"
                    >
                        Kapalı
                    </button>
                </div>

                <div class="relative w-full sm:max-w-xs">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Başlığa göre ara..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all placeholder:text-slate-400"
                    />
                    <svg
                        class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z"
                        />
                    </svg>
                </div>
            </div>

            <!-- Boş durum -->
            <div
                v-if="filteredTickets.length === 0"
                class="bg-white rounded-3xl p-10 text-center border border-slate-100 shadow-sm mt-6"
            >
                <div class="w-24 h-24 bg-indigo-50 text-indigo-300 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"
                        />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Henüz eşleşen talep yok.</h3>
                <p class="text-slate-500 mt-2 text-sm max-w-sm mx-auto">
                    Filtreleri değiştirebilir veya yeni bir talep oluşturarak ekibimizle iletişime geçebilirsiniz.
                </p>
            </div>

            <!-- Talep kartları -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div
                    v-for="ticket in filteredTickets"
                    :key="ticket.id"
                    class="bg-white/90 backdrop-blur-sm border border-slate-100 rounded-[24px] p-5 sm:p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col relative overflow-hidden group"
                >
                    <div class="flex justify-between items-start mb-4">
                        <span
                            class="text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50 px-2 py-1 rounded-lg"
                        >
                            {{ ticket.project?.name }}
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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                            {{ formatDate(ticket.created_at) }}
                        </div>

                        <EnergyButton
                            size="sm"
                            label="Görüşmeleri Aç"
                            @click="openDetails(ticket)"
                        />
                    </div>
                </div>
            </div>

            <!-- Genişletilmiş Detay Modalı -->
            <div
                v-if="showDetailModal && activeTicket"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm px-4 py-6"
            >
                <div class="w-full max-w-5xl bg-slate-900 rounded-3xl shadow-2xl border border-slate-800 overflow-hidden flex flex-col">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.18em]">
                                Vaka Dosyası
                            </p>
                            <p class="mt-1 text-sm sm:text-base font-semibold text-white truncate">
                                {{ activeTicket.title }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span
                                class="hidden sm:inline-flex px-3 py-1 rounded-full text-[11px] font-semibold"
                                :class="getStatusConfig(activeTicket.status).color"
                            >
                                {{ getStatusConfig(activeTicket.status).text }}
                            </span>
                            <EnergyButton
                                v-if="!isResolvedModal"
                                size="sm"
                                label="Çözüldü Olarak İşaretle"
                                :loading="resolving"
                                :disabled="resolving"
                                @click="markResolved"
                            />
                            <button
                                type="button"
                                @click="closeDetails"
                                class="ml-1 inline-flex items-center justify-center w-8 h-8 rounded-full text-slate-400 hover:text-slate-100 hover:bg-slate-700/70 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col md:flex-row bg-slate-900">
                        <!-- Sol panel: Özet + Ana görsel -->
                        <div class="md:w-3/5 border-r border-slate-800 bg-slate-950/40 p-5 flex flex-col gap-4">
                            <div class="rounded-2xl border border-slate-800/80 bg-white/5 px-4 py-3 sm:px-5 sm:py-4 backdrop-blur-sm shadow-lg shadow-slate-900/40">
                                <span class="inline-flex items-center rounded-full bg-slate-900/70 border border-slate-700 px-2.5 py-0.5 text-[10px] font-semibold tracking-[0.16em] uppercase text-slate-300">
                                    {{ activeTicket.project?.name || 'Proje' }}
                                </span>
                                <h2 class="mt-2 text-lg sm:text-xl font-bold text-white">
                                    {{ activeTicket.title }}
                                </h2>
                                <p class="mt-3 text-sm leading-relaxed text-slate-300 whitespace-pre-wrap">
                                    {{ activeTicket.description }}
                                </p>
                            </div>

                            <div class="flex-1 rounded-2xl border border-slate-800/80 bg-slate-900/60 p-4 flex items-center justify-center">
                                <div v-if="activeTicket.main_image || activeTicket.attachment_url" class="w-full">
                                    <img
                                        :src="activeTicket.main_image || activeTicket.attachment_url"
                                        alt="Talep ana görseli"
                                        class="w-full max-h-[400px] rounded-2xl object-contain bg-slate-950 border border-slate-800 shadow-xl"
                                    />
                                </div>
                                <div v-else class="text-xs text-slate-500 text-center">
                                    Bu talep için henüz bir ekran görüntüsü yüklenmemiş.
                                </div>
                            </div>
                        </div>

                        <!-- Sağ panel: Mesajlaşma -->
                        <div class="md:w-2/5 bg-slate-950/60 flex flex-col">
                            <div class="px-4 pt-4 pb-2 border-b border-slate-800">
                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-[0.18em]">
                                    Görüşmeler
                                </p>
                                <p class="mt-1 text-[11px] text-slate-500">
                                    {{ formatDate(activeTicket.created_at) }} tarihinde açıldı
                                </p>
                            </div>

                            <div
                                ref="detailScrollContainer"
                                class="flex-1 overflow-y-auto px-4 py-3 space-y-3 custom-scrollbar max-h-[500px] scrollbar-thin scrollbar-thumb-slate-700"
                            >
                                <div
                                    v-for="comment in publicComments"
                                    :key="comment.id"
                                    class="flex"
                                    :class="comment.is_me ? 'justify-end' : 'justify-start'"
                                >
                                    <div
                                        class="group max-w-[80%] rounded-2xl px-3 py-2 text-xs sm:text-sm shadow-sm space-y-2"
                                        :class="
                                            comment.is_me
                                                ? 'bg-indigo-600 text-white rounded-br-sm'
                                                : 'bg-slate-900 text-slate-100 rounded-bl-sm border border-slate-800'
                                        "
                                    >
                                        <div
                                        v-if="comment.attachment_url"
                                        class="mb-1 cursor-pointer"
                                        >
                                            <img
                                                :src="comment.attachment_url"
                                                alt="Mesaj eki"
                                            class="w-40 h-28 object-cover rounded-xl border border-slate-700 shadow-sm"
                                            @load="scrollToBottomModal"
                                            />
                                        </div>

                                        <p class="whitespace-pre-wrap break-words">
                                            {{ comment.body }}
                                        </p>

                                        <p class="mt-1 text-[10px] opacity-70 text-right">
                                            {{ formatDate(comment.created_at) }}
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-if="publicComments.length === 0"
                                    class="text-xs text-slate-500 text-center mt-4"
                                >
                                    Henüz bu talep için bir mesajlaşma geçmişi bulunmuyor.
                                </div>
                            </div>

                            <div class="border-t border-slate-800 bg-slate-950/80 px-3 py-3">
                                <div
                                    v-if="isResolvedModal"
                                    class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-[11px] font-medium text-emerald-300 flex items-center gap-2"
                                >
                                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Bu talep çözüldü. Yeni mesaj gönderemezsiniz.
                                </div>

                                <form
                                    v-else
                                    @submit.prevent="submitComment"
                                    class="flex flex-col gap-2"
                                >
                                    <div
                                        v-if="attachmentPreview"
                                        class="inline-flex items-center gap-2 text-[11px] text-slate-200 bg-slate-800 px-2 py-0.5 rounded-full border border-slate-700 w-fit"
                                    >
                                        <img
                                            :src="attachmentPreview"
                                            alt="Önizleme"
                                            class="w-8 h-8 rounded-md object-cover border border-slate-600"
                                        />
                                        <button
                                            type="button"
                                            class="text-slate-400 hover:text-slate-100"
                                            @click="resetCommentForm"
                                        >
                                            ×
                                        </button>
                                    </div>

                                    <div
                                        class="flex items-center gap-2 bg-slate-900 rounded-2xl border border-slate-700 px-2 py-1"
                                    >
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-slate-800 text-indigo-400 transition-colors"
                                            @click="attachmentInput?.click()"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 00-5.657-5.657L6.343 10.17"
                                                />
                                            </svg>
                                        </button>

                                        <input
                                            ref="attachmentInput"
                                            type="file"
                                            class="hidden"
                                            accept=".jpg,.jpeg,.png,.webp"
                                            @change="handleAttachmentChange"
                                        />

                                        <textarea
                                            v-model="commentForm.body"
                                            rows="1"
                                            placeholder="Destek ekibine mesaj yazın..."
                                            class="flex-1 bg-transparent border-0 text-xs sm:text-sm text-slate-100 placeholder:text-slate-500 focus:ring-0 focus:outline-none resize-none"
                                        />

                                        <button
                                            type="submit"
                                            :disabled="commentForm.processing || (!commentForm.body && !commentForm.attachment)"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-600 text-white shadow-md hover:bg-indigo-500 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:hover:translate-y-0"
                                        >
                                            <svg
                                                class="w-4 h-4 transform rotate-90"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 12h14M12 5l7 7-7 7"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <TicketCreateModal
                :show="showCreateModal"
                :projects="projects"
                @close="showCreateModal = false"
            />

            <CsatSurveyModal
                :show="showCsatModal"
                :ticket="activeTicket"
                @close="showCsatModal = false"
            />
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>