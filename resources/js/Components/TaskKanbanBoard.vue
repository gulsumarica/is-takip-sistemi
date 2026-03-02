<script setup>
import { computed } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps({
    tasks: { type: Array, default: () => [] }
});

const emit = defineEmits(['status-updated', 'task-completed']);

// Veritabanı mimarisine uygun sütun tanımlamaları
const columns = [
    { id: 'todo', title: 'Yapılacaklar', color: 'bg-slate-100 text-slate-700' },
    { id: 'in_progress', title: 'İşlemde', color: 'bg-amber-100 text-amber-700' },
    { id: 'testing', title: 'Test Aşamasında', color: 'bg-purple-100 text-purple-700' },
    { id: 'done', title: 'Bitti', color: 'bg-emerald-100 text-emerald-700' }
];

// Görevleri durumlarına göre ayırıp, vuedraggable'ın kullanabileceği listelere dönüştürüyoruz
const getTasksByStatus = (status) => {
    return props.tasks.filter(task => task.status === status);
};

const handleDragChange = (event, newStatus) => {
    if (event.added) {
        const task = event.added.element;
        
        // UX Kuralı: Eğer iş "Bitti" (done) sütununa sürüklendiyse süreyi girmesi için modal'ı tetikle
        if (newStatus === 'done') {
            emit('task-completed', { ...task, status: newStatus });
        } else {
            // Diğer sütun geçişlerinde sadece durumu güncelle (API'ye istek at)
            emit('status-updated', { task_id: task.id, status: newStatus });
        }
    }
};
</script>

<template>
    <div class="flex gap-6 p-4 overflow-x-auto min-h-[70vh]">
        <div 
            v-for="column in columns" 
            :key="column.id"
            class="flex-1 min-w-[320px] bg-gray-50/50 backdrop-blur rounded-2xl p-4 flex flex-col border border-gray-100"
        >
            <div class="flex items-center justify-between mb-4 px-2">
                <h3 class="font-bold uppercase text-sm" :class="column.color">
                    {{ column.title }}
                </h3>
                <span class="text-xs font-semibold text-gray-400 bg-white px-2 py-1 rounded-full shadow-sm">
                    {{ getTasksByStatus(column.id).length }}
                </span>
            </div>
            
            <draggable
                :list="getTasksByStatus(column.id)"
                group="tasks"
                item-key="id"
                class="flex-1 flex flex-col gap-3 min-h-[150px]"
                ghost-class="opacity-50"
                @change="(e) => handleDragChange(e, column.id)"
            >
                <template #item="{ element }">
                    <div class="p-4 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md transition-all cursor-grab active:cursor-grabbing">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                #TASK-{{ element.id }}
                            </span>
                            <span v-if="element.priority === 'urgent'" class="w-2 h-2 rounded-full bg-red-500"></span>
                        </div>
                        <p class="text-gray-800 font-medium text-sm leading-snug">{{ element.title }}</p>
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>