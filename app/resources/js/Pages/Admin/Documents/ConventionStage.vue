<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import {
  DocumentTextIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
  ArrowLeftIcon,
  PlusIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

// ── Données du formulaire ───────────────────────────────────────────────────
const form = ref({
  // Établissement (organisme de formation)
  etablissement_nom: '',
  etablissement_statut: '',
  etablissement_siege: '',
  etablissement_representant: '',

  // Stagiaire
  stagiaire_nom: '',
  stagiaire_prenom: '',
  stagiaire_adresse: '',
  stagiaire_tel: '',
  stagiaire_email: '',
  stagiaire_annee_univ: '',
  stagiaire_diplome: '',
  stagiaire_specialite: '',

  // Stage
  theme: '',
  activites: ['', '', '', ''],
  date_debut: '',
  date_fin: '',
  lieu_stage: '',
  structure_accueil: '',

  // Encadrement
  responsable_ird: '',
  responsable_etablissement: '',

  // Gratification
  gratification_montant: '',
  indemnite_transport: '',
  indemnite_restauration: '',
  imputation: '',

  // Signature
  date_signature: new Date().toLocaleDateString('fr-FR'),
})

// ── Aperçu / impression ─────────────────────────────────────────────────────
const showPreview = ref(false)

const formatDate = (dateStr) => {
  if (!dateStr) return '........'
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
}

const activitesFiltered = computed(() =>
  form.value.activites.filter(a => a.trim() !== '')
)

// ── PDF (jsPDF + html2canvas) ───────────────────────────────────────────────
const generating = ref(false)

async function genererPDF() {
  generating.value = true
  showPreview.value = true
  await new Promise(r => setTimeout(r, 300))

  const { default: jsPDF } = await import('jspdf')
  const { default: html2canvas } = await import('html2canvas')

  const el = document.getElementById('convention-preview')
  const canvas = await html2canvas(el, {
    scale: 2,
    useCORS: true,
    backgroundColor: '#ffffff',
    windowWidth: 794,
  })

  const imgData = canvas.toDataURL('image/jpeg', 0.95)
  const pdf = new jsPDF.jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })

  const pdfW = pdf.internal.pageSize.getWidth()
  const pdfH = pdf.internal.pageSize.getHeight()
  const imgW = canvas.width
  const imgH = canvas.height
  const ratio = imgW / imgH
  let h = pdfW / ratio

  let y = 0
  while (y < imgH) {
    const pageCanvas = document.createElement('canvas')
    pageCanvas.width = imgW
    pageCanvas.height = Math.min(imgH - y, (pdfH / pdfW) * imgW)
    const ctx = pageCanvas.getContext('2d')
    ctx.drawImage(canvas, 0, -y)
    const pageData = pageCanvas.toDataURL('image/jpeg', 0.95)
    if (y > 0) pdf.addPage()
    pdf.addImage(pageData, 'JPEG', 0, 0, pdfW, Math.min(pdfH, (pageCanvas.height / imgW) * pdfW))
    y += pageCanvas.height
  }

  pdf.save(`Convention_Stage_${form.value.stagiaire_nom || 'stagiaire'}.pdf`)
  generating.value = false
}

function imprimer() {
  showPreview.value = true
  setTimeout(() => window.print(), 400)
}
</script>

<template>
  <div class="p-6 max-w-5xl mx-auto space-y-6 animate-fade-in">

    <!-- Header -->
    <div class="flex items-center gap-4">
      <Link href="/admin/documents" class="p-2 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 transition-all">
        <ArrowLeftIcon class="w-4 h-4" />
      </Link>
      <div>
        <h1 class="text-2xl font-extrabold text-white flex items-center gap-3">
          <DocumentTextIcon class="w-7 h-7 text-blue-400" />
          Convention de Stage
        </h1>
        <p class="text-slate-400 text-sm mt-0.5">Remplissez les champs ci-dessous puis générez le PDF officiel IRD</p>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- ─── FORMULAIRE ─── -->
      <div class="space-y-5">

        <!-- Établissement -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Organisme de Formation (Établissement)</h2>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom de l'organisme de formation *</label>
            <input v-model="form.etablissement_nom" type="text" placeholder="Ex: Université Cheikh Anta Diop"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Statut juridique</label>
              <input v-model="form.etablissement_statut" type="text" placeholder="Ex: Établissement public"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Représenté par</label>
              <input v-model="form.etablissement_representant" type="text" placeholder="Nom du représentant"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Siège social</label>
            <input v-model="form.etablissement_siege" type="text" placeholder="Adresse du siège social"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
        </div>

        <!-- Stagiaire -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Stagiaire</h2>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom *</label>
              <input v-model="form.stagiaire_nom" type="text" placeholder="NOM"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Prénom(s) *</label>
              <input v-model="form.stagiaire_prenom" type="text" placeholder="Prénom(s)"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Adresse</label>
            <input v-model="form.stagiaire_adresse" type="text" placeholder="Adresse complète"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Tél</label>
              <input v-model="form.stagiaire_tel" type="text" placeholder="Numéro de téléphone"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Email</label>
              <input v-model="form.stagiaire_email" type="email" placeholder="email@exemple.com"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Année universitaire</label>
              <input v-model="form.stagiaire_annee_univ" type="text" placeholder="2025-2026"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Diplôme préparé</label>
              <input v-model="form.stagiaire_diplome" type="text" placeholder="Master, Licence…"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Spécialité</label>
              <input v-model="form.stagiaire_specialite" type="text" placeholder="Spécialité"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
        </div>

        <!-- Stage -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Détails du Stage</h2>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Thème du stage</label>
            <input v-model="form.theme" type="text" placeholder="Thème de l'étude..."
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Activités confiées</label>
            <div class="space-y-2">
              <div v-for="(_, idx) in form.activites" :key="idx" class="flex items-center gap-2">
                <span class="text-slate-500 text-xs font-bold w-5">-</span>
                <input v-model="form.activites[idx]" type="text" :placeholder="`Activité ${idx + 1}`"
                  class="flex-1 bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Date de début</label>
              <input v-model="form.date_debut" type="date"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Date de fin</label>
              <input v-model="form.date_fin" type="date"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Lieu du stage</label>
              <input v-model="form.lieu_stage" type="text" placeholder="Dakar, Sénégal"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Structure d'accueil</label>
              <input v-model="form.structure_accueil" type="text" placeholder="Unité / Service IRD"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Responsable scientifique/administratif pour l'IRD</label>
            <input v-model="form.responsable_ird" type="text" placeholder="Nom et titre du responsable IRD"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Responsable pédagogique pour l'établissement</label>
            <input v-model="form.responsable_etablissement" type="text" placeholder="Nom du responsable pédagogique"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
        </div>

        <!-- Gratification -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Gratification (Article 5)</h2>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Montant/mois (F CFA)</label>
              <input v-model="form.gratification_montant" type="text" placeholder="Ex: 50 000"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Indemnité transport/mois</label>
              <input v-model="form.indemnite_transport" type="text" placeholder="Ex: 15 000"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Indemnité restauration/mois</label>
              <input v-model="form.indemnite_restauration" type="text" placeholder="Ex: 10 000"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Imputation (code budgétaire)</label>
            <input v-model="form.imputation" type="text" placeholder="Code EOTP ou centre de coût"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Date de signature</label>
            <input v-model="form.date_signature" type="text" placeholder="Ex: 11 juin 2026"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3">
          <button @click="genererPDF" :disabled="generating"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-600/20">
            <ArrowDownTrayIcon class="w-5 h-5" />
            {{ generating ? 'Génération...' : 'Générer PDF' }}
          </button>
          <button @click="imprimer"
            class="flex items-center gap-2 px-5 py-3 border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 rounded-xl text-sm font-bold transition-all">
            <PrinterIcon class="w-5 h-5" />
            Imprimer
          </button>
        </div>
      </div>

      <!-- ─── APERÇU PDF ─── -->
      <div class="xl:sticky xl:top-6 xl:self-start">
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4 mb-3 flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Aperçu du document</span>
          <span class="text-xs text-slate-500">Format A4</span>
        </div>
        <!-- Aperçu scrollable -->
        <div class="overflow-auto max-h-[80vh] rounded-xl border border-white/5 bg-white shadow-2xl">
          <div id="convention-preview" class="bg-white text-black font-serif" style="width:794px; padding:40px; font-size:11px; line-height:1.6;">

            <!-- En-tête officiel IRD / UMMISCO -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:3px solid #1a4fa0; padding-bottom:12px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <img src="/images/logo_UMMISCO.webp" alt="UMMISCO" style="height:48px; width:auto; object-fit:contain;" />
                <div style="font-size:9px; color:#444; line-height:1.5;">
                  <strong style="color:#1a4fa0; font-size:10px;">UMMISCO</strong><br>
                  Unité de Modélisation Mathématique<br>
                  et Informatique des Systèmes Complexes<br>
                  IRD · CNRS · Sorbonne · UCAD
                </div>
              </div>
              <div style="text-align:center; flex:1; padding:0 16px;">
                <h1 style="font-size:16px; font-weight:bold; letter-spacing:2px; text-transform:uppercase; color:#1a4fa0; margin:0 0 4px;">CONVENTION DE STAGE</h1>
                <div style="width:60px; height:2px; background:#1a4fa0; margin:0 auto;"></div>
              </div>
              <div style="display:flex; align-items:center; gap:8px;">
                <img src="/images/logo_ucad.webp" alt="UCAD" style="height:40px; width:auto; object-fit:contain; opacity:0.8;" />
              </div>
            </div>

            <!-- Préambule -->
            <p style="margin-bottom:12px;"><strong>ENTRE,</strong></p>
            <p style="margin-bottom:12px;">L'Institut de Recherche pour le développement, établissement public à caractère scientifique et technologique (EPST) ayant son siège 44 boulevard de Dunkerque - CS 9009 - 13572 Marseille France, représenté par M. Pierre MORAND, Représentant de l'IRD au Sénégal, ci-après dénommé «IRD»</p>
            <p style="margin-bottom:12px;"><strong>ET,</strong></p>
            <div style="margin-bottom:12px; border:1px solid #ddd; padding:10px; background:#f9f9f9;">
              <p>Nom de l'organisme de formation : <strong>{{ form.etablissement_nom || '.............................................' }}</strong></p>
              <p>Statut juridique : <strong>{{ form.etablissement_statut || '.............................................' }}</strong></p>
              <p>Siège social : <strong>{{ form.etablissement_siege || '.............................................' }}</strong></p>
              <p>Représenté par : <strong>{{ form.etablissement_representant || '.............................................' }}</strong></p>
              <p>Ci-après dénommé «Etablissement»</p>
            </div>

            <p style="margin-bottom:8px;"><strong>CONCERNANT LE STAGE DE :</strong></p>
            <div style="margin-bottom:12px; border:1px solid #ddd; padding:10px; background:#f9f9f9;">
              <p>Nom, Prénom : <strong>{{ form.stagiaire_nom }} {{ form.stagiaire_prenom }}</strong></p>
              <p>Adresse : <strong>{{ form.stagiaire_adresse || '.............................................' }}</strong></p>
              <p>Tel : <strong>{{ form.stagiaire_tel || '.............................................' }}</strong></p>
              <p>Email : <strong>{{ form.stagiaire_email || '.............................................' }}</strong></p>
              <p>Etudiant pour l'année universitaire : <strong>{{ form.stagiaire_annee_univ || '.............' }}</strong></p>
              <p>Diplôme préparé : <strong>{{ form.stagiaire_diplome || '.............................................' }}</strong></p>
              <p>Spécialité : <strong>{{ form.stagiaire_specialite || '.............................................' }}</strong></p>
            </div>

            <p style="margin-bottom:8px;"><strong>CONSIDERANT :</strong></p>
            <p style="margin-bottom:6px;">que l'étudiant est inscrit régulièrement dans un établissement du Sénégal habilité à délivré le diplôme.</p>
            <p style="margin-bottom:6px;">que la formation de Licence/Master est organisée sous la forme de cours, de conférences, de séminaires, de travaux dirigés, de travaux pratiques, de stages et de conduites de projets individuels et collectifs.</p>
            <p style="margin-bottom:6px;">La mission de formation de l'IRD</p>
            <p style="margin-bottom:16px;">Le partenariat entre l'Université et l'IRD</p>

            <p style="text-align:center; font-weight:bold; font-size:13px; margin-bottom:16px; text-decoration:underline;">IL EST CONVENU CE QUI SUIT :</p>

            <!-- Articles -->
            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 1 : objet</p>
              <p>La présente convention a pour objet de préciser les modalités d'accueil du stagiaire à l'IRD dans le cadre de la préparation de son diplôme.</p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 2 : champ d'application</p>
              <p>Le stage a pour objet de permettre à l'étudiant de mettre en pratique les outils théoriques et méthodologiques acquis au cours de sa formation universitaire, d'identifier ses compétences et découvrir un milieu professionnel.</p>
              <p style="margin-top:6px;">Le stagiaire n'effectue pas une prestation de service mais une étude qui s'inscrit dans le cadre de la formation et du projet de l'étudiant en accord avec l'IRD sur le thème : <strong>{{ form.theme || '...............' }}</strong></p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 3 : activités du stagiaire</p>
              <p>Les responsables scientifiques ou administratifs s'engagent à ne faire exécuter au stagiaire que des travaux ou activités qui concourent à sa formation.</p>
              <p style="margin-top:4px;">Les activités confiées porteront sur les aspects suivants :</p>
              <ul style="margin-left:16px; margin-top:4px;">
                <li v-for="(act, i) in form.activites" :key="i">- {{ act || '.................................................................' }}</li>
              </ul>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 4 : modalités</p>
              <p>Le stage s'effectue du <strong>{{ formatDate(form.date_debut) }}</strong> au <strong>{{ formatDate(form.date_fin) }}</strong></p>
              <p>Lieu du stage : <strong>{{ form.lieu_stage || '.............' }}</strong></p>
              <p>Structure d'accueil : <strong>{{ form.structure_accueil || '.........' }}</strong></p>
              <p style="margin-top:4px;">Encadrement :</p>
              <p>Responsable scientifique/administratif pour l'IRD : <strong>{{ form.responsable_ird || '...............' }}</strong></p>
              <p>Responsable pédagogique pour l'établissement d'enseignement : <strong>{{ form.responsable_etablissement || '.........' }}</strong></p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 5 : gratification</p>
              <p>La gratification est fixée à <strong>{{ form.gratification_montant || '.....' }}</strong> par mois, à ce montant s'ajoute :</p>
              <p>une indemnité de transport de <strong>{{ form.indemnite_transport || '.......' }}</strong> par mois,</p>
              <p>une indemnité de restauration de <strong>{{ form.indemnite_restauration || '......' }}</strong> par mois.</p>
              <p>Le montant de cette gratification est imputé sur : <strong>{{ form.imputation || '.........' }}</strong></p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 6 : statut</p>
              <p>Pendant toute la durée de son stage, le stagiaire conserve son statut d'étudiant et relèvent en matière de discipline, de sanctions, de couverture sociale, médicale et accident du travail de l'établissement dans lequel il est régulièrement inscrit.</p>
              <p style="margin-top:4px;">Tout déplacement du stagiaire hors de son lieu de stage doit respecter les procédures administratives en vigueur à l'IRD et faire l'objet d'une autorisation préalable.</p>
              <p style="margin-top:4px;">Le stagiaire est tenu de respecter le règlement intérieur et les règles d'hygiène et sécurité de l'IRD (annexe 1).</p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 7 : devoirs de réserve et confidentialité</p>
              <p>Le devoir de réserve est de rigueur absolue et apprécié par l'organisme d'accueil compte-tenu de ses spécificités. Le stagiaire prend donc l'engagement de n'utiliser en aucun cas les informations recueillies ou obtenues par lui pour en faire publication, communication à des tiers sans accord préalable de l'organisme d'accueil, y compris le rapport de stage.</p>
              <p style="margin-top:4px;">Cet engagement vaut non seulement pour la durée du stage mais également après son expiration. Le stagiaire s'engage à ne conserver, emporter, ou prendre copie d'aucun document ou logiciel, de quelque nature que ce soit, appartenant à l'organisme d'accueil, sauf accord de ce dernier.</p>
              <p style="margin-top:4px;">Dans le cadre de la confidentialité des informations contenues dans le rapport de stage, l'organisme d'accueil peut demander une restriction de la diffusion du rapport, voire le retrait de certains éléments confidentiels.</p>
              <p style="margin-top:4px;">Les personnes amenées à en connaître sont contraintes par le secret professionnel à n'utiliser ni ne divulguer les informations du rapport.</p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 8 : propriété intellectuelle</p>
              <p>Conformément au code de la propriété intellectuelle, dans le cas où les activités du stagiaire donnent lieu à la création d'une œuvre protégée par le droit d'auteur ou la propriété industrielle (y compris un logiciel), si l'organisme d'accueil souhaite l'utiliser et que le stagiaire en est d'accord, un contrat devra être signé entre le stagiaire (auteur) et l'organisme d'accueil.</p>
              <p style="margin-top:4px;">Le contrat devra alors notamment préciser l'étendue des droits cédés, l'éventuelle exclusivité, la destination, les supports utilisés et la durée de la cession, ainsi que, le cas échéant, le montant de la rémunération due au stagiaire au titre de la cession. Cette clause s'applique quel que soit le statut de l'organisme d'accueil.</p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 9 : clause informatique</p>
              <p>Le stagiaire s'engage à respecter et signe la charte informatique de la structure d'accueil (annexe 2).</p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 10 : absence, prolongation, interruption du stage</p>
              <p>Le stagiaire ne dispose pas de droit à congé. Toutefois, il peut être autorisé, exceptionnellement, sur accord de son responsable scientifique/administratif et de son responsable pédagogique.</p>
              <p style="margin-top:4px;">Le stage peut être prolongé par avenant dans la limite de 6 mois consécutif pour une même année universitaire.</p>
              <p style="margin-top:4px;">Le stagiaire ou l'IRD peuvent interrompre à tout moment le stage après avoir dument informé l'établissement en précisant les raisons de la rupture.</p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 11 : responsabilité civile</p>
              <p>Le stagiaire certifie qu'il possède une assurance couvrant sa responsabilité civile individuelle pendant la durée du stage, susceptible d'être engagée en raison de faits personnels ayant causé des dommages à des tiers à l'occasion du stage.</p>
              <p style="margin-top:4px;">Les autres parties déclarent être garanties au titre de la responsabilité civile.</p>
            </div>

            <div style="margin-bottom:12px;">
              <p style="font-weight:bold;">Article 12 : exclusion</p>
              <p>Le stagiaire ne peut être lié par contrat de travail ou de prestation avec l'IRD.</p>
              <p style="margin-top:4px;">La signature d'une convention de stage annule tout contrat de travail ou de prestation de service en cours avec l'IRD pendant la période du stage.</p>
            </div>

            <div style="margin-bottom:16px;">
              <p style="font-weight:bold;">Article 13 : pièces contractuelles</p>
              <p>Les annexes paraphées et signées par les parties font partie intégrantes de la convention</p>
              <p>Annexe 1 : règlement intérieur hygiène et sécurité</p>
              <p>Annexe 2 : charte utilisateur pour l'usage de ressources informatiques, de service internet et de services intranet</p>
            </div>

            <!-- Signatures -->
            <div style="margin-top:24px; border-top:1px solid #ddd; padding-top:16px;">
              <p style="text-align:center; margin-bottom:16px;">Fait en trois exemplaires, à Dakar, le <strong>{{ form.date_signature }}</strong></p>
              <div style="display:flex; justify-content:space-between; margin-top:24px;">
                <div style="text-align:center; width:30%;">
                  <p style="font-weight:bold; border-bottom:1px solid #000; padding-bottom:4px; margin-bottom:8px;">Pour l'Etablissement d'Enseignement</p>
                  <p style="font-size:10px; color:#555;">(Nom, Prénom, date, cachet et signature)</p>
                  <div style="height:60px;"></div>
                </div>
                <div style="text-align:center; width:30%;">
                  <p style="font-weight:bold; border-bottom:1px solid #000; padding-bottom:4px; margin-bottom:8px;">Pour le stagiaire</p>
                  <p style="font-size:10px; color:#555;">(date et signature)</p>
                  <div style="height:60px;"></div>
                </div>
                <div style="text-align:center; width:30%;">
                  <p style="font-weight:bold; border-bottom:1px solid #000; padding-bottom:4px; margin-bottom:8px;">Pour l'IRD</p>
                  <p style="font-size:10px; color:#555;">Le Représentant de l'IRD au Sénégal</p>
                  <p style="font-size:10px; color:#555;">(date, cachet et signature)</p>
                  <div style="height:60px;"></div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
@media print {
  body * { visibility: hidden; }
  #convention-preview, #convention-preview * { visibility: visible; }
  #convention-preview {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: white !important;
    color: black !important;
    font-family: serif;
    font-size: 11pt;
  }
}
</style>
