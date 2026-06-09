<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { CircleStackIcon, MagnifyingGlassIcon, TrashIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  datasets: Object,  // paginated publications with type='dataset'
  axes: Array,
  filters: Object,
})

const search = ref(props.filters?.q ?? '')
const axeFilter = ref(props.filters?.axe ?? '')

const doSearch = () => {
  router.get('/admin/datasets', {
    q: search.value,
    axe: axeFilter.value
  }, { preserveState: true })
}

const deleteDataset = (id) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer définitivement ce dataset ?')) {
    router.delete(`/admin/datasets/${id}`)
  }
}
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
      <CircleStackIcon class="w-7 h-7 text-indigo-400" />
      Gestion des Datasets (Jeux de données)
    </h1>

    <div class="flex gap-3 flex-wrap">
      <div class="relative flex-1 min-w-48">
        <MagnifyingGlassIcon class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" />
        <input v-model="search" @keyup.enter="doSearch" placeholder="Rechercher par titre de dataset..."
          class="w-full bg-slate-900/60 border border-white/10 rounded-lg pl-9 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
      </div>
      <select v-model="axeFilter" @change="doSearch" class="bg-slate-900/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
        <option value="">Tous les axes</option>
        <option v-for="axe in axes" :key="axe.id" :value="axe.id">{{ axe.nom_fr }}</option>
      </select>
    </div>

    <div class="bg-slate-900/60 border border-white/8 rounded-xl overflow-hidden">
      <table class="w-full">
        <thead class="border-b border-white/8">
          <tr>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Titre / Description</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Auteur</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Axe</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Licence</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Taille (Mo)</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
            <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="db in datasets.data" :key="db.id" class="hover:bg-white/3 transition-colors">
            <td class="px-6 py-4">
              <p class="text-sm font-medium text-white">{{ db.titre_fr }}</p>
              <span class="text-xs text-slate-500">{{ db.dataset?.format_principal }} • Version {{ db.dataset?.version }}</span>
            </td>
            <td class="px-6 py-4 text-xs text-slate-400">
              {{ db.auteur?.prenom }} {{ db.auteur?.nom }}
            </td>
            <td class="px-6 py-4 text-xs text-slate-400">{{ db.axe?.code ?? '—' }}</td>
            <td class="px-6 py-4 text-xs text-slate-300">
              <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase font-mono text-[10px]">
                {{ db.dataset?.licence }}
              </span>
            </td>
            <td class="px-6 py-4 text-xs text-slate-400">
              {{ db.dataset?.taille_totale_mo ? db.dataset.taille_totale_mo + ' Mo' : '—' }}
            </td>
            <td class="px-6 py-4 text-xs text-slate-500">{{ db.created_at?.substring(0, 10) }}</td>
            <td class="px-6 py-4 text-right text-sm font-medium">
              <button @click="deleteDataset(db.id)" class="text-red-400 hover:text-red-300 inline-flex items-center gap-1">
                <TrashIcon class="w-4 h-4" /> Supprimer
              </button>
            </td>
          </tr>
          <tr v-if="datasets.data.length === 0">
            <td colspan="7" class="px-6 py-12 text-center text-slate-500 text-sm">
              Aucun dataset trouvé.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="datasets.last_page > 1" class="flex justify-end gap-2">
      <a v-if="datasets.prev_page_url" :href="datasets.prev_page_url" class="px-3 py-1.5 rounded-lg border border-white/10 hover:border-brand-500/50 text-slate-400 hover:text-white text-sm transition-all">← Précédent</a>
      <a v-if="datasets.next_page_url" :href="datasets.next_page_url" class="px-3 py-1.5 rounded-lg border border-white/10 hover:border-brand-500/50 text-slate-400 hover:text-white text-sm transition-all">Suivant →</a>
    </div>
  </div>
</template>
