<script setup>
import { ref, computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import {
  ArrowPathIcon, CloudArrowDownIcon, ChartBarIcon,
  CheckCircleIcon, ClockIcon, ExclamationTriangleIcon,
  BeakerIcon, GlobeAltIcon, NewspaperIcon, BookOpenIcon
} from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  stats:     Object,
  importLog: String,
})

const form = useForm({
  query:  'UMMISCO Dakar IRD',
  source: 'all',
  limit:  50,
})

const running = ref(false)

function runImport() {
  running.value = true
  form.post('/admin/import/run', {
    onFinish: () => { running.value = false },
  })
}

const sourceIcons = {
  semantic_scholar: BeakerIcon,
  openalex:         GlobeAltIcon,
  arxiv:            NewspaperIcon,
  crossref:         BookOpenIcon,
}
const sourceLabels = {
  semantic_scholar: 'Semantic Scholar',
  openalex:         'OpenAlex',
  arxiv:            'arXiv',
  crossref:         'CrossRef',
}
const sourceColors = {
  semantic_scholar: 'text-blue-400',
  openalex:         'text-violet-400',
  arxiv:            'text-orange-400',
  crossref:         'text-red-400',
}

function formatDate(dateStr) {
  if (!dateStr) return 'Jamais'
  return new Date(dateStr).toLocaleString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}
</script>

<template>
  <div class="p-6 max-w-6xl mx-auto space-y-6 animate-fade-in">

    <!-- En-tête -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-white flex items-center gap-3">
          <CloudArrowDownIcon class="w-7 h-7 text-blue-400" />
          Supervision des imports scientifiques
        </h1>
        <p class="text-slate-400 text-sm mt-1">
          Import automatique nuit à 02h00 depuis Semantic Scholar, OpenAlex et arXiv.
          <span class="text-blue-400 font-medium">Lecture seule</span> — les articles restent sur leurs plateformes d'origine.
        </p>
      </div>
      <Link href="/publications/externes"
        class="flex items-center gap-2 px-4 py-2 border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 rounded-xl text-sm font-semibold transition-all">
        <BookOpenIcon class="w-4 h-4" />
        Voir le portail public
      </Link>
    </div>

    <!-- KPI Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4 text-center">
        <p class="text-3xl font-extrabold text-white">{{ stats?.total?.toLocaleString('fr-FR') ?? '0' }}</p>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mt-1">Articles indexés</p>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4 text-center">
        <p class="text-3xl font-extrabold text-emerald-400">{{ stats?.disponible?.toLocaleString('fr-FR') ?? '0' }}</p>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mt-1">Disponibles</p>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4 text-center">
        <p class="text-3xl font-extrabold text-blue-400">{{ stats?.par_source?.length ?? 0 }}</p>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mt-1">Sources actives</p>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4 text-center">
        <p class="text-sm font-bold text-amber-400">{{ formatDate(stats?.dernier_import) }}</p>
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mt-1">Dernier import</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- ─── Import manuel ─── -->
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="font-bold text-white flex items-center gap-2">
          <ArrowPathIcon class="w-5 h-5 text-blue-400" />
          Lancer un import manuel
        </h2>

        <div v-if="$page.props.flash?.success"
          class="flex items-start gap-3 p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-sm text-emerald-300">
          <CheckCircleIcon class="w-5 h-5 shrink-0 mt-0.5" />
          {{ $page.props.flash.success }}
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Requête de recherche</label>
          <input v-model="form.query" type="text"
            placeholder="Ex: UMMISCO Dakar IRD, epidemiology model Senegal…"
            class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          <p v-if="form.errors.query" class="text-red-400 text-xs mt-1">{{ form.errors.query }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Source</label>
            <select v-model="form.source"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
              <option value="all">Toutes les sources</option>
              <option value="semantic_scholar">Semantic Scholar</option>
              <option value="openalex">OpenAlex</option>
              <option value="arxiv">arXiv</option>
              <option value="crossref">CrossRef</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Limite par source</label>
            <input v-model="form.limit" type="number" min="5" max="200"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
        </div>

        <div class="bg-blue-500/5 border border-blue-500/20 rounded-lg p-3 text-xs text-blue-300">
          ℹ️ L'import automatique se lance chaque nuit à 02h00 avec les requêtes thématiques UMMISCO par défaut.
          Utilisez ce formulaire pour un import ciblé immédiat.
        </div>

        <button @click="runImport" :disabled="running || form.processing"
          class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5">
          <ArrowPathIcon class="w-5 h-5" :class="running ? 'animate-spin' : ''" />
          {{ running ? 'Import en cours...' : 'Lancer l\'import maintenant' }}
        </button>
      </div>

      <!-- ─── Stats par source ─── -->
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="font-bold text-white flex items-center gap-2">
          <ChartBarIcon class="w-5 h-5 text-violet-400" />
          État par source
        </h2>

        <div v-if="stats?.par_source?.length > 0" class="space-y-3">
          <div v-for="src in stats.par_source" :key="src.source"
            class="flex items-center gap-4 p-4 bg-slate-800/40 rounded-xl border border-white/5">
            <component :is="sourceIcons[src.source] ?? BookOpenIcon"
              class="w-8 h-8 shrink-0" :class="sourceColors[src.source] ?? 'text-slate-400'" />
            <div class="flex-1">
              <p class="font-bold text-white text-sm">{{ sourceLabels[src.source] ?? src.source }}</p>
              <p class="text-slate-500 text-xs">Dernier fetch : {{ formatDate(src.last_fetch) }}</p>
            </div>
            <div class="text-right">
              <p class="text-xl font-extrabold text-white">{{ src.total?.toLocaleString('fr-FR') }}</p>
              <p class="text-[10px] text-slate-500 uppercase">articles</p>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8 text-slate-600">
          <CloudArrowDownIcon class="w-10 h-10 mx-auto mb-2 opacity-30" />
          <p class="text-sm">Aucun import encore effectué</p>
          <p class="text-xs mt-1">Lancez le premier import ci-contre</p>
        </div>
      </div>
    </div>

    <!-- ─── Logs d'import ─── -->
    <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6">
      <h2 class="font-bold text-white flex items-center gap-2 mb-4">
        <ClockIcon class="w-5 h-5 text-amber-400" />
        Logs d'import (50 dernières lignes)
      </h2>
      <pre class="bg-black/60 rounded-xl border border-white/5 p-4 text-xs text-green-400 font-mono overflow-auto max-h-64 leading-relaxed whitespace-pre-wrap">{{ importLog }}</pre>
    </div>

    <!-- ─── Derniers articles importés ─── -->
    <div v-if="stats?.derniers?.length > 0" class="bg-slate-900/60 border border-white/8 rounded-xl p-6">
      <h2 class="font-bold text-white flex items-center gap-2 mb-4">
        <BookOpenIcon class="w-5 h-5 text-emerald-400" />
        Derniers articles indexés
      </h2>
      <div class="space-y-2">
        <div v-for="article in stats.derniers" :key="article.id"
          class="flex items-center gap-3 p-3 bg-slate-800/30 rounded-lg border border-white/5">
          <span class="text-[10px] font-bold px-2 py-1 rounded-full border"
            :class="sourceColors[article.source] ?? 'text-slate-400'">
            {{ sourceLabels[article.source] ?? article.source }}
          </span>
          <p class="text-sm text-slate-300 flex-1 truncate">{{ article.titre }}</p>
          <span v-if="article.annee" class="text-xs text-slate-500 shrink-0">{{ article.annee }}</span>
          <span v-if="article.pdf_url" class="text-[10px] text-emerald-400 font-semibold shrink-0">PDF ✓</span>
        </div>
      </div>
    </div>

  </div>
</template>
