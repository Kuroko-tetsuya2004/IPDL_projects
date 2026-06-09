<script setup>
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { ChartBarIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })
const props = defineProps({ stats: Object, pubParMois: Array })
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
      <ChartBarIcon class="w-7 h-7 text-purple-400" />
      Statistiques du laboratoire
    </h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-white">{{ stats?.total_publications ?? 0 }}</div>
        <div class="text-slate-400 text-sm mt-1">Publications</div>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-emerald-400">{{ stats?.total_datasets ?? 0 }}</div>
        <div class="text-slate-400 text-sm mt-1">Datasets</div>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-brand-400">{{ stats?.total_chercheurs ?? 0 }}</div>
        <div class="text-slate-400 text-sm mt-1">Chercheurs</div>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-amber-400">{{ stats?.total_doctorants ?? 0 }}</div>
        <div class="text-slate-400 text-sm mt-1">Doctorants</div>
      </div>
    </div>

    <div v-if="pubParMois?.length > 0" class="bg-slate-900/60 border border-white/8 rounded-xl p-6">
      <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Publications par mois (12 derniers mois)</h2>
      <div class="space-y-2">
        <div v-for="item in pubParMois" :key="item.mois" class="flex items-center gap-3">
          <span class="text-xs text-slate-400 w-24 shrink-0">{{ item.mois }}</span>
          <div class="flex-1 bg-slate-800 rounded-full h-2 overflow-hidden">
            <div class="h-full bg-brand-600 rounded-full transition-all" :style="{ width: Math.min((item.total / Math.max(...pubParMois.map(p => p.total))) * 100, 100) + '%' }"></div>
          </div>
          <span class="text-xs text-white font-medium w-6 text-right">{{ item.total }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
