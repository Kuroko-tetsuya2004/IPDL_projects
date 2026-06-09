<script setup>
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { UserCircleIcon, EnvelopeIcon, PhoneIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({ user: Object })

const form = useForm({
  nom:                 props.user.nom ?? '',
  prenom:              props.user.prenom ?? '',
  titre_academique:    props.user.titre_academique ?? '',
  specialite:          props.user.specialite ?? '',
  orcid_id:            props.user.orcid_id ?? '',
  bio_fr:              props.user.bio_fr ?? '',
  bio_en:              props.user.bio_en ?? '',
  telephone:           props.user.telephone ?? '',
  institution:         props.user.institution ?? '',
  langue_preference:   props.user.langue_preference ?? 'fr',
  email_notifications: props.user.email_notifications ?? true,
})

const submit = () => form.put('/profile')

const roleColors = {
  visitor:          'bg-slate-400/10 text-slate-400 border-slate-400/20',
  researcher:       'bg-blue-400/10 text-blue-400 border-blue-400/20',
  doctoral_student: 'bg-purple-400/10 text-purple-400 border-purple-400/20',
  partner:          'bg-green-400/10 text-green-400 border-green-400/20',
  axe_admin:        'bg-amber-400/10 text-amber-400 border-amber-400/20',
  super_admin:      'bg-red-400/10 text-red-400 border-red-400/20',
}
const roleLabel = {
  visitor: 'Visiteur', researcher: 'Chercheur', doctoral_student: 'Doctorant',
  partner: 'Partenaire', axe_admin: "Admin d'axe", super_admin: 'Super Admin',
}
</script>

<template>
  <div class="p-6 max-w-3xl mx-auto space-y-8 animate-fade-in">

    <!-- En-tête profil -->
    <div class="flex items-center gap-5">
      <div class="w-16 h-16 rounded-full bg-brand-600/20 border border-brand-500/30 flex items-center justify-center">
        <UserCircleIcon class="w-10 h-10 text-brand-400" />
      </div>
      <div>
        <h1 class="text-xl font-bold text-white">{{ user.prenom }} {{ user.nom }}</h1>
        <div class="flex items-center gap-2 mt-1">
          <span :class="['text-xs font-semibold px-2.5 py-0.5 rounded-full border', roleColors[user.role]]">
            {{ roleLabel[user.role] ?? user.role }}
          </span>
          <span class="text-slate-500 text-xs">{{ user.email }}</span>
        </div>
      </div>
    </div>

    <!-- Formulaire -->
    <form @submit.prevent="submit" class="space-y-6">

      <!-- Infos de base -->
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Informations personnelles</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Prénom</label>
            <input v-model="form.prenom" type="text" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500/50 transition-all" required />
            <p v-if="form.errors.prenom" class="text-xs text-red-400 mt-1">{{ form.errors.prenom }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom</label>
            <input v-model="form.nom" type="text" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 focus:border-brand-500/50 transition-all" required />
            <p v-if="form.errors.nom" class="text-xs text-red-400 mt-1">{{ form.errors.nom }}</p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Titre académique</label>
            <select v-model="form.titre_academique" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
              <option value="">—</option>
              <option value="Dr">Dr.</option>
              <option value="Pr">Pr.</option>
              <option value="M">M.</option>
              <option value="Mme">Mme</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Spécialité</label>
            <input v-model="form.specialite" type="text" placeholder="Ex: Épidémiologie" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">ORCID ID</label>
          <input v-model="form.orcid_id" type="text" placeholder="0000-0000-0000-0000" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Institution</label>
          <input v-model="form.institution" type="text" placeholder="Université / Organisation" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Téléphone</label>
          <input v-model="form.telephone" type="tel" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all" />
        </div>
      </div>

      <!-- Biographies -->
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Biographie</h2>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Biographie (Français)</label>
          <textarea v-model="form.bio_fr" rows="4" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all resize-none"></textarea>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-400 mb-1.5">Biography (English)</label>
          <textarea v-model="form.bio_en" rows="4" class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all resize-none"></textarea>
        </div>
      </div>

      <!-- Préférences -->
      <div class="bg-slate-900/60 border border-white/8 rounded-xl p-6 space-y-4">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Préférences</h2>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-white font-medium">Langue d'interface</p>
            <p class="text-xs text-slate-500">Langue par défaut pour l'interface</p>
          </div>
          <select v-model="form.langue_preference" class="bg-slate-800/60 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-all">
            <option value="fr">Français</option>
            <option value="en">English</option>
          </select>
        </div>
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-white font-medium">Notifications par email</p>
            <p class="text-xs text-slate-500">Recevoir les notifications par email</p>
          </div>
          <button type="button" @click="form.email_notifications = !form.email_notifications"
            :class="['w-11 h-6 rounded-full transition-colors relative', form.email_notifications ? 'bg-brand-600' : 'bg-slate-700']">
            <span :class="['absolute top-0.5 w-5 h-5 rounded-full bg-white transition-transform shadow', form.email_notifications ? 'translate-x-5' : 'translate-x-0.5']"></span>
          </button>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3">
        <button type="submit" :disabled="form.processing"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-brand-600 hover:bg-brand-500 disabled:opacity-50 text-white rounded-lg text-sm font-semibold transition-all">
          <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          Sauvegarder les modifications
        </button>
      </div>
    </form>
  </div>
</template>
