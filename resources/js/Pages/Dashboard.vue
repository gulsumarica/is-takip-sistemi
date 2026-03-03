<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TaskKanbanBoard from '@/Components/TaskKanbanBoard.vue';
import TimeEntryModal from '@/Components/TimeEntryModal.vue';
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import confetti from 'canvas-confetti';

const props = defineProps({
    initialTasks: Array
});

const showTimeEntryModal = ref(false);
const pendingCompletedTask = ref(null);

// Kanban'dan normal bir durum değişimi geldiğinde (örn: todo -> in_progress)
const handleStatusUpdated = (payload) => {
    router.put(route('tasks.update-status', payload.task_id), { status: payload.status }, {
        preserveScroll: true,
        only: ['initialTasks'], // Sadece ilgili datayı yenile, tüm sayfayı değil
    });
};

// İş "done" sütununa düştüğünde modal'ı aç
const handleTaskCompleted = (task) => {
    pendingCompletedTask.value = task;
    showTimeEntryModal.value = true;
};

// Modal üzerinden zaman ve onay gönderildiğinde
const handleTimeEntrySubmit = (payload) => {
    // Hem zaman logunu atıyoruz hem de status'ü done yapıyoruz (Backend'de ResolveTaskAction karşılayacak)
    router.post(route('tasks.complete', pendingCompletedTask.value.id), payload, {
        onSuccess: () => { 
            showTimeEntryModal.value = false; 
            pendingCompletedTask.value = null;
            
            // UX kuralı: İş bitirildiğinde konfeti patlat (gamification)
            triggerConfetti();
        },
        preserveScroll: true,
        only: ['initialTasks'],
    });
};

// Enerjik Tasarım Vizyonuna Uygun Konfeti Ayarı
const triggerConfetti = () => {
    confetti({
        particleCount: 150,
        spread: 70,
        origin: { y: 0.6 },
        // Dokümandaki renk paletine uygun: başarı yeşili, gri/mavi enerji, turuncu dinamizm
        colors: ['#10B981', '#475569', '#F59E0B'], 
        disableForReducedMotion: true // Erişilebilirlik (A11y) kuralı
    });
};
</script>

<template>
    <Head title="Geliştirici Paneli" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">İş Akışım</h2>
        </template>

        <div class="py-8">
            <div class="max-w-[1400px] mx-auto sm:px-6 lg:px-8">
                <TaskKanbanBoard 
                    :tasks="initialTasks" 
                    @status-updated="handleStatusUpdated"
                    @task-completed="handleTaskCompleted"
                />
            </div>
        </div>

        <TimeEntryModal 
            :show="showTimeEntryModal" 
            :task="pendingCompletedTask"
            @close="showTimeEntryModal = false"
            @submit="handleTimeEntrySubmit"
        />
    </AppLayout>
</template>