<script setup>
import { Link } from '@inertiajs/vue3';

// Müşteri (Level 6) için örnek menü. İleride rol bazlı (Sidebar'daki gibi) dinamik yapılabilir.
const mobileMenus = [
    { name: 'Ana Akış', route: 'customer.dashboard', icon: 'M4 6h16M4 10h16M4 14h16M4 18h16' },
    { name: 'Bildirimler', route: '#', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    { name: 'Profil', route: '#', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' }
];
</script>

<template>
    <nav class="md:hidden fixed bottom-0 w-full bg-white/95 backdrop-blur-lg border-t border-slate-200 pb-safe z-50">
        <div class="flex justify-around items-center h-16">
            <Link 
                v-for="item in mobileMenus" 
                :key="item.name"
                :href="item.route !== '#' ? route(item.route) : '#'"
                class="flex flex-col items-center justify-center w-full h-full space-y-1 text-slate-400 hover:text-indigo-600 transition-colors"
                :class="{ 'text-indigo-600': item.route !== '#' && route().current(item.route) }"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                </svg>
                <span class="text-[10px] font-medium">{{ item.name }}</span>
            </Link>
        </div>
    </nav>
</template>
