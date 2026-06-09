<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { MagnifyingGlassIcon, FunnelIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  query: String,
  results: Object,
  total: Number,
  axes: Array,
  types: Object,
  filters: Object,
})

const search = ref(props.query ?? '')
const selectedType = ref(props.filters?.type ?? '')
const selectedAxe = ref(props.filters?.axe ?? '')

const doSearch = () => {
  router.get('/search', { q: search.value, type: selectedType.value, axe: selectedAxe.value }, { preserveState: true })
}

const statutTypeIcon = {
  article: '📄', news: '📰', event: '📅', thesis: '🎓',
  report: '📊', dataset: '🗃️', presentation: '🎤',
}
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-white">🔍 Recherche</h1>

    <!-- Barre de recherche -->
    <form @submit.prevent="doSearch" class="flex gap-3">
      <div class="relative flex-1">
        <MagnifyingGlassIcon class="w-5 h-5 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
        <input v-model="search" type="text" placeholder="Chercher des publications, thèses, événements..."
          class="w-full bg-slate-900/60 border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500/50 transition-all" />
      </div>
      <button type="submit" class="px-5 py-3 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-semibold transition-all">
        Rechercher
      </button>
    </form>

    <!-- Filtres -->
    <div class="flex flex-wrap gap-3">
      <select v-model="selectedType" @change="doSearch"
        class="bg-slate-900/60 border border-white/10 rounded-lg px-4 py-2 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
        <option value="">Tous les types</option>
        <option v-for="(label, val) in types" :key="val" :value="val">{{ label }}</option>
      </select>
      <select v-model="selectedAxe" @change="doSearch"
        class="bg-slate-900/60 border border-white/10 rounded-lg px-4 py-2 text-sm text-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
        <option value="">Tous les axes</option>
        <option v-for="axe in axes" :key="axe.id" :value="axe.id">{{ axe.nom_fr }}</option>
      </select>
    </div>

    <!-- Résultats -->
    <div v-if="query">
      <p class="text-slate-400 text-sm mb-4">
        <span class="text-white font-semibold">{{ total }}</span> résultat{{ total !== 1 ? 's' : '' }} pour
        <span class="text-brand-400">"{{ query }}"</span>
      </p>

      <div v-if="total === 0" class="text-center py-16 bg-slate-900/60 border border-white/8 rounded-xl">
        <MagnifyingGlassIcon class="w-12 h-12 text-slate-600 mx-auto mb-4" />
        <p class="text-slate-400">Aucun résultat trouvé pour cette recherche.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="pub in results.data" :key="pub.id"
          class="bg-slate-900/60 border border-white/8 rounded-xl p-5 hover:border-brand-500/30 transition-all group">
          <div class="flex items-start gap-3">
            <span class="text-2xl">{{ statutTypeIcon[pub.type] ?? '📄' }}</span>
            <div class="min-w-0 flex-1">
              <a :href="`/publications/${pub.id}`" class="font-semibold text-white group-hover:text-brand-400 transition-colors">
                {{ pub.titre_fr }}
              </a>
              <div class="flex flex-wrap items-center gap-2 mt-1">
                <span class="text-xs px-2 py-0.5 rounded bg-slate-800 border border-white/5 text-slate-400 capitalize">{{ pub.type }}</span>
                <span v-if="pub.axe" class="text-xs px-2 py-0.5 rounded border text-slate-400" :style="{ borderColor: pub.axe.couleur_hex + '40', color: pub.axe.couleur_hex }">
                  {{ pub.axe.code }}
                </span>
                <span class="text-xs text-slate-500">{{ pub.auteur?.prenom }} {{ pub.auteur?.nom }}</span>
                <span v-if="pub.date_publication" class="text-xs text-slate-500">· {{ pub.date_publication }}</span>
              </div>
              <p v-if="pub.resume_fr" class="text-xs text-slate-400 mt-2 line-clamp-2">
                {{ pub.resume_fr }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- État initial -->
    <div v-else class="text-center py-16 bg-slate-900/60 border border-white/8 rounded-xl">
      <MagnifyingGlassIcon class="w-16 h-16 text-slate-700 mx-auto mb-4" />
      <p class="text-slate-400">Entrez un terme pour rechercher dans les publications.</p>
    </div>
  </div>
</template>
