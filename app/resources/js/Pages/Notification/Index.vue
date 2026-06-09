<script setup>
import { router, Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import {
  BellIcon, CheckCircleIcon, ClipboardDocumentCheckIcon,
  ArrowRightIcon
} from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })
const props = defineProps({
  notifications: Object,
  unreadCount: Number,
  pendingValidations: Array,
  pendingVotes: Array,
})

const markAllRead = () => router.post('/notifications/read-all')
const markRead = (id) => router.post(`/notifications/${id}/read`)

const typeIcon = { info: 'ℹ️', success: '✅', warning: '⚠️', error: '❌' }

const typeLabel = {
  article:      'Article',
  document:     'Recherche en cours',
  event:        'Événement',
  dataset:      'Dataset',
  news:         'Actualité',
  thesis:       'Thèse',
  report:       'Rapport',
  presentation: 'Présentation',
}
</script>

<template>
  <div class="p-6 space-y-8 animate-fade-in max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-white/5 pb-4">
      <h1 class="text-3xl font-extrabold text-white flex items-center gap-3 tracking-tight">
        <BellIcon class="w-8 h-8 text-brand-400" />
        Notifications
        <span v-if="unreadCount > 0" class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-brand-500/20 text-brand-400 border border-brand-500/30">
          {{ unreadCount }}
        </span>
      </h1>
      <button v-if="notifications?.data?.some(n => !n.lu_at)" @click="markAllRead"
        class="text-sm font-semibold text-slate-400 hover:text-white flex items-center gap-1.5 transition-colors">
        <CheckCircleIcon class="w-4 h-4" /> Tout marquer comme lu
      </button>
    </div>

    <!-- Actions Requises (Valider Soumissions / Voter Suppression) -->
    <div v-if="(pendingValidations && pendingValidations.length > 0) || (pendingVotes && pendingVotes.length > 0)" class="space-y-4">
      <h2 class="text-xs font-bold uppercase tracking-wider text-rose-400 flex items-center gap-2">
        <span class="relative flex h-2 w-2">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
        </span>
        Actions requises / Tâches urgentes
      </h2>

      <div class="grid grid-cols-1 gap-4">
        <!-- Validation Soumission -->
        <div v-for="v in pendingValidations" :key="'val-' + v.id"
          class="p-5 rounded-xl border border-amber-500/30 bg-amber-500/5 hover:bg-amber-500/10 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="space-y-1.5">
            <div class="flex items-center flex-wrap gap-2">
              <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 text-[10px] uppercase font-bold border border-amber-500/30">
                Validation Requise
              </span>
              <span class="text-slate-400 text-xs uppercase font-medium">
                {{ typeLabel[v.type] ?? v.type }}
              </span>
            </div>
            <h3 class="text-base font-bold text-white">« {{ v.titre }} »</h3>
            <p class="text-xs text-slate-400">
              Soumis par <span class="text-slate-300 font-semibold">{{ v.auteur }}</span>
              · Le {{ new Date(v.created_at).toLocaleDateString() }}
            </p>
          </div>
          <Link :href="'/publications/' + v.publication_id"
            class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold transition-all hover:-translate-y-0.5 hover:shadow-glow-amber">
            Inspecter la soumission <ArrowRightIcon class="w-4 h-4" />
          </Link>
        </div>

        <!-- Vote De Suppression -->
        <div v-for="vt in pendingVotes" :key="'vote-' + vt.id"
          class="p-5 rounded-xl border border-red-500/30 bg-red-500/5 hover:bg-red-500/10 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="space-y-1.5">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 text-[10px] uppercase font-bold border border-red-500/30">
                Vote de Suppression
              </span>
            </div>
            <h3 class="text-base font-bold text-white">« {{ vt.titre }} »</h3>
            <p class="text-xs text-slate-400">
              Proposé par <span class="text-slate-300 font-semibold">{{ vt.propose_par_nom }}</span> · Le {{ new Date(vt.created_at).toLocaleDateString() }}
            </p>
            <div class="mt-2 text-xs bg-slate-950/40 border border-white/5 p-3 rounded-lg text-slate-300 italic max-w-xl">
              <strong>Motif :</strong> "{{ vt.motif }}"
            </div>
          </div>
          <Link href="/dashboard"
            class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition-all hover:-translate-y-0.5 hover:shadow-glow-red">
            Voter sur le dashboard <ArrowRightIcon class="w-4 h-4" />
          </Link>
        </div>
      </div>
    </div>

    <!-- Notifications Standard -->
    <div class="space-y-4">
      <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
        Historique des notifications
      </h2>

      <div class="bg-slate-900/60 border border-white/8 rounded-xl overflow-hidden shadow-lg">
        <div v-if="!notifications?.data?.length" class="text-center py-16">
          <BellIcon class="w-12 h-12 text-slate-600 mx-auto mb-4" />
          <p class="text-slate-400 text-sm">Aucune notification reçue pour l'instant.</p>
        </div>

        <div v-else class="divide-y divide-white/5">
          <div v-for="n in notifications.data" :key="n.id"
            :class="['px-6 py-4 flex items-start gap-4 transition-colors', !n.lu_at ? 'bg-brand-950/10 hover:bg-brand-950/20' : 'hover:bg-white/2']">
            <span class="text-xl shrink-0 mt-0.5">{{ typeIcon[n.type] ?? 'ℹ️' }}</span>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-white flex items-center gap-2">
                {{ n.titre }}
                <span v-if="!n.lu_at" class="w-2 h-2 rounded-full bg-brand-500 inline-block"></span>
              </p>
              <p class="text-xs text-slate-400 mt-1 leading-relaxed">{{ n.message }}</p>
              <p class="text-[10px] text-slate-500 mt-2 flex items-center gap-1">
                Le {{ new Date(n.created_at).toLocaleString() }}
              </p>
            </div>
            <button v-if="!n.lu_at" @click="markRead(n.id)"
              class="shrink-0 p-1.5 rounded-lg bg-brand-500/10 hover:bg-brand-500/20 text-brand-400 border border-brand-500/20 transition-all"
              title="Marquer comme lu">
              <CheckCircleIcon class="w-4 h-4" />
            </button>
            <CheckCircleIcon v-else class="w-4 h-4 text-slate-700 shrink-0" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
