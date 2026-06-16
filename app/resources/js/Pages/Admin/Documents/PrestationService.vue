<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { ClipboardDocumentListIcon, PrinterIcon, ArrowDownTrayIcon, ArrowLeftIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { logoIrd } from '@/utils/logos.js'

defineOptions({ layout: DashboardLayout })

const form = ref({
  nom: '',
  prenoms: '',
  ne_le: '',
  a: '',
  adresse: '',
  tel: '',
  emploi_fonction: '',
  objet: '',
  produits_attendus: '',
  duree: '',
  date_debut: '',
  date_fin: '',
  responsable_suivi: '',
  montant_brut: '',
  montant_net_lettres: '',
  service: '',
  imputation: '',
  date_acquit: new Date().toLocaleDateString('fr-FR'),
})

const montantBrut = computed(() => parseFloat(form.value.montant_brut) || 0)
const impot       = computed(() => Math.round(montantBrut.value * 0.05))
const montantNet  = computed(() => montantBrut.value - impot.value)

const fmt = (v) => v ? Number(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") : ''
const fmtCFA = (v) => v ? fmt(v) + ' F CFA' : ''

const generating = ref(false)
const previewUrl = ref('')
let previewTimeout = null

const pdfGeneratedMode = ref(false)
const pdfBlobUrlFinal = ref(null)
const pdfBlobFileFinal = ref(null)
const saving = ref(false)
const iframeRef = ref(null)

async function updatePreview() {
  try {
    const doc = await buildPDF()
    const pdfBlob = doc.output('blob')
    if (previewUrl.value) {
      URL.revokeObjectURL(previewUrl.value)
    }
    previewUrl.value = URL.createObjectURL(pdfBlob)
  } catch (e) {
    console.error("Erreur génération aperçu PDF :", e)
  }
}

watch(form, () => {
  clearTimeout(previewTimeout)
  previewTimeout = setTimeout(updatePreview, 600)
}, { deep: true })

onMounted(() => updatePreview())

onUnmounted(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
})

// ── Génération PDF fidèle au document original FI-8 V5 ────────────────────
async function buildPDF() {
  const { jsPDF } = await import('jspdf')
  const { default: autoTable } = await import('jspdf-autotable')

  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
  const mL = 15, mR = 195, pageW = 210
  let y = 15

  // ══ EN-TÊTE 3 COLONNES (fidèle au document Excel original) ══
  autoTable(doc, {
    startY: y,
    body: [[
      {
        content: '\n\n\n\n\nReprésentation de l\'IRD\nau Sénégal\nTél : 00221 33 849 35 35\nBP 1386 - Dakar',
        styles: { fontSize: 8, cellWidth: 40, valign: 'middle', fontStyle: 'normal' }
      },
      {
        content: 'RECU DE PRESTATION DE SERVICE',
        styles: { fontSize: 14, fontStyle: 'bold', halign: 'center', valign: 'middle', cellWidth: 100 }
      },
      {
        content: 'Identification : FI - 8\nDate de création : 10/07/08\nDate de Modification : 24/08/2011\nVersion : V5',
        styles: { fontSize: 7.5, cellWidth: 40, valign: 'middle', halign: 'right', fontStyle: 'normal' }
      },
    ]],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3 },
    margin: { left: mL, right: 15 },
  })

  if (logoIrd) {
    const imgX = mL + (40 - 22) / 2
    const imgY = 15 + 3
    doc.addImage(logoIrd, 'PNG', imgX, imgY, 22, 10)
  }

  y = doc.lastAutoTable.finalY + 2

  // ══ SECTION INFORMATIONS PRESTATAIRE ══
  const row = (label, val, bold = false) => [
    { content: label, styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 70 } },
    { content: val || '', styles: { fontSize: 9, fontStyle: 'bold', textColor: val ? [0, 0, 0] : [0, 0, 0], cellWidth: 110 } },
  ]

  const rowDouble = (label1, val1, label2, val2) => [
    { content: label1, styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 30 } },
    { content: val1 || '', styles: { fontSize: 9, fontStyle: 'bold', textColor: val1 ? [0, 0, 0] : [0, 0, 0], cellWidth: 50 } },
    { content: label2, styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 30 } },
    { content: val2 || '', styles: { fontSize: 9, fontStyle: 'bold', textColor: val2 ? [0, 0, 0] : [0, 0, 0], cellWidth: 70 } },
  ]

  autoTable(doc, {
    startY: y,
    body: [
      rowDouble('NOM :', form.value.nom, 'Prénoms :', form.value.prenoms),
      rowDouble('né le :', form.value.ne_le, 'à :', form.value.a),
      row('Adresse :', form.value.adresse),
      rowDouble('Tél :', form.value.tel, 'Emploi/Fonction :', form.value.emploi_fonction),
      row('Objet de la prestation :', form.value.objet),
      row('Produits attendus :', form.value.produits_attendus),
      [
        { content: 'Durée (maximum 9 jours consécutifs) :', styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 70 } },
        { content: (form.value.duree || '') + (form.value.date_debut || form.value.date_fin ? `    du : ${form.value.date_debut || '...'} au : ${form.value.date_fin || '...'}` : ''), styles: { fontSize: 9, fontStyle: 'bold', textColor: [0, 0, 0], cellWidth: 110 } },
      ],
      row('Nom du responsable du suivi :', form.value.responsable_suivi),
    ],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3, minCellHeight: 9 },
    margin: { left: mL, right: 15 },
    columnStyles: {},
  })

  y = doc.lastAutoTable.finalY + 1

  // ══ SECTION MONTANTS ══
  autoTable(doc, {
    startY: y,
    body: [
      [
        {
          content: 'Montant net à percevoir soit 95% du montant brut (en chiffres) (**) :',
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 120 }
        },
        {
          content: montantNet.value > 0 ? fmt(montantNet.value) : '',
          styles: { fontSize: 9, cellWidth: 40, halign: 'right', fontStyle: 'bold', textColor: montantNet.value > 0 ? [0, 0, 0] : [0, 0, 0] }
        },
        {
          content: 'F CFA',
          styles: { fontSize: 9, cellWidth: 20, halign: 'center' }
        },
      ],
      [
        {
          content: 'Montant net à percevoir (en lettres) (**) : ',
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 80 }
        },
        {
          content: form.value.montant_net_lettres || '',
          colSpan: 2,
          styles: { fontSize: 9, fontStyle: 'bold', textColor: form.value.montant_net_lettres ? [0, 0, 0] : [0, 0, 0], fillColor: [245, 245, 245] }
        },
      ],
      // --- Admin section ---
      [
        {
          content: 'À remplir par l\'administration',
          colSpan: 3,
          styles: { fontStyle: 'bold', fontSize: 9, halign: 'center', fillColor: [220, 220, 220] }
        },
      ],
      [
        {
          content: 'Impôt sur le revenu : 5% du montant brut (*) :',
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 120 }
        },
        {
          content: impot.value > 0 ? fmt(impot.value) : '',
          styles: { fontSize: 9, halign: 'right', cellWidth: 40, fontStyle: 'bold', textColor: impot.value > 0 ? [0, 0, 0] : [0, 0, 0] }
        },
        {
          content: 'F CFA',
          styles: { fontSize: 9, halign: 'center', cellWidth: 20 }
        },
      ],
      [
        {
          content: 'Montant brut de la prestation :',
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 120 }
        },
        {
          content: montantBrut.value > 0 ? fmt(montantBrut.value) : '',
          styles: { fontSize: 9, halign: 'right', cellWidth: 40, fontStyle: 'bold', textColor: montantBrut.value > 0 ? [0, 0, 0] : [0, 0, 0] }
        },
        {
          content: 'F CFA',
          styles: { fontSize: 9, halign: 'center', cellWidth: 20 }
        },
      ],
      [
        {
          content: 'SERVICE/UR/US :',
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 40 }
        },
        {
          content: form.value.service || '',
          colSpan: 2,
          styles: { fontSize: 9, fontStyle: 'bold', textColor: form.value.service ? [0, 0, 0] : [0, 0, 0] }
        },
      ],
      [
        {
          content: 'IMPUTATION\ncode EOTP ou centre\n(budget de fonctionnement) :',
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245] }
        },
        {
          content: form.value.imputation || '',
          colSpan: 2,
          styles: { fontSize: 9, fontStyle: 'bold', textColor: form.value.imputation ? [0, 0, 0] : [0, 0, 0] }
        },
      ],
    ],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3, minCellHeight: 9 },
    margin: { left: mL, right: 15 },
  })

  y = doc.lastAutoTable.finalY + 2

  // ══ SIGNATURES CERTIFIÉ ══
  autoTable(doc, {
    startY: y,
    body: [[
      {
        content: 'Certifié le service fait\n\n\n\nLe Responsable du suivi',
        styles: { fontSize: 9, halign: 'center', minCellHeight: 28 }
      },
      {
        content: 'Certifié le service fait\n\n\n\nL\'Ordonnateur',
        styles: { fontSize: 9, halign: 'center', minCellHeight: 28 }
      },
    ]],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3 },
    margin: { left: mL, right: 15 },
  })

  y = doc.lastAutoTable.finalY + 1

  // ══ POUR ACQUIT ══
  autoTable(doc, {
    startY: y,
    body: [[
      {
        content: 'POUR ACQUIT :',
        styles: { fontStyle: 'bold', fontSize: 9, cellWidth: 110, minCellHeight: 20 }
      },
      {
        content: `Date :  ${form.value.date_acquit}`,
        styles: { fontSize: 9, cellWidth: 70, fontStyle: 'bold', textColor: form.value.date_acquit ? [0, 0, 0] : [0, 0, 0] }
      },
    ]],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3 },
    margin: { left: mL, right: 15 },
  })

  y = doc.lastAutoTable.finalY + 4

  // ══ NOTES DE BAS ══
  doc.setFontSize(8)
  doc.setFont('helvetica', 'normal')
  doc.text('(*) Si le montant de la prestation est égal ou supérieur à 25 000 F CFA.', mL, y)
  y += 4
  doc.text('NB : Les impôts ne sont pas prélevés pour les prestations effectuées hors du Sénégal par des non-résidents.', mL, y)
  y += 4
  doc.text('(**) Montant maximum autorisé : 100.000 F CFA/prestation.', mL, y)

  return doc
}

async function genererPDF() {
  generating.value = true
  const doc = await buildPDF()
  const pdfBlob = doc.output('blob')
  
  pdfBlobFileFinal.value = pdfBlob
  pdfBlobUrlFinal.value = URL.createObjectURL(pdfBlob)
  pdfGeneratedMode.value = true
  generating.value = false
}

async function enregistrer(background = false) {
  if (!pdfBlobFileFinal.value) return
  if (!background) saving.value = true

  try {
    const formData = new FormData()
    formData.append('type_document', 'prestation_service')
    formData.append('donnees', JSON.stringify(form.value))
    formData.append('pdf_file', pdfBlobFileFinal.value, 'prestation.pdf')
    
    await window.axios.post('/admin/documents/store', formData)
    
    if (!background) {
      saving.value = false
      router.visit('/admin/documents')
    }
  } catch (error) {
    if (!background) saving.value = false
    console.error("Erreur lors de l'enregistrement :", error)
  }
}

function imprimerFromModal() {
  if (window.confirm("Voulez-vous confirmer l'impression ? Le document sera automatiquement enregistré dans l'historique.")) {
    enregistrer(true)
    if (iframeRef.value) {
      iframeRef.value.contentWindow.print()
      retour()
    }
  }
}

function retour() {
  pdfGeneratedMode.value = false
  if (pdfBlobUrlFinal.value) {
    URL.revokeObjectURL(pdfBlobUrlFinal.value)
    pdfBlobUrlFinal.value = null
  }
}
</script>

<template>
  <div class="p-6 max-w-5xl mx-auto space-y-6 animate-fade-in">

    <!-- HEADER COMMUN -->
    <div class="flex items-center gap-4">
      <button v-if="pdfGeneratedMode" @click="retour" class="p-2 rounded-lg border border-[var(--border)] text-[var(--text-subtle)] hover:text-[var(--text)] hover:bg-white/5 transition-all">
        <ArrowLeftIcon class="w-4 h-4" />
      </button>
      <Link v-else href="/admin/documents" class="p-2 rounded-lg border border-[var(--border)] text-[var(--text-subtle)] hover:text-[var(--text)] hover:bg-white/5 transition-all">
        <ArrowLeftIcon class="w-4 h-4" />
      </Link>
      <div>
        <h1 class="text-2xl font-extrabold text-[var(--text)] flex items-center gap-3">
          <ClipboardDocumentListIcon class="w-7 h-7 text-emerald-400" />
          Reçu de Prestation de Service
        </h1>
        <p class="text-[var(--text-subtle)] text-sm mt-0.5">
          Identification : <span class="text-emerald-400 font-semibold">FI - 8 · Version V5</span> — IRD Représentation Sénégal
        </p>
      </div>
    </div>

    <!-- MODE: FORMULAIRE -->
    <div v-show="!pdfGeneratedMode" class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- ─── FORMULAIRE ─── -->
      <div class="space-y-5">

        <!-- Prestataire -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Informations du Prestataire</h2>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">NOM *</label>
              <input v-model="form.nom" type="text" placeholder="NOM (majuscules)"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Prénoms *</label>
              <input v-model="form.prenoms" type="text" placeholder="Prénoms"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Né(e) le</label>
              <input v-model="form.ne_le" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">À (lieu de naissance)</label>
              <input v-model="form.a" type="text" placeholder="Ville"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Adresse</label>
            <input v-model="form.adresse" type="text" placeholder="Adresse complète"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Tél</label>
              <input v-model="form.tel" type="text" placeholder="Téléphone"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Emploi/Fonction</label>
              <input v-model="form.emploi_fonction" type="text" placeholder="Titre ou fonction"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
          </div>
        </div>

        <!-- Prestation -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Détails de la Prestation</h2>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Objet de la prestation *</label>
            <textarea v-model="form.objet" rows="2" placeholder="Description de la mission..."
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Produits attendus</label>
            <textarea v-model="form.produits_attendus" rows="2" placeholder="Livrables attendus..."
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Durée <span class="text-[var(--text-subtle)] font-normal">(maximum 9 jours consécutifs)</span></label>
            <input v-model="form.duree" type="text" placeholder="Ex: 5 jours"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Du</label>
              <input v-model="form.date_debut" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Au</label>
              <input v-model="form.date_fin" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Nom du responsable du suivi</label>
            <input v-model="form.responsable_suivi" type="text" placeholder="Nom du responsable IRD"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
        </div>

        <!-- Montants -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Montants</h2>
          <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-lg p-3 text-xs text-emerald-300">
            💡 Saisissez le <strong>montant brut</strong> — l'impôt 5% et le net 95% sont calculés automatiquement.
            Maximum autorisé : <strong>100 000 F CFA</strong>.
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Montant brut de la prestation (F CFA) *</label>
            <input :value="form.montant_brut" @input="form.montant_brut = $event.target.value.replace(/[^0-9]/g, '')" type="text" inputmode="numeric" placeholder="Ex: 50000"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-[var(--surface-alt)]/40 rounded-lg p-3 border border-[var(--border)]">
              <div class="text-[10px] text-[var(--text-subtle)] uppercase font-bold mb-1">Impôt sur le revenu (5%)</div>
              <div class="text-sm font-bold text-red-400">{{ impot > 0 ? fmtCFA(impot) : '—' }}</div>
            </div>
            <div class="bg-[var(--surface-alt)]/40 rounded-lg p-3 border border-[var(--border)]">
              <div class="text-[10px] text-[var(--text-subtle)] uppercase font-bold mb-1">Montant net à percevoir (95%)</div>
              <div class="text-sm font-bold text-emerald-400">{{ montantNet > 0 ? fmtCFA(montantNet) : '—' }}</div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Montant net en lettres *</label>
            <input v-model="form.montant_net_lettres" type="text" placeholder="Ex: Quarante sept mille cinq cents francs CFA"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
        </div>

        <!-- Administration -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400">À remplir par l'administration</h2>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">SERVICE/UR/US</label>
            <input v-model="form.service" type="text" placeholder="Service / Unité de Recherche"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">IMPUTATION — code EOTP ou centre</label>
            <input v-model="form.imputation" type="text" placeholder="Code EOTP ou centre de coût"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Date (pour acquit)</label>
            <input v-model="form.date_acquit" type="text" placeholder="JJ/MM/AAAA"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="genererPDF" :disabled="generating"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5">
            <ArrowDownTrayIcon class="w-5 h-5" />
            {{ generating ? 'Génération...' : 'Générer PDF fidèle' }}
          </button>
          <button @click="imprimer"
            class="flex items-center gap-2 px-5 py-3 border border-[var(--border)] text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-white/5 rounded-xl text-sm font-bold transition-all">
            <PrinterIcon class="w-5 h-5" />
            Imprimer
          </button>
        </div>
      </div>

      <!-- ─── APERÇU FIDÈLE AU DOCUMENT FI-8 V5 ─── -->
      <div class="xl:sticky xl:top-6 xl:self-start flex flex-col h-[80vh]">
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 mb-3 flex items-center justify-between shrink-0">
          <span class="text-xs font-semibold text-[var(--text-subtle)] uppercase tracking-wider">Aperçu en direct — fidèle au document officiel</span>
        </div>
        <div class="flex-1 rounded-xl border border-[var(--border)] bg-[var(--surface)]/40 shadow-2xl overflow-hidden relative">
          <iframe v-if="previewUrl" :src="previewUrl" class="absolute inset-0 w-full h-full" frameborder="0"></iframe>
          <div v-else class="absolute inset-0 flex items-center justify-center text-[var(--text-subtle)] font-medium text-sm">
            Génération de l'aperçu PDF...
          </div>
        </div>
      </div>
    </div>

    <!-- MODE: PDF GÉNÉRÉ -->
    <div v-if="pdfGeneratedMode" class="flex flex-col h-[85vh] bg-[var(--surface)] border border-[var(--border)] rounded-xl shadow-2xl overflow-hidden animate-fade-in">
      <div class="flex items-center justify-between px-6 py-4 border-b border-[var(--border)] bg-[var(--surface-alt)]">
        <h3 class="text-lg font-bold text-[var(--text)] flex items-center gap-2">
          Aperçu final du document
        </h3>
        <div class="flex items-center gap-3">
          <button @click="retour" class="flex items-center gap-2 px-4 py-2 bg-slate-700/50 hover:bg-slate-700 text-white rounded-lg text-sm font-semibold transition-all">
            <ArrowLeftIcon class="w-4 h-4" />
            Retour
          </button>
          <button @click="imprimerFromModal" class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-sm font-semibold transition-all shadow-sm border border-[var(--border)]">
            <PrinterIcon class="w-4 h-4" />
            Imprimer
          </button>
          <button @click="enregistrer(false)" :disabled="saving" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-bold transition-all disabled:opacity-50 shadow-lg shadow-emerald-500/20">
            <ArrowDownTrayIcon class="w-4 h-4" />
            {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
          </button>
        </div>
      </div>
      <div class="flex-1 w-full bg-slate-900/50 overflow-hidden">
        <iframe ref="iframeRef" :src="pdfBlobUrlFinal" class="w-full h-full border-none"></iframe>
      </div>
    </div>

  </div>
</template>
