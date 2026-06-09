<script setup>
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { ShieldCheckIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })
const props = defineProps({ acls: Object })
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
      <ShieldCheckIcon class="w-7 h-7 text-red-400" />
      Contrôle d'accès (ACL)
    </h1>

    <div class="bg-slate-900/60 border border-white/8 rounded-xl overflow-hidden">
      <div v-if="!acls?.data?.length" class="text-center py-16">
        <ShieldCheckIcon class="w-12 h-12 text-slate-600 mx-auto mb-4" />
        <p class="text-slate-400">Aucune entrée ACL configurée.</p>
      </div>
      <table v-else class="w-full">
        <thead class="border-b border-white/8">
          <tr>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Utilisateur (Accordé par)</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Ressource</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Groupe / Rôle</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Permissions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr v-for="acl in acls.data" :key="acl.id" class="hover:bg-white/3 transition-colors">
            <td class="px-6 py-4">
              <template v-if="acl.nom">
                <p class="text-sm text-white">{{ acl.prenom }} {{ acl.nom }}</p>
                <p class="text-xs text-slate-500">{{ acl.email }}</p>
              </template>
              <template v-else>
                <p class="text-sm text-slate-400 italic">Système</p>
              </template>
            </td>
            <td class="px-6 py-4 text-xs font-mono text-slate-300">{{ acl.ressource_type }}/{{ acl.ressource_id?.substring(0, 8) }}</td>
            <td class="px-6 py-4 text-xs text-slate-400">{{ acl.groupe }}</td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1">
                <span v-for="p in acl.permissions" :key="p" class="text-xs font-medium px-2 py-0.5 rounded-full border bg-brand-500/10 text-brand-400 border-brand-500/20">
                  {{ p }}
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
