<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@layouts/DashboardLayout.vue'
import { DocumentTextIcon, PrinterIcon, ArrowDownTrayIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

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

// ── Génération PDF multi-pages fidèle au document Convention de Stage ───────
async function genererPDF() {
  generating.value = true
  const { jsPDF } = await import('jspdf')

  const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
  const mL = 20, mR = 190, pageW = 210, pageH = 297
  const lineH = 5.5
  let y = 20

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

  // ══ TITRE ══
  doc.setFontSize(16)
  doc.setFont('helvetica', 'bold')
  doc.text('CONVENTION DE STAGE', pageW / 2, y, { align: 'center' })
  y += 4
  y = hRule(y)
  y += 5

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
  doc.setFont('helvetica', 'normal')
  const etabLines = [
    `Nom de l'organisme de formation : ${val(f.etablissement_nom)}`,
    `Statut juridique : ${val(f.etablissement_statut)}`,
    `Siège social : ${val(f.etablissement_siege)}`,
    `Représenté par : ${val(f.etablissement_representant)}`,
    `Ci-après dénommé «Etablissement»`,
  ]
  const etabH = etabLines.length * 5.5 + 6
  y = checkPage(y, etabH + 5)
  doc.setFillColor(249, 249, 249)
  doc.setDrawColor(180, 180, 180)
  doc.rect(mL, y - 3, 170, etabH, 'FD')
  etabLines.forEach(line => { doc.text(line, mL + 3, y); y += 5.5 })
  y += 5

  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text('CONCERNANT LE STAGE DE :', mL, y); y += lineH + 2

  // Bloc stagiaire
  doc.setFontSize(9)
  doc.setFont('helvetica', 'normal')
  const stagLines = [
    `Nom, Prénom : ${val(f.stagiaire_nom + ' ' + f.stagiaire_prenom)}`,
    `Adresse : ${val(f.stagiaire_adresse)}`,
    `Tel : ${val(f.stagiaire_tel)}`,
    `Email : ${val(f.stagiaire_email)}`,
    `Etudiant pour l'année universitaire : ${valShort(f.stagiaire_annee_univ)}`,
    `Diplôme préparé : ${val(f.stagiaire_diplome)}`,
    `Spécialité : ${val(f.stagiaire_specialite)}`,
  ]
  const stagH = stagLines.length * 5.5 + 6
  y = checkPage(y, stagH + 5)
  doc.setFillColor(249, 249, 249)
  doc.setDrawColor(180, 180, 180)
  doc.rect(mL, y - 3, 170, stagH, 'FD')
  stagLines.forEach(line => { doc.text(line, mL + 3, y); y += 5.5 })
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
    `Le stage a pour objet de permettre à l'étudiant de mettre en pratique les outils théoriques et méthodologiques acquis au cours de sa formation universitaire, d'identifier ses compétences et découvrir un milieu professionnel.\n\nLe stagiaire n'effectue pas une prestation de service mais une étude qui s'inscrit dans le cadre de la formation et du projet de l'étudiant en accord avec l'IRD sur le thème : ${val(f.theme)}`
  )

  // Article 3 avec activités
  y = checkPage(y, 25)
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text("Article 3 : activités du stagiaire", mL, y); y += lineH
  doc.setFont('helvetica', 'normal')
  y = writeText("Les responsables scientifiques ou administratifs s'engagent à ne faire exécuter au stagiaire que des travaux ou activités qui concourent à sa formation.", mL, y, 170, 10)
  y = writeText("Les activités confiées porteront sur les aspects suivants :", mL, y, 170, 10)
  f.activites.forEach(act => {
    if (act && act.trim()) { y = writeText(`- ${act}`, mL + 5, y, 160, 10) }
    else { y = writeText('- .............................................................................', mL + 5, y, 160, 10) }
  })
  y += 2

  // Article 4 avec dates et lieux
  y = checkPage(y, 25)
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text("Article 4 : modalités", mL, y); y += lineH
  doc.setFont('helvetica', 'normal')
  y = writeText(`Le stage s'effectue du ${val(f.date_debut)} au ${val(f.date_fin)}`, mL, y, 170, 10)
  y = writeText(`Lieu du stage : ${val(f.lieu_stage)}`, mL, y, 170, 10)
  y = writeText(`Structure d'accueil : ${val(f.structure_accueil)}`, mL, y, 170, 10)
  y = writeText("Encadrement :", mL, y, 170, 10)
  y = writeText(`Responsable scientifique/administratif pour l'IRD : ${val(f.responsable_ird)}`, mL + 5, y, 165, 10)
  y = writeText(`Responsable pédagogique pour l'établissement d'enseignement : ${val(f.responsable_etablissement)}`, mL + 5, y, 165, 10)
  y += 2

  // Article 5 gratification
  y = checkPage(y, 20)
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text("Article 5 : gratification", mL, y); y += lineH
  doc.setFont('helvetica', 'normal')
  y = writeText(`La gratification est fixée à ${val(f.gratification_montant)} par mois, à ce montant s'ajoute :`, mL, y, 170, 10)
  y = writeText(`une indemnité de transport de ${val(f.indemnite_transport)} par mois,`, mL + 5, y, 165, 10)
  y = writeText(`une indemnité de restauration de ${val(f.indemnite_restauration)} par mois.`, mL + 5, y, 165, 10)
  y = writeText(`Le montant de cette gratification est imputé sur : ${val(f.imputation)}`, mL, y, 170, 10)
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

  doc.save(`Convention_Stage_${f.stagiaire_nom || 'stagiaire'}.pdf`)
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
          <DocumentTextIcon class="w-7 h-7 text-blue-400" />
          Convention de Stage
        </h1>
        <p class="text-slate-400 text-sm mt-0.5">
          Le PDF généré est <span class="text-blue-400 font-semibold">fidèle au modèle officiel IRD</span> — 13 articles, multi-pages automatiques
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

      <!-- ─── FORMULAIRE ─── -->
      <div class="space-y-5">

        <!-- Établissement -->
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-5 space-y-4">
          <h2 class="text-xs font-bold uppercase tracking-wider text-blue-400">Organisme de Formation (Établissement)</h2>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Nom de l'organisme *</label>
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
              <input v-model="form.stagiaire_tel" type="text" placeholder="Téléphone"
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
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Thème du stage (Article 2)</label>
            <input v-model="form.theme" type="text" placeholder="Thème de l'étude..."
              class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-400 mb-1.5">Activités confiées (Article 3)</label>
            <div class="space-y-2">
              <div v-for="(_, idx) in form.activites" :key="idx" class="flex items-center gap-2">
                <span class="text-slate-500 text-xs w-4">-</span>
                <input v-model="form.activites[idx]" type="text" :placeholder="`Activité ${idx + 1}`"
                  class="flex-1 bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Date de début</label>
              <input v-model="form.date_debut" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Date de fin</label>
              <input v-model="form.date_fin" type="text" placeholder="JJ/MM/AAAA"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
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
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Transport/mois</label>
              <input v-model="form.indemnite_transport" type="text" placeholder="Ex: 15 000"
                class="w-full bg-slate-800/60 border border-white/10 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1.5">Restauration/mois</label>
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

        <div class="flex gap-3">
          <button @click="genererPDF" :disabled="generating"
            class="flex-1 flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white rounded-xl text-sm font-bold transition-all hover:-translate-y-0.5">
            <ArrowDownTrayIcon class="w-5 h-5" />
            {{ generating ? 'Génération PDF...' : 'Générer PDF fidèle (multi-pages)' }}
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
        <div class="bg-slate-900/60 border border-white/8 rounded-xl p-4 mb-3">
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Aperçu — fidèle au document original</p>
          <p class="text-[11px] text-slate-500">Le PDF généré est multi-pages et reproduit fidèlement les 13 articles officiels de la Convention de Stage IRD.</p>
        </div>
        <div class="overflow-auto max-h-[80vh] rounded-xl border border-white/5 bg-white shadow-2xl" id="print-zone">
          <div style="width:794px; padding:36px 48px; font-family:Arial,Helvetica,sans-serif; font-size:10px; color:#000; background:#fff; line-height:1.6;">

            <div style="text-align:center; font-size:18px; font-weight:bold; letter-spacing:1px; margin-bottom:6px;">CONVENTION DE STAGE</div>
            <hr style="border:none; border-top:1.5px solid #000; margin-bottom:14px;" />

            <p style="font-weight:bold; margin-bottom:6px;">ENTRE,</p>
            <p style="margin-bottom:10px;">L'Institut de Recherche pour le développement, établissement public à caractère scientifique et technologique (EPST) ayant son siège 44 boulevard de Dunkerque - CS 9009 - 13572 Marseille France, représenté par M. Pierre MORAND, Représentant de l'IRD au Sénégal, ci-après dénommé «IRD»</p>

            <p style="font-weight:bold; margin-bottom:6px;">ET,</p>
            <div style="background:#f9f9f9; border:1px solid #ccc; padding:8px 12px; margin-bottom:10px; font-size:9.5px; line-height:1.7;">
              <div>Nom de l'organisme de formation : <strong>{{ form.etablissement_nom || '...........................' }}</strong></div>
              <div>Statut juridique : <strong>{{ form.etablissement_statut || '...........................' }}</strong></div>
              <div>Siège social : <strong>{{ form.etablissement_siege || '...........................' }}</strong></div>
              <div>Représenté par : <strong>{{ form.etablissement_representant || '...........................' }}</strong></div>
              <div>Ci-après dénommé «Etablissement»</div>
            </div>

            <p style="font-weight:bold; margin-bottom:6px;">CONCERNANT LE STAGE DE :</p>
            <div style="background:#f9f9f9; border:1px solid #ccc; padding:8px 12px; margin-bottom:10px; font-size:9.5px; line-height:1.7;">
              <div>Nom, Prénom : <strong>{{ form.stagiaire_nom }} {{ form.stagiaire_prenom }}</strong></div>
              <div>Adresse : <strong>{{ form.stagiaire_adresse || '...........................' }}</strong></div>
              <div>Tel : <strong>{{ form.stagiaire_tel || '...........................' }}</strong></div>
              <div>Email : <strong>{{ form.stagiaire_email || '...........................' }}</strong></div>
              <div>Etudiant pour l'année universitaire : <strong>{{ form.stagiaire_annee_univ || '.........' }}</strong></div>
              <div>Diplôme préparé : <strong>{{ form.stagiaire_diplome || '...........................' }}</strong></div>
              <div>Spécialité : <strong>{{ form.stagiaire_specialite || '...........................' }}</strong></div>
            </div>

            <p style="font-weight:bold; margin-bottom:4px;">CONSIDERANT :</p>
            <p style="margin-bottom:3px;">que l'étudiant est inscrit régulièrement dans un établissement du Sénégal habilité à délivré le diplôme.</p>
            <p style="margin-bottom:3px;">que la formation de Licence/Master est organisée sous la forme de cours, de conférences, de séminaires, de travaux dirigés, de travaux pratiques, de stages et de conduites de projets individuels et collectifs.</p>
            <p style="margin-bottom:3px;">La mission de formation de l'IRD</p>
            <p style="margin-bottom:10px;">Le partenariat entre l'Université et l'IRD</p>

            <p style="text-align:center; font-weight:bold; font-size:12px; text-decoration:underline; margin-bottom:12px;">IL EST CONVENU CE QUI SUIT :</p>

            <!-- Articles -->
            <p style="font-weight:bold; margin-bottom:2px;">Article 1 : objet</p>
            <p style="margin-bottom:8px;">La présente convention a pour objet de préciser les modalités d'accueil du stagiaire à l'IRD dans le cadre de la préparation de son diplôme.</p>

            <p style="font-weight:bold; margin-bottom:2px;">Article 2 : champ d'application</p>
            <p style="margin-bottom:4px;">Le stage a pour objet de permettre à l'étudiant de mettre en pratique les outils théoriques et méthodologiques acquis au cours de sa formation universitaire, d'identifier ses compétences et découvrir un milieu professionnel.</p>
            <p style="margin-bottom:8px;">Le stagiaire n'effectue pas une prestation de service mais une étude qui s'inscrit dans le cadre de la formation et du projet de l'étudiant en accord avec l'IRD sur le thème : <strong>{{ form.theme || '.....................................' }}</strong></p>

            <p style="font-weight:bold; margin-bottom:2px;">Article 3 : activités du stagiaire</p>
            <p style="margin-bottom:3px;">Les responsables scientifiques ou administratifs s'engagent à ne faire exécuter au stagiaire que des travaux ou activités qui concourent à sa formation.</p>
            <p style="margin-bottom:3px;">Les activités confiées porteront sur les aspects suivants :</p>
            <ul style="margin-left:16px; margin-bottom:8px; line-height:1.9;">
              <li v-for="(act, i) in form.activites" :key="i">- {{ act || '.......................................................................' }}</li>
            </ul>

            <p style="font-weight:bold; margin-bottom:2px;">Article 4 : modalités</p>
            <p>Le stage s'effectue du <strong>{{ form.date_debut || '......' }}</strong> au <strong>{{ form.date_fin || '......' }}</strong></p>
            <p>Lieu du stage : <strong>{{ form.lieu_stage || '..............................' }}</strong></p>
            <p>Structure d'accueil : <strong>{{ form.structure_accueil || '..............................' }}</strong></p>
            <p>Encadrement :</p>
            <p style="margin-left:12px;">Responsable scientifique/administratif pour l'IRD : <strong>{{ form.responsable_ird || '...' }}</strong></p>
            <p style="margin-left:12px; margin-bottom:8px;">Responsable pédagogique pour l'établissement d'enseignement : <strong>{{ form.responsable_etablissement || '...' }}</strong></p>

            <p style="font-weight:bold; margin-bottom:2px;">Article 5 : gratification</p>
            <p>La gratification est fixée à <strong>{{ form.gratification_montant || '.........' }}</strong> par mois, à ce montant s'ajoute :</p>
            <p style="margin-left:12px;">une indemnité de transport de <strong>{{ form.indemnite_transport || '.........' }}</strong> par mois,</p>
            <p style="margin-left:12px;">une indemnité de restauration de <strong>{{ form.indemnite_restauration || '.........' }}</strong> par mois.</p>
            <p style="margin-bottom:8px;">Le montant de cette gratification est imputé sur : <strong>{{ form.imputation || '.........' }}</strong></p>

            <p style="color:#555; font-style:italic; margin-bottom:6px;">[ Articles 6 à 13 inclus dans le PDF généré — statut, confidentialité, propriété intellectuelle, clause informatique, absence, responsabilité civile, exclusion, pièces contractuelles ]</p>

            <!-- Signatures (aperçu) -->
            <hr style="border:none; border-top:1px solid #ccc; margin:12px 0;" />
            <p style="text-align:center; margin-bottom:12px;">Fait en trois exemplaires, à Dakar, le <strong>{{ form.date_signature }}</strong></p>
            <div style="display:flex; justify-content:space-between; margin-top:20px;">
              <div style="text-align:center; width:30%;">
                <div style="font-weight:bold; font-size:9px; border-bottom:1px solid #000; padding-bottom:4px;">Pour l'Etablissement</div>
                <div style="height:40px;"></div>
              </div>
              <div style="text-align:center; width:30%;">
                <div style="font-weight:bold; font-size:9px; border-bottom:1px solid #000; padding-bottom:4px;">Pour le stagiaire</div>
                <div style="height:40px;"></div>
              </div>
              <div style="text-align:center; width:30%;">
                <div style="font-weight:bold; font-size:9px; border-bottom:1px solid #000; padding-bottom:4px;">Pour l'IRD</div>
                <div style="height:40px;"></div>
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
  #print-zone, #print-zone * { visibility: visible; }
  #print-zone { position: fixed; top: 0; left: 0; width: 100%; background: white !important; }
}
</style>
