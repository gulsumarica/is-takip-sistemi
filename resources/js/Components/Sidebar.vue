<script setup>
import { computed, ref } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import { UsersIcon, ShieldCheckIcon, ArrowRightOnRectangleIcon } from "@heroicons/vue/24/outline";

// Inertia üzerinden Laravel'deki auth()->user() bilgisini alıyoruz
const user = computed(() => usePage().props.auth.user);
// Rol seviyesini al (1: Admin, 2: Yönetici, 4: Analist, 5: Geliştirici, 6: Müşteri) vb.
const roleLevel = computed(() => user.value?.role?.level || 6);

const showLogoutToast = ref(false);

const handleLogout = () => {
	if (roleLevel.value === 6) {
		showLogoutToast.value = true;

		setTimeout(() => {
			router.post(route("logout"), {}, { preserveScroll: true });
		}, 1200);
	} else {
		router.post(route("logout"), {}, { preserveScroll: true });
	}
};

// Rol bazlı dinamik menü oluşturucu
const menuItems = computed(() => {
	const items = [];

	// MÜŞTERİ MENÜSÜ (Seviye 6)
	if (roleLevel.value === 6) {
		items.push(
			{
				name: "Müşteri Talepleri",
				route: "customer.dashboard",
				icon: "M4 6h16M4 10h16M4 14h16M4 18h16",
			},
			{
				name: "Sıkça Sorulan Sorular",
				route: "customer.faq",
				icon: "M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
			},
		);
	}

	// GELİŞTİRİCİ MENÜSÜ (Seviye 5)
	if (roleLevel.value === 5) {
		items.push(
			{
				name: "İş Akışım",
				route: "developer.dashboard",
				icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4",
			},
			{
				name: "Zaman Kayıtlarım",
				route: "developer.timesheet",
				icon: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
			},
		);
	}

	// ANALİST & ŞEF MENÜSÜ (Seviye 3, 4)
	if (roleLevel.value === 3 || roleLevel.value === 4) {
		items.push(
			{
				name: "Analist Özeti",
				route: "analyst.dashboard",
				icon: "M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z",
			},
			{
				name: "Bekleyen Talepler",
				route: "tickets.index",
				icon: "M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10",
			},
			{
				name: "Görev Dağılımı",
				route: "tasks.board",
				icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
			},
		);
	}

	// YÖNETİCİ & ADMIN MENÜSÜ (Seviye 1, 2)
	if (roleLevel.value === 1 || roleLevel.value === 2) {
		items.push(
			{
				name: "Genel Bakış",
				route: "manager.dashboard",
				icon: "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
			},
			{
				name: "Proje Raporları",
				route: "reports.projects",
				icon: "M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z",
			},
			{
				name: "Kullanıcılar",
				route: "users.index",
				icon: "M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z",
			},
		);
	}

	return items;
});
</script>

<template>
	<aside
		class="hidden md:flex flex-col w-64 bg-slate-900 min-h-screen transition-all duration-300">
		<div class="h-20 flex items-center px-8 border-b border-slate-800">
			<div class="flex items-center gap-3">
				<div
					class="w-8 h-8 bg-indigo-500 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
					<svg
						class="w-5 h-5 text-white"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24">
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2.5"
							d="M13 10V3L4 14h7v7l9-11h-7z"></path>
					</svg>
				</div>
				<span class="text-white font-bold text-xl tracking-tight"
					>Güncel<span class="text-indigo-400">Task</span></span
				>
			</div>
		</div>

		<div class="flex-1 py-8 px-4 overflow-y-auto custom-scrollbar">
			<nav class="space-y-2">
				<Link
					v-for="item in menuItems"
					:key="item.route"
					:href="route(item.route)"
					class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all group"
					:class="
						route().current(item.route)
							? 'bg-indigo-600/10 text-indigo-400 font-semibold'
							: 'text-slate-400 hover:bg-slate-800 hover:text-slate-200'
					">
					<svg
						class="w-5 h-5 transition-transform group-hover:scale-110"
						:class="
							route().current(item.route)
								? 'text-indigo-400'
								: 'text-slate-500 group-hover:text-slate-300'
						"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24">
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							:d="item.icon"></path>
					</svg>
					{{ item.name }}
				</Link>
			</nav>
		</div>

		<div class="p-4 border-t border-slate-800">
			<div
				class="flex items-center justify-between gap-3 px-4 py-3 bg-slate-800/50 rounded-2xl border border-slate-700/50">
				<div class="flex items-center gap-3 min-w-0">
					<div
						class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 font-bold">
						{{
							user?.name
								?.split(" ")
								.map((n) => n[0])
								.join("")
								.substring(0, 2)
								.toUpperCase() || "U"
						}}
					</div>
					<div class="flex-1 min-w-0">
						<p class="text-sm font-semibold text-white truncate">
							{{ user?.name }}
						</p>
						<p class="text-xs text-slate-400 truncate capitalize">
							{{ user?.role?.name || "Kullanıcı" }}
						</p>
					</div>
				</div>

					<Link
						as="button"
						:href="route('logout')"
						method="post"
						type="button"
						@click.prevent="handleLogout"
						class="ml-auto inline-flex items-center justify-center w-9 h-9 rounded-full text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition-all duration-200 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-red-500/60"
					>
						<ArrowRightOnRectangleIcon class="w-5 h-5" />
					</Link>
			</div>
		</div>

		<nav class="space-y-1">
			<div v-if="user.role_level === 1" class="pt-4">
				<p
					class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">
					Sistem Yönetimi
				</p>
				<div class="mt-1 space-y-1">
					<Link
						:href="route('admin.users.index')"
						:class="[
							route().current('admin.users.*')
								? 'bg-slate-100 text-indigo-600'
								: 'text-slate-600 hover:bg-slate-50',
						]"
						class="group flex items-center px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200">
						<UsersIcon class="mr-3 h-5 w-5" />
						Kullanıcı Yönetimi
					</Link>

					<Link
						:href="route('admin.roles.index')"
						:class="[
							route().current('admin.roles.*')
								? 'bg-slate-100 text-indigo-600'
								: 'text-slate-600 hover:bg-slate-50',
						]"
						class="group flex items-center px-3 py-2 text-sm font-medium rounded-xl transition-all duration-200">
						<ShieldCheckIcon class="mr-3 h-5 w-5" />
						Rol ve İzinler
					</Link>
				</div>
			</div>
		</nav>

		<Transition
			enter-active-class="transition ease-out duration-200"
			enter-from-class="opacity-0 translate-y-2"
			enter-to-class="opacity-100 translate-y-0"
			leave-active-class="transition ease-in duration-150"
			leave-from-class="opacity-100 translate-y-0"
			leave-to-class="opacity-0 translate-y-2"
		>
			<div
				v-if="showLogoutToast"
				class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full bg-slate-900/95 border border-slate-700 text-xs font-medium text-slate-100 shadow-xl shadow-slate-900/60 flex items-center gap-2"
			>
				<svg
					class="w-4 h-4 text-amber-300"
					fill="none"
					stroke="currentColor"
					viewBox="0 0 24 24"
				>
					<path
						stroke-linecap="round"
						stroke-linejoin="round"
						stroke-width="2"
						d="M12 9v3m0 4h.01M10.29 3.86L3.82 18a1 1 0 00.9 1.42h14.56a1 1 0 00.9-1.42L13.71 3.86a1 1 0 00-1.82 0z"
					/>
				</svg>
				<span>Deneyiminizi puanlamayı unutmayın!</span>
			</div>
		</Transition>
	</aside>
</template>
