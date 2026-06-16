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
    <h1 class="text-2xl font-bold text-[var(--text)] flex items-center gap-3">
      <Cog6ToothIcon class="w-7 h-7 text-[var(--text-subtle)]" />
      Paramètres système
    </h1>

    <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl overflow-hidden">
      <div class="divide-y divide-[var(--border)]">
        <div v-for="p in parametres" :key="p.cle" class="px-6 py-4 flex items-center gap-4">
          <div class="flex-1 min-w-0">
            <p class="text-sm font-mono font-medium text-brand-400">{{ p.cle }}</p>
            <p class="text-xs text-[var(--text-subtle)] mt-0.5">{{ p.description }}</p>
          </div>
          <div class="flex items-center gap-3">
            <div v-if="editingKey === p.cle">
              <input v-model="editForm.valeur" type="text"
                class="bg-[var(--surface-alt)] border border-brand-500/50 rounded-lg px-3 py-1.5 text-sm text-[var(--text)] focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all w-48" />
            </div>
            <span v-else class="text-sm text-[var(--text)] font-mono bg-[var(--surface-alt)] px-3 py-1.5 rounded-lg">{{ p.valeur }}</span>
            <button v-if="editingKey === p.cle" @click="saveEdit(p.cle)"
              class="p-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 transition-all">
              <CheckIcon class="w-4 h-4" />
            </button>
            <button v-else @click="startEdit(p)"
              class="p-1.5 rounded-lg bg-[var(--surface-alt)] hover:bg-slate-700 text-[var(--text-subtle)] hover:text-[var(--text)] border border-[var(--border)] transition-all">
              <PencilIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
