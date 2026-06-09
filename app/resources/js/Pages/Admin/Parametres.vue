<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { Cog6ToothIcon, PencilIcon, CheckIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })
const props = defineProps({ parametres: Array })

const editingKey = ref(null)
const editForm = useForm({ valeur: '' })

const startEdit = (p) => {
  editingKey.value = p.cle
  editForm.valeur = p.valeur
}

const saveEdit = (cle) => {
  editForm.put(`/admin/parametres/${cle}`, { onSuccess: () => { editingKey.value = null } })
}
</script>

<template>
  <div class="p-6 space-y-6 animate-fade-in">
    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
      <Cog6ToothIcon class="w-7 h-7 text-slate-400" />
      Paramètres système
    </h1>

    <div class="bg-slate-900/60 border border-white/8 rounded-xl overflow-hidden">
      <div class="divide-y divide-white/5">
        <div v-for="p in parametres" :key="p.cle" class="px-6 py-4 flex items-center gap-4">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-mono font-medium text-brand-400">{{ p.cle }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ p.description }}</p>
          </div>
          <div class="flex items-center gap-3">
            <div v-if="editingKey === p.cle">
              <input v-model="editForm.valeur" type="text"
                class="bg-slate-800 border border-brand-500/50 rounded-lg px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all w-48" />
            </div>
            <span v-else class="text-sm text-white font-mono bg-slate-800 px-3 py-1.5 rounded-lg">{{ p.valeur }}</span>
            <button v-if="editingKey === p.cle" @click="saveEdit(p.cle)"
              class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 transition-all">
              <CheckIcon class="w-4 h-4" />
            </button>
            <button v-else @click="startEdit(p)"
              class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white border border-white/5 transition-all">
              <PencilIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
