<script setup>
import { ref } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { CheckCircleIcon, XCircleIcon, ClockIcon, DocumentTextIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  publications: Object,  // paginated
  userRole: String,
})

const statutColor = {
  draft:     'bg-slate-400/10 text-slate-400 border-slate-400/20',
  pending:   'bg-amber-400/10 text-amber-400 border-amber-400/20',
  published: 'bg-emerald-400/10 text-emerald-400 border-emerald-400/20',
  rejected:  'bg-red-400/10 text-red-400 border-red-400/20',
  archived:  'bg-slate-500/10 text-slate-500 border-slate-500/20',
}
const statutLabel = {
  draft: 'Brouillon', pending: 'En attente', published: 'Publié',
  rejected: 'Rejeté', archived: 'Archivé',
}

const deletePublication = (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')) {
    router.delete(`/publications/${id}`)
  }
}

const isSyncing = ref(false)
const syncOrcid = () => {
  isSyncing.value = true
  router.post('/publications/sync-orcid', {}, {
    preserveScroll: true,
    onFinish: () => {
      isSyncing.value = false
    }
  })
}
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <DocumentTextIcon class="w-7 h-7 text-brand-400" />
        Mes publications
      </h1>
      <div class="flex items-center gap-3">
        <button @click="syncOrcid" :disabled="isSyncing"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-white/10 hover:border-brand-500/50 hover:bg-white/5 text-white text-sm font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed">
          <svg v-if="isSyncing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          {{ isSyncing ? 'Synchronisation...' : 'Synchroniser via ORCID' }}
        </button>
        <a href="/publications/soumettre"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition-all">
          + Nouvelle publication
        </a>
      </div>
    </div>

    <!-- Tableau -->
    <div class="bg-slate-900/60 border border-white/8 rounded-xl overflow-hidden">
      <div v-if="publications?.data?.length === 0" class="text-center py-16">
        <DocumentTextIcon class="w-12 h-12 text-slate-600 mx-auto mb-4" />
        <p class="text-slate-400">Vous n'avez pas encore de publications.</p>
        <a href="/publications/soumettre" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-sm font-semibold transition-all">
          Soumettre ma première publication
        </a>
      </div>

      <table v-else class="w-full">
        <thead class="border-b border-white/8">
          <tr>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Titre</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Type</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Statut</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="pub in publications.data" :key="pub.id" class="hover:bg-white/3 transition-colors">
            <td class="px-6 py-4">
              <a :href="`/publications/${pub.id}`" target="_blank" class="text-sm font-medium text-white hover:text-brand-400 transition-colors">
                {{ pub.titre_fr }}
              </a>
              <p v-if="pub.axe" class="text-xs text-slate-500 mt-0.5">{{ pub.axe?.code }}</p>
            </td>
            <td class="px-6 py-4">
              <span class="text-xs text-slate-400 capitalize">{{ pub.type }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="['text-xs font-medium px-2.5 py-0.5 rounded-full border', statutColor[pub.statut]]">
                {{ statutLabel[pub.statut] ?? pub.statut }}
              </span>
            </td>
            <td class="px-6 py-4 text-xs text-slate-400">
              {{ pub.date_publication ?? pub.created_at }}
            </td>
            <td class="px-6 py-4 text-right text-xs font-semibold space-x-3">
              <a :href="`/publications/${pub.id}`" target="_blank" class="text-emerald-400 hover:text-emerald-300 inline-flex items-center gap-1">
                Consulter
              </a>
              <Link :href="`/publications/${pub.id}/modifier`" class="text-brand-400 hover:text-brand-300 inline-flex items-center gap-1">
                <PencilIcon class="w-3.5 h-3.5" /> Modifier
              </Link>
              <button @click="deletePublication(pub.id)" class="text-red-400 hover:text-red-300 inline-flex items-center gap-1">
                <TrashIcon class="w-3.5 h-3.5" /> Supprimer
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="publications?.last_page > 1" class="flex items-center justify-between text-sm text-slate-400">
      <span>{{ publications.from }}–{{ publications.to }} sur {{ publications.total }}</span>
      <div class="flex gap-2">
        <a v-if="publications.prev_page_url" :href="publications.prev_page_url"
          class="px-3 py-1.5 rounded-lg border border-white/10 hover:border-brand-500/50 hover:text-white transition-all">← Précédent</a>
        <a v-if="publications.next_page_url" :href="publications.next_page_url"
          class="px-3 py-1.5 rounded-lg border border-white/10 hover:border-brand-500/50 hover:text-white transition-all">Suivant →</a>
      </div>
    </div>
  </div>
</template>
