<script setup>
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { CircleStackIcon, PlusIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })
const props = defineProps({ datasets: Array })

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
      <a href="/datasets/nouveau"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition-all">
        <PlusIcon class="w-4 h-4" /> Nouveau dataset
      </a>
    </div>

    <div v-if="!datasets?.length" class="text-center py-16 bg-slate-900/60 border border-white/8 rounded-xl">
      <CircleStackIcon class="w-12 h-12 text-slate-600 mx-auto mb-4" />
      <p class="text-slate-400 mb-2">Vous n'avez pas encore de datasets.</p>
      <p class="text-slate-500 text-sm">Les datasets doivent avoir une licence avant publication (RG-007).</p>
      <a href="/datasets/nouveau" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-sm font-semibold transition-all">
        Créer mon premier dataset
      </a>
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
