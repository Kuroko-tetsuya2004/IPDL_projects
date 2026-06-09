<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { ChartBarIcon, PencilIcon, PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  axes: Array,
  users: Array,
  userRole: String
})

const showCreateModal = ref(false)
const editingAxe = ref(null)

const createForm = useForm({
  code: '',
  nom_fr: '',
  nom_en: '',
  description_fr: '',
  description_en: '',
  couleur_hex: '#3B82F6',
  ordre_affichage: 1,
  actif: true,
  responsable_id: ''
})

const editForm = useForm({
  id: '',
  code: '',
  nom_fr: '',
  nom_en: '',
  description_fr: '',
  description_en: '',
  couleur_hex: '',
  ordre_affichage: 1,
  actif: true,
  responsable_id: ''
})

const openCreate = () => {
  createForm.reset()
  showCreateModal.value = true
}

const saveCreate = () => {
  createForm.post('/admin/axes', {
    onSuccess: () => {
      showCreateModal.value = false
      createForm.reset()
    }
  })
}

const openEdit = (axe) => {
  editingAxe.value = axe
  editForm.id = axe.id
  editForm.code = axe.code
  editForm.nom_fr = axe.nom_fr
  editForm.nom_en = axe.nom_en || ''
  editForm.description_fr = axe.description_fr || ''
  editForm.description_en = axe.description_en || ''
  editForm.couleur_hex = axe.couleur_hex || '#3B82F6'
  editForm.ordre_affichage = axe.ordre_affichage
  editForm.actif = axe.actif
  editForm.responsable_id = axe.responsable_id || ''
}

const saveEdit = () => {
  editForm.put(`/admin/axes/${editForm.id}`, {
    onSuccess: () => {
      editingAxe.value = null
      editForm.reset()
    }
  })
}
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white flex items-center gap-3">
        <ChartBarIcon class="w-7 h-7 text-purple-400" />
        Axes thématiques
      </h1>
      <button v-if="props.userRole === 'super_admin'" @click="openCreate"
        class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold rounded-lg shadow-md hover:shadow-brand-500/20 transition-all">
        <PlusIcon class="w-4 h-4" /> Créer un axe
      </button>
    </div>

    <!-- Liste des axes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div v-for="axe in axes" :key="axe.id"
        class="bg-slate-900/60 border border-white/8 rounded-xl p-6 hover:border-purple-500/30 transition-all flex flex-col justify-between">
        <div>
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2">
              <div class="w-4 h-4 rounded-full" :style="{ background: axe.couleur_hex ?? '#3B82F6' }"></div>
              <span v-if="!axe.actif" class="px-1.5 py-0.5 text-[10px] font-bold bg-red-500/20 text-red-400 border border-red-500/10 rounded">Inactif</span>
            </div>
            <div class="flex gap-3 text-xs text-slate-400">
              <span>📄 {{ axe.publications_count }} pub.</span>
              <span>👥 {{ axe.membres_count }} membres</span>
            </div>
          </div>
          <h3 class="font-bold text-white text-lg">{{ axe.nom_fr }}</h3>
          <p class="text-sm text-slate-400 italic mt-0.5 mb-2">{{ axe.nom_en }}</p>
          <span class="inline-block px-2 py-0.5 bg-slate-800 border border-white/5 rounded text-xs text-slate-400 font-mono">{{ axe.code }}</span>
          <p v-if="axe.description_fr" class="text-sm text-slate-400 mt-3 line-clamp-3">{{ axe.description_fr }}</p>
        </div>

        <div class="mt-4 pt-3 border-t border-white/8 flex items-center justify-between text-xs text-slate-450">
          <div>
            <span v-if="axe.responsable" class="text-slate-400">
              Responsable : <strong class="text-slate-200">{{ axe.responsable.prenom }} {{ axe.responsable.nom }}</strong>
            </span>
            <span v-else class="text-slate-500 italic">Aucun responsable</span>
          </div>
          <button v-if="props.userRole === 'super_admin'" @click="openEdit(axe)"
            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium transition-colors">
            <PencilIcon class="w-3.5 h-3.5" /> Modifier
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de création -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" @click.self="showCreateModal = false">
      <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 w-full max-w-lg shadow-glass animate-slide-up max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/5">
          <h3 class="text-lg font-semibold text-white">Créer un axe thématique</h3>
          <button @click="showCreateModal = false" class="text-slate-400 hover:text-white transition-colors">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="saveCreate" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Code de l'axe (unique)</label>
              <input v-model="createForm.code" required placeholder="ex: epidemio"
                class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
              <div v-if="createForm.errors.code" class="text-red-400 text-xs mt-1">{{ createForm.errors.code }}</div>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Couleur</label>
              <div class="flex gap-2">
                <input type="color" v-model="createForm.couleur_hex"
                  class="w-10 h-9 bg-slate-850 border border-white/10 rounded-lg p-1 cursor-pointer" />
                <input v-model="createForm.couleur_hex" placeholder="#3B82F6"
                  class="flex-1 bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
              </div>
              <div v-if="createForm.errors.couleur_hex" class="text-red-400 text-xs mt-1">{{ createForm.errors.couleur_hex }}</div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom (Français)</label>
            <input v-model="createForm.nom_fr" required placeholder="ex: Épidémiologie et Modélisation"
              class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
            <div v-if="createForm.errors.nom_fr" class="text-red-400 text-xs mt-1">{{ createForm.errors.nom_fr }}</div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom (Anglais)</label>
            <input v-model="createForm.nom_en" placeholder="ex: Epidemiology and Modeling"
              class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
            <div v-if="createForm.errors.nom_en" class="text-red-400 text-xs mt-1">{{ createForm.errors.nom_en }}</div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Description (Français)</label>
            <textarea v-model="createForm.description_fr" rows="3" placeholder="Description de l'axe en français..."
              class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all"></textarea>
            <div v-if="createForm.errors.description_fr" class="text-red-400 text-xs mt-1">{{ createForm.errors.description_fr }}</div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Description (Anglais)</label>
            <textarea v-model="createForm.description_en" rows="3" placeholder="Description de l'axe en anglais..."
              class="w-full bg-slate-855 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all"></textarea>
            <div v-if="createForm.errors.description_en" class="text-red-400 text-xs mt-1">{{ createForm.errors.description_en }}</div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Responsable d'axe (Chercheur)</label>
              <select v-model="createForm.responsable_id"
                class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
                <option value="">Aucun responsable</option>
                <option v-for="u in users" :key="u.id" :value="u.id">
                  {{ u.prenom }} {{ u.nom }}
                </option>
              </select>
              <div v-if="createForm.errors.responsable_id" class="text-red-400 text-xs mt-1">{{ createForm.errors.responsable_id }}</div>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Ordre d'affichage</label>
              <input type="number" v-model="createForm.ordre_affichage" required min="1"
                class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
              <div v-if="createForm.errors.ordre_affichage" class="text-red-400 text-xs mt-1">{{ createForm.errors.ordre_affichage }}</div>
            </div>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" id="actif_create" v-model="createForm.actif"
              class="w-4 h-4 rounded border-white/10 bg-slate-850 text-brand-600 focus:ring-brand-500/55 focus:ring-offset-slate-900 focus:outline-none" />
            <label for="actif_create" class="text-sm text-slate-300 font-medium cursor-pointer">Axe actif</label>
          </div>

          <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/5">
            <button type="button" @click="showCreateModal = false" class="px-4 py-2 text-slate-400 hover:text-white text-sm transition-colors">Annuler</button>
            <button type="submit" :disabled="createForm.processing"
              class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-sm font-semibold transition-all disabled:opacity-50">Créer</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal de modification -->
    <div v-if="editingAxe" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4" @click.self="editingAxe = null">
      <div class="bg-slate-900 border border-white/10 rounded-2xl p-6 w-full max-w-lg shadow-glass animate-slide-up max-h-[95vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/5">
          <h3 class="text-lg font-semibold text-white">Modifier l'axe thématique</h3>
          <button @click="editingAxe = null" class="text-slate-400 hover:text-white transition-colors">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <form @submit.prevent="saveEdit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Code de l'axe (unique)</label>
              <input v-model="editForm.code" required placeholder="ex: epidemio"
                class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
              <div v-if="editForm.errors.code" class="text-red-400 text-xs mt-1">{{ editForm.errors.code }}</div>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Couleur</label>
              <div class="flex gap-2">
                <input type="color" v-model="editForm.couleur_hex"
                  class="w-10 h-9 bg-slate-850 border border-white/10 rounded-lg p-1 cursor-pointer" />
                <input v-model="editForm.couleur_hex" placeholder="#3B82F6"
                  class="flex-1 bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
              </div>
              <div v-if="editForm.errors.couleur_hex" class="text-red-400 text-xs mt-1">{{ editForm.errors.couleur_hex }}</div>
            </div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom (Français)</label>
            <input v-model="editForm.nom_fr" required placeholder="ex: Épidémiologie et Modélisation"
              class="w-full bg-slate-855 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
            <div v-if="editForm.errors.nom_fr" class="text-red-400 text-xs mt-1">{{ editForm.errors.nom_fr }}</div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom (Anglais)</label>
            <input v-model="editForm.nom_en" placeholder="ex: Epidemiology and Modeling"
              class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
            <div v-if="editForm.errors.nom_en" class="text-red-400 text-xs mt-1">{{ editForm.errors.nom_en }}</div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Description (Français)</label>
            <textarea v-model="editForm.description_fr" rows="3" placeholder="Description de l'axe en français..."
              class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all"></textarea>
            <div v-if="editForm.errors.description_fr" class="text-red-400 text-xs mt-1">{{ editForm.errors.description_fr }}</div>
          </div>

          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Description (Anglais)</label>
            <textarea v-model="editForm.description_en" rows="3" placeholder="Description de l'axe en anglais..."
              class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all"></textarea>
            <div v-if="editForm.errors.description_en" class="text-red-400 text-xs mt-1">{{ editForm.errors.description_en }}</div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Responsable d'axe (Chercheur)</label>
              <select v-model="editForm.responsable_id"
                class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
                <option value="">Aucun responsable</option>
                <option v-for="u in users" :key="u.id" :value="u.id">
                  {{ u.prenom }} {{ u.nom }}
                </option>
              </select>
              <div v-if="editForm.errors.responsable_id" class="text-red-400 text-xs mt-1">{{ editForm.errors.responsable_id }}</div>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Ordre d'affichage</label>
              <input type="number" v-model="editForm.ordre_affichage" required min="1"
                class="w-full bg-slate-850 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
              <div v-if="editForm.errors.ordre_affichage" class="text-red-400 text-xs mt-1">{{ editForm.errors.ordre_affichage }}</div>
            </div>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" id="actif_edit" v-model="editForm.actif"
              class="w-4 h-4 rounded border-white/10 bg-slate-850 text-brand-600 focus:ring-brand-500/55 focus:ring-offset-slate-900 focus:outline-none" />
            <label for="actif_edit" class="text-sm text-slate-300 font-medium cursor-pointer">Axe actif</label>
          </div>

          <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-white/5">
            <button type="button" @click="editingAxe = null" class="px-4 py-2 text-slate-400 hover:text-white text-sm transition-colors">Annuler</button>
            <button type="submit" :disabled="editForm.processing"
              class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-lg text-sm font-semibold transition-all disabled:opacity-50">Sauvegarder</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
