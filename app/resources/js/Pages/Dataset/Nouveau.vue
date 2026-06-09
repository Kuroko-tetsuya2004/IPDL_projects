<script setup>
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { CircleStackIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({ axes: Array })

const form = useForm({
  titre_fr:   '',
  titre_en:   '',
  resume_fr:  '',
  licence:    '',
  mots_cles:  '',
  axe_id:     '',
  fichier:    null,
})

const submit = () => form.post('/datasets', { onSuccess: () => form.reset() })

const licences = [
  'CC BY 4.0',
  'CC BY-SA 4.0',
  'CC BY-NC 4.0',
  'CC BY-NC-SA 4.0',
  'CC BY-ND 4.0',
  'CC0 1.0 (Domaine public)',
  'MIT',
  'Apache 2.0',
  'Propriétaire',
]
</script>

<template>
  <div class="p-6 max-w-2xl mx-auto space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
      <CircleStackIcon class="w-7 h-7 text-purple-400" />
      Nouveau dataset
    </h1>

    <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 text-amber-400 text-sm flex items-start gap-3">
      <span class="text-lg">⚠️</span>
      <div>
        <strong>Règle RG-007 :</strong> Tout dataset doit avoir une licence avant publication.
        Choisissez soigneusement la licence qui correspond à vos droits de partage.
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Informations</h2>

        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Titre (Français) *</label>
          <input v-model="form.titre_fr" type="text" required class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
          <p v-if="form.errors.titre_fr" class="text-xs text-red-400 mt-1">{{ form.errors.titre_fr }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Title (English)</label>
          <input v-model="form.titre_en" type="text" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Description *</label>
          <textarea v-model="form.resume_fr" rows="4" required class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all resize-none" placeholder="Décrivez le contenu, la méthodologie de collecte, et l'usage prévu..."></textarea>
          <p v-if="form.errors.resume_fr" class="text-xs text-red-400 mt-1">{{ form.errors.resume_fr }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Licence * <span class="text-amber-400">(obligatoire — RG-007)</span></label>
          <select v-model="form.licence" required class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
            <option value="">— Choisir une licence —</option>
            <option v-for="l in licences" :key="l" :value="l">{{ l }}</option>
          </select>
          <p v-if="form.errors.licence" class="text-xs text-red-400 mt-1">{{ form.errors.licence }}</p>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Mots-clés</label>
          <input v-model="form.mots_cles" type="text" placeholder="données, épidémiologie, sénégal" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Fichier du jeu de données (optionnel)</label>
          <input type="file" @change="e => form.fichier = e.target.files[0]"
            class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-600 file:text-white hover:file:bg-brand-500" />
          <p v-if="form.errors.fichier" class="text-xs text-red-400 mt-1">{{ form.errors.fichier }}</p>
        </div>

        <div v-if="axes?.length">
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Axe thématique</label>
          <select v-model="form.axe_id" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
            <option value="">— Aucun axe —</option>
            <option v-for="axe in axes" :key="axe.id" :value="axe.id">{{ axe.nom_fr }}</option>
          </select>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <a href="/mes-datasets" class="px-6 py-2.5 border border-white/10 text-slate-400 hover:text-white rounded-lg text-sm font-semibold transition-all">Annuler</a>
        <button type="submit" :disabled="form.processing"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 text-white rounded-lg text-sm font-semibold transition-all">
          <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          Créer le dataset
        </button>
      </div>
    </form>
  </div>
</template>
