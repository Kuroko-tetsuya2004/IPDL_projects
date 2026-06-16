<script setup>
import { computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import {
  DocumentTextIcon, CircleStackIcon, ClipboardDocumentCheckIcon,
  UsersIcon, ChartBarIcon, ArrowUpRightIcon, ClockIcon, CheckCircleIcon,
  AcademicCapIcon, IdentificationIcon, UserGroupIcon, ShieldCheckIcon,
  GlobeAltIcon, EyeIcon, PencilSquareIcon, EnvelopeIcon,
  PlusCircleIcon, SparklesIcon
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
  }, { preserveScroll: true })
}

const roleLabel = {
  visitor:          'Visiteur',
  researcher:       'Chercheur',
  doctoral_student: 'Doctorant',
  partner:          'Partenaire',
  axe_admin:        'Admin d\'axe',
  super_admin:      'Super Admin',
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
  draft:             { bg: 'var(--bg-tertiary)',        color: 'var(--text-subtle)', border: 'var(--border-strong)' },
  submitted:         { bg: 'rgba(37,99,235,0.10)',      color: 'var(--primary-light)', border: 'rgba(37,99,235,0.25)' },
  under_review:      { bg: 'rgba(217,119,6,0.10)',      color: '#fbbf24', border: 'rgba(217,119,6,0.25)' },
  published:         { bg: 'rgba(5,150,105,0.10)',      color: 'var(--success)', border: 'rgba(5,150,105,0.25)' },
  rejected:          { bg: 'rgba(225,29,72,0.10)',      color: 'var(--danger)', border: 'rgba(225,29,72,0.25)' },
  revision_required: { bg: 'rgba(124,58,237,0.10)',     color: 'var(--purple)', border: 'rgba(124,58,237,0.25)' },
}

const workflowStatusLabel = {
  pending:          'En attente',
  approved:         'Approuvé',
  rejected:         'Rejeté',
  revision_required:'Révision requise',
}

const getStatutStyle = (s) => {
  const c = statutColor[s]
  if (!c) return {}
  return { background: c.bg, color: c.color, border: `1px solid ${c.border}` }
}

// Metrics for super admin
const metrics = computed(() => [
  {
    label: 'Utilisateurs inscrits',
    value: props.totalUsers ?? 0,
    icon: UsersIcon,
    color: '#2563eb',
    link: '/admin/membres',
    linkLabel: 'Gérer les membres'
  },
  {
    label: 'Publications totales',
    value: props.stats?.total_publications ?? 0,
    icon: DocumentTextIcon,
    color: '#059669',
    link: '/admin/publications',
    linkLabel: 'Voir le catalogue'
  },
  {
    label: 'Datasets ouverts',
    value: props.stats?.total_datasets ?? 0,
    icon: CircleStackIcon,
    color: '#0891b2',
    link: '/admin/datasets',
    linkLabel: 'Gérer les datasets'
  },
  {
    label: 'Axes thématiques',
    value: props.stats?.total_axes ?? 0,
    icon: ChartBarIcon,
    color: '#7c3aed',
    link: '/admin/axes',
    linkLabel: 'Axes de recherche'
  },
])
</script>

<template>
  <div style="padding: 2rem 2rem 4rem; max-width: 1400px; margin: 0 auto;" class="animate-fade-in">

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- EN-TÊTE PERSONNALISÉ                                       -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:2.5rem; padding-bottom:2rem; border-bottom:1px solid var(--border);">
      <div>
        <p style="font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:var(--text-subtle); margin-bottom:0.375rem;">
          Espace membre UMMISCO
        </p>
        <h1 style="font-size:clamp(1.5rem,4vw,2.25rem); font-weight:900; letter-spacing:-0.025em; color:var(--text); line-height:1.2;">
          Bonjour, <span :style="`background: linear-gradient(135deg, var(--primary-light), var(--accent)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;`">{{ user?.prenom }} {{ user?.nom }}</span> 👋
        </h1>
        <div style="display:flex; align-items:center; gap:0.625rem; margin-top:0.625rem;">
          <span style="display:inline-flex; align-items:center; padding:0.2rem 0.75rem; border-radius:9999px; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; background:var(--primary-glow); color:var(--primary-light); border:1px solid rgba(37,99,235,0.25);">
            {{ roleLabel[userRole] ?? userRole }}
          </span>
          <span style="font-size:0.75rem; color:var(--text-subtle);">Espace d'administration personnalisé</span>
        </div>
      </div>

      <Link href="/publications/soumettre"
        style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.75rem 1.5rem; border-radius:12px; background:linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color:#fff; font-size:0.85rem; font-weight:700; text-decoration:none; box-shadow:0 4px 14px var(--primary-glow); transition:all 0.25s ease; border:none;"
        @mouseenter="e => { e.currentTarget.style.transform='translateY(-2px)'; e.currentTarget.style.boxShadow='0 8px 24px var(--primary-glow)'; }"
        @mouseleave="e => { e.currentTarget.style.transform=''; e.currentTarget.style.boxShadow='0 4px 14px var(--primary-glow)'; }">
        <PlusCircleIcon style="width:16px; height:16px;" />
        Nouvelle publication
      </Link>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- VOTES SUPPRESSION (Alerte critique)                        -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div v-if="demandesSuppression && demandesSuppression.length > 0" style="margin-bottom:2rem;">
      <div style="background:rgba(225,29,72,0.06); border:1px solid rgba(225,29,72,0.20); border-left:4px solid var(--danger); border-radius:16px; padding:1.75rem; box-shadow:0 4px 16px rgba(225,29,72,0.08);">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; padding-bottom:0.875rem; border-bottom:1px solid rgba(225,29,72,0.12);">
          <h2 style="font-size:1rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.75rem;">
            <span style="position:relative; display:inline-flex; width:12px; height:12px;">
              <span style="position:absolute; inset:0; border-radius:50%; background:var(--danger); animation:pulse-ring 1.5s ease infinite;"></span>
              <span style="position:relative; width:12px; height:12px; border-radius:50%; background:var(--danger);"></span>
            </span>
            Vote requis — Suppression de publication
          </h2>
          <span style="font-size:0.72rem; font-weight:700; padding:0.25rem 0.75rem; border-radius:9999px; background:rgba(225,29,72,0.10); color:var(--danger); border:1px solid rgba(225,29,72,0.20);">
            {{ demandesSuppression.length }} {{ demandesSuppression.length > 1 ? 'demandes actives' : 'demande active' }}
          </span>
        </div>

        <div style="display:flex; flex-direction:column; gap:1.25rem;">
          <div v-for="demande in demandesSuppression" :key="demande.id"
            style="background:var(--card-bg); border:1px solid var(--border); border-radius:12px; padding:1.25rem;">
            <div style="display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:1rem;">
              <div style="flex:1; min-width:0;">
                <h3 style="font-size:0.88rem; font-weight:700; color:var(--text); margin-bottom:0.375rem;">
                  <span style="font-size:0.65rem; font-weight:700; text-transform:uppercase; padding:0.15rem 0.5rem; border-radius:4px; background:var(--bg-tertiary); color:var(--text-subtle); margin-right:0.5rem;">
                    {{ typeLabel[demande.publication_type] ?? demande.publication_type }}
                  </span>
                  "{{ demande.publication_titre }}"
                </h3>
                <p style="font-size:0.78rem; color:var(--text-muted);">
                  Proposé par <strong style="color:var(--text);">{{ demande.propose_par_nom }}</strong>
                  · Le {{ new Date(demande.created_at).toLocaleDateString('fr-FR') }}
                </p>
                <div style="margin-top:0.625rem; padding:0.625rem 0.875rem; background:var(--bg-tertiary); border-radius:8px; font-size:0.78rem; color:var(--text-muted); font-style:italic;">
                  <strong>Motif :</strong> "{{ demande.motif }}"
                </div>
              </div>

              <div style="width:240px; flex-shrink:0;">
                <div style="margin-bottom:0.5rem;">
                  <div style="display:flex; justify-content:space-between; font-size:0.72rem; font-weight:600; margin-bottom:0.375rem;">
                    <span style="color:var(--success);">Pour : {{ demande.votes_pour }} / {{ demande.seuil }}</span>
                    <span style="color:var(--text-subtle);">Total : {{ demande.total_voters }}</span>
                  </div>
                  <div style="height:6px; background:var(--bg-tertiary); border-radius:9999px; overflow:hidden; border:1px solid var(--border);">
                    <div :style="{ width: Math.min(100, (demande.votes_pour / demande.seuil) * 100) + '%', height:'100%', background:'var(--danger)', borderRadius:'9999px', transition:'width 0.4s ease' }"></div>
                  </div>
                  <div style="display:flex; justify-content:space-between; font-size:0.65rem; color:var(--text-subtle); margin-top:0.25rem;">
                    <span>Garder : {{ demande.votes_contre }}</span>
                    <span>Seuil majorité</span>
                  </div>
                </div>

                <div v-if="userRole === 'super_admin'" style="text-align:center; font-size:0.75rem; color:var(--text-subtle); padding:0.5rem; background:var(--bg-tertiary); border-radius:8px; border:1px solid var(--border);">
                  Suivi en tant que Super Admin
                </div>
                <div v-else style="display:flex; gap:0.5rem;">
                  <button @click="voter(demande.id, true)"
                    :disabled="demande.user_vote === true || demande.user_vote === 1"
                    :style="`flex:1; font-size:0.78rem; font-weight:700; padding:0.5rem; border-radius:8px; border:1px solid; cursor:${(demande.user_vote===true||demande.user_vote===1)?'default':'pointer'}; transition:all 0.2s; ${(demande.user_vote===true||demande.user_vote===1) ? 'background:rgba(5,150,105,0.10);color:var(--success);border-color:rgba(5,150,105,0.25);' : 'background:var(--danger);color:#fff;border-color:transparent;'}`">
                    {{ (demande.user_vote === true || demande.user_vote === 1) ? '✓ Supprimer' : 'Supprimer' }}
                  </button>
                  <button @click="voter(demande.id, false)"
                    :disabled="demande.user_vote === false || demande.user_vote === 0"
                    :style="`flex:1; font-size:0.78rem; font-weight:700; padding:0.5rem; border-radius:8px; border:1px solid; cursor:${(demande.user_vote===false||demande.user_vote===0)?'default':'pointer'}; transition:all 0.2s; ${(demande.user_vote===false||demande.user_vote===0) ? 'background:rgba(5,150,105,0.10);color:var(--success);border-color:rgba(5,150,105,0.25);' : 'background:var(--surface);color:var(--text-muted);border-color:var(--border-strong);'}`">
                    {{ (demande.user_vote === false || demande.user_vote === 0) ? '✓ Garder' : 'Garder' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 1. SUPER ADMIN                                             -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div v-if="userRole === 'super_admin'" style="display:flex; flex-direction:column; gap:2rem;">

      <!-- Metric Cards -->
      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(230px,1fr)); gap:1.25rem;">
        <div v-for="m in metrics" :key="m.label"
          style="border-radius:16px; border:1px solid var(--border); background:var(--card-bg); padding:1.5rem; box-shadow:var(--shadow); transition:all 0.25s ease; position:relative; overflow:hidden;"
          @mouseenter="e => { e.currentTarget.style.transform='translateY(-3px)'; e.currentTarget.style.boxShadow='var(--shadow-md)'; e.currentTarget.style.borderColor='var(--border-strong)'; }"
          @mouseleave="e => { e.currentTarget.style.transform=''; e.currentTarget.style.boxShadow='var(--shadow)'; e.currentTarget.style.borderColor='var(--border)'; }">
          <!-- Top accent line -->
          <div :style="`position:absolute; top:0; left:0; right:0; height:3px; background:${m.color};`"></div>
          <!-- Glow bg -->
          <div :style="`position:absolute; bottom:-20px; right:-20px; width:80px; height:80px; border-radius:50%; background:${m.color}; opacity:0.06; pointer-events:none;`"></div>

          <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-top:0.375rem;">
            <div>
              <div style="font-size:0.75rem; font-weight:600; color:var(--text-subtle); margin-bottom:0.375rem;">{{ m.label }}</div>
              <div style="font-size:2.25rem; font-weight:900; letter-spacing:-0.04em; color:var(--text); line-height:1;">{{ m.value }}</div>
            </div>
            <div :style="`width:44px; height:44px; border-radius:12px; background:${m.color}18; border:1px solid ${m.color}28; display:flex; align-items:center; justify-content:center; flex-shrink:0;`">
              <component :is="m.icon" :style="`width:22px; height:22px; color:${m.color};`" />
            </div>
          </div>
          <Link :href="m.link"
            :style="`display:inline-flex; align-items:center; gap:0.3rem; margin-top:1rem; font-size:0.72rem; font-weight:700; color:${m.color}; text-decoration:none; opacity:0.85; transition:opacity 0.2s;`"
            @mouseenter="e => e.currentTarget.style.opacity='1'"
            @mouseleave="e => e.currentTarget.style.opacity='0.85'">
            {{ m.linkLabel }}
            <ArrowUpRightIcon style="width:11px; height:11px;" />
          </Link>
        </div>
      </div>

      <!-- Split layout: workflow + publications | actions + stats -->
      <div style="display:grid; grid-template-columns:1fr 300px; gap:1.5rem;">

        <div style="display:flex; flex-direction:column; gap:1.5rem; min-width:0;">

          <!-- Soumissions en attente -->
          <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
            <div style="display:flex; align-items:center; justify-content:space-between; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg-secondary);">
              <h2 style="font-size:0.9rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.625rem;">
                <ClipboardDocumentCheckIcon style="width:18px; height:18px; color:#fbbf24;" />
                Soumissions en attente de validation
              </h2>
              <Link href="/soumissions" style="font-size:0.72rem; font-weight:700; color:var(--primary-light); text-decoration:none; display:flex; align-items:center; gap:0.25rem;">
                Tout gérer <ArrowUpRightIcon style="width:11px; height:11px;" />
              </Link>
            </div>
            <div v-if="soumissionsEnAttente?.length > 0">
              <div v-for="s in soumissionsEnAttente" :key="s.id"
                style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); transition:background 0.15s ease;"
                @mouseenter="e => e.currentTarget.style.background='var(--bg-tertiary)'"
                @mouseleave="e => e.currentTarget.style.background=''">
                <div>
                  <h3 style="font-size:0.85rem; font-weight:700; color:var(--text);">{{ s.publication?.titre_fr }}</h3>
                  <p style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                    Par <span style="font-weight:700; color:var(--text);">{{ s.soumetteur?.prenom }} {{ s.soumetteur?.nom }}</span>
                    · Axe <span style="color:var(--primary-light); font-weight:700;">{{ s.publication?.axe?.code }}</span>
                    · {{ new Date(s.date_soumission).toLocaleDateString('fr-FR') }}
                  </p>
                </div>
                <Link :href="`/publications/${s.publication_id}`"
                  style="font-size:0.72rem; font-weight:700; padding:0.4rem 0.875rem; border-radius:8px; border:1px solid var(--border-strong); color:var(--text-muted); text-decoration:none; flex-shrink:0; transition:all 0.2s;"
                  @mouseenter="e => { e.currentTarget.style.background='var(--primary-glow)'; e.currentTarget.style.color='var(--primary-light)'; e.currentTarget.style.borderColor='rgba(37,99,235,0.3)'; }"
                  @mouseleave="e => { e.currentTarget.style.background=''; e.currentTarget.style.color='var(--text-muted)'; e.currentTarget.style.borderColor='var(--border-strong)'; }">
                  Inspecter
                </Link>
              </div>
            </div>
            <div v-else style="padding:3rem; text-align:center; color:var(--text-subtle); font-size:0.85rem;">
              <CheckCircleIcon style="width:32px; height:32px; margin:0 auto 0.75rem; color:var(--success);" />
              <p style="font-weight:600; color:var(--text-muted);">Aucune soumission en attente</p>
              <p style="font-size:0.75rem; margin-top:0.25rem;">Le workflow de validation est à jour 🎉</p>
            </div>
          </div>

          <!-- Productions récentes -->
          <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
            <div style="display:flex; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg-secondary);">
              <h2 style="font-size:0.9rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.625rem;">
                <ClockIcon style="width:18px; height:18px; color:var(--primary-light);" />
                Dernières productions du laboratoire
              </h2>
            </div>
            <div v-if="publicationsRecentes?.length > 0">
              <div v-for="pub in publicationsRecentes" :key="pub.id"
                style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); transition:background 0.15s ease;"
                @mouseenter="e => e.currentTarget.style.background='var(--bg-tertiary)'"
                @mouseleave="e => e.currentTarget.style.background=''">
                <div style="min-width:0; flex:1; padding-right:1rem;">
                  <h3 style="font-size:0.85rem; font-weight:700; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ pub.titre_fr }}</h3>
                  <p style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                    {{ pub.auteur?.prenom }} {{ pub.auteur?.nom }} · Axe {{ pub.axe?.code ?? 'Global' }}
                  </p>
                </div>
                <span style="font-size:0.68rem; font-weight:700; text-transform:uppercase; padding:0.2rem 0.65rem; border-radius:9999px; background:var(--primary-glow); color:var(--primary-light); border:1px solid rgba(37,99,235,0.2); flex-shrink:0;">
                  {{ typeLabel[pub.type] ?? pub.type }}
                </span>
              </div>
            </div>
          </div>

        </div>

        <!-- Right column -->
        <div style="display:flex; flex-direction:column; gap:1.25rem;">

          <!-- Quick actions -->
          <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:1.5rem; box-shadow:var(--shadow);">
            <h2 style="font-size:0.82rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
              <ShieldCheckIcon style="width:16px; height:16px; color:var(--primary-light);" />
              Outils d'administration
            </h2>
            <div style="display:flex; flex-direction:column; gap:0.375rem;">
              <Link v-for="item in [
                { href:'/admin/membres', label:'Gérer les membres', icon: UsersIcon },
                { href:'/admin/axes', label:'Axes thématiques', icon: ChartBarIcon },
                { href:'/admin/statistiques', label:'Statistiques', icon: ChartBarIcon },
                { href:'/admin/parametres', label:'Paramètres système', icon: ShieldCheckIcon },
              ]" :key="item.href" :href="item.href"
                style="display:flex; align-items:center; justify-content:space-between; padding:0.625rem 0.875rem; border-radius:10px; border:1px solid var(--border); text-decoration:none; transition:all 0.2s; background:transparent;"
                @mouseenter="e => { e.currentTarget.style.background='var(--primary-glow)'; e.currentTarget.style.borderColor='rgba(37,99,235,0.3)'; }"
                @mouseleave="e => { e.currentTarget.style.background=''; e.currentTarget.style.borderColor='var(--border)'; }">
                <span style="font-size:0.8rem; font-weight:600; color:var(--text);">{{ item.label }}</span>
                <component :is="item.icon" style="width:14px; height:14px; color:var(--text-subtle);" />
              </Link>
            </div>
          </div>

          <!-- Publications par statut -->
          <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:1.5rem; box-shadow:var(--shadow);">
            <h2 style="font-size:0.82rem; font-weight:800; color:var(--text); margin-bottom:1rem;">Publications par statut</h2>
            <div style="display:flex; flex-direction:column; gap:0.625rem;">
              <div v-for="(count, status) in statsStatuts" :key="status"
                style="display:flex; align-items:center; justify-content:space-between;">
                <span style="font-size:0.78rem; font-weight:600; color:var(--text-muted); text-transform:capitalize;">{{ status }}</span>
                <span :style="`font-size:0.72rem; font-weight:800; padding:0.2rem 0.625rem; border-radius:9999px; ${getStatutStyle(status) ? `background:${getStatutStyle(status).background}; color:${getStatutStyle(status).color}; border:${getStatutStyle(status).border};` : 'background:var(--bg-tertiary); color:var(--text-subtle);'}`">
                  {{ count }}
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 2. AXE ADMIN                                               -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div v-else-if="userRole === 'axe_admin'" style="display:flex; flex-direction:column; gap:1.75rem;">

      <!-- Axe header banner -->
      <div v-if="axe"
        :style="`padding:1.75rem; border-radius:16px; border:1px solid ${axe.couleur_hex ?? '#2563eb'}30; background:linear-gradient(135deg, ${axe.couleur_hex ?? '#2563eb'}0a 0%, transparent 100%); position:relative; overflow:hidden;`">
        <div :style="`position:absolute; top:0; left:0; right:0; height:3px; background:${axe.couleur_hex ?? '#2563eb'};`"></div>
        <div :style="`position:absolute; bottom:-30px; right:-30px; width:120px; height:120px; border-radius:50%; background:${axe.couleur_hex ?? '#2563eb'}; opacity:0.06; pointer-events:none;`"></div>
        <div style="display:flex; align-items:center; justify-content:space-between; position:relative;">
          <div>
            <span :style="`font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; padding:0.2rem 0.65rem; border-radius:9999px; background:${axe.couleur_hex ?? '#2563eb'}18; color:${axe.couleur_hex ?? '#2563eb'}; border:1px solid ${axe.couleur_hex ?? '#2563eb'}30;`">
              Axe de recherche géré
            </span>
            <h2 style="font-size:1.5rem; font-weight:900; letter-spacing:-0.02em; color:var(--text); margin-top:0.625rem; margin-bottom:0.375rem;">{{ axe.nom_fr }}</h2>
            <p style="font-size:0.82rem; color:var(--text-muted);">{{ axe.description_fr }}</p>
          </div>
          <div :style="`width:56px; height:56px; border-radius:16px; background:${axe.couleur_hex ?? '#2563eb'}; display:flex; align-items:center; justify-content:center; font-size:1rem; font-weight:900; color:#fff; flex-shrink:0;`">
            {{ axe.code }}
          </div>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 280px; gap:1.5rem;">
        <div style="display:flex; flex-direction:column; gap:1.5rem; min-width:0;">

          <!-- Workflow validation -->
          <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
            <div style="display:flex; align-items:center; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg-secondary);">
              <h2 style="font-size:0.9rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.625rem;">
                <ClipboardDocumentCheckIcon style="width:18px; height:18px; color:#fbbf24;" />
                Workflow de validation de l'axe
              </h2>
            </div>
            <div v-if="soumissionsEnAttente?.length > 0">
              <div v-for="s in soumissionsEnAttente" :key="s.id"
                style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); transition:background 0.15s ease;"
                @mouseenter="e => e.currentTarget.style.background='var(--bg-tertiary)'"
                @mouseleave="e => e.currentTarget.style.background=''">
                <div>
                  <h3 style="font-size:0.85rem; font-weight:700; color:var(--text);">{{ s.publication?.titre_fr }}</h3>
                  <p style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                    Par <strong style="color:var(--text);">{{ s.soumetteur?.prenom }} {{ s.soumetteur?.nom }}</strong>
                    · {{ new Date(s.date_soumission).toLocaleDateString('fr-FR') }}
                  </p>
                </div>
                <Link :href="`/publications/${s.publication_id}`"
                  style="font-size:0.72rem; font-weight:700; padding:0.4rem 0.875rem; border-radius:8px; border:1px solid var(--border-strong); color:var(--text-muted); text-decoration:none; flex-shrink:0; transition:all 0.2s;"
                  @mouseenter="e => { e.currentTarget.style.background='var(--primary-glow)'; e.currentTarget.style.color='var(--primary-light)'; }"
                  @mouseleave="e => { e.currentTarget.style.background=''; e.currentTarget.style.color='var(--text-muted)'; }">
                  Inspecter
                </Link>
              </div>
            </div>
            <div v-else style="padding:3rem; text-align:center; color:var(--text-subtle); font-size:0.85rem;">
              <CheckCircleIcon style="width:32px; height:32px; margin:0 auto 0.75rem; color:var(--success);" />
              <p style="font-weight:600; color:var(--text-muted);">Aucune soumission en attente dans votre axe</p>
            </div>
          </div>

          <!-- Publications de l'axe -->
          <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
            <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg-secondary);">
              <h2 style="font-size:0.9rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.625rem;">
                <DocumentTextIcon style="width:18px; height:18px; color:var(--primary-light);" />
                Publications récentes de l'axe
              </h2>
            </div>
            <div v-if="publicationsRecentes?.length > 0">
              <div v-for="pub in publicationsRecentes" :key="pub.id"
                style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); transition:background 0.15s ease;"
                @mouseenter="e => e.currentTarget.style.background='var(--bg-tertiary)'"
                @mouseleave="e => e.currentTarget.style.background=''">
                <div style="min-width:0; flex:1; padding-right:1rem;">
                  <h3 style="font-size:0.85rem; font-weight:700; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ pub.titre_fr }}</h3>
                  <p style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">{{ pub.auteur?.prenom }} {{ pub.auteur?.nom }} · {{ new Date(pub.created_at).toLocaleDateString('fr-FR') }}</p>
                </div>
                <span style="font-size:0.68rem; font-weight:700; padding:0.2rem 0.65rem; border-radius:9999px; background:var(--primary-glow); color:var(--primary-light); border:1px solid rgba(37,99,235,0.2); flex-shrink:0; text-transform:uppercase;">
                  {{ typeLabel[pub.type] ?? pub.type }}
                </span>
              </div>
            </div>
            <div v-else style="padding:2.5rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">Aucune publication dans cet axe.</div>
          </div>
        </div>

        <!-- Membres de l'axe -->
        <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:1.5rem; box-shadow:var(--shadow); overflow:hidden;">
          <h2 style="font-size:0.82rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.5rem; margin-bottom:1rem;">
            <UserGroupIcon style="width:16px; height:16px; color:var(--purple);" />
            Membres de l'axe ({{ membres?.length ?? 0 }})
          </h2>
          <div v-if="membres?.length > 0" style="display:flex; flex-direction:column; gap:0.5rem; max-height:400px; overflow-y:auto;">
            <div v-for="m in membres" :key="m.id"
              style="display:flex; align-items:center; gap:0.75rem; padding:0.5rem 0.625rem; border-radius:10px; transition:background 0.15s ease;"
              @mouseenter="e => e.currentTarget.style.background='var(--bg-tertiary)'"
              @mouseleave="e => e.currentTarget.style.background=''">
              <div style="width:32px; height:32px; border-radius:50%; background:var(--primary-glow); border:1.5px solid rgba(37,99,235,0.25); display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:800; color:var(--primary-light); flex-shrink:0; text-transform:uppercase;">
                {{ m.prenom.charAt(0) }}{{ m.nom.charAt(0) }}
              </div>
              <div style="min-width:0;">
                <div style="font-size:0.8rem; font-weight:700; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ m.prenom }} {{ m.nom }}</div>
                <div style="font-size:0.68rem; color:var(--text-subtle);">{{ roleLabel[m.role] ?? m.role }}</div>
              </div>
            </div>
          </div>
          <div v-else style="text-align:center; padding:2rem 0; font-size:0.8rem; color:var(--text-subtle);">Aucun membre rattaché.</div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 3. CHERCHEUR                                               -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div v-else-if="userRole === 'researcher'" style="display:grid; grid-template-columns:1fr 280px; gap:1.5rem;">

      <!-- Publications list -->
      <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg-secondary);">
          <h2 style="font-size:0.9rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.625rem;">
            <DocumentTextIcon style="width:18px; height:18px; color:var(--primary-light);" />
            Mes publications scientifiques
          </h2>
          <Link href="/mes-publications" style="font-size:0.72rem; font-weight:700; color:var(--primary-light); text-decoration:none; display:flex; align-items:center; gap:0.25rem;">
            Tout voir <ArrowUpRightIcon style="width:11px; height:11px;" />
          </Link>
        </div>
        <div v-if="mesPublications?.length > 0">
          <div v-for="pub in mesPublications" :key="pub.id"
            style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); transition:background 0.15s ease;"
            @mouseenter="e => e.currentTarget.style.background='var(--bg-tertiary)'"
            @mouseleave="e => e.currentTarget.style.background=''">
            <div style="min-width:0; flex:1; padding-right:1rem;">
              <h3 style="font-size:0.85rem; font-weight:700; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ pub.titre_fr }}</h3>
              <p style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem; text-transform:capitalize;">
                {{ typeLabel[pub.type] ?? pub.type }} · Axe {{ pub.axe?.code ?? 'Global' }} · {{ new Date(pub.created_at).toLocaleDateString('fr-FR') }}
              </p>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem; flex-shrink:0;">
              <span :style="`font-size:0.68rem; font-weight:800; padding:0.2rem 0.625rem; border-radius:9999px; ${getStatutStyle(pub.statut) ? `background:${getStatutStyle(pub.statut).background}; color:${getStatutStyle(pub.statut).color}; border:${getStatutStyle(pub.statut).border};` : ''}`">
                {{ pub.statut }}
              </span>
              <Link :href="`/publications/${pub.id}/modifier`"
                style="padding:0.3rem; border-radius:6px; color:var(--text-subtle); text-decoration:none; transition:all 0.2s;"
                @mouseenter="e => { e.currentTarget.style.background='var(--bg-tertiary)'; e.currentTarget.style.color='var(--text)'; }"
                @mouseleave="e => { e.currentTarget.style.background=''; e.currentTarget.style.color='var(--text-subtle)'; }">
                <PencilSquareIcon style="width:15px; height:15px;" />
              </Link>
            </div>
          </div>
        </div>
        <div v-else style="padding:4rem 2rem; text-align:center;">
          <DocumentTextIcon style="width:48px; height:48px; color:var(--text-subtle); margin:0 auto 1rem;" />
          <p style="font-size:0.9rem; font-weight:600; color:var(--text-muted); margin-bottom:0.5rem;">Aucune publication soumise</p>
          <Link href="/publications/soumettre" style="display:inline-flex; align-items:center; gap:0.375rem; font-size:0.8rem; font-weight:700; padding:0.6rem 1.25rem; border-radius:10px; background:var(--primary-light); color:#fff; text-decoration:none; margin-top:0.5rem;">
            <PlusCircleIcon style="width:14px; height:14px;" />
            Soumettre ma première publication
          </Link>
        </div>
      </div>

      <!-- Profil chercheur -->
      <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:1.5rem; box-shadow:var(--shadow); position:relative; overflow:hidden;">
        <div style="position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg, var(--primary-light), var(--accent));"></div>
        <h2 style="font-size:0.82rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.5rem; margin-bottom:1.25rem; padding-bottom:0.875rem; border-bottom:1px solid var(--border);">
          <IdentificationIcon style="width:16px; height:16px; color:var(--accent);" />
          Mon profil chercheur
        </h2>
        <div v-if="profile" style="display:flex; flex-direction:column; gap:1rem;">
          <div>
            <div style="font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-subtle); margin-bottom:0.25rem;">Spécialité</div>
            <div style="font-size:0.85rem; font-weight:600; color:var(--text);">{{ profile.specialite ?? 'Non définie' }}</div>
          </div>
          <div>
            <div style="font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-subtle); margin-bottom:0.25rem;">Indicateur H-Index</div>
            <div style="font-size:1.75rem; font-weight:900; color:var(--accent); letter-spacing:-0.03em;">{{ profile.h_index ?? 'N/A' }}</div>
          </div>
          <div>
            <div style="font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-subtle); margin-bottom:0.375rem;">Domaines d'expertise</div>
            <div v-if="profile.domaines_expertise" style="display:flex; flex-wrap:wrap; gap:0.375rem;">
              <span v-for="tag in profile.domaines_expertise" :key="tag"
                style="font-size:0.68rem; font-weight:600; padding:0.2rem 0.5rem; border-radius:6px; background:var(--bg-tertiary); border:1px solid var(--border); color:var(--text-muted);">
                #{{ tag }}
              </span>
            </div>
            <div v-else style="font-size:0.78rem; color:var(--text-subtle); font-style:italic;">Aucune expertise renseignée.</div>
          </div>
        </div>
        <div v-else style="text-align:center; padding:2rem 0; color:var(--text-subtle); font-size:0.8rem; font-style:italic;">Profil chercheur non initialisé.</div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 4. DOCTORANT                                               -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div v-else-if="userRole === 'doctoral_student'" style="display:flex; flex-direction:column; gap:1.75rem;">

      <!-- Fiche thèse -->
      <div v-if="profile" style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
        <div style="height:3px; background:linear-gradient(90deg, var(--accent), var(--primary-light));"></div>
        <div style="padding:1.75rem;">
          <h2 style="font-size:0.9rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.625rem; margin-bottom:1.5rem; padding-bottom:0.875rem; border-bottom:1px solid var(--border);">
            <AcademicCapIcon style="width:18px; height:18px; color:var(--primary-light);" />
            Fiche de Thèse de Doctorat
          </h2>
          <div style="display:grid; grid-template-columns:1fr 220px; gap:1.5rem;">
            <div>
              <span style="font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-subtle);">Sujet de thèse</span>
              <p style="font-size:1rem; font-weight:700; color:var(--text); font-style:italic; margin-top:0.375rem; line-height:1.4;">
                « {{ profile.titre_these ?? 'Non renseigné' }} »
              </p>
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-top:1.25rem;">
                <div>
                  <span style="font-size:0.65rem; font-weight:800; text-transform:uppercase; color:var(--text-subtle); display:block; margin-bottom:0.25rem;">École Doctorale</span>
                  <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted);">{{ profile.ecole_doctorale ?? 'Non renseignée' }}</span>
                </div>
                <div>
                  <span style="font-size:0.65rem; font-weight:800; text-transform:uppercase; color:var(--text-subtle); display:block; margin-bottom:0.25rem;">Financement</span>
                  <span style="font-size:0.82rem; font-weight:600; color:var(--text-muted);">{{ profile.financement ?? 'Non renseigné' }}</span>
                </div>
              </div>
            </div>
            <div style="background:var(--bg-tertiary); border:1px solid var(--border); border-radius:12px; padding:1rem;">
              <div v-if="directeur">
                <span style="font-size:0.62rem; font-weight:800; text-transform:uppercase; color:var(--text-subtle);">Directeur de thèse</span>
                <div style="font-size:0.85rem; font-weight:700; color:var(--text); margin-top:0.25rem;">{{ directeur.titre_academique }} {{ directeur.prenom }} {{ directeur.nom }}</div>
              </div>
              <div v-if="coDirecteur" style="margin-top:0.875rem;">
                <span style="font-size:0.62rem; font-weight:800; text-transform:uppercase; color:var(--text-subtle);">Co-directeur</span>
                <div style="font-size:0.85rem; font-weight:700; color:var(--text); margin-top:0.25rem;">{{ coDirecteur.titre_academique }} {{ coDirecteur.prenom }} {{ coDirecteur.nom }}</div>
              </div>
              <div style="margin-top:0.875rem; font-size:0.72rem; color:var(--text-subtle);">
                Inscrit en : {{ profile.date_inscription ? new Date(profile.date_inscription).getFullYear() : 'N/A' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
        <!-- Publications -->
        <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
          <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg-secondary);">
            <h2 style="font-size:0.9rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.625rem;">
              <DocumentTextIcon style="width:18px; height:18px; color:var(--primary-light);" /> Mes publications
            </h2>
          </div>
          <div v-if="mesPublications?.length > 0">
            <div v-for="pub in mesPublications" :key="pub.id"
              style="display:flex; align-items:center; justify-content:space-between; padding:0.875rem 1.5rem; border-bottom:1px solid var(--border); transition:background 0.15s;"
              @mouseenter="e => e.currentTarget.style.background='var(--bg-tertiary)'"
              @mouseleave="e => e.currentTarget.style.background=''">
              <div style="min-width:0; flex:1; padding-right:0.75rem;">
                <h3 style="font-size:0.82rem; font-weight:700; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ pub.titre_fr }}</h3>
                <p style="font-size:0.7rem; color:var(--text-subtle); margin-top:0.2rem; text-transform:capitalize;">{{ typeLabel[pub.type] ?? pub.type }}</p>
              </div>
              <span :style="`font-size:0.65rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:9999px; flex-shrink:0; ${getStatutStyle(pub.statut) ? `background:${getStatutStyle(pub.statut).background}; color:${getStatutStyle(pub.statut).color}; border:${getStatutStyle(pub.statut).border};` : ''}`">
                {{ pub.statut }}
              </span>
            </div>
          </div>
          <div v-else style="padding:2.5rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">Aucune publication soumise.</div>
        </div>

        <!-- Suivi validations -->
        <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
          <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg-secondary);">
            <h2 style="font-size:0.9rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.625rem;">
              <ClipboardDocumentCheckIcon style="width:18px; height:18px; color:#fbbf24;" /> Suivi des validations
            </h2>
          </div>
          <div v-if="soumissionsEnAttente?.length > 0">
            <div v-for="s in soumissionsEnAttente" :key="s.id" style="padding:1rem 1.5rem; border-bottom:1px solid var(--border);">
              <div style="display:flex; align-items:center; justify-content:space-between; gap:0.5rem;">
                <h3 style="font-size:0.82rem; font-weight:700; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1;">{{ s.publication?.titre_fr }}</h3>
                <span :style="`font-size:0.65rem; font-weight:800; padding:0.15rem 0.5rem; border-radius:9999px; flex-shrink:0; ${getStatutStyle(s.publication?.statut) ? `background:${getStatutStyle(s.publication?.statut).background}; color:${getStatutStyle(s.publication?.statut).color}; border:${getStatutStyle(s.publication?.statut).border};` : ''}`">
                  {{ workflowStatusLabel[s.statut] ?? s.statut }}
                </span>
              </div>
              <div style="font-size:0.7rem; color:var(--text-subtle); margin-top:0.375rem; display:flex; align-items:center; gap:0.375rem;">
                <ClockIcon style="width:12px; height:12px;" />
                Soumis le {{ new Date(s.date_soumission).toLocaleDateString('fr-FR') }}
              </div>
              <div v-if="s.commentaire" style="margin-top:0.625rem; padding:0.5rem 0.75rem; background:rgba(225,29,72,0.06); border:1px solid rgba(225,29,72,0.12); border-radius:8px; font-size:0.75rem; color:var(--danger);">
                <strong>Révision :</strong> {{ s.commentaire }}
              </div>
            </div>
          </div>
          <div v-else style="padding:2.5rem; text-align:center; font-size:0.82rem; color:var(--text-subtle);">Aucune validation en attente.</div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════ -->
    <!-- 5. VISITEUR / PARTENAIRE                                   -->
    <!-- ══════════════════════════════════════════════════════════ -->
    <div v-else style="display:flex; flex-direction:column; gap:1.75rem;">

      <!-- Welcome banner -->
      <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:2rem; box-shadow:var(--shadow); position:relative; overflow:hidden;">
        <div style="position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg, var(--primary-light), var(--accent));"></div>
        <div style="position:absolute; bottom:-40px; right:-40px; width:160px; height:160px; border-radius:50%; background:var(--primary-light); opacity:0.04; pointer-events:none;"></div>
        <h2 style="font-size:1.2rem; font-weight:800; color:var(--text); display:flex; align-items:center; gap:0.75rem; margin-bottom:0.875rem;">
          <GlobeAltIcon style="width:22px; height:22px; color:var(--primary-light);" />
          Bienvenue sur l'Espace Laboratoire UMMISCO
        </h2>
        <p style="font-size:0.88rem; color:var(--text-muted); line-height:1.7; max-width:650px;">
          En tant que partenaire ou visiteur académique, cet espace vous permet de consulter des ressources,
          d'accéder aux statistiques de recherche et de suivre les dernières productions scientifiques ouvertes.
        </p>
      </div>

      <!-- Quick metrics -->
      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px,1fr)); gap:1rem;">
        <div v-for="m in [
          { label:'Publications', value: stats?.total_publications ?? 0, color:'#2563eb' },
          { label:'Datasets ouverts', value: stats?.total_datasets ?? 0, color:'#059669' },
          { label:'Chercheurs rattachés', value: stats?.total_chercheurs ?? 0, color:'#7c3aed' },
        ]" :key="m.label"
          style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; padding:1.25rem; box-shadow:var(--shadow); position:relative; overflow:hidden;">
          <div :style="`position:absolute; top:0; left:0; right:0; height:3px; background:${m.color};`"></div>
          <div :style="`font-size:2rem; font-weight:900; letter-spacing:-0.04em; color:${m.color}; line-height:1;`">{{ m.value }}</div>
          <div style="font-size:0.75rem; font-weight:600; color:var(--text-subtle); margin-top:0.375rem;">{{ m.label }}</div>
        </div>
      </div>

      <!-- Dernières publications -->
      <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);">
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg-secondary);">
          <h2 style="font-size:0.9rem; font-weight:800; color:var(--text);">Dernières publications scientifiques parues</h2>
        </div>
        <div v-if="publicationsRecentes?.length > 0">
          <div v-for="pub in publicationsRecentes" :key="pub.id"
            style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid var(--border); transition:background 0.15s ease;"
            @mouseenter="e => e.currentTarget.style.background='var(--bg-tertiary)'"
            @mouseleave="e => e.currentTarget.style.background=''">
            <div style="min-width:0; flex:1; padding-right:1rem;">
              <h3 style="font-size:0.85rem; font-weight:700; color:var(--text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ pub.titre_fr }}</h3>
              <p style="font-size:0.72rem; color:var(--text-muted); margin-top:0.25rem;">
                {{ pub.auteur?.prenom }} {{ pub.auteur?.nom }} · Axe {{ pub.axe?.code ?? 'Global' }}
              </p>
            </div>
            <Link :href="`/publications/${pub.id}`"
              style="font-size:0.72rem; font-weight:700; padding:0.4rem 0.875rem; border-radius:8px; background:var(--primary-glow); color:var(--primary-light); text-decoration:none; border:1px solid rgba(37,99,235,0.2); flex-shrink:0; transition:all 0.2s;"
              @mouseenter="e => { e.currentTarget.style.background='var(--primary-light)'; e.currentTarget.style.color='#fff'; }"
              @mouseleave="e => { e.currentTarget.style.background='var(--primary-glow)'; e.currentTarget.style.color='var(--primary-light)'; }">
              Consulter
            </Link>
          </div>
        </div>
        <div v-else style="padding:3rem; text-align:center; font-size:0.85rem; color:var(--text-subtle);">Aucune publication récente disponible.</div>
      </div>
    </div>

  </div>
</template>
