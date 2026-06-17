<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { ShoppingCartIcon, PrinterIcon, ArrowDownTrayIcon, ArrowLeftIcon, PlusIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline'

import { logoIrd, logoUcad, logoReuUmmisco } from '@/utils/logos.js'

defineOptions({ layout: DashboardLayout })

const form = ref({
  demandeur_nom_prenom: '',
  service_structure: '',
  eotp_centre_cout: '',
  adresse_livraison: '',
  date_dakar: new Date().toLocaleDateString('fr-FR'),
})

const articles = ref([
  { objet: '', fournisseur: '', prix_unitaire: '', quantite: '' },
  { objet: '', fournisseur: '', prix_unitaire: '', quantite: '' },
  { objet: '', fournisseur: '', prix_unitaire: '', quantite: '' },
])

const ajouterLigne = () => articles.value.push({ objet: '', fournisseur: '', prix_unitaire: '', quantite: '' })
const supprimerLigne = (i) => { if (articles.value.length > 1) articles.value.splice(i, 1) }

const prixTotal = (a) => {
  const r = (parseFloat(a.prix_unitaire) || 0) * (parseFloat(a.quantite) || 0)
  return r > 0 ? r : null
}

const totalGeneral = computed(() =>
  articles.value.reduce((s, a) => s + (prixTotal(a) || 0), 0)
)

const fmt = (v) => v != null && v !== '' ? Number(v).toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ") : ''

const generating = ref(false)
const pdfGeneratedMode = ref(false)
const pdfBlobUrl = ref(null)
const pdfBlobFile = ref(null)
const saving = ref(false)
const iframeRef = ref(null)

// ── Génération PDF fidèle au document original ─────────────────────────────
async function genererPDF() {
  generating.value = true
  const { jsPDF } = await import('jspdf')
  const { default: autoTable } = await import('jspdf-autotable')

  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
  const mL = 20, mR = 190
  const pageW = 210
  let y = 15

  // ══ EN-TÊTE 3 COLONNES (fidèle au document Word FI-4) ══
  autoTable(doc, {
    startY: y,
    body: [[
      {
        content: '\n\n\n\n\nReprésentation\ndu Sénégal\nTél : 00221 33 849 83 30\nBP 1386 - Dakar',
        styles: { fontSize: 8, cellWidth: 40, valign: 'middle', fontStyle: 'normal' }
      },
      {
        content: 'DEMANDE DE BON D\'ACHAT',
        styles: { fontSize: 14, fontStyle: 'bold', halign: 'center', valign: 'middle', cellWidth: 90 }
      },
      {
        content: 'Identification : FI - 4\nDate de création : 10/07/08\nDate de Modification : 12/11/08\nVersion : 2\nNombre de pages : 1/1',
        styles: { fontSize: 7.5, cellWidth: 40, valign: 'middle', halign: 'right', fontStyle: 'normal' }
      },
    ]],
    theme: 'grid',
    tableLineColor: [0, 0, 0],
    tableLineWidth: 0.5,
    styles: { lineColor: [0, 0, 0], lineWidth: 0.3 },
    margin: { left: mL, right: 20 },
  })

  if (logoIrd) {
    const imgX = mL + (40 - 22) / 2
    const imgY = 15 + 3
    doc.addImage(logoIrd, 'PNG', imgX, imgY, 22, 10)
  }

  y = doc.lastAutoTable.finalY + 8

  // ── CHAMPS EN-TÊTE ──
  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')

  const fieldLine = (label, valStr) => {
    doc.setFont('helvetica', 'normal'); doc.setTextColor(0, 0, 0)
    doc.text(label, mL, y)
    if (valStr) {
      doc.setFont('helvetica', 'bold')
      doc.setTextColor(0, 0, 0)
      doc.text(valStr, mL + doc.getTextWidth(label) + 1, y)
      doc.setTextColor(0, 0, 0)
    }
    y += 7
  }

  fieldLine('NOM ET PRENOM(S) DU DEMANDEUR : ', form.value.demandeur_nom_prenom)
  fieldLine('SERVICE ou STRUCTURE : ', form.value.service_structure)
  fieldLine('EOTP ou CENTRE DE COÛT : ', form.value.eotp_centre_cout)
  y += 3

  // ── TABLEAU ARTICLES ──
  const MIN_ROWS = 8
  const rows = [...articles.value]
  while (rows.length < MIN_ROWS) rows.push({ objet: '', fournisseur: '', prix_unitaire: '', quantite: '' })

  const tableBody = rows.map(a => {
    const total = prixTotal(a)
    return [
      { content: a.objet || '', styles: { fontStyle: 'bold', textColor: a.objet ? [0, 0, 0] : [0,0,0] } },
      { content: a.fournisseur || '', styles: { fontStyle: 'bold', textColor: a.fournisseur ? [0, 0, 0] : [0,0,0] } },
      { content: a.prix_unitaire ? fmt(a.prix_unitaire) : '', styles: { fontStyle: 'bold', textColor: a.prix_unitaire ? [0, 0, 0] : [0,0,0] } },
      { content: a.quantite || '', styles: { fontStyle: 'bold', textColor: a.quantite ? [0, 0, 0] : [0,0,0] } },
      { content: total != null ? fmt(total) : '', styles: { fontStyle: 'bold', textColor: total != null ? [0, 0, 0] : [0,0,0] } },
    ]
  })

  // Ligne total
  tableBody.push([
    { content: 'TOTAL', colSpan: 4, styles: { halign: 'right', fontStyle: 'bold', fillColor: [240, 240, 240] } },
    { content: totalGeneral.value > 0 ? fmt(totalGeneral.value) + ' F CFA' : '', styles: { fontStyle: 'bold', fillColor: [240, 240, 240] } },
  ])

  autoTable(doc, {
    startY: y,
    head: [['Objet de la commande', 'Fournisseur', 'Prix unitaire', 'Quantité', 'Prix Total']],
    body: tableBody,
    theme: 'grid',
    headStyles: { fillColor: [220, 220, 220], textColor: 0, fontStyle: 'bold', fontSize: 9, halign: 'center' },
    bodyStyles: { fontSize: 9, minCellHeight: 10 },
    columnStyles: {
      0: { cellWidth: 50 },
      1: { cellWidth: 40 },
      2: { cellWidth: 30, halign: 'right' },
      3: { cellWidth: 20, halign: 'center' },
      4: { cellWidth: 30, halign: 'right' },
    },
    margin: { left: mL, right: 20 },
  })

  y = doc.lastAutoTable.finalY + 8

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(10)
  doc.setTextColor(0, 0, 0)
  doc.text('Adresse de Livraison : ', mL, y)
  if (form.value.adresse_livraison) {
    doc.setFont('helvetica', 'bold')
    doc.setTextColor(0, 0, 0)
    doc.text(form.value.adresse_livraison, mL + doc.getTextWidth('Adresse de Livraison : '), y)
    doc.setTextColor(0, 0, 0)
  }
  y += 12

  // ── NOTE ATTENTION ──
  doc.setFont('helvetica', 'bold')
  doc.setFontSize(9)
  doc.text(
    "Attention\u00a0: Toute demande de bon d\u2019achat doit \u00eatre accompagn\u00e9e de facture pro forma",
    pageW / 2, y, { align: 'center' }
  )
  y += 14

  // ── DATE ──
  doc.setFont('helvetica', 'normal')
  doc.setFontSize(10)
  doc.text(`Dakar le\u00a0: `, mL, y)
  if (form.value.date_dakar) {
    doc.setFont('helvetica', 'bold')
    doc.setTextColor(0, 0, 0)
    doc.text(form.value.date_dakar, mL + doc.getTextWidth(`Dakar le\u00a0: `), y)
    doc.setTextColor(0, 0, 0)
  }
  y += 18

  // ── SIGNATURES ──
  const colLeft = mL + 25
  const colRight = 125
  doc.setFont('helvetica', 'bold')
  doc.setFontSize(10)
  doc.text('SIGNATURE DU DEMANDEUR', colLeft, y, { align: 'center' })
  doc.text("SIGNATURE DU\nRESPONSABLE D\u2019ENVELOPPE", colRight + 25, y, { align: 'center' })
  y += 25
  doc.setLineWidth(0.3)
  doc.line(colLeft - 25, y, colLeft + 25, y)
  doc.line(colRight, y, colRight + 55, y)

  const pdfBlob = doc.output('blob')
  pdfBlobFile.value = pdfBlob
  pdfBlobUrl.value = URL.createObjectURL(pdfBlob)
  pdfGeneratedMode.value = true
  generating.value = false
}

async function enregistrer(background = false) {
  if (!pdfBlobFile.value) return
  if (!background) saving.value = true

  const formData = new FormData()
  formData.append('type_document', 'bon_achat')
  formData.append('donnees', JSON.stringify({ 
    ...form.value, 
    articles: articles.value, 
    total: totalGeneral.value 
  }))
  formData.append('pdf_file', pdfBlobFile.value, 'bon_achat.pdf')

  try {
    await window.axios.post('/admin/documents/store', formData)
    if (!background) {
      saving.value = false
      router.visit('/admin/documents')
    }
  } catch (error) {
    if (!background) saving.value = false
    console.error("Erreur lors de l'enregistrement:", error)
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

function imprimer() {
  window.print()
}
function retour() {
  pdfGeneratedMode.value = false
  if (pdfBlobUrl.value) {
    URL.revokeObjectURL(pdfBlobUrl.value)
    pdfBlobUrl.value = null
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
          <ShoppingCartIcon class="w-7 h-7 text-amber-400" />
          Demande de Bon d'Achat
        </h1>
        <p class="text-[var(--text-subtle)] text-sm mt-0.5">
          Le PDF généré sera <span class="text-amber-400 font-semibold">identique au document officiel IRD</span>.
          Accompagner de facture pro forma.
        </p>
      </div>
    </div>

    <!-- MODE: FORMULAIRE -->
    <div v-show="!pdfGeneratedMode" class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- ─── FORMULAIRE ─── -->
      <div class="space-y-5">

        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Informations du Demandeur</h2>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">NOM ET PRENOM(S) DU DEMANDEUR *</label>
            <input v-model="form.demandeur_nom_prenom" type="text" placeholder="Nom et prénom(s) complets"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">SERVICE ou STRUCTURE</label>
            <input v-model="form.service_structure" type="text" placeholder="Service ou structure"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">EOTP ou CENTRE DE COÛT</label>
            <input v-model="form.eotp_centre_cout" type="text" placeholder="Code EOTP ou centre de coût"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
        </div>

        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Articles commandés</h2>
            <button @click="ajouterLigne"
              class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-amber-600/20 hover:bg-amber-600/30 text-amber-400 rounded-lg border border-amber-500/20 transition-all">
              <PlusIcon class="w-3.5 h-3.5" />Ajouter
            </button>
          </div>
          <div class="space-y-3">
            <div v-for="(art, idx) in articles" :key="idx" class="p-3 bg-[var(--surface-alt)]/40 rounded-lg border border-[var(--border)] space-y-2">
              <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-bold text-[var(--text-subtle)] uppercase">Ligne {{ idx + 1 }}</span>
                <button v-if="articles.length > 1" @click="supprimerLigne(idx)" class="p-1 rounded text-[var(--text-subtle)] hover:text-red-400 transition-all">
                  <TrashIcon class="w-3.5 h-3.5" />
                </button>
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="block text-[10px] text-[var(--text-subtle)] mb-1">Objet de la commande</label>
                  <input v-model="art.objet" type="text" placeholder="Description"
                    class="w-full bg-slate-700/60 border border-[var(--border)] rounded px-3 py-2 text-xs text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50" />
                </div>
                <div>
                  <label class="block text-[10px] text-[var(--text-subtle)] mb-1">Fournisseur</label>
                  <input v-model="art.fournisseur" type="text" placeholder="Fournisseur"
                    class="w-full bg-slate-700/60 border border-[var(--border)] rounded px-3 py-2 text-xs text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50" />
                </div>
              </div>
              <div class="grid grid-cols-3 gap-2">
                <div>
                  <label class="block text-[10px] text-[var(--text-subtle)] mb-1">Prix unitaire (F CFA)</label>
                  <input :value="art.prix_unitaire" @input="art.prix_unitaire = $event.target.value.replace(/[^0-9]/g, '')" type="text" inputmode="numeric" placeholder="0"
                    class="w-full bg-slate-700/60 border border-[var(--border)] rounded px-3 py-2 text-xs text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50" />
                </div>
                <div>
                  <label class="block text-[10px] text-[var(--text-subtle)] mb-1">Quantité</label>
                  <input :value="art.quantite" @input="art.quantite = $event.target.value.replace(/[^0-9]/g, '')" type="text" inputmode="numeric" placeholder="0"
                    class="w-full bg-slate-700/60 border border-[var(--border)] rounded px-3 py-2 text-xs text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50" />
                </div>
                <div>
                  <label class="block text-[10px] text-[var(--text-subtle)] mb-1">Prix Total</label>
                  <div class="bg-slate-600/40 border border-amber-500/20 rounded px-3 py-2 text-xs font-bold text-amber-400 min-h-[32px]">
                    {{ prixTotal(art) != null ? fmt(prixTotal(art)) + ' F CFA' : '—' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="flex justify-between items-center p-3 bg-amber-500/10 rounded-lg border border-amber-500/20">
            <span class="text-xs font-bold text-[var(--text-muted)] uppercase">Total général</span>
            <span class="text-base font-extrabold text-amber-400">{{ totalGeneral > 0 ? fmt(totalGeneral) + ' F CFA' : '—' }}</span>
          </div>
        </div>

        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Livraison & Signature</h2>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Adresse de Livraison</label>
            <input v-model="form.adresse_livraison" type="text" placeholder="Adresse complète"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Dakar le</label>
            <input v-model="form.date_dakar" type="text" placeholder="Ex: 11 juin 2026"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="genererPDF" :disabled="generating"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-amber-600 hover:bg-amber-500 disabled:opacity-50 text-[var(--text)] rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5">
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

      <!-- ─── APERÇU FIDÈLE ─── -->
      <div class="xl:sticky xl:top-6 xl:self-start">
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 mb-3 flex items-center justify-between">
          <span class="text-xs font-semibold text-[var(--text-subtle)] uppercase tracking-wider">Aperçu — fidèle au document original</span>
        </div>
        <div class="overflow-auto max-h-[80vh] rounded-xl border border-[var(--border)] bg-white shadow-2xl" id="print-zone">
          <!-- Document Bon d'Achat original -->
          <div style="width:794px; padding:36px 48px; font-family:Arial,Helvetica,sans-serif; font-size:11px; color:#000; background:#fff;">

            <!-- En-tête 3 colonnes -->
            <table style="width:100%; border-collapse:collapse; border:1.5px solid #000; margin-bottom:14px;">
              <tr>
                <td style="border:1px solid #000; padding:8px; width:30%; text-align:center; vertical-align:top; font-size:9px; line-height:1.5;">
                  <img v-if="logoIrd" :src="logoIrd" alt="Logo IRD" style="height:35px; margin:0 auto 10px; display:block;" />
                  <strong>Représentation<br>du Sénégal</strong><br>
                  Tél : 00221 33 849 83 30<br>
                  BP 1386 - Dakar
                </td>
                <td style="border:1px solid #000; padding:8px; text-align:center; vertical-align:middle; font-size:14px; font-weight:bold; width:40%;">
                  DEMANDE DE BON D'ACHAT
                </td>
                <td style="border:1px solid #000; padding:8px; text-align:right; vertical-align:middle; font-size:8px; line-height:1.6; width:30%;">
                  Identification : FI - 4<br>
                  Date de création : 10/07/08<br>
                  Date de Modification : 12/11/08<br>
                  Version : 2<br>
                  Nombre de pages : 1/1
                </td>
              </tr>
            </table>

            <!-- Champs demandeur -->
            <table style="width:100%; border-collapse:collapse; margin-bottom:6px;">
              <tr>
                <td style="padding:4px 0; width:100%;">
                  NOM ET PRENOM(S) DU DEMANDEUR&nbsp;:
                  <span style="font-weight:bold; border-bottom:1px solid #999; display:inline-block; min-width:200px; padding:0 4px;">{{ form.demandeur_nom_prenom || '' }}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:4px 0;">
                  SERVICE ou STRUCTURE&nbsp;:
                  <span style="font-weight:bold; border-bottom:1px solid #999; display:inline-block; min-width:260px; padding:0 4px;">{{ form.service_structure || '' }}</span>
                </td>
              </tr>
              <tr>
                <td style="padding:4px 0;">
                  EOTP ou CENTRE DE CO&Ucirc;T&nbsp;:
                  <span style="font-weight:bold; border-bottom:1px solid #999; display:inline-block; min-width:250px; padding:0 4px;">{{ form.eotp_centre_cout || '' }}</span>
                </td>
              </tr>
            </table>

            <!-- Tableau articles -->
            <table style="width:100%; border-collapse:collapse; border:1.5px solid #333; margin-bottom:10px; margin-top:8px;">
              <thead>
                <tr style="background:#e8e8e8;">
                  <th style="border:1px solid #333; padding:6px 8px; text-align:left; font-size:10px;">Objet de la commande</th>
                  <th style="border:1px solid #333; padding:6px 8px; text-align:left; font-size:10px;">Fournisseur&nbsp;</th>
                  <th style="border:1px solid #333; padding:6px 8px; text-align:right; font-size:10px; white-space:nowrap;">Prix unitaire</th>
                  <th style="border:1px solid #333; padding:6px 8px; text-align:center; font-size:10px;">Quantité</th>
                  <th style="border:1px solid #333; padding:6px 8px; text-align:right; font-size:10px; white-space:nowrap;">Prix Total</th>
                </tr>
              </thead>
              <tbody>
                <template v-for="(art, idx) in articles" :key="idx">
                  <tr>
                    <td style="border:1px solid #999; padding:6px 8px; min-height:22px; white-space:pre-wrap; word-break:break-word;">{{ art.objet }}</td>
                    <td style="border:1px solid #999; padding:6px 8px; white-space:pre-wrap; word-break:break-word;">{{ art.fournisseur }}</td>
                    <td style="border:1px solid #999; padding:6px 8px; text-align:right;">{{ art.prix_unitaire ? fmt(art.prix_unitaire) : '' }}</td>
                    <td style="border:1px solid #999; padding:6px 8px; text-align:center;">{{ art.quantite }}</td>
                    <td style="border:1px solid #999; padding:6px 8px; text-align:right; font-weight:bold;">{{ prixTotal(art) != null ? fmt(prixTotal(art)) : '' }}</td>
                  </tr>
                </template>
                <!-- Lignes vides jusqu'à min 8 -->
                <tr v-for="n in Math.max(0, 8 - articles.length)" :key="'e'+n">
                  <td style="border:1px solid #ccc; padding:0; height:22px;"></td>
                  <td style="border:1px solid #ccc; padding:0;"></td>
                  <td style="border:1px solid #ccc; padding:0;"></td>
                  <td style="border:1px solid #ccc; padding:0;"></td>
                  <td style="border:1px solid #ccc; padding:0;"></td>
                </tr>
              </tbody>
            </table>

            <!-- Adresse livraison -->
            <p style="margin:8px 0 4px;">
              Adresse de Livraison&nbsp;:
              <span style="font-weight:bold; border-bottom:1px solid #999; display:inline-block; min-width:300px; padding:0 4px;">{{ form.adresse_livraison || '' }}</span>
            </p>

            <!-- Note pro forma -->
            <div style="border:1.5px solid #333; padding:6px 12px; margin:12px 0; text-align:center; font-size:10px;">
              <strong>Attention&nbsp;:</strong> Toute demande de bon d'achat doit être accompagnée de facture pro forma
            </div>

            <!-- Date + Signatures -->
            <div style="margin-top:10px; display:flex; justify-content:flex-start;">
              <span>Dakar le&nbsp;: <strong>{{ form.date_dakar }}</strong></span>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:28px; padding:0 10px;">
              <div style="text-align:center; width:42%;">
                <div style="font-weight:bold; font-size:10px; margin-bottom:4px;">SIGNATURE DU DEMANDEUR</div>
                <div style="border-bottom:1px solid #333; height:50px; margin-top:6px;"></div>
              </div>
              <div style="text-align:center; width:42%;">
                <div style="font-weight:bold; font-size:10px; margin-bottom:4px;">SIGNATURE DU<br>RESPONSABLE D'ENVELOPPE</div>
                <div style="border-bottom:1px solid #333; height:50px; margin-top:6px;"></div>
              </div>
            </div>

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
        </div>
      </div>
      <div class="flex-1 w-full bg-slate-900/50 overflow-hidden">
        <iframe ref="iframeRef" :src="pdfBlobUrl" class="w-full h-full border-none"></iframe>
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
