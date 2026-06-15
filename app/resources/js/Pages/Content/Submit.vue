<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { DocumentPlusIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  userRole: String,
})

const isDoctorant = computed(() => props.userRole === 'doctoral_student')

const form = useForm({
  doi: '',
})

const submit = () => {
  form.post('/publications/submit', {
    onSuccess: () => form.reset(),
  })
}

const isSyncing = ref(false)
const syncOrcid = () => {
  isSyncing.value = true
  router.post('/publications/sync-orcid', {}, {
    preserveScroll: true,
    onFinish: () => {
      isSyncing.value = false
    }
  })
}
</script>

<template>
  <div class="p-6 max-w-2xl mx-auto space-y-6 animate-fade-in">
    <div>
      <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <DocumentPlusIcon class="w-7 h-7 text-brand-400" />
        Ajouter une publication
      </h1>
      <p class="text-slate-400 text-sm mt-2">
        Renseignez simplement le DOI de votre publication pour l'importer automatiquement, ou synchronisez toutes vos publications via votre ORCID.
      </p>
      <p v-if="isDoctorant" class="text-amber-400 text-sm mt-2 flex items-center gap-2">
        ⚠️ En tant que doctorant, vos publications importeront passeront par un workflow de validation.
      </p>
    </div>

    <!-- Option 1: ORCID Sync -->
    <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 flex flex-col items-center text-center space-y-4">
      <div class="p-3 bg-brand-500/10 rounded-full">
        <ArrowPathIcon class="w-8 h-8 text-brand-400" />
      </div>
      <div>
        <h2 class="text-lg font-semibold text-white">Synchronisation de masse (ORCID)</h2>
        <p class="text-sm text-slate-400 mt-1">
          Importez automatiquement toutes vos publications en une seule fois. (Assurez-vous d'avoir renseigné votre ORCID dans votre profil).
        </p>
      </div>
      <button @click="syncOrcid" :disabled="isSyncing"
        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-white/10 text-white text-sm font-semibold transition-all disabled:opacity-50">
        <svg v-if="isSyncing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ isSyncing ? 'Synchronisation en cours...' : 'Lancer la synchronisation ORCID' }}
      </button>
    </div>

    <div class="flex items-center gap-4 py-2">
      <div class="flex-1 h-px bg-white/10"></div>
      <span class="text-sm text-slate-500 font-medium">OU</span>
      <div class="flex-1 h-px bg-white/10"></div>
    </div>

    <!-- Option 2: DOI Import -->
    <form @submit.prevent="submit" class="bg-slate-900/60 border border-brand-500/30 rounded-xl p-6 space-y-4 shadow-[0_0_15px_rgba(var(--color-brand-500),0.1)]">
      <div>
        <h2 class="text-lg font-semibold text-white">Importation spécifique (DOI)</h2>
        <p class="text-sm text-slate-400 mt-1 mb-4">
          Vous n'avez pas d'ORCID ou vous souhaitez importer une publication spécifique ? Entrez son identifiant DOI (ex: 10.1038/s41586-021-03615-2).
        </p>
        <label class="block text-xs font-medium text-slate-400 mb-1.5">Identifiant DOI *</label>
        <input v-model="form.doi" type="text" required placeholder="Ex: 10.xxxx/xxxxx"
          class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        <p v-if="form.errors.doi" class="text-xs text-red-400 mt-1">{{ form.errors.doi }}</p>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <a href="/mes-publications" class="px-6 py-2.5 border border-white/10 text-slate-400 hover:text-white rounded-lg text-sm font-semibold transition-all">
          Annuler
        </a>
        <button type="submit" :disabled="form.processing"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 text-white rounded-lg text-sm font-semibold transition-all shadow-[0_0_15px_rgba(var(--color-brand-600),0.4)]">
          <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          Importer cette publication
        </button>
      </div>
    </form>
  </div>
</template>
