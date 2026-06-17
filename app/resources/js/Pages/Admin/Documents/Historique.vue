<script setup>
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { DocumentTextIcon, ArrowLeftIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  documents: Object
})

const getDocTitle = (type) => {
  return type === 'convention_stage' ? 'Convention de Stage' : 
         type === 'prestation_service' ? 'Prestation de Service' :
         type === 'bon_achat' ? "Bon d'Achat" : 'Document Administratif'
}

const getDocColor = (type) => {
  return type === 'convention_stage' ? 'text-blue-400 bg-blue-500/10 border-blue-500/20' : 
         type === 'prestation_service' ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 
         type === 'bon_achat' ? 'text-amber-400 bg-amber-500/10 border-amber-500/20' : 'text-[var(--text-subtle)] bg-slate-500/10 border-slate-500/20'
}
</script>

<template>
  <div class="p-6 max-w-7xl mx-auto space-y-6 animate-fade-in">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Link href="/admin/documents" class="p-2 rounded-lg border border-[var(--border)] text-[var(--text-subtle)] hover:text-[var(--text)] hover:bg-white/5 transition-all">
          <ArrowLeftIcon class="w-4 h-4" />
        </Link>
        <div>
          <h1 class="text-2xl font-extrabold text-[var(--text)] flex items-center gap-3">
            <DocumentTextIcon class="w-7 h-7 text-indigo-400" />
            Historique des Documents
          </h1>
          <p class="text-[var(--text-subtle)] text-sm mt-0.5">
            Retrouvez ici tous les documents administratifs générés et archivés.
          </p>
        </div>
      </div>
    </div>

    <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-[var(--text-muted)]">
          <thead class="bg-[var(--surface-alt)]/80 text-xs uppercase text-[var(--text-subtle)]">
            <tr>
              <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Référence</th>
              <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Type de Document</th>
              <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Généré par</th>
              <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Date</th>
              <th scope="col" class="px-6 py-4 font-semibold tracking-wider text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[var(--border)]">
            <tr v-for="doc in documents.data" :key="doc.id" class="hover:bg-white/[0.02] transition-colors">
              <td class="px-6 py-4 font-medium text-[var(--text)]">{{ doc.reference }}</td>
              <td class="px-6 py-4">
                <span :class="['px-2.5 py-1 text-xs font-semibold rounded-full border', getDocColor(doc.type_document)]">
                  {{ getDocTitle(doc.type_document) }}
                </span>
              </td>
              <td class="px-6 py-4 text-[var(--text-subtle)]">
                {{ doc.user ? (doc.user.prenom + ' ' + doc.user.nom) : 'Système' }}
              </td>
              <td class="px-6 py-4 text-[var(--text-subtle)] whitespace-nowrap">
                {{ new Date(doc.created_at).toLocaleString('fr-FR') }}
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <a :href="`/admin/documents/${doc.id}/view`" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/20 text-sky-400 text-xs font-semibold rounded-lg transition-all">
                    <DocumentTextIcon class="w-4 h-4" />
                    Aperçu
                  </a>
                  <a :href="`/admin/documents/${doc.id}/download`" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/20 text-indigo-400 text-xs font-semibold rounded-lg transition-all">
                    <ArrowDownTrayIcon class="w-4 h-4" />
                    Télécharger
                  </a>
                </div>
              </td>
            </tr>
            <tr v-if="documents.data.length === 0">
              <td colspan="5" class="px-6 py-12 text-center text-[var(--text-subtle)]">
                Aucun document n'a été généré pour le moment.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="documents.links && documents.data.length > 0" class="px-6 py-4 border-t border-[var(--border)] flex items-center justify-center gap-2">
        <template v-for="(link, k) in documents.links" :key="k">
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all"
            :class="link.active ? 'bg-indigo-500 text-[var(--text)] shadow-lg' : 'bg-[var(--surface-alt)] text-[var(--text-subtle)] hover:text-[var(--text)] hover:bg-slate-700'"
          />
          <span v-else v-html="link.label" class="px-3 py-1.5 text-xs font-medium text-slate-600"></span>
        </template>
      </div>
    </div>
  </div>
</template>
