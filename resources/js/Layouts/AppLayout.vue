<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import TopNavbar from '@/Components/TopNavbar.vue';
import MobileBottomNav from '@/Components/MobileBottomNav.vue';
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex">
        
        <Sidebar />

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative pb-16 md:pb-0">
            
            <TopNavbar />

            <main class="flex-1 overflow-y-auto w-full custom-scrollbar">
                <slot />
            </main>

            <MobileBottomNav />

        </div>

    </div>
</template>