<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { DocumentTextIcon, MagnifyingGlassIcon, FunnelIcon, TrashIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({ publications: Object, axes: Array, filters: Object, userRole: String })

const search = ref(props.filters?.q ?? '')
const statutFilter = ref(props.filters?.statut ?? '')
const axeFilter = ref(props.filters?.axe ?? '')

const doSearch = () => router.get('/admin/publications', { q: search.value, statut: statutFilter.value, axe: axeFilter.value }, { preserveState: true })

const statutColor = {
  draft: 'bg-slate-400/10 text-slate-400 border-slate-400/20',
  pending: 'bg-amber-400/10 text-amber-400 border-amber-400/20',
  published: 'bg-emerald-400/10 text-emerald-400 border-emerald-400/20',
  rejected: 'bg-red-400/10 text-red-400 border-red-400/20',
}

const isDeleteModalOpen = ref(false)
const selectedPublication = ref(null)
const deleteMotif = ref('')

const openDeleteModal = (pub) => {
  selectedPublication.value = pub
  deleteMotif.value = ''
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false
  selectedPublication.value = null
}

const submitDelete = () => {
  if (!deleteMotif.value || deleteMotif.value.trim().length < 5) {
    alert('Veuillez saisir un motif de suppression valide (au moins 5 caractères).')
    return
  }
  router.post(`/admin/publications/${selectedPublication.value.id}/proposer-suppression`, {
    motif: deleteMotif.value
  }, {
    onSuccess: () => {
      closeDeleteModal()
    }
  })
}
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
      <DocumentTextIcon class="w-7 h-7 text-emerald-400" />
      Publications — Modération
    </h1>

    <div class="flex gap-3 flex-wrap">
      <div class="relative flex-1 min-w-48">
        <MagnifyingGlassIcon class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input v-model="search" @keyup.enter="doSearch" placeholder="Chercher un titre..."
          class="w-full bg-slate-900/60 border border-white/10 rounded-lg pl-9 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
      </div>
      <select v-model="statutFilter" @change="doSearch" class="bg-slate-900/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
        <option value="">Tous les statuts</option>
        <option value="draft">Brouillon</option>
        <option value="pending">En attente</option>
        <option value="published">Publié</option>
        <option value="rejected">Rejeté</option>
      </select>
      <select v-model="axeFilter" @change="doSearch" class="bg-slate-900/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
        <option value="">Tous les axes</option>
        <option v-for="axe in axes" :key="axe.id" :value="axe.id">{{ axe.nom_fr }}</option>
      </select>
    </div>

    <div class="bg-slate-900/60 border border-white/8 rounded-xl overflow-hidden">
      <table class="w-full">
        <thead class="border-b border-white/8">
          <tr>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Titre</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Auteur</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Axe</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Statut</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
            <th v-if="userRole === 'super_admin'" class="px-6 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="pub in publications.data" :key="pub.id" class="hover:bg-white/3 transition-colors">
            <td class="px-6 py-4">
              <p class="text-sm font-medium text-white">{{ pub.titre_fr }}</p>
              <span class="text-xs text-slate-500 capitalize">{{ pub.type }}</span>
            </td>
            <td class="px-6 py-4 text-xs text-slate-400">
              {{ pub.auteur?.prenom }} {{ pub.auteur?.nom }}
            </td>
            <td class="px-6 py-4 text-xs text-slate-400">{{ pub.axe?.code ?? '—' }}</td>
            <td class="px-6 py-4">
              <span :class="['text-xs font-medium px-2.5 py-0.5 rounded-full border', statutColor[pub.statut] ?? '']">
                {{ pub.statut }}
              </span>
            </td>
            <td class="px-6 py-4 text-xs text-slate-500">{{ pub.created_at?.substring(0, 10) }}</td>
            <td v-if="userRole === 'super_admin'" class="px-6 py-4 text-right">
              <button @click="openDeleteModal(pub)" class="text-red-400 hover:text-red-300 transition-colors flex items-center gap-1 ml-auto text-xs font-medium bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 px-2.5 py-1 rounded-lg">
                <TrashIcon class="w-3.5 h-3.5" />
                Supprimer
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="publications.last_page > 1" class="flex justify-end gap-2">
      <a v-if="publications.prev_page_url" :href="publications.prev_page_url" class="px-3 py-1.5 rounded-lg border border-white/10 hover:border-brand-500/50 text-slate-400 hover:text-white text-sm transition-all">← Précédent</a>
      <a v-if="publications.next_page_url" :href="publications.next_page_url" class="px-3 py-1.5 rounded-lg border border-white/10 hover:border-brand-500/50 text-slate-400 hover:text-white text-sm transition-all">Suivant →</a>
    </div>

    <!-- Modal de motif de suppression -->
    <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
      <div class="w-full max-w-md bg-slate-900 border border-white/10 rounded-xl overflow-hidden shadow-2xl animate-scale-in">
        <div class="p-6 space-y-4">
          <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <TrashIcon class="w-5 h-5 text-red-500" />
            Proposer la suppression
          </h3>
          <p class="text-sm text-slate-400">
            Vous êtes sur le point de proposer la suppression de la publication <span class="text-white font-semibold">"{{ selectedPublication?.titre_fr }}"</span>.
          </p>
          <div class="space-y-2">
            <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Motif de suppression (requis)</label>
            <textarea v-model="deleteMotif" rows="4" placeholder="Veuillez saisir le motif qui sera envoyé aux chercheurs de l'axe pour le vote..."
              class="w-full bg-slate-950 border border-white/10 rounded-lg p-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-red-500/50 transition-all"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button @click="closeDeleteModal" class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">
              Annuler
            </button>
            <button @click="submitDelete" :disabled="!deleteMotif.trim() || deleteMotif.trim().length < 5"
              class="bg-red-600 hover:bg-red-500 disabled:opacity-50 disabled:hover:bg-red-600 text-white font-medium text-sm px-4 py-2 rounded-lg transition-colors shadow-lg shadow-red-600/20">
              Confirmer la proposition
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
