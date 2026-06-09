<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { UsersIcon, DocumentTextIcon, CircleStackIcon, ClipboardDocumentCheckIcon, ChartBarIcon, Cog6ToothIcon, ShieldCheckIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({ stats: Object })
</script>

<template>
  <div class="p-6 space-y-8 animate-fade-in">
    <h1 class="text-2xl font-bold text-white">🛡️ Administration</h1>

    <!-- Stats rapides -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5">
        <UsersIcon class="w-6 h-6 text-brand-400 mb-2" />
        <div class="text-2xl font-bold text-white">{{ stats?.total_users ?? 0 }}</div>
        <div class="text-slate-400 text-sm">Utilisateurs</div>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5">
        <DocumentTextIcon class="w-6 h-6 text-emerald-400 mb-2" />
        <div class="text-2xl font-bold text-white">{{ stats?.total_publications ?? 0 }}</div>
        <div class="text-slate-400 text-sm">Publications</div>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5">
        <ClipboardDocumentCheckIcon class="w-6 h-6 text-amber-400 mb-2" />
        <div class="text-2xl font-bold text-white">{{ stats?.pending_workflow ?? 0 }}</div>
        <div class="text-slate-400 text-sm">En attente</div>
      </div>
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5">
        <CircleStackIcon class="w-6 h-6 text-purple-400 mb-2" />
        <div class="text-2xl font-bold text-white">{{ stats?.total_axes ?? 0 }}</div>
        <div class="text-slate-400 text-sm">Axes</div>
      </div>
    </div>

    <!-- Menu d'accès rapide -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <a href="/admin/membres" class="bg-slate-900/60 border border-white/8 rounded-xl p-6 hover:border-brand-500/30 hover:-translate-y-0.5 transition-all group">
        <UsersIcon class="w-8 h-8 text-brand-400 mb-3" />
        <h3 class="font-semibold text-white group-hover:text-brand-400 transition-colors">Gestion des membres</h3>
        <p class="text-slate-400 text-sm mt-1">Voir, modifier les rôles et statuts</p>
      </a>
      <a href="/soumissions" class="bg-slate-900/60 border border-white/8 rounded-xl p-6 hover:border-amber-500/30 hover:-translate-y-0.5 transition-all group">
        <ClipboardDocumentCheckIcon class="w-8 h-8 text-amber-400 mb-3" />
        <h3 class="font-semibold text-white group-hover:text-amber-400 transition-colors">Validation workflow</h3>
        <p class="text-slate-400 text-sm mt-1">Approuver ou rejeter les soumissions</p>
        <span v-if="stats?.pending_workflow > 0" class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs bg-amber-400/10 text-amber-400 border border-amber-400/20">
          {{ stats.pending_workflow }} en attente
        </span>
      </a>
      <a href="/admin/publications" class="bg-slate-900/60 border border-white/8 rounded-xl p-6 hover:border-emerald-500/30 hover:-translate-y-0.5 transition-all group">
        <DocumentTextIcon class="w-8 h-8 text-emerald-400 mb-3" />
        <h3 class="font-semibold text-white group-hover:text-emerald-400 transition-colors">Publications</h3>
        <p class="text-slate-400 text-sm mt-1">Modération et gestion du contenu</p>
      </a>
      <a href="/admin/axes" class="bg-slate-900/60 border border-white/8 rounded-xl p-6 hover:border-purple-500/30 hover:-translate-y-0.5 transition-all group">
        <ChartBarIcon class="w-8 h-8 text-purple-400 mb-3" />
        <h3 class="font-semibold text-white group-hover:text-purple-400 transition-colors">Axes thématiques</h3>
        <p class="text-slate-400 text-sm mt-1">Superviser les axes de recherche</p>
      </a>
      <a href="/admin/parametres" class="bg-slate-900/60 border border-white/8 rounded-xl p-6 hover:border-slate-500/50 hover:-translate-y-0.5 transition-all group">
        <Cog6ToothIcon class="w-8 h-8 text-slate-400 mb-3" />
        <h3 class="font-semibold text-white group-hover:text-slate-300 transition-colors">Paramètres système</h3>
        <p class="text-slate-400 text-sm mt-1">Configuration globale du portail</p>
      </a>
      <a href="/admin/acl" class="bg-slate-900/60 border border-white/8 rounded-xl p-6 hover:border-red-500/30 hover:-translate-y-0.5 transition-all group">
        <ShieldCheckIcon class="w-8 h-8 text-red-400 mb-3" />
        <h3 class="font-semibold text-white group-hover:text-red-400 transition-colors">Contrôle d'accès</h3>
        <p class="text-slate-400 text-sm mt-1">ACL granulaires par ressource</p>
      </a>
    </div>
  </div>
</template>
