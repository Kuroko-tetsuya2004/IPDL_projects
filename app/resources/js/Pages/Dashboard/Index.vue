<script setup>
import { computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import {
  DocumentTextIcon, CircleStackIcon, ClipboardDocumentCheckIcon,
  UsersIcon, ChartBarIcon, ArrowUpRightIcon, ClockIcon, CheckCircleIcon,
  AcademicCapIcon, IdentificationIcon, UserGroupIcon, ShieldCheckIcon,
  GlobeAltIcon, EyeIcon, PencilSquareIcon, EnvelopeIcon
} from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
  stats: Object,
  userRole: String,
  totalUsers: Number,
  soumissionsEnAttente: Array,
  publicationsRecentes: Array,
  statsStatuts: Object,
  axe: Object,
  membres: Array,
  profile: Object,
  directeur: Object,
  coDirecteur: Object,
  mesPublications: Array,
  demandesSuppression: Array,
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

const voter = (demandeId, daccordValue) => {
  router.post(`/demandes-suppression/${demandeId}/voter`, {
    daccord: daccordValue
  }, {
    preserveScroll: true
  })
}

const roleLabel = {
  visitor:         'Visiteur',
  researcher:      'Chercheur',
  doctoral_student:'Doctorant',
  partner:         'Partenaire',
  axe_admin:       'Admin d\'axe',
  super_admin:     'Super Admin',
}

const typeLabel = {
  article:      'Article',
  document:     'Recherche en cours',
  event:        'Événement',
  dataset:      'Dataset',
  news:         'Actualité',
  thesis:       'Thèse',
  report:       'Rapport',
  presentation: 'Présentation',
}

const statutColor = {
  draft:            'text-slate-400 bg-slate-400/10 border-slate-400/20',
  submitted:        'text-blue-400 bg-blue-400/10 border-blue-400/20',
  under_review:     'text-amber-400 bg-amber-400/10 border-amber-400/20',
  published:        'text-emerald-400 bg-emerald-400/10 border-emerald-400/20',
  rejected:         'text-red-400 bg-red-400/10 border-red-400/20',
  revision_required:'text-rose-400 bg-rose-400/10 border-rose-400/20',
}

const workflowStatusLabel = {
  pending:          'En attente',
  approved:         'Approuvé',
  rejected:         'Rejeté',
  revision_required:'Révision requise',
}
</script>

<template>
  <div class="p-6 space-y-8 animate-fade-in text-slate-100">

    <!-- En-tête Global -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-white/5 pb-6">
      <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Bonjour, {{ user?.prenom }} {{ user?.nom }} 👋</h1>
        <p class="text-slate-400 text-sm mt-1.5 flex items-center gap-2">
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-600/20 text-brand-400 border border-brand-500/30">
            {{ roleLabel[userRole] ?? userRole }}
          </span>
          · Espace d'administration personnel
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Link href="/publications/soumettre"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition-all hover:-translate-y-0.5 hover:shadow-glow-blue">
          <DocumentTextIcon class="w-4 h-4" />
          Nouvelle publication
        </Link>
      </div>
    </div>

    <!-- Section Workflow des Votes de Suppression -->
    <div v-if="demandesSuppression && demandesSuppression.length > 0" class="space-y-4">
      <div class="border-l-4 border-red-500 bg-slate-900/60 p-6 rounded-r-xl border border-white/10 shadow-lg space-y-4">
        <div class="flex items-center justify-between border-b border-white/5 pb-3">
          <h2 class="font-bold text-white flex items-center gap-2.5">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
            </span>
            <span class="text-lg">Vote requis : Suppression de publication</span>
          </h2>
          <span class="text-xs font-semibold px-2 py-0.5 rounded bg-red-500/10 text-red-400 border border-red-500/20">
            {{ demandesSuppression.length }} {{ demandesSuppression.length > 1 ? 'demandes en cours' : 'demande en cours' }}
          </span>
        </div>

        <div class="space-y-6 divide-y divide-white/5">
          <div v-for="demande in demandesSuppression" :key="demande.id" class="pt-4 first:pt-0 space-y-3">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
              <div class="space-y-1">
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                  <span class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px] text-slate-300 uppercase font-semibold">
                    {{ typeLabel[demande.publication_type] ?? demande.publication_type }}
                  </span>
                  "{{ demande.publication_titre }}"
                </h3>
                <p class="text-xs text-slate-400">
                  Proposé par <span class="text-slate-300 font-semibold">{{ demande.propose_par_nom }}</span>
                  · Le {{ new Date(demande.created_at).toLocaleDateString() }}
                </p>
                <div class="mt-2 text-xs bg-slate-950/80 border border-white/5 p-3 rounded-lg text-slate-300 italic">
                  <strong>Motif :</strong> "{{ demande.motif }}"
                </div>
              </div>

              <!-- Vote Controls & Progress -->
              <div class="w-full md:w-64 space-y-3 shrink-0">
                <div class="space-y-1">
                  <div class="flex justify-between text-xs font-semibold">
                    <span class="text-emerald-400">Supprimer : {{ demande.votes_pour }} / {{ demande.seuil }} requis</span>
                    <span class="text-slate-400">Total axe : {{ demande.total_voters }}</span>
                  </div>
                  <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden border border-white/5">
                    <div class="h-full bg-red-500 transition-all duration-300" 
                      :style="{ width: Math.min(100, (demande.votes_pour / demande.seuil) * 100) + '%' }">
                    </div>
                  </div>
                  <div class="flex justify-between text-[10px] text-slate-500">
                    <span>Garder : {{ demande.votes_contre }}</span>
                    <span>Majorité absolue</span>
                  </div>
                </div>

                <!-- If User is Super Admin, they just see monitoring info -->
                <div v-if="userRole === 'super_admin'" class="text-xs text-center text-slate-400 font-medium py-1.5 bg-slate-800/40 rounded-lg border border-white/5">
                  Suivi par le Super Admin
                </div>

                <!-- If User is Researcher or Axe Admin, they can vote -->
                <div v-else class="flex gap-2">
                  <button @click="voter(demande.id, true)"
                    :disabled="demande.user_vote === true || demande.user_vote === 1"
                    :class="[
                      'flex-1 text-xs font-semibold py-2 px-3 rounded-lg transition-colors border text-center',
                      (demande.user_vote === true || demande.user_vote === 1)
                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 cursor-default'
                        : 'bg-red-600 hover:bg-red-500 text-white border-red-500/20 shadow-md shadow-red-600/10'
                    ]">
                    {{ (demande.user_vote === true || demande.user_vote === 1) ? '✓ Supprimer' : 'Supprimer' }}
                  </button>
                  <button @click="voter(demande.id, false)"
                    :disabled="demande.user_vote === false || demande.user_vote === 0"
                    :class="[
                      'flex-1 text-xs font-semibold py-2 px-3 rounded-lg transition-colors border text-center',
                      (demande.user_vote === false || demande.user_vote === 0)
                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 cursor-default'
                        : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-white/10'
                    ]">
                    {{ (demande.user_vote === false || demande.user_vote === 0) ? '✓ Garder' : 'Garder' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <!-- 1. DASHBOARD SUPER ADMIN -->
    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <div v-if="userRole === 'super_admin'" class="space-y-6">
      
      <!-- Metrics Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-semibold text-slate-400">Utilisateurs inscrits</div>
              <div class="text-3xl font-bold text-white mt-1">{{ totalUsers }}</div>
            </div>
            <div class="p-3 bg-brand-500/10 rounded-lg text-brand-400">
              <UsersIcon class="w-6 h-6" />
            </div>
          </div>
          <Link href="/admin/membres" class="text-xs text-brand-400 hover:underline flex items-center gap-1 mt-4">
            Gérer les utilisateurs <ArrowUpRightIcon class="w-3 h-3" />
          </Link>
        </div>

        <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-semibold text-slate-400">Total Publications</div>
              <div class="text-3xl font-bold text-white mt-1">{{ stats?.total_publications ?? 0 }}</div>
            </div>
            <div class="p-3 bg-emerald-500/10 rounded-lg text-emerald-400">
              <DocumentTextIcon class="w-6 h-6" />
            </div>
          </div>
          <Link href="/admin/publications" class="text-xs text-emerald-400 hover:underline flex items-center gap-1 mt-4">
            Voir le catalogue <ArrowUpRightIcon class="w-3 h-3" />
          </Link>
        </div>

        <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-semibold text-slate-400">Jeux de Données (Datasets)</div>
              <div class="text-3xl font-bold text-white mt-1">{{ stats?.total_datasets ?? 0 }}</div>
            </div>
            <div class="p-3 bg-cyan-500/10 rounded-lg text-cyan-400">
              <CircleStackIcon class="w-6 h-6" />
            </div>
          </div>
          <Link href="/admin/datasets" class="text-xs text-cyan-400 hover:underline flex items-center gap-1 mt-4">
            Gérer les datasets <ArrowUpRightIcon class="w-3 h-3" />
          </Link>
        </div>

        <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-semibold text-slate-400">Axes Actifs</div>
              <div class="text-3xl font-bold text-white mt-1">{{ stats?.total_axes ?? 0 }}</div>
            </div>
            <div class="p-3 bg-purple-500/10 rounded-lg text-purple-400">
              <ChartBarIcon class="w-6 h-6" />
            </div>
          </div>
          <Link href="/admin/axes" class="text-xs text-purple-400 hover:underline flex items-center gap-1 mt-4">
            Axes thématiques <ArrowUpRightIcon class="w-3 h-3" />
          </Link>
        </div>
      </div>

      <!-- Main Layout Panels (Split screen) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Column 1 & 2 : Pending workflow & recent publications -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Validation Workflow List -->
          <div class="bg-slate-900/60 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 bg-slate-900/40 flex items-center justify-between">
              <h2 class="font-bold text-white flex items-center gap-2">
                <ClipboardDocumentCheckIcon class="w-5 h-5 text-amber-400" />
                Soumissions en attente de validation globale
              </h2>
              <Link href="/soumissions" class="text-xs text-brand-400 hover:underline flex items-center gap-1">
                Gérer tout <ArrowUpRightIcon class="w-3 h-3" />
              </Link>
            </div>
            
            <div v-if="soumissionsEnAttente?.length > 0" class="divide-y divide-white/5">
              <div v-for="s in soumissionsEnAttente" :key="s.id" class="px-6 py-4 flex items-center justify-between hover:bg-white/2 transition-colors">
                <div>
                  <h3 class="text-sm font-bold text-white">{{ s.publication?.titre_fr }}</h3>
                  <p class="text-xs text-slate-400 mt-1">
                    Soumis par <span class="text-slate-300 font-semibold">{{ s.soumetteur?.prenom }} {{ s.soumetteur?.nom }}</span> · 
                    Axe : <span class="text-brand-400 font-semibold">{{ s.publication?.axe?.code }}</span> ·
                    Le {{ new Date(s.date_soumission).toLocaleDateString() }}
                  </p>
                </div>
                <Link :href="`/publications/${s.publication_id}`" class="text-xs px-3 py-1.5 rounded-lg border border-white/10 text-slate-300 hover:bg-white/5 font-semibold">
                  Inspecter
                </Link>
              </div>
            </div>
            <div v-else class="p-8 text-center text-slate-400 text-sm">
              🎉 Aucune soumission en attente à valider.
            </div>
          </div>

          <!-- Recent Portal activity -->
          <div class="bg-slate-900/60 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 bg-slate-900/40">
              <h2 class="font-bold text-white flex items-center gap-2">
                <ClockIcon class="w-5 h-5 text-brand-400" />
                Dernières productions du laboratoire
              </h2>
            </div>
            
            <div v-if="publicationsRecentes?.length > 0" class="divide-y divide-white/5">
              <div v-for="pub in publicationsRecentes" :key="pub.id" class="px-6 py-4 flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-semibold text-white truncate max-w-md">{{ pub.titre_fr }}</h3>
                  <p class="text-xs text-slate-400 mt-1">
                    Auteur : {{ pub.auteur?.prenom }} {{ pub.auteur?.nom }} ·
                    Axe : {{ pub.axe?.code ?? 'Global' }}
                  </p>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-brand-500/10 text-brand-400 border border-brand-500/20 capitalize">
                  {{ typeLabel[pub.type] ?? pub.type }}
                </span>
              </div>
            </div>
          </div>

        </div>

        <!-- Column 3 : Quick Actions & Status chart list -->
        <div class="space-y-6">
          
          <!-- Actions panel -->
          <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6">
            <h2 class="font-bold text-white flex items-center gap-2 mb-4">
              <ShieldCheckIcon class="w-5 h-5 text-brand-400" />
              Outils d'administration
            </h2>
            <div class="flex flex-col gap-2">
              <Link href="/admin/membres" class="flex items-center justify-between p-3 rounded-lg border border-white/5 hover:border-brand-500/30 hover:bg-brand-500/5 transition-all">
                <span class="text-sm font-semibold text-slate-200">Gérer les membres</span>
                <UsersIcon class="w-4 h-4 text-slate-400" />
              </Link>
              <Link href="/admin/axes" class="flex items-center justify-between p-3 rounded-lg border border-white/5 hover:border-brand-500/30 hover:bg-brand-500/5 transition-all">
                <span class="text-sm font-semibold text-slate-200">Axes thématiques</span>
                <ChartBarIcon class="w-4 h-4 text-slate-400" />
              </Link>
              <Link href="/admin/parametres" class="flex items-center justify-between p-3 rounded-lg border border-white/5 hover:border-brand-500/30 hover:bg-brand-500/5 transition-all">
                <span class="text-sm font-semibold text-slate-200">Paramètres système</span>
                <ShieldCheckIcon class="w-4 h-4 text-slate-400" />
              </Link>
            </div>
          </div>

          <!-- Status stats list -->
          <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6">
            <h3 class="font-bold text-white text-sm mb-4">Publications par statut</h3>
            <div class="space-y-3">
              <div v-for="(count, status) in statsStatuts" :key="status" class="flex items-center justify-between text-xs">
                <span class="capitalize font-semibold text-slate-300">{{ status }}</span>
                <span :class="['px-2.5 py-0.5 rounded-full border text-[11px] font-bold', statutColor[status] ?? '']">{{ count }}</span>
              </div>
            </div>
          </div>

        </div>

      </div>

    </div>

    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <!-- 2. DASHBOARD AXE ADMIN -->
    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <div v-else-if="userRole === 'axe_admin'" class="space-y-6">
      
      <!-- Axe header banner -->
      <div v-if="axe" class="p-6 rounded-xl border" :style="{ borderColor: (axe.couleur_hex ?? '#3B82F6') + '40', background: 'linear-gradient(135deg, ' + (axe.couleur_hex ?? '#3B82F6') + '10 0%, transparent 100%)' }">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded" :style="{ backgroundColor: (axe.couleur_hex ?? '#3B82F6') + '20', color: (axe.couleur_hex ?? '#3B82F6') }">
              Axe de recherche géré
            </span>
            <h2 class="text-xl font-extrabold text-white mt-2">{{ axe.nom_fr }}</h2>
            <p class="text-xs text-slate-400 mt-1">{{ axe.description_fr }}</p>
          </div>
          <div class="w-12 h-12 rounded-lg flex items-center justify-center text-xl font-bold text-white" :style="{ backgroundColor: axe.couleur_hex ?? '#3B82F6' }">
            {{ axe.code }}
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Workflow and Publications of the Axe -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Validation queue -->
          <div class="bg-slate-900/60 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 bg-slate-900/40 flex items-center justify-between">
              <h2 class="font-bold text-white flex items-center gap-2">
                <ClipboardDocumentCheckIcon class="w-5 h-5 text-amber-400" />
                Workflow de validation de l'axe
              </h2>
            </div>
            
            <div v-if="soumissionsEnAttente?.length > 0" class="divide-y divide-white/5">
              <div v-for="s in soumissionsEnAttente" :key="s.id" class="px-6 py-4 flex items-center justify-between hover:bg-white/2 transition-colors">
                <div>
                  <h3 class="text-sm font-bold text-white">{{ s.publication?.titre_fr }}</h3>
                  <p class="text-xs text-slate-400 mt-1">
                    Soumis par <span class="text-slate-300 font-semibold">{{ s.soumetteur?.prenom }} {{ s.soumetteur?.nom }}</span> ·
                    Soumis le : {{ new Date(s.date_soumission).toLocaleDateString() }}
                  </p>
                </div>
                <Link :href="`/publications/${s.publication_id}`" class="text-xs px-3 py-1.5 rounded-lg border border-white/10 text-slate-300 hover:bg-white/5 font-semibold">
                  Inspecter
                </Link>
              </div>
            </div>
            <div v-else class="p-8 text-center text-slate-400 text-sm">
              👍 Aucune soumission en attente dans votre axe.
            </div>
          </div>

          <!-- Recent Axe publications -->
          <div class="bg-slate-900/60 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 bg-slate-900/40">
              <h2 class="font-bold text-white flex items-center gap-2">
                <DocumentTextIcon class="w-5 h-5 text-brand-400" />
                Publications récentes de l'axe
              </h2>
            </div>
            
            <div v-if="publicationsRecentes?.length > 0" class="divide-y divide-white/5">
              <div v-for="pub in publicationsRecentes" :key="pub.id" class="px-6 py-4 flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-semibold text-white">{{ pub.titre_fr }}</h3>
                  <p class="text-xs text-slate-400 mt-1">
                    Par : {{ pub.auteur?.prenom }} {{ pub.auteur?.nom }} ·
                    {{ new Date(pub.created_at).toLocaleDateString() }}
                  </p>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-brand-500/10 text-brand-400 border border-brand-500/20 capitalize">
                  {{ typeLabel[pub.type] ?? pub.type }}
                </span>
              </div>
            </div>
            <div v-else class="p-8 text-center text-slate-400 text-sm">
              Aucune publication enregistrée dans cet axe.
            </div>
          </div>

        </div>

        <!-- Right column: Axe members list -->
        <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6">
          <h2 class="font-bold text-white flex items-center gap-2 mb-4">
            <UserGroupIcon class="w-5 h-5 text-purple-400" />
            Membres rattachés à l'axe ({{ membres?.length ?? 0 }})
          </h2>
          
          <div v-if="membres?.length > 0" class="space-y-4 max-h-[400px] overflow-y-auto pr-1">
            <div v-for="m in membres" :key="m.id" class="flex items-center gap-3 p-2 rounded-lg hover:bg-white/2">
              <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center font-bold text-xs text-brand-400 uppercase">
                {{ m.prenom.charAt(0) }}{{ m.nom.charAt(0) }}
              </div>
              <div>
                <div class="text-xs font-bold text-white">{{ m.prenom }} {{ m.nom }}</div>
                <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                  <span class="capitalize text-slate-300 font-semibold">{{ roleLabel[m.role] ?? m.role }}</span>
                  · {{ m.email }}
                </div>
              </div>
            </div>
          </div>
          <div v-else class="p-4 text-center text-slate-500 text-xs">
            Aucun membre affecté à cet axe principal.
          </div>
        </div>

      </div>

    </div>

    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <!-- 3. DASHBOARD CHERCHEUR -->
    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <div v-else-if="userRole === 'researcher'" class="space-y-6">
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Column 1 & 2 : Publications list -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-slate-900/60 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 bg-slate-900/40 flex items-center justify-between">
              <h2 class="font-bold text-white flex items-center gap-2">
                <DocumentTextIcon class="w-5 h-5 text-brand-400" />
                Mes publications scientifiques
              </h2>
              <Link href="/mes-publications" class="text-xs text-brand-400 hover:underline flex items-center gap-1">
                Tout voir <ArrowUpRightIcon class="w-3 h-3" />
              </Link>
            </div>

            <div v-if="mesPublications?.length > 0" class="divide-y divide-white/5">
              <div v-for="pub in mesPublications" :key="pub.id" class="px-6 py-4 flex items-center justify-between hover:bg-white/2 transition-colors">
                <div class="min-w-0 pr-4">
                  <h3 class="text-sm font-bold text-white truncate">{{ pub.titre_fr }}</h3>
                  <p class="text-xs text-slate-400 mt-1 capitalize">
                    {{ typeLabel[pub.type] ?? pub.type }} · Axe : {{ pub.axe?.code ?? 'Global' }} ·
                    Modifié le : {{ new Date(pub.created_at).toLocaleDateString() }}
                  </p>
                </div>
                <div class="flex items-center gap-3">
                  <span :class="['text-[11px] font-bold px-2.5 py-0.5 rounded-full border capitalize', statutColor[pub.statut] ?? '']">
                    {{ pub.statut }}
                  </span>
                  <Link :href="`/publications/${pub.id}/modifier`" class="p-1 rounded text-slate-400 hover:text-white hover:bg-white/5" title="Modifier">
                    <PencilSquareIcon class="w-4 h-4" />
                  </Link>
                </div>
              </div>
            </div>
            <div v-else class="p-10 text-center text-slate-400">
              <DocumentTextIcon class="w-12 h-12 text-slate-600 mx-auto mb-3" />
              <p class="text-sm">Vous n'avez pas encore soumis de publication.</p>
              <Link href="/publications/soumettre" class="mt-4 inline-flex text-xs font-semibold px-4 py-2 bg-brand-600 rounded-lg text-white hover:bg-brand-500">
                Soumettre ma première publication
              </Link>
            </div>
          </div>
        </div>

        <!-- Right column: Researcher Profile details card -->
        <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6 space-y-6">
          <h2 class="font-bold text-white flex items-center gap-2 pb-3 border-b border-white/5">
            <IdentificationIcon class="w-5 h-5 text-cyan-400" />
            Mon profil chercheur
          </h2>

          <div v-if="profile" class="space-y-4">
            <div>
              <div class="text-[10px] uppercase font-bold text-slate-500">Spécialité</div>
              <div class="text-sm font-semibold text-slate-200 mt-0.5">{{ profile.specialite ?? 'Non définie' }}</div>
            </div>

            <div>
              <div class="text-[10px] uppercase font-bold text-slate-500">Indicateur H-Index</div>
              <div class="text-xl font-bold text-cyan-400 mt-0.5">{{ profile.h_index ?? 'N/A' }}</div>
            </div>

            <div>
              <div class="text-[10px] uppercase font-bold text-slate-500">Statut laboratoire</div>
              <div class="text-sm font-semibold text-slate-200 mt-0.5 capitalize">{{ profile.statut_chercheur ?? 'Permanent' }}</div>
            </div>

            <div>
              <div class="text-[10px] uppercase font-bold text-slate-500">Domaines d'expertise</div>
              <div v-if="profile.domaines_expertise" class="flex flex-wrap gap-1 mt-1.5">
                <span v-for="tag in profile.domaines_expertise" :key="tag" class="px-2 py-0.5 bg-slate-800 text-[10px] rounded border border-white/5 text-slate-300">
                  {{ tag }}
                </span>
              </div>
              <div v-else class="text-xs text-slate-500 italic mt-1">Aucune expertise renseignée.</div>
            </div>
          </div>

          <div v-else class="text-center p-4">
            <p class="text-xs text-slate-500 italic">Profil chercheur non initialisé.</p>
          </div>
        </div>

      </div>

    </div>

    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <!-- 4. DASHBOARD DOCTORANT -->
    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <div v-else-if="userRole === 'doctoral_student'" class="space-y-6">
      
      <!-- Thesis details block -->
      <div v-if="profile" class="bg-slate-900/60 border border-white/10 rounded-xl p-6 shadow-sm">
        <h2 class="font-extrabold text-white text-base flex items-center gap-2 pb-3 border-b border-white/5">
          <AcademicCapIcon class="w-6 h-6 text-brand-400" />
          Fiche de Thèse de Doctorat
        </h2>
        
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 space-y-3">
            <span class="text-[10px] uppercase tracking-wider font-bold text-slate-500">Sujet de thèse</span>
            <p class="text-base font-bold text-slate-100 italic">« {{ profile.titre_these ?? 'Non renseigné' }} »</p>
            
            <div class="grid grid-cols-2 gap-4 mt-2">
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 block">École Doctorale</span>
                <span class="text-xs font-semibold text-slate-300">{{ profile.ecole_doctorale ?? 'Non renseignée' }}</span>
              </div>
              <div>
                <span class="text-[10px] uppercase font-bold text-slate-500 block">Financement</span>
                <span class="text-xs font-semibold text-slate-300">{{ profile.financement ?? 'Non renseigné' }}</span>
              </div>
            </div>
          </div>
          
          <div class="space-y-3 bg-white/2 p-4 rounded-lg border border-white/5">
            <div v-if="directeur">
              <span class="text-[10px] uppercase font-bold text-slate-500">Directeur de Thèse</span>
              <div class="text-xs font-bold text-white mt-0.5">{{ directeur.titre_academique }} {{ directeur.prenom }} {{ directeur.nom }}</div>
            </div>
            <div v-if="coDirecteur" class="mt-2">
              <span class="text-[10px] uppercase font-bold text-slate-500">Co-directeur de Thèse</span>
              <div class="text-xs font-bold text-white mt-0.5">{{ coDirecteur.titre_academique }} {{ coDirecteur.prenom }} {{ coDirecteur.nom }}</div>
            </div>
            <div class="mt-3 text-[10px] text-slate-400">
              Inscrit en : {{ profile.date_inscription ? new Date(profile.date_inscription).getFullYear() : 'N/A' }}
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Publications of the PhD student -->
        <div class="bg-slate-900/60 border border-white/10 rounded-xl overflow-hidden">
          <div class="px-6 py-4 border-b border-white/10 bg-slate-900/40">
            <h2 class="font-bold text-white flex items-center gap-2">
              <DocumentTextIcon class="w-5 h-5 text-brand-400" />
              Mes publications
            </h2>
          </div>

          <div v-if="mesPublications?.length > 0" class="divide-y divide-white/5">
            <div v-for="pub in mesPublications" :key="pub.id" class="px-6 py-4 flex items-center justify-between">
              <div>
                <h3 class="text-sm font-semibold text-white truncate max-w-sm">{{ pub.titre_fr }}</h3>
                <p class="text-xs text-slate-400 mt-1 capitalize">
                  {{ typeLabel[pub.type] ?? pub.type }} · Modifier le : {{ new Date(pub.created_at).toLocaleDateString() }}
                </p>
              </div>
              <span :class="['text-[11px] font-bold px-2.5 py-0.5 rounded-full border capitalize', statutColor[pub.statut] ?? '']">
                {{ pub.statut }}
              </span>
            </div>
          </div>
          <div v-else class="p-8 text-center text-slate-400 text-xs">
            Aucune publication soumise.
          </div>
        </div>

        <!-- Validation workflow list -->
        <div class="bg-slate-900/60 border border-white/10 rounded-xl overflow-hidden">
          <div class="px-6 py-4 border-b border-white/10 bg-slate-900/40">
            <h2 class="font-bold text-white flex items-center gap-2">
              <ClipboardDocumentCheckIcon class="w-5 h-5 text-amber-400" />
              Suivi de mes validations en cours
            </h2>
          </div>

          <div v-if="soumissionsEnAttente?.length > 0" class="divide-y divide-white/5">
            <div v-for="s in soumissionsEnAttente" :key="s.id" class="px-6 py-4">
              <div class="flex items-center justify-between gap-3">
                <h3 class="text-sm font-semibold text-white truncate max-w-sm">{{ s.publication?.titre_fr }}</h3>
                <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full border', statutColor[s.publication?.statut] ?? '']">
                  {{ workflowStatusLabel[s.statut] ?? s.statut }}
                </span>
              </div>
              <div class="text-xs text-slate-400 mt-1.5 flex items-center gap-1.5">
                <ClockIcon class="w-3.5 h-3.5" /> Soumis le {{ new Date(s.date_soumission).toLocaleDateString() }}
              </div>
              <div v-if="s.commentaire" class="mt-2.5 p-2 bg-rose-500/5 border border-rose-500/10 rounded text-xs text-rose-300">
                <strong>Commentaire de révision :</strong> {{ s.commentaire }}
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center text-slate-400 text-xs">
            Aucune validation en attente pour le moment.
          </div>
        </div>

      </div>

    </div>

    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <!-- 5. DASHBOARD VISITEUR / PARTENAIRE -->
    <!-- ──────────────────────────────────────────────────────────────────────── -->
    <div v-else class="space-y-6">
      
      <div class="bg-slate-900/60 border border-white/10 rounded-xl p-6">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
          <GlobeAltIcon class="w-5 h-5 text-brand-400" />
          Bienvenue sur l'Espace Laboratoire UMMISCO
        </h2>
        <p class="text-sm text-slate-400 mt-2 leading-relaxed">
          En tant que partenaire ou visiteur académique du laboratoire, cet espace vous permet de consulter des ressources, d'accéder aux statistiques de recherche, et de suivre les dernières productions scientifiques ouvertes.
        </p>
      </div>

      <!-- Quick metrics -->
      <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5">
          <div class="text-2xl font-bold text-white">{{ stats?.total_publications ?? 0 }}</div>
          <div class="text-slate-400 text-xs mt-1">Publications</div>
        </div>
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5">
          <div class="text-2xl font-bold text-emerald-400">{{ stats?.total_datasets ?? 0 }}</div>
          <div class="text-slate-400 text-xs mt-1">Datasets ouverts</div>
        </div>
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5">
          <div class="text-2xl font-bold text-brand-400">{{ stats?.total_chercheurs ?? 0 }}</div>
          <div class="text-slate-400 text-xs mt-1">Chercheurs rattachés</div>
        </div>
      </div>

      <!-- Recent outputs -->
      <div class="bg-slate-900/60 border border-white/10 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/10 bg-slate-900/40">
          <h2 class="font-bold text-white text-sm">Dernières publications scientifiques parues</h2>
        </div>
        
        <div v-if="publicationsRecentes?.length > 0" class="divide-y divide-white/5">
          <div v-for="pub in publicationsRecentes" :key="pub.id" class="px-6 py-4 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-semibold text-white">{{ pub.titre_fr }}</h3>
              <p class="text-xs text-slate-400 mt-1">
                Par : {{ pub.auteur?.prenom }} {{ pub.auteur?.nom }} ·
                Axe : {{ pub.axe?.code ?? 'Global' }}
              </p>
            </div>
            <Link :href="`/publications/${pub.id}`" class="text-xs px-3 py-1 bg-white/5 hover:bg-white/10 text-white rounded font-medium">
              Consulter
            </Link>
          </div>
        </div>
        <div v-else class="p-6 text-center text-slate-500 text-xs">
          Aucune publication récente disponible.
        </div>
      </div>

    </div>

  </div>
</template>
