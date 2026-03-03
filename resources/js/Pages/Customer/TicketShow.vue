<script setup>
import { computed, ref, nextTick, onMounted, watch } from "vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import CsatSurveyModal from "@/Components/CsatSurveyModal.vue";
import GlassCard from "@/Components/GlassCard.vue";
import EnergyButton from "@/Components/EnergyButton.vue";

const props = defineProps({
	ticket: {
		type: Object,
		required: true,
	},
});

const showCsatModal = ref(false);
const resolving = ref(false);

const getStatusConfig = (status) => {
	const configs = {
		opened: { text: "Açık", color: "bg-slate-100 text-slate-700" },
		in_progress: { text: "İşlemde", color: "bg-amber-100 text-amber-700" },
		waiting_customer: {
			text: "Yanıt Bekliyor",
			color: "bg-purple-100 text-purple-700",
		},
		resolved: { text: "Çözüldü", color: "bg-emerald-100 text-emerald-700" },
	};
	return (
		configs[status] || {
			text: "Bilinmiyor",
			color: "bg-gray-100 text-gray-700",
		}
	);
};

const formatDate = (dateString) => {
	return dateString
		? new Date(dateString).toLocaleDateString("tr-TR", {
				day: "numeric",
				month: "short",
				year: "numeric",
			})
		: "";
};

const user = computed(() => usePage().props.auth.user);

const isOwner = computed(() => {
	if (!user.value || !props.ticket) return false;
	return props.ticket.customer_id === user.value.id;
});

const isResolved = computed(() => props.ticket?.status === "resolved");

const publicComments = computed(() => {
	if (!props.ticket.comments) return [];
	return [...props.ticket.comments].sort(
		(a, b) => new Date(a.created_at) - new Date(b.created_at),
	);
});

const isSupportedImage = (url) => {
	if (!url) return false;
	return /\.(jpe?g|png|webp)$/i.test(url.split("?")[0]);
};

const getFileName = (url) => {
	if (!url) return "";
	return url.split("/").pop().split("?")[0];
};

const commentForm = useForm({
	body: "",
	attachment: null,
});

const attachmentPreview = ref(null);
const attachmentInput = ref(null);
const scrollContainer = ref(null);

const handleAttachmentChange = (event) => {
	const file = event.target.files[0];
	if (!file) return;
	commentForm.attachment = file;
	attachmentPreview.value = URL.createObjectURL(file);
};

const resetCommentForm = () => {
	commentForm.reset("body", "attachment");
	attachmentPreview.value = null;
	if (attachmentInput.value) {
		attachmentInput.value.value = "";
	}
};

const scrollToBottom = async () => {
	await nextTick();
	if (scrollContainer.value) {
		scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
	}
};

const submitComment = () => {
	if (!props.ticket?.id) return;

	commentForm.post(route("tickets.comments.store", props.ticket.id), {
		preserveScroll: true,
		onSuccess: () => {
			resetCommentForm();
			scrollToBottom();
		},
	});
};

const startResolveFlow = () => {
	if (!props.ticket?.id || resolving.value) return;
	resolving.value = true;

	router.put(
		route("tickets.resolve", props.ticket.id),
		{},
		{
			preserveScroll: true,
			onSuccess: () => {
				showCsatModal.value = true;
			},
			onFinish: () => {
				resolving.value = false;
			},
		},
	);
};

const lightboxImage = ref(null);

const openLightbox = (url) => {
	lightboxImage.value = url;
};

const closeLightbox = () => {
	lightboxImage.value = null;
};

const viewFullImage = (url) => {
	if (!url) return;
	openLightbox(url);
};

onMounted(() => {
	scrollToBottom();
});

watch(
	() => props.ticket.comments,
	() => {
		scrollToBottom();
	},
	{ deep: true },
);
</script>

<template>
	<Head :title="ticket.title || 'Talep Detayı'" />

	<AppLayout>
		<div class="py-6 sm:py-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="mb-6">
				<Link
					:href="route('customer.dashboard')"
					class="text-sm font-semibold text-slate-500 hover:text-indigo-600 flex items-center gap-1">
					<svg
						class="w-4 h-4"
						fill="none"
						stroke="currentColor"
						viewBox="0 0 24 24">
						<path
							stroke-linecap="round"
							stroke-linejoin="round"
							stroke-width="2"
							d="M15 19l-7-7 7-7"></path>
					</svg>
					Taleplerime Dön
				</Link>
			</div>

			<div
				class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
				<div
					class="p-6 sm:p-8 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
					<div class="flex items-center gap-3">
						<span
							class="text-xs font-bold text-slate-400 uppercase tracking-wider bg-slate-50 px-2 py-1 rounded-lg">
							{{ ticket.project?.name || "—" }}
						</span>
						<span
							class="px-3 py-1 rounded-full text-xs font-bold"
							:class="getStatusConfig(ticket.status).color">
							{{ getStatusConfig(ticket.status).text }}
						</span>
					</div>

					<div v-if="!isResolved && isOwner" class="flex items-center gap-2">
						<EnergyButton
							:loading="resolving"
							:disabled="resolving"
							size="sm"
							label="Sorunum Çözüldü, Talebi Kapat"
							@click="startResolveFlow" />
					</div>
				</div>

				<div class="p-6 sm:p-8">
					<h1 class="text-xl sm:text-2xl font-bold text-slate-800 mb-2">
						{{ ticket.title }}
					</h1>

					<div class="mb-4">
						<GlassCard
							:description="ticket.description"
							:image-url="ticket.attachment_url || ticket.first_media_url"
							@image-click="viewFullImage" />
					</div>

					<div
						class="text-xs text-slate-400 font-medium flex items-center gap-1.5 mb-6">
						<svg
							class="w-4 h-4"
							fill="none"
							stroke="currentColor"
							viewBox="0 0 24 24">
							<path
								stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
						</svg>
						{{ formatDate(ticket.created_at) }}
					</div>

					<!-- Mesajlaşma Alanı -->
					<div class="mt-8 border-t border-slate-100 pt-6">
						<h2 class="text-sm font-semibold text-slate-700 mb-3">
							Mesajlaşma Geçmişi
						</h2>

						<div
							ref="scrollContainer"
							class="space-y-3 max-h-[500px] overflow-y-auto pr-1 custom-scrollbar scrollbar-thin scrollbar-thumb-slate-200">
							<div
								v-for="comment in publicComments"
								:key="comment.id"
								class="flex"
								:class="comment.is_me ? 'justify-end' : 'justify-start'">
								<div
									class="group max-w-[80%] rounded-2xl px-3 py-2 text-sm shadow-sm space-y-2"
									:class="
										comment.is_me
											? 'bg-indigo-600 text-white rounded-br-sm'
											: 'bg-white text-slate-800 rounded-bl-sm border border-slate-100'
									">
									<div
										v-if="
											comment.attachment_url &&
											isSupportedImage(comment.attachment_url)
										"
										class="relative mb-1 cursor-pointer"
										@click="viewFullImage(comment.attachment_url)">
										<img
											:src="comment.attachment_url"
											alt="Mesaj eki"
											class="w-48 h-32 object-cover rounded-xl border-2 border-white shadow-sm"
											loading="lazy"
											@load="scrollToBottom" />
										<div
											class="absolute inset-0 rounded-xl bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
											<svg
												class="w-6 h-6 text-white"
												fill="none"
												stroke="currentColor"
												viewBox="0 0 24 24">
												<path
													stroke-linecap="round"
													stroke-linejoin="round"
													stroke-width="2"
													d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" />
											</svg>
										</div>
									</div>
									<p class="whitespace-pre-wrap break-words">
										{{ comment.body }}
									</p>

									<div
										v-if="
											comment.attachment_url &&
											isSupportedImage(comment.attachment_url)
										"
										class="mt-1">
										<span
											class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium"
											:class="
												comment.is_me
													? 'bg-indigo-500/20 text-indigo-100 border border-indigo-300/40'
													: 'bg-slate-100 text-slate-600 border border-slate-200'
											">
											1 Görsel Eklendi
										</span>
									</div>

									<div
										v-else-if="comment.attachment_url"
										class="mt-1 text-[11px] text-slate-500">
										Dosya: {{ getFileName(comment.attachment_url) }}
									</div>

									<p class="mt-1 text-[10px] opacity-70 text-right">
										{{ formatDate(comment.created_at) }}
									</p>
								</div>
							</div>

							<div
								v-if="publicComments.length === 0"
								class="text-xs text-slate-400 text-center mt-4">
								Henüz bu talep için bir mesajlaşma geçmişi bulunmuyor.
							</div>
						</div>

						<!-- Mesaj formu -->
						<div class="mt-4">
							<div
								v-if="isResolved"
								class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-700 flex items-center gap-2">
								<svg
									class="w-4 h-4 text-emerald-500"
									fill="none"
									stroke="currentColor"
									viewBox="0 0 24 24">
									<path
										stroke-linecap="round"
										stroke-linejoin="round"
										stroke-width="2"
										d="M5 13l4 4L19 7" />
								</svg>
								Bu talep çözüldü. Yeni mesaj gönderemezsiniz.
							</div>

							<form
								v-else
								@submit.prevent="submitComment"
								class="flex flex-col gap-2">
								<div
									v-if="attachmentPreview"
									class="inline-flex items-center gap-2 text-[11px] text-slate-600 bg-slate-100 px-2 py-1 rounded-full border border-slate-200 w-fit">
									<img
										:src="attachmentPreview"
										alt="Önizleme"
										class="w-8 h-8 rounded-md object-cover border border-slate-200" />
									<button
										type="button"
										class="text-slate-400 hover:text-slate-700"
										@click="resetCommentForm">
										×
									</button>
								</div>

								<div
									class="flex items-center gap-2 bg-slate-100 rounded-2xl border border-slate-200 px-2 py-1">
									<button
										type="button"
										class="inline-flex items-center justify-center w-8 h-8 rounded-full hover:bg-slate-200 text-indigo-500 transition-colors"
										@click="attachmentInput?.click()">
										<svg
											class="w-4 h-4"
											fill="none"
											stroke="currentColor"
											viewBox="0 0 24 24">
											<path
												stroke-linecap="round"
												stroke-linejoin="round"
												stroke-width="2"
												d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828a4 4 0 00-5.657-5.657L6.343 10.17" />
										</svg>
									</button>

									<input
										ref="attachmentInput"
										type="file"
										class="hidden"
										accept=".jpg,.jpeg,.png,.webp"
										@change="handleAttachmentChange" />

									<textarea
										v-model="commentForm.body"
										rows="1"
										placeholder="Destek ekibine mesaj yazın..."
										class="flex-1 bg-transparent border-0 text-sm text-slate-800 placeholder:text-slate-400 focus:ring-0 focus:outline-none resize-none" />

									<button
										type="submit"
										:disabled="
											commentForm.processing ||
											(!commentForm.body && !commentForm.attachment)
										"
										class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-600 text-white shadow-md hover:bg-indigo-500 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:hover:translate-y-0">
										<svg
											class="w-4 h-4 transform -rotate-90"
											fill="none"
											stroke="currentColor"
											viewBox="0 0 24 24">
											<path
												stroke-linecap="round"
												stroke-linejoin="round"
												stroke-width="2"
												d="M5 12h14M12 5l7 7-7 7" />
										</svg>
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<CsatSurveyModal
			:show="showCsatModal"
			:ticket="ticket"
			@close="showCsatModal = false" />

		<!-- Lightbox -->
		<div
			v-if="lightboxImage"
			class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center"
			@click.self="closeLightbox">
			<img
				:src="lightboxImage"
				alt="Önizleme"
				class="max-w-[90vw] max-h-[90vh] rounded-2xl object-contain shadow-2xl" />
		</div>
	</AppLayout>
</template>
