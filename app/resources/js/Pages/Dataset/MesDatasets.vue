<script setup>
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { CircleStackIcon, PlusIcon, CloudArrowDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { useForm, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineOptions({ layout: DashboardLayout })
const props = defineProps({ datasets: Array })

const user = computed(() => usePage().props.auth.user)

const importForm = useForm({
  doi: ''
})

const importDoi = () => {
  importForm.post('/datasets/import-doi', {
    preserveScroll: true,
    onSuccess: () => importForm.reset()
  })
}

const syncOrcid = () => {
  router.post('/datasets/sync-orcid', {}, {
    preserveScroll: true
  })
}

const statutColor = {
  draft: 'bg-slate-400/10 text-slate-400 border-slate-400/20',
  pending: 'bg-amber-400/10 text-amber-400 border-amber-400/20',
  published: 'bg-emerald-400/10 text-emerald-400 border-emerald-400/20',
}
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <CircleStackIcon class="w-7 h-7 text-purple-400" />
        Mes datasets
      </h1>
    </div>

    <!-- Panneau d'import DataCite -->
    <div class="bg-surface border border-border-strong rounded-xl p-5 shadow-sm">
      <div class="flex items-center gap-2 mb-4">
        <CloudArrowDownIcon class="w-5 h-5 text-primary-light" />
        <h2 class="font-semibold text-text">Importer depuis DataCite</h2>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Par DOI -->
        <div class="space-y-3">
          <label class="block text-sm font-medium text-text-muted">Import rapide via DOI</label>
          <form @submit.prevent="importDoi" class="flex gap-2">
            <input v-model="importForm.doi" type="text" class="input flex-1" placeholder="ex: 10.5281/zenodo.12345" required>
            <button type="submit" class="btn-primary" :disabled="importForm.processing">
              <CloudArrowDownIcon class="w-4 h-4" />
              <span v-if="importForm.processing">Import...</span>
              <span v-else>Importer</span>
            </button>
          </form>
          <p v-if="importForm.errors.doi" class="text-sm text-red-500">{{ importForm.errors.doi }}</p>
        </div>

        <!-- Par ORCID -->
        <div class="space-y-3 border-t lg:border-t-0 lg:border-l border-border-strong pt-4 lg:pt-0 lg:pl-6">
          <label class="block text-sm font-medium text-text-muted">Synchronisation ORCID</label>
          <div v-if="user?.orcid_id">
            <p class="text-xs text-text-subtle mb-3">Nous rechercherons tous vos datasets associés à votre ORCID ({{ user.orcid_id }}).</p>
            <button @click="syncOrcid" class="btn-secondary w-full justify-center">
              <CloudArrowDownIcon class="w-4 h-4" /> Synchroniser mes datasets
            </button>
          </div>
          <div v-else class="text-sm text-amber-500 bg-amber-500/10 p-3 rounded-lg border border-amber-500/20">
            Votre ORCID n'est pas renseigné. <a href="/profile" class="underline font-medium">Mettre à jour le profil</a>
          </div>
        </div>
      </div>
      
      <div class="mt-4 pt-4 border-t border-border-strong text-right">
        <a href="/datasets/externes" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary-light hover:underline">
          <MagnifyingGlassIcon class="w-4 h-4" /> Parcourir les datasets mondiaux
        </a>
      </div>
    </div>

    <div v-if="!datasets?.length" class="text-center py-16 bg-slate-900/60 border border-white/8 rounded-xl">
      <CircleStackIcon class="w-12 h-12 text-slate-600 mx-auto mb-4" />
      <p class="text-slate-400 mb-2">Vous n'avez pas encore de datasets.</p>
      <p class="text-slate-500 text-sm">Les datasets doivent avoir une licence avant publication (RG-007).</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="ds in datasets" :key="ds.id"
        class="bg-slate-900/60 border border-white/8 rounded-xl p-5 hover:border-purple-500/30 transition-all">
        <div class="flex items-start justify-between mb-3">
          <CircleStackIcon class="w-5 h-5 text-purple-400 shrink-0" />
          <span :class="['text-xs font-medium px-2.5 py-0.5 rounded-full border', statutColor[ds.statut] ?? '']">
            {{ ds.statut }}
          </span>
        </div>
        <h3 class="font-semibold text-white text-sm">{{ ds.titre_fr }}</h3>
        <p class="text-xs text-slate-500 mt-1">Créé le {{ ds.created_at?.substring(0, 10) }}</p>
      </div>
    </div>
  </div>
</template>
