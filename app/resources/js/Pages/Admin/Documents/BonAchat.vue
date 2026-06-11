<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import {
  ShoppingCartIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
  ArrowLeftIcon,
  PlusIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const form = ref({
  demandeur_nom_prenom: '',
  service_structure: '',
  eotp_centre_cout: '',
  adresse_livraison: '',
  date_dakar: new Date().toLocaleDateString('fr-FR'),
})

// Tableau d'articles dynamique
const articles = ref([
  { objet: '', fournisseur: '', prix_unitaire: '', quantite: '', prix_total: '' },
])

const ajouterLigne = () => {
  articles.value.push({ objet: '', fournisseur: '', prix_unitaire: '', quantite: '', prix_total: '' })
}

const supprimerLigne = (idx) => {
  if (articles.value.length > 1) {
    articles.value.splice(idx, 1)
  }
}

// Calcul automatique du prix total par ligne
const calculerTotal = (article) => {
  const pu = parseFloat(article.prix_unitaire) || 0
  const qte = parseFloat(article.quantite) || 0
  const total = pu * qte
  article.prix_total = total > 0 ? total.toFixed(0) : ''
}

// Total général
const totalGeneral = computed(() => {
  return articles.value.reduce((sum, a) => {
    return sum + (parseFloat(a.prix_total) || 0)
  }, 0)
})

const formatMontant = (v) => {
  if (!v && v !== 0) return ''
  return new Intl.NumberFormat('fr-FR').format(v) + ' F CFA'
}

const generating = ref(false)

async function genererPDF() {
  generating.value = true
  await new Promise(r => setTimeout(r, 300))

  const { default: jsPDF } = await import('jspdf')
  const { default: html2canvas } = await import('html2canvas')

  const el = document.getElementById('bonachat-preview')
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
  const imgH = (canvas.height * pdfW) / canvas.width

  pdf.addImage(imgData, 'JPEG', 0, 0, pdfW, imgH)
  pdf.save(`Bon_Achat_${form.value.demandeur_nom_prenom || 'demandeur'}.pdf`)
  generating.value = false
}

function imprimer() {
  window.print()
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
          <ShoppingCartIcon class="w-7 h-7 text-amber-400" />
          Demande de Bon d'Achat
        </h1>
        <p class="text-slate-400 text-sm mt-0.5">
          Toute demande de bon d'achat doit être accompagnée de <span class="text-amber-400 font-semibold">facture pro forma</span>
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- ─── FORMULAIRE ─── -->
      <div class="space-y-5">

        <!-- Demandeur -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Informations du Demandeur</h2>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">NOM ET PRENOM(S) DU DEMANDEUR *</label>
            <input v-model="form.demandeur_nom_prenom" type="text" placeholder="Nom complet du demandeur"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">SERVICE ou STRUCTURE</label>
            <input v-model="form.service_structure" type="text" placeholder="Service ou structure d'appartenance"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">EOTP ou CENTRE DE COÛT</label>
            <input v-model="form.eotp_centre_cout" type="text" placeholder="Code EOTP ou centre de coût"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
        </div>

        <!-- Articles -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Articles commandés</h2>
            <button @click="ajouterLigne"
              class="flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 bg-amber-600/20 hover:bg-amber-600/30 text-amber-400 rounded-lg border border-amber-500/20 transition-all">
              <PlusIcon class="w-3.5 h-3.5" />
              Ajouter une ligne
            </button>
          </div>

          <div class="space-y-3">
            <div v-for="(art, idx) in articles" :key="idx"
              class="p-3 bg-slate-800/40 rounded-lg border border-white/5 space-y-2">
              <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-bold text-slate-500 uppercase">Article {{ idx + 1 }}</span>
                <button v-if="articles.length > 1" @click="supprimerLigne(idx)"
                  class="p-1 rounded text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
                  <TrashIcon class="w-3.5 h-3.5" />
                </button>
              </div>
              <div>
                <label class="block text-[10px] font-medium text-slate-500 mb-1">Objet de la commande</label>
                <input v-model="art.objet" type="text" placeholder="Description de l'article"
                  class="w-full bg-slate-700/60 border border-white/8 rounded px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50" />
              </div>
              <div>
                <label class="block text-[10px] font-medium text-slate-500 mb-1">Fournisseur</label>
                <input v-model="art.fournisseur" type="text" placeholder="Nom du fournisseur"
                  class="w-full bg-slate-700/60 border border-white/8 rounded px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50" />
              </div>
              <div class="grid grid-cols-3 gap-2">
                <div>
                  <label class="block text-[10px] font-medium text-slate-500 mb-1">Prix unitaire (F CFA)</label>
                  <input v-model="art.prix_unitaire" type="number" placeholder="0" @input="calculerTotal(art)"
                    class="w-full bg-slate-700/60 border border-white/8 rounded px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50" />
                </div>
                <div>
                  <label class="block text-[10px] font-medium text-slate-500 mb-1">Quantité</label>
                  <input v-model="art.quantite" type="number" placeholder="0" @input="calculerTotal(art)"
                    class="w-full bg-slate-700/60 border border-white/8 rounded px-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-amber-500/50" />
                </div>
                <div>
                  <label class="block text-[10px] font-medium text-slate-500 mb-1">Prix Total</label>
                  <div class="bg-slate-600/40 border border-amber-500/20 rounded px-3 py-2 text-xs font-bold text-amber-400">
                    {{ art.prix_total ? formatMontant(art.prix_total) : '—' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Total général -->
          <div class="flex justify-between items-center p-3 bg-amber-500/10 rounded-lg border border-amber-500/20">
            <span class="text-xs font-bold text-slate-300 uppercase">Total général</span>
            <span class="text-base font-extrabold text-amber-400">{{ formatMontant(totalGeneral) }}</span>
          </div>
        </div>

        <!-- Livraison & Date -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-amber-400">Livraison & Signature</h2>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Adresse de Livraison</label>
            <input v-model="form.adresse_livraison" type="text" placeholder="Adresse complète de livraison"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Dakar le</label>
            <input v-model="form.date_dakar" type="text" placeholder="Ex: 11 juin 2026"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/50" />
          </div>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3">
          <button @click="genererPDF" :disabled="generating"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-amber-600 hover:bg-amber-500 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-amber-600/20">
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

      <!-- ─── APERÇU ─── -->
      <div class="xl:sticky xl:top-6 xl:self-start">
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4 mb-3 flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Aperçu du document</span>
          <span class="text-xs text-slate-500">Format A4</span>
        </div>
        <div class="overflow-auto max-h-[80vh] rounded-xl border border-white/5 bg-white shadow-2xl">
          <div id="bonachat-preview" class="bg-white text-black" style="width:794px; padding:40px; font-size:11px; font-family:Arial,sans-serif; line-height:1.6;">

            <!-- En-tête -->
            <div style="text-align:center; margin-bottom:20px; border-bottom:3px solid #000; padding-bottom:10px;">
              <h1 style="font-size:18px; font-weight:bold; text-transform:uppercase; letter-spacing:2px; margin:0;">DEMANDE D'ACHAT</h1>
              <p style="font-size:10px; margin:4px 0 0; color:#555;">Représentation de l'IRD au Sénégal — Tél : 00221 33 849 35 35 — BP 1386 - Dakar</p>
            </div>

            <!-- Demandeur -->
            <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
              <tr>
                <td style="padding:6px 0; width:60%;">NOM ET PRENOM(S) DU DEMANDEUR : <strong>{{ form.demandeur_nom_prenom || '......................................' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:6px 0;">SERVICE ou STRUCTURE : <strong>{{ form.service_structure || '......................................' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:6px 0;">EOTP ou CENTRE DE COÛT : <strong>{{ form.eotp_centre_cout || '......................................' }}</strong></td>
              </tr>
            </table>

            <!-- Tableau articles -->
            <table style="width:100%; border-collapse:collapse; margin-bottom:16px; border:1px solid #999;">
              <thead>
                <tr style="background:#f0f0f0;">
                  <th style="padding:8px; border:1px solid #999; text-align:left; font-size:10px;">Objet de la commande</th>
                  <th style="padding:8px; border:1px solid #999; text-align:left; font-size:10px;">Fournisseur</th>
                  <th style="padding:8px; border:1px solid #999; text-align:right; font-size:10px; white-space:nowrap;">Prix unitaire</th>
                  <th style="padding:8px; border:1px solid #999; text-align:center; font-size:10px;">Quantité</th>
                  <th style="padding:8px; border:1px solid #999; text-align:right; font-size:10px; white-space:nowrap;">Prix Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(art, idx) in articles" :key="idx">
                  <td style="padding:7px 8px; border:1px solid #ccc;">{{ art.objet || '' }}</td>
                  <td style="padding:7px 8px; border:1px solid #ccc;">{{ art.fournisseur || '' }}</td>
                  <td style="padding:7px 8px; border:1px solid #ccc; text-align:right;">{{ art.prix_unitaire ? new Intl.NumberFormat('fr-FR').format(art.prix_unitaire) : '' }}</td>
                  <td style="padding:7px 8px; border:1px solid #ccc; text-align:center;">{{ art.quantite || '' }}</td>
                  <td style="padding:7px 8px; border:1px solid #ccc; text-align:right; font-weight:bold;">{{ art.prix_total ? new Intl.NumberFormat('fr-FR').format(art.prix_total) : '' }}</td>
                </tr>
                <!-- Ligne vide si peu d'articles -->
                <tr v-for="n in Math.max(0, 5 - articles.length)" :key="'empty-' + n">
                  <td style="padding:7px 8px; border:1px solid #ccc; height:28px;"></td>
                  <td style="padding:7px 8px; border:1px solid #ccc;"></td>
                  <td style="padding:7px 8px; border:1px solid #ccc;"></td>
                  <td style="padding:7px 8px; border:1px solid #ccc;"></td>
                  <td style="padding:7px 8px; border:1px solid #ccc;"></td>
                </tr>
                <!-- Total -->
                <tr style="background:#f9f9f9;">
                  <td colspan="4" style="padding:8px; border:1px solid #999; text-align:right; font-weight:bold;">TOTAL GÉNÉRAL</td>
                  <td style="padding:8px; border:1px solid #999; text-align:right; font-weight:bold; font-size:12px;">
                    {{ totalGeneral > 0 ? new Intl.NumberFormat('fr-FR').format(totalGeneral) + ' F CFA' : '' }}
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Livraison -->
            <div style="margin-bottom:16px;">
              <p>Adresse de Livraison : <strong>{{ form.adresse_livraison || '.................................................................' }}</strong></p>
            </div>

            <!-- Note pro forma -->
            <div style="background:#fff8e1; border:1px solid #f0c040; padding:8px; margin-bottom:20px; font-size:10px;">
              <strong>Attention :</strong> Toute demande de bon d'achat doit être accompagnée de facture pro forma
            </div>

            <!-- Signatures -->
            <div style="display:flex; justify-content:space-between; margin-top:10px;">
              <div style="text-align:left;">
                <p>Dakar le : <strong>{{ form.date_dakar }}</strong></p>
              </div>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:30px;">
              <div style="text-align:center; width:40%;">
                <p style="font-weight:bold; border-bottom:1px solid #000; padding-bottom:4px; margin-bottom:6px;">SIGNATURE DU DEMANDEUR</p>
                <div style="height:60px;"></div>
              </div>
              <div style="text-align:center; width:40%;">
                <p style="font-weight:bold; border-bottom:1px solid #000; padding-bottom:4px; margin-bottom:6px;">SIGNATURE DU RESPONSABLE D'ENVELOPPE</p>
                <div style="height:60px;"></div>
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
  #bonachat-preview, #bonachat-preview * { visibility: visible; }
  #bonachat-preview {
    position: fixed;
    top: 0; left: 0;
    width: 100%;
    background: white !important;
    color: black !important;
    font-family: Arial, sans-serif;
    font-size: 11pt;
  }
}
</style>
