<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { DocumentPlusIcon, TagIcon, CalendarIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  axes: Array,
  userRole: String,
})

const isDoctorant = computed(() => props.userRole === 'doctoral_student')

const form = useForm({
  titre_fr:       '',
  titre_en:       '',
  resume_fr:      '',
  resume_en:      '',
  type:           'article',
  axe_id:         '',
  mots_cles:      '',
  date_publication: '',
  doi:            '',
  url_externe:    '',
  visibilite:     'public',
  langue_contenu: 'fr',
  fichier:        null,
})

const submit = () => {
  form.post('/publications/submit', {
    onSuccess: () => form.reset(),
  })
}

const types = computed(() => {
  const list = [
    { value: 'article',      label: 'Article scientifique' },
    { value: 'document',     label: 'Recherche en cours' },
    { value: 'news',         label: 'Actualité / Annonce' },
    { value: 'thesis',       label: 'Thèse / Mémoire' },
    { value: 'report',       label: 'Rapport de recherche' },
    { value: 'presentation', label: 'Présentation / Poster' },
  ]
  if (props.userRole === 'axe_admin' || props.userRole === 'super_admin') {
    list.push({ value: 'event', label: 'Événement' })
  }
  return list
})
</script>

<template>
  <div class="p-6 max-w-3xl mx-auto space-y-6 animate-fade-in">
    <div>
      <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <DocumentPlusIcon class="w-7 h-7 text-brand-400" />
        Soumettre une publication
      </h1>
      <p v-if="isDoctorant" class="text-amber-400 text-sm mt-2 flex items-center gap-2">
        ⚠️ En tant que doctorant, votre publication passera par un workflow de validation avant publication.
      </p>
      <p v-else class="text-slate-400 text-sm mt-2">
        Les chercheurs peuvent publier directement sans validation (RG-012).
      </p>
    </div>

    <form @submit.prevent="submit" class="space-y-6">

      <!-- Type & Axe -->
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Classification</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Type de publication *</label>
            <select v-model="form.type" required class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
              <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Axe thématique</label>
            <select v-model="form.axe_id" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
              <option value="">— Aucun axe —</option>
              <option v-for="axe in axes" :key="axe.id" :value="axe.id">{{ axe.nom_fr }} ({{ axe.code }})</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Langue principale</label>
            <select v-model="form.langue_contenu" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
              <option value="fr">Français</option>
              <option value="en">English</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Visibilité (Public cible)</label>
            <select v-model="form.visibilite" :disabled="props.userRole !== 'axe_admin' && props.userRole !== 'super_admin'" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
              <option value="public">Public</option>
              <option value="internal">Interne (Membres)</option>
              <option value="partners">Partenaires</option>
            </select>
            <p v-if="props.userRole !== 'axe_admin' && props.userRole !== 'super_admin'" class="text-[10px] text-slate-500 mt-1">
              Modification réservée aux responsables d'axes.
            </p>
          </div>
        </div>
      </div>

      <!-- Titres & Résumés -->
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Contenu</h2>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Titre (Français) *</label>
          <input v-model="form.titre_fr" type="text" required placeholder="Titre de votre publication"
            class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
          <p v-if="form.errors.titre_fr" class="text-xs text-red-400 mt-1">{{ form.errors.titre_fr }}</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Title (English)</label>
          <input v-model="form.titre_en" type="text" placeholder="English title (optional)"
            class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Résumé (Français) *</label>
          <textarea v-model="form.resume_fr" rows="5" required placeholder="Résumé de votre travail..."
            class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all resize-none"></textarea>
          <p v-if="form.errors.resume_fr" class="text-xs text-red-400 mt-1">{{ form.errors.resume_fr }}</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Abstract (English)</label>
          <textarea v-model="form.resume_en" rows="4" placeholder="English abstract (optional)"
            class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all resize-none"></textarea>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Mots-clés</label>
          <input v-model="form.mots_cles" type="text" placeholder="épidémiologie, modélisation, UCAD (séparés par virgules)"
            class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Document joint (obligatoire pour thèses, rapports, présentations)</label>
          <input type="file" @change="e => form.fichier = e.target.files[0]"
            class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-600 file:text-white hover:file:bg-brand-500" />
          <p v-if="form.errors.fichier" class="text-xs text-red-400 mt-1">{{ form.errors.fichier }}</p>
        </div>
      </div>

      <!-- Métadonnées -->
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Métadonnées</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">DOI</label>
            <input v-model="form.doi" type="text" placeholder="10.xxxx/xxxxx"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">URL externe</label>
            <input v-model="form.url_externe" type="url" placeholder="https://..."
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Date de publication</label>
          <input v-model="form.date_publication" type="date"
            class="bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end gap-3">
        <a href="/mes-publications" class="px-6 py-2.5 border border-white/10 text-slate-400 hover:text-white rounded-lg text-sm font-semibold transition-all">
          Annuler
        </a>
        <button type="submit" :disabled="form.processing"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 text-white rounded-lg text-sm font-semibold transition-all">
          <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ isDoctorant ? '📤 Soumettre pour validation' : '✅ Publier' }}
        </button>
      </div>

    </form>
  </div>
</template>
