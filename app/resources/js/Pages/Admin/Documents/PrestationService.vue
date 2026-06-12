<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { ClipboardDocumentListIcon, PrinterIcon, ArrowDownTrayIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

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

const fmt = (v) => v ? new Intl.NumberFormat('fr-FR').format(v) : ''
const fmtCFA = (v) => v ? fmt(v) + ' F CFA' : ''

const generating = ref(false)

// ── Génération PDF fidèle au document original FI-8 V5 ────────────────────
async function genererPDF() {
  generating.value = true
  const { jsPDF } = await import('jspdf')
  const { default: autoTable } = await import('jspdf-autotable')

  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
  const mL = 15, mR = 195, pageW = 210
  let y = 15

  // ── LOGOS (En-tête) ──
  if (logoIrd) doc.addImage(logoIrd, 'JPEG', mL, y, 22, 10)
  y += 20

  // ══ EN-TÊTE 3 COLONNES (fidèle au document Excel original) ══
  autoTable(doc, {
    startY: y,
    body: [[
      {
        content: 'Représentation de l\'IRD\nau Sénégal\nTél : 00221 33 849 35 35\nBP 1386 - Dakar',
        styles: { fontSize: 8, cellWidth: 48, valign: 'middle', fontStyle: 'normal' }
      },
      {
        content: 'RECU DE PRESTATION DE SERVICE',
        styles: { fontSize: 14, fontStyle: 'bold', halign: 'center', valign: 'middle', cellWidth: 104 }
      },
      {
        content: 'Identification : FI - 8\nDate de création : 10/07/08\nDate de Modification : 24/08/2011\nVersion : V5',
        styles: { fontSize: 7.5, cellWidth: 38, valign: 'middle', halign: 'right', fontStyle: 'normal' }
      },
    ]],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3 },
    margin: { left: mL, right: 15 },
  })

  y = doc.lastAutoTable.finalY + 2

  // ══ SECTION INFORMATIONS PRESTATAIRE ══
  const row = (label, val, bold = false) => [
    { content: label, styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 72 } },
    { content: val || '', styles: { fontSize: 9, fontStyle: bold ? 'bold' : 'normal', cellWidth: 118 } },
  ]

  const rowDouble = (label1, val1, label2, val2) => [
    { content: label1, styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 32 } },
    { content: val1 || '', styles: { fontSize: 9, cellWidth: 52 } },
    { content: label2, styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 28 } },
    { content: val2 || '', styles: { fontSize: 9, cellWidth: 78 } },
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
        { content: 'Durée (maximum 9 jours consécutifs) :', styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 72 } },
        { content: (form.value.duree || '') + (form.value.date_debut || form.value.date_fin ? `    du : ${form.value.date_debut || '...'} au : ${form.value.date_fin || '...'}` : ''), styles: { fontSize: 9, cellWidth: 118 } },
      ],
      row('Nom du responsable du suivi :', form.value.responsable_suivi),
    ],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3, minCellHeight: 7 },
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
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 130 }
        },
        {
          content: montantNet.value > 0 ? fmt(montantNet.value) : '',
          styles: { fontSize: 9, cellWidth: 40, halign: 'right' }
        },
        {
          content: 'F CFA',
          styles: { fontSize: 9, cellWidth: 20, halign: 'center' }
        },
      ],
      [
        {
          content: `Montant net à percevoir (en lettres) (**) :     ${form.value.montant_net_lettres || ''}`,
          colSpan: 3,
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245] }
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
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 130 }
        },
        {
          content: impot.value > 0 ? fmt(impot.value) : '',
          styles: { fontSize: 9, halign: 'right', cellWidth: 40 }
        },
        {
          content: 'F CFA',
          styles: { fontSize: 9, halign: 'center', cellWidth: 20 }
        },
      ],
      [
        {
          content: 'Montant brut de la prestation :',
          styles: { fontStyle: 'bold', fontSize: 9, fillColor: [245, 245, 245], cellWidth: 130 }
        },
        {
          content: montantBrut.value > 0 ? fmt(montantBrut.value) : '',
          styles: { fontSize: 9, halign: 'right', cellWidth: 40 }
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
          styles: { fontSize: 9 }
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
          styles: { fontSize: 9 }
        },
      ],
    ],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3, minCellHeight: 8 },
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
        styles: { fontStyle: 'bold', fontSize: 9, cellWidth: 120, minCellHeight: 20 }
      },
      {
        content: `Date :  ${form.value.date_acquit}`,
        styles: { fontSize: 9, cellWidth: 70 }
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

  doc.save(`Recu_Prestation_Service_${form.value.nom || 'prestataire'}.pdf`)
  generating.value = false
}

function imprimer() {
  window.print()
}
</script>

<template>
  <div class="p-6 max-w-5xl mx-auto space-y-6 animate-fade-in">

    <div class="flex items-center gap-4">
      <Link href="/admin/documents" class="p-2 rounded-lg border border-white/10 text-slate-400 hover:text-white hover:bg-white/5 transition-all">
        <ArrowLeftIcon class="w-4 h-4" />
      </Link>
      <div>
        <h1 class="text-2xl font-extrabold text-white flex items-center gap-3">
          <ClipboardDocumentListIcon class="w-7 h-7 text-emerald-400" />
          Reçu de Prestation de Service
        </h1>
        <p class="text-slate-400 text-sm mt-0.5">
          Identification : <span class="text-emerald-400 font-semibold">FI - 8 · Version V5</span> — IRD Représentation Sénégal
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- ─── FORMULAIRE ─── -->
      <div class="space-y-5">

        <!-- Prestataire -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Informations du Prestataire</h2>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">NOM *</label>
              <input v-model="form.nom" type="text" placeholder="NOM (majuscules)"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Prénoms *</label>
              <input v-model="form.prenoms" type="text" placeholder="Prénoms"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Né(e) le</label>
              <input v-model="form.ne_le" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">À (lieu de naissance)</label>
              <input v-model="form.a" type="text" placeholder="Ville"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Adresse</label>
            <input v-model="form.adresse" type="text" placeholder="Adresse complète"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Tél</label>
              <input v-model="form.tel" type="text" placeholder="Téléphone"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Emploi/Fonction</label>
              <input v-model="form.emploi_fonction" type="text" placeholder="Titre ou fonction"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
          </div>
        </div>

        <!-- Prestation -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Détails de la Prestation</h2>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Objet de la prestation *</label>
            <textarea v-model="form.objet" rows="2" placeholder="Description de la mission..."
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Produits attendus</label>
            <textarea v-model="form.produits_attendus" rows="2" placeholder="Livrables attendus..."
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Durée <span class="text-slate-500 font-normal">(maximum 9 jours consécutifs)</span></label>
            <input v-model="form.duree" type="text" placeholder="Ex: 5 jours"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Du</label>
              <input v-model="form.date_debut" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Au</label>
              <input v-model="form.date_fin" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom du responsable du suivi</label>
            <input v-model="form.responsable_suivi" type="text" placeholder="Nom du responsable IRD"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
        </div>

        <!-- Montants -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400">Montants</h2>
          <div class="bg-emerald-500/5 border border-emerald-500/20 rounded-lg p-3 text-xs text-emerald-300">
            💡 Saisissez le <strong>montant brut</strong> — l'impôt 5% et le net 95% sont calculés automatiquement.
            Maximum autorisé : <strong>100 000 F CFA</strong>.
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Montant brut de la prestation (F CFA) *</label>
            <input v-model="form.montant_brut" type="number" placeholder="Ex: 50000"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-800/40 rounded-lg p-3 border border-white/5">
              <div class="text-[10px] text-slate-500 uppercase font-bold mb-1">Impôt sur le revenu (5%)</div>
              <div class="text-sm font-bold text-red-400">{{ impot > 0 ? fmtCFA(impot) : '—' }}</div>
            </div>
            <div class="bg-slate-800/40 rounded-lg p-3 border border-white/5">
              <div class="text-[10px] text-slate-500 uppercase font-bold mb-1">Montant net à percevoir (95%)</div>
              <div class="text-sm font-bold text-emerald-400">{{ montantNet > 0 ? fmtCFA(montantNet) : '—' }}</div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Montant net en lettres *</label>
            <input v-model="form.montant_net_lettres" type="text" placeholder="Ex: Quarante sept mille cinq cents francs CFA"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
        </div>

        <!-- Administration -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-400">À remplir par l'administration</h2>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">SERVICE/UR/US</label>
            <input v-model="form.service" type="text" placeholder="Service / Unité de Recherche"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">IMPUTATION — code EOTP ou centre</label>
            <input v-model="form.imputation" type="text" placeholder="Code EOTP ou centre de coût"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Date (pour acquit)</label>
            <input v-model="form.date_acquit" type="text" placeholder="JJ/MM/AAAA"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="genererPDF" :disabled="generating"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5">
            <ArrowDownTrayIcon class="w-5 h-5" />
            {{ generating ? 'Génération...' : 'Générer PDF fidèle' }}
          </button>
          <button @click="imprimer"
            class="flex items-center gap-2 px-5 py-3 border border-white/10 text-slate-300 hover:text-white hover:bg-white/5 rounded-xl text-sm font-bold transition-all">
            <PrinterIcon class="w-5 h-5" />
            Imprimer
          </button>
        </div>
      </div>

      <!-- ─── APERÇU FIDÈLE AU DOCUMENT FI-8 V5 ─── -->
      <div class="xl:sticky xl:top-6 xl:self-start">
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4 mb-3 flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Aperçu — fidèle au document FI-8 V5</span>
        </div>
        <div class="overflow-auto max-h-[80vh] rounded-xl border border-white/5 bg-white shadow-2xl" id="print-zone">
          <div style="width:794px; padding:24px 32px; font-family:Arial,Helvetica,sans-serif; font-size:10px; color:#000; background:#fff;">

            <!-- En-tête 3 colonnes -->
            <table style="width:100%; border-collapse:collapse; border:1.5px solid #000; margin-bottom:0;">
              <tr>
                <td style="border:1px solid #000; padding:8px; width:30%; vertical-align:middle; font-size:9px; line-height:1.5;">
                  <strong>Représentation de l'IRD<br>au Sénégal</strong><br>
                  Tél : 00221 33 849 35 35<br>
                  BP 1386 - Dakar
                </td>
                <td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:middle; font-size:14px; font-weight:bold; width:40%;">
                  RECU DE PRESTATION DE SERVICE
                </td>
                <td style="border:1px solid #000; padding:8px; text-align:right; vertical-align:middle; font-size:8px; line-height:1.6; width:30%;">
                  Identification : FI - 8<br>
                  Date de création : 10/07/08<br>
                  Date de Modification : 24/08/2011<br>
                  Version : V5
                </td>
              </tr>
            </table>

            <!-- Champs prestataire -->
            <table style="width:100%; border-collapse:collapse; border:1px solid #000; border-top:0;">
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold; width:30%;">NOM :</td>
                <td style="border:1px solid #999; padding:5px 8px; width:20%;">{{ form.nom }}</td>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold; width:15%;">Prénoms :</td>
                <td style="border:1px solid #999; padding:5px 8px; width:35%;">{{ form.prenoms }}</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">né le :</td>
                <td style="border:1px solid #999; padding:5px 8px;">{{ form.ne_le }}</td>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">à :</td>
                <td style="border:1px solid #999; padding:5px 8px;">{{ form.a }}</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;" colspan="1">Adresse :</td>
                <td style="border:1px solid #999; padding:5px 8px;" colspan="3">{{ form.adresse }}</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">Tél :</td>
                <td style="border:1px solid #999; padding:5px 8px;">{{ form.tel }}</td>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">Emploi/Fonction :</td>
                <td style="border:1px solid #999; padding:5px 8px;">{{ form.emploi_fonction }}</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">Objet de la prestation :</td>
                <td style="border:1px solid #999; padding:5px 8px;" colspan="3">{{ form.objet }}</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">Produits attendus :</td>
                <td style="border:1px solid #999; padding:5px 8px;" colspan="3">{{ form.produits_attendus }}</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">Durée (maximum 9 jours consécutifs) :</td>
                <td style="border:1px solid #999; padding:5px 8px;" colspan="3">
                  {{ form.duree }}&nbsp;&nbsp;&nbsp;
                  <span v-if="form.date_debut || form.date_fin">du : {{ form.date_debut }}&nbsp;&nbsp;au : {{ form.date_fin }}</span>
                </td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">Nom du responsable du suivi :</td>
                <td style="border:1px solid #999; padding:5px 8px;" colspan="3">{{ form.responsable_suivi }}</td>
              </tr>
              <!-- Montants -->
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;" colspan="2">
                  Montant net à percevoir soit 95% du montant brut (en chiffres) (**) :
                </td>
                <td style="border:1px solid #999; padding:5px 8px; font-weight:bold; text-align:right;" colspan="1">{{ montantNet > 0 ? fmt(montantNet) : '' }}</td>
                <td style="border:1px solid #999; padding:5px 8px;">F CFA</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;" colspan="2">Montant net à percevoir (en lettres) (**) :</td>
                <td style="border:1px solid #999; padding:5px 8px;" colspan="2">{{ form.montant_net_lettres }}</td>
              </tr>
              <!-- Section admin -->
              <tr>
                <td style="border:1px solid #000; padding:5px 8px; text-align:center; font-weight:bold; background:#ddd;" colspan="4">
                  À remplir par l'administration
                </td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;" colspan="2">Impôt sur le revenu : 5% du montant brut (*) :</td>
                <td style="border:1px solid #999; padding:5px 8px; text-align:right; font-weight:bold;">{{ impot > 0 ? fmt(impot) : '' }}</td>
                <td style="border:1px solid #999; padding:5px 8px;">F CFA</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;" colspan="2">Montant brut de la prestation :</td>
                <td style="border:1px solid #999; padding:5px 8px; text-align:right; font-weight:bold;">{{ montantBrut > 0 ? fmt(montantBrut) : '' }}</td>
                <td style="border:1px solid #999; padding:5px 8px;">F CFA</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">SERVICE/UR/US :</td>
                <td style="border:1px solid #999; padding:5px 8px;" colspan="3">{{ form.service }}</td>
              </tr>
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; background:#f5f5f5; font-weight:bold;">IMPUTATION<br>code EOTP ou centre<br>(budget de fonctionnement) :</td>
                <td style="border:1px solid #999; padding:5px 8px;" colspan="3">{{ form.imputation }}</td>
              </tr>
            </table>

            <!-- Certifié le service fait -->
            <table style="width:100%; border-collapse:collapse; border:1px solid #000; border-top:0; margin-bottom:0;">
              <tr>
                <td style="border:1px solid #999; padding:8px; text-align:center; width:50%; height:55px; vertical-align:bottom;">
                  <div>Certifié le service fait</div>
                  <div style="margin-top:28px;">Le Responsable du suivi</div>
                </td>
                <td style="border:1px solid #999; padding:8px; text-align:center; width:50%; vertical-align:bottom;">
                  <div>Certifié le service fait</div>
                  <div style="margin-top:28px;">L'Ordonnateur</div>
                </td>
              </tr>
            </table>

            <!-- Pour acquit -->
            <table style="width:100%; border-collapse:collapse; border:1px solid #000; border-top:0; margin-bottom:10px;">
              <tr>
                <td style="border:1px solid #999; padding:5px 8px; font-weight:bold; width:60%; height:28px;">POUR ACQUIT :</td>
                <td style="border:1px solid #999; padding:5px 8px; width:40%;">Date : {{ form.date_acquit }}</td>
              </tr>
            </table>

            <!-- Notes -->
            <div style="font-size:8px; color:#333; line-height:1.8; margin-top:4px;">
              <p>(*) Si le montant de la prestation est égal ou supérieur à 25 000 F CFA.</p>
              <p>NB : Les impôts ne sont pas prélevés pour les prestations effectuées hors du Sénégal par des non-résidents.</p>
              <p>(**) Montant maximum autorisé : 100.000 F CFA/prestation.</p>
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
  #print-zone, #print-zone * { visibility: visible; }
  #print-zone { position: fixed; top: 0; left: 0; width: 100%; background: white !important; }
}
</style>
