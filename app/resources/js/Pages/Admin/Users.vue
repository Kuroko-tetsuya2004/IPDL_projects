<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { UsersIcon, MagnifyingGlassIcon, PencilIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({ users: Object, filters: Object, axes: Array })

const search = ref(props.filters?.q ?? '')
const roleFilter = ref(props.filters?.role ?? '')
const editingUser = ref(null)
const editForm = useForm({ role: '', statut: '', axe_principal_id: '' })

const doSearch = () => router.get('/admin/membres', { q: search.value, role: roleFilter.value }, { preserveState: true })

const openEdit = (user) => {
  editingUser.value = user
  editForm.role = user.role
  editForm.statut = user.statut
  editForm.axe_principal_id = user.axe_principal_id || ''
}

const saveEdit = () => {
  editForm.put(`/admin/membres/${editingUser.value.id}`, {
    onSuccess: () => { editingUser.value = null },
  })
}

const quickValidate = (user) => {
  router.put(`/admin/membres/${user.id}`, {
    role: user.role,
    statut: 'active'
  })
}

const roleColors = {
  visitor: 'text-[var(--text-subtle)]', researcher: 'text-blue-400',
  doctoral_student: 'text-purple-400', partner: 'text-green-400',
  axe_admin: 'text-amber-400', super_admin: 'text-red-400',
}
const roleLabel = {
  visitor: 'Visiteur', researcher: 'Chercheur', doctoral_student: 'Doctorant',
  partner: 'Partenaire', axe_admin: "Admin d'axe", super_admin: 'Super Admin',
}
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-[var(--text)] flex items-center gap-3">
      <UsersIcon class="w-7 h-7 text-brand-400" />
      Gestion des membres
    </h1>

    <!-- Filtres -->
    <div class="flex gap-3 flex-wrap">
      <div class="relative flex-1 min-w-48">
        <MagnifyingGlassIcon class="w-4 h-4 text-[var(--text-subtle)] absolute left-3 top-1/2 -translate-y-1/2" />
        <input v-model="search" @keyup.enter="doSearch" placeholder="Nom, prénom, email..."
          class="w-full bg-[var(--surface)] border border-[var(--border)] rounded-lg pl-9 pr-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
      </div>
      <select v-model="roleFilter" @change="doSearch"
        class="bg-[var(--surface)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
        <option value="">Tous les rôles</option>
        <option v-for="(label, val) in roleLabel" :key="val" :value="val">{{ label }}</option>
      </select>
      <button @click="doSearch" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-sm font-semibold transition-all">Filtrer</button>
    </div>

    <!-- Tableau -->
    <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl overflow-hidden">
      <table class="w-full">
        <thead class="border-b border-[var(--border)]">
          <tr>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-[var(--text-subtle)] uppercase tracking-wider">Utilisateur</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-[var(--text-subtle)] uppercase tracking-wider">Rôle</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-[var(--text-subtle)] uppercase tracking-wider">Statut</th>
            <th class="px-6 py-3.5 text-left text-xs font-semibold text-[var(--text-subtle)] uppercase tracking-wider">Inscription</th>
            <th class="px-6 py-3.5 text-right text-xs font-semibold text-[var(--text-subtle)] uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[var(--border)]">
          <tr v-for="user in users.data" :key="user.id" class="hover:bg-white/3 transition-colors">
            <td class="px-6 py-4">
              <div class="font-medium text-[var(--text)] text-sm">{{ user.prenom }} {{ user.nom }}</div>
              <div class="text-xs text-[var(--text-subtle)] mt-0.5">{{ user.email }}</div>
              <div v-if="user.role === 'researcher' && user.specialite" class="text-xs text-brand-400 mt-1">
                🔬 Spécialité : {{ user.specialite }}
                <span class="text-[var(--text-subtle)]" v-if="user.axe_nom">({{ user.axe_nom }})</span>
              </div>
              <div v-if="user.role === 'doctoral_student'" class="text-xs text-purple-400 mt-1">
                🎓 Domaine : {{ user.domaine_expertise || 'Non renseigné' }}
                <span class="text-[var(--text-subtle)]" v-if="user.axe_nom">({{ user.axe_nom }})</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <span :class="['text-xs font-medium', roleColors[user.role]]">{{ roleLabel[user.role] ?? user.role }}</span>
            </td>
            <td class="px-6 py-4">
              <span :class="['text-xs font-medium px-2.5 py-0.5 rounded-full border', 
                user.statut === 'active' ? 'bg-emerald-400/10 text-emerald-400 border-emerald-400/20' : 
                (user.statut === 'pending' ? 'bg-amber-400/10 text-amber-400 border-amber-400/20' : 
                'bg-red-400/10 text-red-400 border-red-400/20')
              ]">
                {{ user.statut === 'active' ? 'Actif' : (user.statut === 'pending' ? 'En attente' : user.statut) }}
              </span>
            </td>
            <td class="px-6 py-4 text-xs text-[var(--text-subtle)]">{{ user.created_at?.substring(0, 10) }}</td>
            <td class="px-6 py-4 text-right flex justify-end gap-2">
              <button v-if="user.statut === 'pending'" @click="quickValidate(user)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600/90 hover:bg-emerald-500 text-white text-xs font-semibold transition-all">
                ✓ Valider
              </button>
              <button @click="openEdit(user)" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-[var(--surface-alt)] hover:bg-slate-700 text-[var(--text-muted)] text-xs font-medium transition-all">
                <PencilIcon class="w-3.5 h-3.5" /> Modifier / Valider
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="users.last_page > 1" class="flex items-center justify-between text-sm text-[var(--text-subtle)]">
      <span>{{ users.from }}–{{ users.to }} sur {{ users.total }}</span>
      <div class="flex gap-2">
        <a v-if="users.prev_page_url" :href="users.prev_page_url" class="px-3 py-1.5 rounded-lg border border-[var(--border)] hover:border-brand-500/50 hover:text-[var(--text)] transition-all">← Précédent</a>
        <a v-if="users.next_page_url" :href="users.next_page_url" class="px-3 py-1.5 rounded-lg border border-[var(--border)] hover:border-brand-500/50 hover:text-[var(--text)] transition-all">Suivant →</a>
      </div>
    </div>

    <!-- Modal d'édition -->
    <div v-if="editingUser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="editingUser = null">
      <div class="bg-[var(--surface)] border border-[var(--border)] rounded-2xl p-6 w-full max-w-sm shadow-glass animate-slide-up">
        <h3 class="text-lg font-semibold text-[var(--text)] mb-1">Modifier l'utilisateur</h3>
        <p class="text-[var(--text-subtle)] text-sm mb-4">{{ editingUser.prenom }} {{ editingUser.nom }}</p>

        <!-- Détails Inscription pour Validation -->
        <div v-if="editingUser.role === 'researcher' && editingUser.specialite" class="text-xs text-brand-400 bg-brand-500/5 border border-brand-500/10 rounded-lg p-3 mb-4">
          <strong class="block text-[var(--text-subtle)] mb-1">🔬 Spécialité Chercheur :</strong>
          {{ editingUser.specialite }}
        </div>
        <div v-if="editingUser.role === 'doctoral_student'" class="text-xs text-purple-400 bg-purple-500/5 border border-purple-500/10 rounded-lg p-3 mb-4">
          <strong class="block text-[var(--text-subtle)] mb-1">🎓 Domaine d'expertise Doctorant :</strong>
          {{ editingUser.domaine_expertise || 'Non renseigné' }}
          <div v-if="editingUser.axe_nom" class="mt-2 text-[var(--text-subtle)]">
            <strong>Axe thématique :</strong> {{ editingUser.axe_nom }}
          </div>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Rôle</label>
            <select v-model="editForm.role" class="w-full bg-[var(--surface-alt)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
              <option v-for="(label, val) in roleLabel" :key="val" :value="val">{{ label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Axe thématique principal</label>
            <select v-model="editForm.axe_principal_id" class="w-full bg-[var(--surface-alt)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
              <option value="">— Aucun axe (Global) —</option>
              <option v-for="axe in axes" :key="axe.id" :value="axe.id">{{ axe.nom_fr }} ({{ axe.code }})</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Statut</label>
            <select v-model="editForm.statut" class="w-full bg-[var(--surface-alt)] border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
              <option value="active">Actif</option>
              <option value="inactive">Inactif</option>
              <option value="archived">Archivé</option>
              <option value="pending">En attente (Validation)</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button @click="editingUser = null" class="px-4 py-2 text-[var(--text-subtle)] hover:text-[var(--text)] text-sm transition-colors">Annuler</button>
          <button @click="saveEdit" :disabled="editForm.processing" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-sm font-semibold transition-all disabled:opacity-50">Sauvegarder</button>
        </div>
      </div>
    </div>
  </div>
</template>
