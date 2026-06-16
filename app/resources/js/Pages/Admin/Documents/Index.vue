<script setup>
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import {
  DocumentTextIcon,
  ClipboardDocumentListIcon,
  ShoppingCartIcon,
  ArrowRightIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const documents = [
  {
    href: '/admin/documents/convention-stage',
    icon: DocumentTextIcon,
    color: 'from-blue-500/20 to-blue-600/10 border-blue-500/30 text-blue-400',
    iconBg: 'bg-blue-500/10',
    title: 'Convention de Stage',
    description: 'Générer une convention d\'accueil de stagiaire conforme au modèle IRD — informations stagiaire, établissement, durée, gratification et encadrement.',
    badge: 'IRD — EPST',
  },
  {
    href: '/admin/documents/prestation-service',
    icon: ClipboardDocumentListIcon,
    color: 'from-emerald-500/20 to-emerald-600/10 border-emerald-500/30 text-emerald-400',
    iconBg: 'bg-emerald-500/10',
    title: 'Reçu de Prestation de Service',
    description: 'Générer un reçu officiel de prestation de service — informations prestataire, objet, durée, montant brut/net avec calcul automatique de l\'impôt (5%).',
    badge: 'FI-8 — V5',
  },
  {
    href: '/admin/documents/bon-achat',
    icon: ShoppingCartIcon,
    color: 'from-amber-500/20 to-amber-600/10 border-amber-500/30 text-amber-400',
    iconBg: 'bg-amber-500/10',
    title: 'Bon d\'Achat (Demande d\'Achat)',
    description: 'Générer une demande de bon d\'achat avec tableau d\'articles détaillé — fournisseurs, quantités, prix unitaires et total. Nécessite une facture pro forma.',
    badge: 'Comptabilité',
  },
]
</script>

<template>
  <div class="p-6 max-w-5xl mx-auto space-y-8 animate-fade-in">

    <!-- Header -->
    <div class="border-b border-white/5 pb-6 flex items-start sm:items-center justify-between flex-col sm:flex-row gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
          <ClipboardDocumentListIcon class="w-8 h-8 text-brand-400" />
          Documents Administratifs
        </h1>
        <p class="text-slate-400 text-sm mt-2">
          Générez, téléchargez et imprimez les documents officiels IRD/UMMISCO au format PDF.
          <span class="text-amber-400 font-semibold">Accès réservé au Super Administrateur.</span>
        </p>
      </div>
      <Link href="/admin/documents/historique" class="flex items-center shrink-0 gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-500/20">
        <DocumentTextIcon class="w-5 h-5" />
        Historique des Archives
      </Link>
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <Link
        v-for="doc in documents"
        :key="doc.href"
        :href="doc.href"
        :class="[
          'group relative flex flex-col p-6 rounded-2xl border bg-gradient-to-br transition-all duration-300',
          'hover:-translate-y-1 hover:shadow-xl cursor-pointer',
          doc.color
        ]"
      >
        <!-- Badge -->
        <span class="absolute top-4 right-4 text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded bg-white/5 text-slate-400 border border-white/10">
          {{ doc.badge }}
        </span>

        <!-- Icon -->
        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center mb-5 transition-transform group-hover:scale-110', doc.iconBg]">
          <component :is="doc.icon" class="w-6 h-6" :class="doc.color.split(' ').find(c => c.startsWith('text-'))" />
        </div>

        <!-- Content -->
        <h2 class="text-white font-bold text-lg leading-tight mb-2">{{ doc.title }}</h2>
        <p class="text-slate-400 text-sm leading-relaxed flex-1">{{ doc.description }}</p>

        <!-- CTA -->
        <div class="flex items-center gap-2 mt-5 text-sm font-semibold" :class="doc.color.split(' ').find(c => c.startsWith('text-'))">
          Remplir le formulaire
          <ArrowRightIcon class="w-4 h-4 transition-transform group-hover:translate-x-1" />
        </div>
      </Link>
    </div>

    <!-- Info banner -->
    <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 flex items-start gap-4">
      <div class="w-8 h-8 rounded-lg bg-brand-500/10 flex items-center justify-center shrink-0 mt-0.5">
        <DocumentTextIcon class="w-5 h-5 text-brand-400" />
      </div>
      <div>
        <p class="text-sm font-semibold text-white mb-1">Comment ça fonctionne ?</p>
        <p class="text-sm text-slate-400 leading-relaxed">
          Sélectionnez le type de document, remplissez le formulaire avec les informations du concerné, puis cliquez sur
          <strong class="text-white">« Générer PDF »</strong> pour télécharger le document, ou sur
          <strong class="text-white">« Imprimer »</strong> pour envoyer directement à l'imprimante.
          Les documents générés sont conformes aux modèles officiels de l'IRD Sénégal.
        </p>
      </div>
    </div>

  </div>
</template>
