<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { ClipboardDocumentCheckIcon, CheckIcon, XMarkIcon, ClockIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  soumissions: Object,
  userRole: String,
})

const rejectModal = ref({ open: false, id: null })
const rejectComment = ref('')
const isAdmin = ['axe_admin', 'super_admin'].includes(props.userRole)

const approve = (id) => {
  if (!confirm('Approuver cette publication ?')) return
  router.post(`/workflow/${id}/approve`)
}

const openReject = (id) => {
  rejectModal.value = { open: true, id }
  rejectComment.value = ''
}

const submitReject = () => {
  router.post(`/workflow/${rejectModal.value.id}/reject`, {
    commentaire: rejectComment.value,
  })
  rejectModal.value.open = false
}
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
      <ClipboardDocumentCheckIcon class="w-7 h-7 text-amber-400" />
      {{ isAdmin ? 'Soumissions en attente' : 'Mes soumissions' }}
    </h1>

    <div class="bg-slate-900/60 border border-white/8 rounded-xl overflow-hidden">
      <div v-if="!soumissions?.data?.length" class="text-center py-16">
        <ClipboardDocumentCheckIcon class="w-12 h-12 text-slate-600 mx-auto mb-4" />
        <p class="text-slate-400">Aucune soumission en attente.</p>
      </div>

      <div v-else class="divide-y divide-white/5">
        <div v-for="s in soumissions.data" :key="s.id" class="px-6 py-5 hover:bg-white/3 transition-colors">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
              <h3 class="font-medium text-white truncate">
                <a :href="`/publications/${s.publication?.id}`" target="_blank" class="hover:text-brand-400 transition-colors">
                  {{ s.publication?.titre_fr }}
                </a>
              </h3>
              <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-400">
                <span class="capitalize px-2 py-0.5 rounded bg-slate-800 border border-white/5">{{ s.publication?.type }}</span>
                <span v-if="s.publication?.axe" class="text-slate-500">{{ s.publication.axe.nom_fr }}</span>
                <span class="flex items-center gap-1">
                  <ClockIcon class="w-3 h-3" /> {{ s.date_soumission }}
                </span>
                <span v-if="s.soumetteur">
                  Par {{ s.soumetteur.prenom }} {{ s.soumetteur.nom }}
                </span>
              </div>
              <p v-if="s.commentaire_auteur" class="text-xs text-slate-500 mt-2 italic">
                "{{ s.commentaire_auteur }}"
              </p>
            </div>

            <!-- Actions (admins seulement) -->
            <div v-if="isAdmin" class="flex items-center gap-2 shrink-0">
              <button @click="approve(s.id)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 text-xs font-medium transition-all">
                <CheckIcon class="w-3.5 h-3.5" /> Approuver
              </button>
              <button @click="openReject(s.id)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 text-xs font-medium transition-all">
                <XMarkIcon class="w-3.5 h-3.5" /> Rejeter
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de rejet -->
    <div v-if="rejectModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="rejectModal.open = false">
      <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 w-full max-w-md shadow-glass animate-slide-up">
        <h3 class="text-lg font-semibold text-white mb-4">Motif du rejet</h3>
        <textarea v-model="rejectComment" rows="4" placeholder="Expliquez pourquoi cette publication est rejetée..."
          class="w-full bg-slate-800 border border-white/10 rounded-lg px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-red-500/50 transition-all resize-none"></textarea>
        <div class="flex justify-end gap-3 mt-4">
          <button @click="rejectModal.open = false" class="px-4 py-2 text-slate-400 hover:text-white text-sm transition-colors">Annuler</button>
          <button @click="submitReject" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm font-semibold transition-all">Confirmer le rejet</button>
        </div>
      </div>
    </div>
  </div>
</template>
