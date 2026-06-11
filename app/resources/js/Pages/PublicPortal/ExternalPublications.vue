<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import {
  MagnifyingGlassIcon, BookOpenIcon, ArrowTopRightOnSquareIcon,
  DocumentArrowDownIcon, AdjustmentsHorizontalIcon, FunnelIcon,
  GlobeAltIcon, BeakerIcon, NewspaperIcon, ChevronRightIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  articles:       Object,
  filters:        Object,
  availableYears: Array,
  sourceStats:    Object,
})

const searchInput = ref(props.filters?.query ?? '')
const selectedSource = ref(props.filters?.source ?? '')
const selectedYear   = ref(props.filters?.annee ?? '')

// Debounce la recherche
let debounceTimer = null
watch([searchInput, selectedSource, selectedYear], () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => applyFilters(), 400)
})

function applyFilters() {
  router.get('/publications/externes', {
    q:      searchInput.value,
    source: selectedSource.value,
    annee:  selectedYear.value,
  }, { preserveScroll: true, replace: true })
}

const sourceColors = {
  semantic_scholar: { bg: 'bg-blue-500/10 border-blue-500/20',  text: 'text-blue-400',  label: 'Semantic Scholar' },
  openalex:         { bg: 'bg-violet-500/10 border-violet-500/20', text: 'text-violet-400', label: 'OpenAlex' },
  arxiv:            { bg: 'bg-orange-500/10 border-orange-500/20', text: 'text-orange-400', label: 'arXiv' },
}

function getSourceStyle(source) {
  return sourceColors[source] ?? { bg: 'bg-slate-500/10 border-slate-500/20', text: 'text-slate-400', label: source }
}

function externalUrl(article) {
  switch (article.source) {
    case 'semantic_scholar': return `https://www.semanticscholar.org/paper/${article.external_id}`
    case 'openalex':         return `https://openalex.org/works/${article.external_id}`
    case 'arxiv':            return `https://arxiv.org/abs/${article.external_id}`
    default: return article.doi ? `https://doi.org/${article.doi}` : null
  }
}

function parseAuteurs(auteurs) {
  try { return JSON.parse(auteurs ?? '[]') } catch { return [] }
}
</script>

<template>
  <Head title="Publications scientifiques externes — UMMISCO" />

  <div class="min-h-screen bg-surface-900 text-white pt-24 pb-16">

    <!-- Hero -->
    <div class="relative overflow-hidden border-b border-white/5 bg-gradient-to-br from-surface-900 via-slate-900 to-blue-950">
      <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 50%, #0ea5e9 0%, transparent 60%), radial-gradient(circle at 70% 30%, #6366f1 0%, transparent 50%);"></div>
      <div class="relative max-w-6xl mx-auto px-6 py-16">
        <div class="flex items-center gap-3 mb-4">
          <BookOpenIcon class="w-8 h-8 text-blue-400" />
          <h1 class="text-3xl font-extrabold tracking-tight">Publications Scientifiques</h1>
        </div>
        <p class="text-slate-300 text-lg max-w-2xl mb-2">
          Accédez aux travaux scientifiques issus de <span class="text-white font-semibold">Semantic Scholar</span>,
          <span class="text-white font-semibold">OpenAlex</span> et <span class="text-white font-semibold">arXiv</span>.
        </p>
        <p class="text-slate-500 text-sm">
          ℹ️ Ces articles proviennent de plateformes tierces — lecture seule, mises à jour automatiquement chaque nuit.
        </p>

        <!-- Stats par source -->
        <div class="flex flex-wrap gap-3 mt-6">
          <div v-for="(count, src) in sourceStats" :key="src"
            class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-semibold"
            :class="getSourceStyle(src).bg + ' ' + getSourceStyle(src).text + ' border-' + src">
            {{ getSourceStyle(src).label }} · {{ count?.toLocaleString('fr-FR') }} articles
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 py-10">
      <div class="flex flex-col lg:flex-row gap-8">

        <!-- ─── FILTRES (sidebar) ─── -->
        <aside class="lg:w-64 shrink-0 space-y-5">
          <!-- Recherche -->
          <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 flex items-center gap-2">
              <MagnifyingGlassIcon class="w-3.5 h-3.5" /> Recherche
            </label>
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
              <input v-model="searchInput" type="text" placeholder="Titre, auteur, mot-clé…"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg pl-9 pr-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>

          <!-- Source -->
          <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 block">Source</label>
            <div class="space-y-2">
              <button @click="selectedSource = ''"
                class="w-full text-left text-sm px-3 py-2 rounded-lg transition-all"
                :class="selectedSource === '' ? 'bg-blue-600/30 text-blue-300 font-semibold' : 'text-slate-400 hover:bg-white/5'">
                Toutes les sources
              </button>
              <button v-for="src in ['semantic_scholar','openalex','arxiv']" :key="src"
                @click="selectedSource = src"
                class="w-full text-left text-sm px-3 py-2 rounded-lg transition-all"
                :class="selectedSource === src
                  ? getSourceStyle(src).bg + ' ' + getSourceStyle(src).text + ' font-semibold'
                  : 'text-slate-400 hover:bg-white/5'">
                {{ getSourceStyle(src).label }}
              </button>
            </div>
          </div>

          <!-- Année -->
          <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3 block">Année</label>
            <select v-model="selectedYear"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50">
              <option value="">Toutes les années</option>
              <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
        </aside>

        <!-- ─── LISTE DES ARTICLES ─── -->
        <div class="flex-1 min-w-0">
          <!-- Compteur -->
          <div class="flex items-center justify-between mb-5">
            <p class="text-sm text-slate-400">
              <span class="font-bold text-white">{{ articles.total?.toLocaleString('fr-FR') }}</span> article(s)
              <span v-if="filters?.query"> pour « <em class="text-blue-400">{{ filters.query }}</em> »</span>
            </p>
          </div>

          <!-- Articles -->
          <div v-if="articles.data?.length > 0" class="space-y-4">
            <article v-for="article in articles.data" :key="article.id"
              class="group bg-slate-900/60 border border-white/8 hover:border-blue-500/30 rounded-xl p-5 transition-all hover:shadow-lg hover:shadow-blue-900/20">

              <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                  <!-- Source badge + année -->
                  <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11px] font-bold border"
                      :class="getSourceStyle(article.source).bg + ' ' + getSourceStyle(article.source).text">
                      {{ getSourceStyle(article.source).label }}
                    </span>
                    <span v-if="article.annee" class="text-xs text-slate-500">{{ article.annee }}</span>
                    <span v-if="article.type_publication && article.type_publication !== 'article'"
                      class="text-[10px] px-2 py-0.5 rounded-full bg-slate-700/50 text-slate-400 border border-white/5">
                      {{ article.type_publication }}
                    </span>
                  </div>

                  <!-- Titre -->
                  <h2 class="text-base font-bold text-white leading-snug mb-2 line-clamp-2 group-hover:text-blue-200 transition-colors">
                    {{ article.titre || 'Titre non disponible' }}
                  </h2>

                  <!-- Auteurs -->
                  <p v-if="article.auteurs" class="text-xs text-slate-400 mb-2 line-clamp-1">
                    {{ parseAuteurs(article.auteurs).slice(0, 5).join(' · ') }}
                    <span v-if="parseAuteurs(article.auteurs).length > 5" class="text-slate-600">
                      et {{ parseAuteurs(article.auteurs).length - 5 }} autres
                    </span>
                  </p>

                  <!-- Journal -->
                  <p v-if="article.journal" class="text-xs text-slate-500 italic">{{ article.journal }}</p>

                  <!-- Résumé -->
                  <p v-if="article.resume" class="text-xs text-slate-400 mt-2 line-clamp-2 leading-relaxed">
                    {{ article.resume }}
                  </p>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-2 shrink-0">
                  <!-- Lire sur la source -->
                  <a v-if="externalUrl(article)" :href="externalUrl(article)" target="_blank" rel="noopener noreferrer"
                    class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-blue-600/20 hover:bg-blue-600/40 text-blue-300 border border-blue-500/20 rounded-lg transition-all whitespace-nowrap">
                    <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
                    Voir l'article
                  </a>
                  <!-- PDF si disponible -->
                  <a v-if="article.pdf_url" :href="article.pdf_url" target="_blank" rel="noopener noreferrer"
                    class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-300 border border-emerald-500/20 rounded-lg transition-all whitespace-nowrap">
                    <DocumentArrowDownIcon class="w-3.5 h-3.5" />
                    PDF gratuit
                  </a>
                </div>
              </div>

              <!-- DOI -->
              <div v-if="article.doi" class="mt-3 flex items-center gap-1.5 text-[10px] text-slate-600">
                <span class="uppercase font-bold text-slate-700">DOI</span>
                <a :href="`https://doi.org/${article.doi}`" target="_blank" class="hover:text-slate-400 transition-colors">
                  {{ article.doi }}
                </a>
              </div>
            </article>
          </div>

          <!-- Empty state -->
          <div v-else class="text-center py-20">
            <BookOpenIcon class="w-12 h-12 text-slate-700 mx-auto mb-4" />
            <p class="text-slate-500 font-medium">Aucun article trouvé</p>
            <p class="text-slate-600 text-sm mt-1">Modifiez vos filtres ou revenez demain après le prochain import automatique.</p>
          </div>

          <!-- Pagination -->
          <div v-if="articles.last_page > 1" class="flex items-center justify-center gap-2 mt-8">
            <Link v-for="link in articles.links" :key="link.label"
              :href="link.url || ''"
              :class="[
                'px-3 py-2 rounded-lg text-sm font-medium transition-all',
                link.active ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5',
                !link.url ? 'opacity-30 pointer-events-none' : ''
              ]"
              v-html="link.label" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
