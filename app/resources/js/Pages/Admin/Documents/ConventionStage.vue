<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { DocumentTextIcon, PrinterIcon, ArrowDownTrayIcon, ArrowLeftIcon, PlusIcon, TrashIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { logoIrd, logoUcad, logoReuUmmisco } from '@/utils/logos.js'

defineOptions({ layout: DashboardLayout })

const form = ref({
  etablissement_nom: '',
  etablissement_statut: '',
  etablissement_siege: '',
  etablissement_representant: '',
  stagiaire_nom: '',
  stagiaire_prenom: '',
  stagiaire_adresse: '',
  stagiaire_tel: '',
  stagiaire_email: '',
  stagiaire_annee_univ: '',
  stagiaire_diplome: '',
  stagiaire_specialite: '',
  theme: '',
  activites: ['', '', '', ''],
  date_debut: '',
  date_fin: '',
  lieu_stage: '',
  structure_accueil: '',
  responsable_ird: '',
  responsable_etablissement: '',
  gratification_montant: '',
  indemnite_transport: '',
  indemnite_restauration: '',
  imputation: '',
  date_signature: new Date().toLocaleDateString('fr-FR'),
})

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

const ajouterActivite = () => form.value.activites.push('')
const supprimerActivite = (i) => { if (form.value.activites.length > 1) form.value.activites.splice(i, 1) }

// ── Génération PDF multi-pages fidèle au document Convention de Stage ───────
async function buildPDF() {
  const { jsPDF } = await import('jspdf')

  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
  const mL = 20, mR = 190, pageW = 210, pageH = 297
  const lineH = 5.5
  let y = 15

  // ── LOGOS (En-tête) ──
  if (logoIrd) doc.addImage(logoIrd, 'PNG', mL, y, 22, 10)
  if (logoUcad) doc.addImage(logoUcad, 'PNG', mR - 15, y, 15, 15)
  y += 20

  const f = form.value
  const val = (v) => v || '........................................'
  const valShort = (v) => v || '...............'

  // Helper — écrit un bloc de texte et retourne le nouveau y
  const writeText = (text, x, startY, maxW = 170, size = 10, style = 'normal') => {
    doc.setFontSize(size)
    doc.setFont('helvetica', style)
    const lines = doc.splitTextToSize(text, maxW)
    lines.forEach(line => {
      if (startY + lineH > pageH - 20) {
        doc.addPage()
        startY = 20
      }
      doc.text(line, x, startY)
      startY += lineH
    })
    return startY
  }

  const checkPage = (currentY, needed = 15) => {
    if (currentY + needed > pageH - 20) {
      doc.addPage()
      return 20
    }
    return currentY
  }

  const hRule = (currentY) => {
    doc.setLineWidth(0.3)
    doc.setDrawColor(0)
    doc.line(mL, currentY, mR, currentY)
    return currentY + 3
  }

  // ══ TITRE (encadré gris fidèle au document) ══
  doc.setFillColor(242, 242, 242)
  doc.setDrawColor(0)
  doc.setLineWidth(0.5)
  doc.rect(mL, y - 6, mR - mL, 12, 'FD')
  doc.setFontSize(14)
  doc.setFont('helvetica', 'bold')
  doc.text('CONVENTION DE STAGE', pageW / 2, y + 2, { align: 'center' })
  y += 15

  // ══ PRÉAMBULE ══
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text('ENTRE,', mL, y); y += lineH + 2

  y = writeText(
    "L'Institut de Recherche pour le développement, établissement public à caractère scientifique et technologique (EPST) ayant son siège 44 boulevard de Dunkerque - CS 9009 - 13572 Marseille France, représenté par M. Pierre MORAND, Représentant de l'IRD au Sénégal, ci-après dénommé «IRD»",
    mL, y, 170, 10, 'normal'
  )
  y += 3

  doc.setFont('helvetica', 'bold')
  doc.text('ET,', mL, y); y += lineH + 2

  // Bloc établissement (encadré)
  doc.setFontSize(9)
  const etabFields = [
    { l: "Nom de l'organisme de formation : ", v: val(f.etablissement_nom) },
    { l: "Statut juridique : ", v: val(f.etablissement_statut) },
    { l: "Siège social : ", v: val(f.etablissement_siege) },
    { l: "Représenté par : ", v: val(f.etablissement_representant) },
    { l: "Ci-après dénommé ", v: "«Etablissement»" },
  ]
  const etabH = etabFields.length * 5.5 + 6
  y = checkPage(y, etabH + 5)
  doc.setFillColor(249, 249, 249)
  doc.setDrawColor(180, 180, 180)
  doc.rect(mL, y - 3, 170, etabH, 'FD')
  
  const writeFieldLine = (lbl, valStr, x, currentY, highlight = true) => {
    doc.setFont('helvetica', 'normal'); doc.setTextColor(0, 0, 0)
    doc.text(lbl, x, currentY)
    if (valStr) {
      doc.setFont('helvetica', 'bold')
      if (highlight && !valStr.startsWith('.')) doc.setTextColor(0, 0, 0)
      doc.text(valStr, x + doc.getTextWidth(lbl), currentY)
      doc.setTextColor(0, 0, 0)
    }
  }

  etabFields.forEach(field => {
    writeFieldLine(field.l, field.v, mL + 3, y, field.l !== 'Ci-après dénommé ')
    y += 5.5
  })
  y += 5

  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text('CONCERNANT LE STAGE DE :', mL, y); y += lineH + 2

  // Bloc stagiaire
  doc.setFontSize(9)
  const stagFields = [
    { l: "Nom, Prénom : ", v: val(f.stagiaire_nom + ' ' + f.stagiaire_prenom) },
    { l: "Adresse : ", v: val(f.stagiaire_adresse) },
    { l: "Tel : ", v: val(f.stagiaire_tel) },
    { l: "Email : ", v: val(f.stagiaire_email) },
    { l: "Etudiant pour l'année universitaire : ", v: valShort(f.stagiaire_annee_univ) },
    { l: "Diplôme préparé : ", v: val(f.stagiaire_diplome) },
    { l: "Spécialité : ", v: val(f.stagiaire_specialite) },
  ]
  const stagH = stagFields.length * 5.5 + 6
  y = checkPage(y, stagH + 5)
  doc.setFillColor(249, 249, 249)
  doc.setDrawColor(180, 180, 180)
  doc.rect(mL, y - 3, 170, stagH, 'FD')
  stagFields.forEach(field => {
    writeFieldLine(field.l, field.v, mL + 3, y)
    y += 5.5
  })
  y += 5

  // CONSIDÉRANT
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text('CONSIDERANT :', mL, y); y += lineH + 1

  doc.setFont('helvetica', 'normal')
  y = writeText("que l'étudiant est inscrit régulièrement dans un établissement du Sénégal habilité à délivré le diplôme.", mL, y, 170, 10)
  y = writeText("que la formation de Licence/Master est organisée sous la forme de cours, de conférences, de séminaires, de travaux dirigés, de travaux pratiques, de stages et de conduites de projets individuels et collectifs.", mL, y, 170, 10)
  y = writeText("La mission de formation de l'IRD", mL, y, 170, 10)
  y = writeText("Le partenariat entre l'Université et l'IRD", mL, y, 170, 10)
  y += 4

  // IL EST CONVENU
  doc.setFontSize(11)
  doc.setFont('helvetica', 'bold')
  doc.text('IL EST CONVENU CE QUI SUIT :', pageW / 2, y, { align: 'center' })
  y += lineH + 2

  // ══ ARTICLES ══
  const article = (num, titre, contenu) => {
    y = checkPage(y, 20)
    doc.setFontSize(10)
    doc.setFont('helvetica', 'bold')
    doc.text(`Article ${num} : ${titre}`, mL, y)
    y += lineH
    doc.setFont('helvetica', 'normal')
    y = writeText(contenu, mL, y, 170, 10)
    y += 2
  }

  article('1', 'objet',
    "La présente convention a pour objet de préciser les modalités d'accueil du stagiaire à l'IRD dans le cadre de la préparation de son diplôme."
  )

  article('2', 'champ d\'application',
    `Le stage a pour objet de permettre à l'étudiant de mettre en pratique les outils théoriques et méthodologiques acquis au cours de sa formation universitaire, d'identifier ses compétences et découvrir un milieu professionnel.\n\nLe stagiaire n'effectue pas une prestation de service mais une étude qui s'inscrit dans le cadre de la formation et du projet de l'étudiant en accord avec l'IRD sur le thème :`
  )
  writeFieldLine("", val(f.theme), mL, y - 1)
  y += 4

  // Article 3 avec activités
  y = checkPage(y, 25)
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text("Article 3 : activités du stagiaire", mL, y); y += lineH
  doc.setFont('helvetica', 'normal')
  y = writeText("Les responsables scientifiques ou administratifs s'engagent à ne faire exécuter au stagiaire que des travaux ou activités qui concourent à sa formation.", mL, y, 170, 10)
  y = writeText("Les activités confiées porteront sur les aspects suivants :", mL, y, 170, 10)
  const filledActivites = f.activites.filter(act => act && act.trim() !== '')
  if (filledActivites.length > 0) {
    filledActivites.forEach(act => {
      writeFieldLine("- ", act, mL + 5, y); y += lineH
    })
  } else {
    y = writeText('- .............................................................................', mL + 5, y, 160, 10)
  }
  y += 2

  // Article 4 avec dates et lieux
  y = checkPage(y, 25)
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text("Article 4 : modalités", mL, y); y += lineH
  doc.setFontSize(10)
  writeFieldLine("Le stage s'effectue du ", val(f.date_debut), mL, y, true)
  const offset1 = doc.getTextWidth("Le stage s'effectue du " + val(f.date_debut))
  doc.setFont('helvetica', 'normal')
  doc.text(" au ", mL + offset1, y)
  writeFieldLine("", val(f.date_fin), mL + offset1 + doc.getTextWidth(" au "), y, true)
  y += lineH
  writeFieldLine("Lieu du stage : ", val(f.lieu_stage), mL, y); y += lineH
  writeFieldLine("Structure d'accueil : ", val(f.structure_accueil), mL, y); y += lineH
  doc.setFont('helvetica', 'normal')
  doc.text("Encadrement :", mL, y); y += lineH
  writeFieldLine("Responsable scientifique/administratif pour l'IRD : ", val(f.responsable_ird), mL + 5, y); y += lineH
  writeFieldLine("Responsable pédagogique pour l'établissement d'enseignement : ", val(f.responsable_etablissement), mL + 5, y); y += lineH
  y += 2

  // Article 5 gratification
  y = checkPage(y, 20)
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text("Article 5 : gratification", mL, y); y += lineH
  doc.setFontSize(10)
  writeFieldLine("La gratification est fixée à ", val(f.gratification_montant), mL, y)
  doc.setFont('helvetica', 'normal'); doc.text(" par mois, à ce montant s'ajoute :", mL + doc.getTextWidth("La gratification est fixée à " + val(f.gratification_montant)), y); y += lineH
  writeFieldLine("une indemnité de transport de ", val(f.indemnite_transport), mL + 5, y)
  doc.setFont('helvetica', 'normal'); doc.text(" par mois,", mL + 5 + doc.getTextWidth("une indemnité de transport de " + val(f.indemnite_transport)), y); y += lineH
  writeFieldLine("une indemnité de restauration de ", val(f.indemnite_restauration), mL + 5, y)
  doc.setFont('helvetica', 'normal'); doc.text(" par mois.", mL + 5 + doc.getTextWidth("une indemnité de restauration de " + val(f.indemnite_restauration)), y); y += lineH
  writeFieldLine("Le montant de cette gratification est imputé sur : ", val(f.imputation), mL, y); y += lineH
  y += 2

  article('6', 'statut',
    "Pendant toute la durée de son stage, le stagiaire conserve son statut d'étudiant et relèvent en matière de discipline, de sanctions, de couverture sociale, médicale et accident du travail de l'établissement dans lequel il est régulièrement inscrit.\n\nTout déplacement du stagiaire hors de son lieu de stage doit respecter les procédures administratives en vigueur à l'IRD et faire l'objet d'une autorisation préalable.\n\nLe stagiaire est tenu de respecter le règlement intérieur et les règles d'hygiène et sécurité de l'IRD (annexe 1)."
  )

  article('7', 'devoirs de réserve et confidentialité',
    "Le devoir de réserve est de rigueur absolue et apprécié par l'organisme d'accueil compte-tenu de ses spécificités. Le stagiaire prend donc l'engagement de n'utiliser en aucun cas les informations recueillies ou obtenues par lui pour en faire publication, communication à des tiers sans accord préalable de l'organisme d'accueil, y compris le rapport de stage.\n\nCet engagement vaut non seulement pour la durée du stage mais également après son expiration. Le stagiaire s'engage à ne conserver, emporter, ou prendre copie d'aucun document ou logiciel, de quelque nature que ce soit, appartenant à l'organisme d'accueil, sauf accord de ce dernier.\n\nDans le cadre de la confidentialité des informations contenues dans le rapport de stage, l'organisme d'accueil peut demander une restriction de la diffusion du rapport, voire le retrait de certains éléments confidentiels.\n\nLes personnes amenées à en connaître sont contraintes par le secret professionnel à n'utiliser ni ne divulguer les informations du rapport."
  )

  article('8', 'propriété intellectuelle',
    "Conformément au code de la propriété intellectuelle, dans le cas où les activités du stagiaire donnent lieu à la création d'une œuvre protégée par le droit d'auteur ou la propriété industrielle (y compris un logiciel), si l'organisme d'accueil souhaite l'utiliser et que le stagiaire en est d'accord, un contrat devra être signé entre le stagiaire (auteur) et l'organisme d'accueil.\n\nLe contrat devra alors notamment préciser l'étendue des droits cédés, l'éventuelle exclusivité, la destination, les supports utilisés et la durée de la cession, ainsi que, le cas échéant, le montant de la rémunération due au stagiaire au titre de la cession. Cette clause s'applique quel que soit le statut de l'organisme d'accueil."
  )

  article('9', 'clause informatique',
    "Le stagiaire s'engage à respecter et signe la charte informatique de la structure d'accueil (annexe 2)."
  )

  article('10', 'absence, prolongation, interruption du stage',
    "Le stagiaire ne dispose pas de droit à congé. Toutefois, il peut être autorisé, exceptionnellement, sur accord de son responsable scientifique/administratif et de son responsable pédagogique.\n\nLe stage peut être prolongé par avenant dans la limite de 6 mois consécutif pour une même année universitaire.\n\nLe stagiaire ou l'IRD peuvent interrompre à tout moment le stage après avoir dument informé l'établissement en précisant les raisons de la rupture."
  )

  article('11', 'responsabilité civile',
    "Le stagiaire certifie qu'il possède une assurance couvrant sa responsabilité civile individuelle pendant la durée du stage, susceptible d'être engagée en raison de faits personnels ayant causé des dommages à des tiers à l'occasion du stage.\n\nLes autres parties déclarent être garanties au titre de la responsabilité civile."
  )

  article('12', 'exclusion',
    "Le stagiaire ne peut être lié par contrat de travail ou de prestation avec l'IRD.\n\nLa signature d'une convention de stage annule tout contrat de travail ou de prestation de service en cours avec l'IRD pendant la période du stage."
  )

  article('13', 'pièces contractuelles',
    "Les annexes paraphées et signées par les parties font partie intégrantes de la convention\nAnnexe 1 : règlement intérieur hygiène et sécurité\nAnnexe 2 : charte utilisateur pour l'usage de ressources informatiques, de service internet et de services intranet"
  )

  // ══ SIGNATURES ══
  y = checkPage(y, 55)
  y += 5
  doc.setLineWidth(0.3)
  doc.line(mL, y, mR, y)
  y += 6

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(10)
  doc.text(`Fait en trois exemplaires, à Dakar, le ${f.date_signature}`, pageW / 2, y, { align: 'center' })
  y += 12

  // 3 colonnes signatures
  const col1 = mL + 3, col2 = pageW / 2 - 25, col3 = mR - 65
  const sigW = 55

  doc.setFontSize(9)
  doc.setFont('helvetica', 'bold')
  doc.text("Pour l'Etablissement\nd'Enseignement", col1, y, { maxWidth: sigW, align: 'center' })
  doc.text("Pour le stagiaire", col2 + 12, y, { maxWidth: sigW, align: 'center' })
  doc.text("Pour l'IRD", col3 + 18, y, { maxWidth: sigW, align: 'center' })
  y += 10

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(8)
  doc.text("(Nom, Prénom, date, cachet\net signature)", col1, y, { maxWidth: sigW + 5, align: 'center' })
  doc.text("(date et signature)", col2 + 12, y, { maxWidth: sigW, align: 'center' })
  doc.text("Le Représentant de l'IRD\nau Sénégal", col3 + 18, y, { maxWidth: sigW, align: 'center' })
  y += 20

  doc.setLineWidth(0.4)
  doc.line(mL, y, mL + sigW, y)
  doc.line(col2, y, col2 + sigW, y)
  doc.line(col3, y, col3 + sigW, y)

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
    formData.append('type_document', 'convention_stage')
    formData.append('donnees', JSON.stringify(form.value))
    formData.append('pdf_file', pdfBlobFileFinal.value, 'convention.pdf')
    
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
      // On n'appelle plus retour() immédiatement car cela détruit l'iframe et annule l'impression
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
          <DocumentTextIcon class="w-7 h-7 text-blue-400" />
          Convention de Stage
        </h1>
        <p class="text-[var(--text-subtle)] text-sm mt-0.5">
          Le PDF généré est <span class="text-blue-400 font-semibold">fidèle au modèle officiel IRD</span> — 13 articles, multi-pages automatiques
        </p>
      </div>
    </div>

    <!-- MODE: FORMULAIRE -->
    <div v-show="!pdfGeneratedMode" class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- ─── FORMULAIRE ─── -->
      <div class="space-y-5">

        <!-- Établissement -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Organisme de Formation (Établissement)</h2>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Nom de l'organisme *</label>
            <input v-model="form.etablissement_nom" type="text" placeholder="Ex: Université Cheikh Anta Diop"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Statut juridique</label>
              <input v-model="form.etablissement_statut" type="text" placeholder="Ex: Établissement public"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Représenté par</label>
              <input v-model="form.etablissement_representant" type="text" placeholder="Nom du représentant"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Siège social</label>
            <input v-model="form.etablissement_siege" type="text" placeholder="Adresse du siège social"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
        </div>

        <!-- Stagiaire -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Stagiaire</h2>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Nom *</label>
              <input v-model="form.stagiaire_nom" type="text" placeholder="NOM"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Prénom(s) *</label>
              <input v-model="form.stagiaire_prenom" type="text" placeholder="Prénom(s)"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Adresse</label>
            <input v-model="form.stagiaire_adresse" type="text" placeholder="Adresse complète"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Tél</label>
              <input v-model="form.stagiaire_tel" type="text" placeholder="Téléphone"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Email</label>
              <input v-model="form.stagiaire_email" type="email" placeholder="email@exemple.com"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Année universitaire</label>
              <input v-model="form.stagiaire_annee_univ" type="text" placeholder="2025-2026"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Diplôme préparé</label>
              <input v-model="form.stagiaire_diplome" type="text" placeholder="Master, Licence…"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Spécialité</label>
              <input v-model="form.stagiaire_specialite" type="text" placeholder="Spécialité"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
        </div>

        <!-- Stage -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Détails du Stage</h2>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Thème du stage (Article 2)</label>
            <input v-model="form.theme" type="text" placeholder="Thème de l'étude..."
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="block text-xs font-medium text-[var(--text-subtle)]">Activités confiées (Article 3)</label>
              <button @click="ajouterActivite" class="flex items-center gap-1.5 text-[10px] font-semibold px-2 py-1 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded transition-all border border-blue-500/20">
                <PlusIcon class="w-3 h-3" />Ajouter
              </button>
            </div>
            <div class="space-y-2">
              <div v-for="(_, idx) in form.activites" :key="idx" class="flex items-center gap-2">
                <span class="text-[var(--text-subtle)] text-xs w-4">-</span>
                <input v-model="form.activites[idx]" type="text" :placeholder="`Activité ${idx + 1}`"
                  class="flex-1 bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
                <button v-if="form.activites.length > 1" @click="supprimerActivite(idx)" class="p-1.5 rounded-lg border border-[var(--border)] text-[var(--text-subtle)] hover:text-red-400 hover:bg-red-500/10 transition-all">
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Date de début</label>
              <input v-model="form.date_debut" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Date de fin</label>
              <input v-model="form.date_fin" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Lieu du stage</label>
              <input v-model="form.lieu_stage" type="text" placeholder="Dakar, Sénégal"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Structure d'accueil</label>
              <input v-model="form.structure_accueil" type="text" placeholder="Unité / Service IRD"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Responsable scientifique/administratif pour l'IRD</label>
            <input v-model="form.responsable_ird" type="text" placeholder="Nom et titre du responsable IRD"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Responsable pédagogique pour l'établissement</label>
            <input v-model="form.responsable_etablissement" type="text" placeholder="Nom du responsable pédagogique"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
        </div>

        <!-- Gratification -->
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Gratification (Article 5)</h2>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Montant/mois (F CFA)</label>
              <input v-model="form.gratification_montant" type="text" placeholder="Ex: 50 000"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Transport/mois</label>
              <input v-model="form.indemnite_transport" type="text" placeholder="Ex: 15 000"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Restauration/mois</label>
              <input v-model="form.indemnite_restauration" type="text" placeholder="Ex: 10 000"
                class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Imputation (code budgétaire)</label>
            <input v-model="form.imputation" type="text" placeholder="Code EOTP ou centre de coût"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-[var(--text-subtle)] mb-1.5">Date de signature</label>
            <input v-model="form.date_signature" type="text" placeholder="Ex: 11 juin 2026"
              class="w-full bg-[var(--surface-alt)]/60 border border-[var(--border)] rounded-lg px-4 py-2.5 text-sm text-[var(--text)] placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
        </div>

        <div class="flex gap-3">
          <button @click="genererPDF" :disabled="generating"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-[var(--text)] rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5">
            <ArrowDownTrayIcon class="w-5 h-5" />
            {{ generating ? 'Génération PDF...' : 'Générer PDF fidèle (multi-pages)' }}
          </button>
          <button @click="imprimer"
            class="flex items-center gap-2 px-5 py-3 border border-[var(--border)] text-[var(--text-muted)] hover:text-[var(--text)] hover:bg-white/5 rounded-xl text-sm font-bold transition-all">
            <PrinterIcon class="w-5 h-5" />
            Imprimer
          </button>
        </div>
      </div>

      <!-- ─── APERÇU ─── -->
      <div class="xl:sticky xl:top-6 xl:self-start flex flex-col h-[80vh]">
        <div class="bg-[var(--surface)] border border-[var(--border)] rounded-xl p-4 mb-3 shrink-0">
          <p class="text-xs font-semibold text-[var(--text-subtle)] uppercase tracking-wider mb-1">Aperçu en direct — fidèle au document original</p>
          <p class="text-[11px] text-[var(--text-subtle)]">Le PDF généré est multi-pages et reproduit fidèlement les 13 articles officiels de la Convention de Stage IRD.</p>
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
        </div>
      </div>
      <div class="flex-1 w-full bg-slate-900/50 overflow-hidden">
        <iframe ref="iframeRef" :src="pdfBlobUrlFinal" class="w-full h-full border-none"></iframe>
      </div>
    </div>

  </div>
</template>
