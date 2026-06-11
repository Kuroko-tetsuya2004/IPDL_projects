<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import {
  ClipboardDocumentListIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
  ArrowLeftIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: DashboardLayout })

const form = ref({
  // Prestataire
  nom: '',
  prenoms: '',
  ne_le: '',
  a: '',
  adresse: '',
  tel: '',
  emploi_fonction: '',

  // Prestation
  objet: '',
  produits_attendus: '',
  duree: '',
  date_debut: '',
  date_fin: '',
  responsable_suivi: '',

  // Montants
  montant_brut: '',
  montant_net_lettres: '',

  // Administration
  service: '',
  imputation: '',

  // Acquit
  date_acquit: new Date().toLocaleDateString('fr-FR'),
})

// Calculs automatiques
const montantBrut = computed(() => {
  const v = parseFloat(form.value.montant_brut) || 0
  return v
})
const impot = computed(() => Math.round(montantBrut.value * 0.05))
const montantNet = computed(() => montantBrut.value - impot.value)

const formatMontant = (v) => v ? new Intl.NumberFormat('fr-FR').format(v) + ' F CFA' : '.....................'

const generating = ref(false)

async function genererPDF() {
  generating.value = true
  await new Promise(r => setTimeout(r, 300))

  const { default: jsPDF } = await import('jspdf')
  const { default: html2canvas } = await import('html2canvas')

  const el = document.getElementById('prestation-preview')
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

  if (imgH <= pdfH) {
    pdf.addImage(imgData, 'JPEG', 0, 0, pdfW, imgH)
  } else {
    pdf.addImage(imgData, 'JPEG', 0, 0, pdfW, imgH)
  }

  pdf.save(`Recu_Prestation_Service_${form.value.nom || 'prestataire'}.pdf`)
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
          <ClipboardDocumentListIcon class="w-7 h-7 text-emerald-400" />
          Reçu de Prestation de Service
        </h1>
        <p class="text-slate-400 text-sm mt-0.5">Identification : FI - 8 · Version V5 · IRD Représentation Sénégal</p>
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
              <input v-model="form.nom" type="text" placeholder="NOM (en majuscules)"
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
              <input v-model="form.a" type="text" placeholder="Ville de naissance"
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
              <input v-model="form.tel" type="text" placeholder="Numéro de téléphone"
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
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Durée <span class="text-slate-500">(maximum 9 jours consécutifs)</span></label>
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
            💡 Saisissez le <strong>montant brut</strong>. L'impôt (5%) et le montant net (95%) sont calculés automatiquement.
            <br>Montant maximum autorisé : 100 000 F CFA/prestation.
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Montant brut de la prestation (F CFA) *</label>
            <input v-model="form.montant_brut" type="number" placeholder="Ex: 50000"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <!-- Calculs automatiques -->
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-800/40 rounded-lg p-3 border border-white/5">
              <div class="text-[10px] text-slate-500 uppercase font-bold mb-1">Impôt sur le revenu (5%)</div>
              <div class="text-sm font-bold text-red-400">{{ formatMontant(impot) }}</div>
            </div>
            <div class="bg-slate-800/40 rounded-lg p-3 border border-white/5">
              <div class="text-[10px] text-slate-500 uppercase font-bold mb-1">Montant net (95% du brut)</div>
              <div class="text-sm font-bold text-emerald-400">{{ formatMontant(montantNet) }}</div>
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Montant net à percevoir (en lettres) *</label>
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
            <label class="block text-xs font-medium text-slate-400 mb-1.5">IMPUTATION — code EOTP ou centre (budget de fonctionnement)</label>
            <input v-model="form.imputation" type="text" placeholder="Code EOTP ou centre de coût"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Date (pour acquit)</label>
            <input v-model="form.date_acquit" type="text" placeholder="JJ/MM/AAAA"
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/50" />
          </div>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3">
          <button @click="genererPDF" :disabled="generating"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5 hover:shadow-lg hover:shadow-emerald-600/20">
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
          <div id="prestation-preview" class="bg-white text-black" style="width:794px; padding:40px; font-size:11px; font-family:Arial,sans-serif; line-height:1.6;">

            <!-- En-tête IRD + Titre -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; border-bottom:2px solid #000; padding-bottom:12px;">
              <div style="font-size:10px; line-height:1.5;">
                <strong>Représentation de l'IRD<br>au Sénégal</strong><br>
                Tél : 00221 33 849 35 35<br>
                BP 1386 - Dakar
              </div>
              <div style="text-align:center; flex:1;">
                <h1 style="font-size:16px; font-weight:bold; text-transform:uppercase; margin:0;">RECU DE PRESTATION DE SERVICE</h1>
              </div>
              <div style="font-size:9px; text-align:right; line-height:1.5; color:#555;">
                Identification : FI - 8<br>
                Date de création : 10/07/08<br>
                Date de Modification : 24/08/2011<br>
                Version : V5
              </div>
            </div>

            <!-- Informations prestataire -->
            <table style="width:100%; border-collapse:collapse; margin-bottom:12px;">
              <tr>
                <td style="padding:4px; width:50%;">NOM : <strong>{{ form.nom || '..............................' }}</strong></td>
                <td style="padding:4px;">Prénoms : <strong>{{ form.prenoms || '..............................' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:4px;">né le : <strong>{{ form.ne_le || '................' }}</strong></td>
                <td style="padding:4px;">à : <strong>{{ form.a || '................' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:4px;" colspan="2">Adresse : <strong>{{ form.adresse || '.................................................................' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:4px;">Tél : <strong>{{ form.tel || '................' }}</strong></td>
                <td style="padding:4px;"></td>
              </tr>
              <tr>
                <td style="padding:4px;" colspan="2">Emploi/Fonction : <strong>{{ form.emploi_fonction || '.................................................................' }}</strong></td>
              </tr>
            </table>

            <!-- Prestation -->
            <table style="width:100%; border:1px solid #ccc; border-collapse:collapse; margin-bottom:12px;">
              <tr style="background:#f5f5f5;">
                <td colspan="2" style="padding:6px; font-weight:bold; border-bottom:1px solid #ccc;">Détails de la prestation</td>
              </tr>
              <tr>
                <td style="padding:6px; border-bottom:1px solid #eee;" colspan="2">Objet de la prestation : <strong>{{ form.objet || '.................................................................' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:6px; border-bottom:1px solid #eee;" colspan="2">Produits attendus : <strong>{{ form.produits_attendus || '.................................................................' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:6px; border-bottom:1px solid #eee;">Durée (maximum 9 jours consécutifs) : <strong>{{ form.duree || '.........' }}</strong></td>
                <td style="padding:6px; border-bottom:1px solid #eee;">du : <strong>{{ form.date_debut || '.........' }}</strong> &nbsp; au : <strong>{{ form.date_fin || '.........' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:6px;" colspan="2">Nom du responsable du suivi : <strong>{{ form.responsable_suivi || '.................................................................' }}</strong></td>
              </tr>
            </table>

            <!-- Montants -->
            <table style="width:100%; border:1px solid #ccc; border-collapse:collapse; margin-bottom:12px;">
              <tr style="background:#f5f5f5;">
                <td colspan="3" style="padding:6px; font-weight:bold; border-bottom:1px solid #ccc;">Montants</td>
              </tr>
              <tr>
                <td style="padding:6px; border-bottom:1px solid #eee;" colspan="2">
                  Montant net à percevoir soit 95% du montant brut (en chiffres) (**) :
                  <strong>{{ formatMontant(montantNet) }}</strong>
                </td>
                <td style="padding:6px; border-bottom:1px solid #eee; text-align:right;"></td>
              </tr>
              <tr>
                <td style="padding:6px; border-bottom:1px solid #eee;" colspan="3">
                  Montant net à percevoir (en lettres) (**) : <strong>{{ form.montant_net_lettres || '.................................................................' }}</strong>
                </td>
              </tr>
              <tr style="background:#fff8e1;">
                <td style="padding:6px; border-bottom:1px solid #eee;" colspan="2">
                  <strong>À remplir par l'administration</strong>
                </td>
                <td></td>
              </tr>
              <tr>
                <td style="padding:6px; border-bottom:1px solid #eee;">Impôt sur le revenu : 5% du montant brut (*) :</td>
                <td style="padding:6px; border-bottom:1px solid #eee; text-align:center;"><strong>{{ formatMontant(impot) }}</strong></td>
                <td style="padding:6px; border-bottom:1px solid #eee;">F CFA</td>
              </tr>
              <tr>
                <td style="padding:6px; border-bottom:1px solid #eee;">Montant brut de la prestation :</td>
                <td style="padding:6px; border-bottom:1px solid #eee; text-align:center;"><strong>{{ formatMontant(montantBrut) }}</strong></td>
                <td style="padding:6px; border-bottom:1px solid #eee;">F CFA</td>
              </tr>
              <tr>
                <td style="padding:6px; border-bottom:1px solid #eee;" colspan="3">SERVICE/UR/US : <strong>{{ form.service || '.................................................................' }}</strong></td>
              </tr>
              <tr>
                <td style="padding:6px;" colspan="3">IMPUTATION code EOTP ou centre (budget de fonctionnement) : <strong>{{ form.imputation || '................................' }}</strong></td>
              </tr>
            </table>

            <!-- Signatures -->
            <div style="display:flex; justify-content:space-between; margin-top:16px; margin-bottom:20px;">
              <div style="text-align:center; width:45%;">
                <p style="font-weight:bold; border-bottom:1px solid #000; margin-bottom:6px; padding-bottom:4px;">Certifié le service fait</p>
                <p style="font-size:10px;">Le Responsable du suivi</p>
                <div style="height:50px;"></div>
              </div>
              <div style="text-align:center; width:45%;">
                <p style="font-weight:bold; border-bottom:1px solid #000; margin-bottom:6px; padding-bottom:4px;">Certifié le service fait</p>
                <p style="font-size:10px;">L'Ordonnateur</p>
                <div style="height:50px;"></div>
              </div>
            </div>

            <!-- Acquit -->
            <div style="border:1px solid #ccc; padding:8px; margin-bottom:12px; display:flex; justify-content:space-between;">
              <span><strong>POUR ACQUIT :</strong></span>
              <span>Date : <strong>{{ form.date_acquit }}</strong></span>
            </div>

            <!-- Notes de bas -->
            <div style="font-size:9px; color:#555; line-height:1.6; border-top:1px solid #ddd; padding-top:8px;">
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
  #prestation-preview, #prestation-preview * { visibility: visible; }
  #prestation-preview {
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
