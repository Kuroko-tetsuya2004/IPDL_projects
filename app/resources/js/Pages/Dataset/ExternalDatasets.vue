<script setup>
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { 
  CloudArrowDownIcon, MagnifyingGlassIcon, ExclamationCircleIcon,
  DocumentTextIcon, ArrowLeftIcon, IdentificationIcon
} from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  datasets: Object,
  filters: Object,
})

const searchForm = useForm({
  q: props.filters.q || '',
  annee: props.filters.annee || '',
})

const fetchLiveForm = useForm({
  q: props.filters.q || ''
})

const importForm = useForm({
  doi: ''
})

// Lancer recherche locale
const searchLocal = () => {
  searchForm.get('/datasets/externes', { preserveState: true })
}

// Lancer fetch DataCite
const fetchLive = () => {
  if (!fetchLiveForm.q) return
  fetchLiveForm.post('/datasets/fetch-live', {
    preserveState: true,
    preserveScroll: true,
  })
}

// Importer un dataset
const importDataset = (doi) => {
  importForm.doi = doi
  importForm.post('/datasets/import-doi', {
    preserveScroll: true,
    onSuccess: () => {
      alert('Dataset importé avec succès dans votre profil.')
    }
  })
}
</script>

<template>
  <Head title="Recherche de Datasets" />

  <div class="p-6 space-y-6 max-w-7xl mx-auto animate-fade-in">
    <!-- Header -->
    <div class="flex items-center gap-4 border-b border-border-strong pb-4">
      <a href="/mes-datasets" class="btn-ghost !px-2 text-text-subtle hover:text-text">
        <ArrowLeftIcon class="w-5 h-5" />
      </a>
      <div>
        <h1 class="text-2xl font-bold text-text flex items-center gap-2">
          <CloudArrowDownIcon class="w-7 h-7 text-primary-light" />
          Datasets Mondiaux
        </h1>
        <p class="text-sm text-text-muted mt-1">
          Explorez et importez des jeux de données depuis DataCite (Zenodo, Dryad, Figshare...).
        </p>
      </div>
    </div>

    <!-- Actions Rapides & Recherche -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Recherche Locale -->
      <div class="lg:col-span-2 bg-surface border border-border-strong rounded-xl p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-text mb-3">Recherche dans l'index UMMISCO</h3>
        <form @submit.prevent="searchLocal" class="flex gap-2">
          <input v-model="searchForm.q" type="text" class="input flex-1" placeholder="Mots-clés, auteurs, titre...">
          <button type="submit" class="btn-secondary">
            <MagnifyingGlassIcon class="w-4 h-4" /> Chercher
          </button>
        </form>
        <p class="text-xs text-text-subtle mt-2">Recherche parmi les {{ datasets.total }} datasets déjà indexés localement.</p>
      </div>

      <!-- Recherche Live DataCite -->
      <div class="bg-primary-glow border border-primary/20 rounded-xl p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-primary-light mb-3 flex items-center gap-2">
          <CloudArrowDownIcon class="w-4 h-4" /> Fetch DataCite (Live)
        </h3>
        <form @submit.prevent="fetchLive" class="flex gap-2">
          <input v-model="fetchLiveForm.q" type="text" class="input flex-1 bg-surface" placeholder="Sujet, DOI..." required>
          <button type="submit" class="btn-primary" :disabled="fetchLiveForm.processing">
            <span v-if="fetchLiveForm.processing">Fetch...</span>
            <span v-else>Fetch</span>
          </button>
        </form>
        <p class="text-xs text-text-subtle mt-2 text-primary-light/80">
          Interroge l'API mondiale DataCite en temps réel.
        </p>
      </div>

    </div>

    <!-- Liste des résultats -->
    <div class="space-y-4">
      <div v-if="datasets.data.length === 0" class="text-center py-16 bg-surface border border-border-strong rounded-xl">
        <ExclamationCircleIcon class="w-12 h-12 text-text-subtle mx-auto mb-3" />
        <p class="text-text-muted">Aucun dataset trouvé pour cette recherche.</p>
        <button @click="fetchLive" class="mt-4 btn-primary text-sm">
          Essayer le Fetch Live sur DataCite
        </button>
      </div>

      <div v-for="ds in datasets.data" :key="ds.id" class="card p-5 flex flex-col md:flex-row gap-5">
        <div class="flex-1 space-y-3">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="badge-blue text-[0.65rem] uppercase tracking-wider">{{ ds.source }}</span>
            <span v-if="ds.annee" class="text-xs font-mono bg-bg-tertiary px-2 py-0.5 rounded text-text-muted border border-border">
              {{ ds.annee }}
            </span>
            <span v-if="ds.type_dataset" class="text-xs text-text-subtle capitalize">{{ ds.type_dataset }}</span>
          </div>

          <h3 class="text-lg font-bold text-text leading-tight">{{ ds.titre }}</h3>
          
          <div v-if="ds.auteurs" class="text-sm text-text-muted flex items-start gap-2">
            <IdentificationIcon class="w-4 h-4 mt-0.5 shrink-0" />
            <span>{{ Array.isArray(ds.auteurs) ? ds.auteurs.join(', ') : ds.auteurs }}</span>
          </div>

          <p v-if="ds.resume" class="text-sm text-text-subtle line-clamp-2 leading-relaxed">
            {{ ds.resume }}
          </p>

          <div class="flex items-center gap-3 text-xs text-text-muted pt-2">
            <span v-if="ds.doi" class="font-mono bg-surface-800 px-2 py-1 rounded">DOI: {{ ds.doi }}</span>
            <span v-if="ds.licence">{{ ds.licence }}</span>
          </div>
        </div>

        <div class="flex flex-col gap-2 shrink-0 justify-center md:border-l border-border-strong pl-5 md:w-48">
          <button @click="importDataset(ds.doi)" class="btn-primary w-full justify-center" :disabled="!ds.doi">
            Importer ce dataset
          </button>
          <a v-if="ds.doi" :href="`https://doi.org/${ds.doi}`" target="_blank" class="btn-secondary w-full justify-center">
            Voir la source
          </a>
        </div>
      </div>
    </div>

    <!-- Pagination (simplifiée pour l'exemple) -->
    <div v-if="datasets.links && datasets.data.length > 0" class="flex justify-center pt-4">
      <div class="flex gap-1">
        <template v-for="(link, idx) in datasets.links" :key="idx">
          <a v-if="link.url" :href="link.url" 
             class="px-3 py-1.5 text-sm rounded border transition-colors"
             :class="link.active ? 'bg-primary border-primary text-white' : 'bg-surface border-border-strong text-text hover:bg-bg-tertiary'"
             v-html="link.label">
          </a>
          <span v-else class="px-3 py-1.5 text-sm rounded border border-border-strong bg-bg-tertiary text-text-subtle" v-html="link.label"></span>
        </template>
      </div>
    </div>
  </div>
</template>
