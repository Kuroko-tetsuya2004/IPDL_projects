<template>
  <footer class="border-t border-white/8 bg-surface-900/60 backdrop-blur-sm mt-auto">

    <!-- Bande partenaires en haut du footer -->
    <div class="border-b border-white/5 bg-black/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <p class="text-center text-[10px] text-slate-600 uppercase font-semibold tracking-widest mb-4">En partenariat avec</p>
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16">
          <!-- UCAD -->
          <div class="flex items-center gap-3 opacity-50 hover:opacity-80 transition-opacity duration-300 group">
            <img src="/images/logo_ucad.webp" alt="UCAD" class="h-10 w-auto object-contain filter brightness-110 grayscale group-hover:grayscale-0 transition-all duration-300" />
            <div class="hidden sm:block">
              <p class="text-xs font-bold text-slate-400 group-hover:text-white transition-colors">UCAD</p>
              <p class="text-[10px] text-slate-600">Université Cheikh Anta Diop</p>
            </div>
          </div>
          <!-- IRD -->
          <div class="flex items-center gap-3 opacity-50 hover:opacity-80 transition-opacity duration-300">
            <div class="h-10 w-16 flex items-center justify-center">
              <span class="text-lg font-black text-slate-500 hover:text-white transition-colors tracking-widest">IRD</span>
            </div>
            <div class="hidden sm:block">
              <p class="text-xs font-bold text-slate-400">IRD</p>
              <p class="text-[10px] text-slate-600">Institut de Recherche pour le Développement</p>
            </div>
          </div>
          <!-- CNRS -->
          <div class="flex items-center gap-3 opacity-50 hover:opacity-80 transition-opacity duration-300">
            <div class="h-10 w-16 flex items-center justify-center">
              <span class="text-lg font-black text-slate-500 hover:text-white transition-colors tracking-widest">CNRS</span>
            </div>
            <div class="hidden sm:block">
              <p class="text-xs font-bold text-slate-400">CNRS</p>
              <p class="text-[10px] text-slate-600">Centre National de la Recherche Scientifique</p>
            </div>
          </div>
          <!-- Sorbonne -->
          <div class="flex items-center gap-3 opacity-50 hover:opacity-80 transition-opacity duration-300">
            <div class="h-10 flex items-center justify-center">
              <span class="text-xs font-black text-slate-500 hover:text-white transition-colors tracking-wider">Sorbonne<br>Université</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">

        <!-- Brand UMMISCO -->
        <div class="md:col-span-2">
          <div class="flex items-center gap-4 mb-5">
            <img src="/images/logo_UMMISCO.webp" alt="UMMISCO" class="h-12 w-auto object-contain filter drop-shadow-lg" />
            <div>
              <p class="text-white font-extrabold text-base tracking-wide">UMMISCO</p>
              <p class="text-slate-500 text-[11px] leading-tight max-w-[220px]">Unité de Modélisation Mathématique et Informatique des Systèmes Complexes</p>
            </div>
          </div>
          <p class="text-slate-400 text-sm leading-relaxed max-w-sm mb-1">
            Laboratoire de recherche pluridisciplinaire dédié à la modélisation des systèmes complexes,
            basé à Dakar, Sénégal.
          </p>
          <p class="text-slate-600 text-xs mb-5">UMR 209 — CNRS / IRD / Sorbonne Université / UCAD</p>

          <!-- Newsletter -->
          <form @submit.prevent="subscribe" class="flex gap-2 mt-4">
            <input v-model="email" type="email" placeholder="votre@email.com"
              class="input text-sm flex-1 py-2 bg-slate-800/60 border border-white/10 rounded-lg px-4 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50" />
            <button type="submit" class="btn-primary btn-sm px-4 shrink-0 whitespace-nowrap">
              S'abonner
            </button>
          </form>
          <p v-if="subscribed" class="text-emerald-400 text-xs mt-2">✓ Abonnement confirmé !</p>
        </div>

        <!-- Navigation -->
        <div>
          <h3 class="text-white font-semibold text-sm mb-5 flex items-center gap-2">
            <span class="w-4 h-0.5 bg-brand-500 rounded-full inline-block"></span>
            Portail
          </h3>
          <ul class="space-y-3">
            <li v-for="link in portalLinks" :key="link.href">
              <a :href="link.href" class="text-slate-400 text-sm hover:text-white transition-colors hover:translate-x-0.5 inline-block">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Resources -->
        <div>
          <h3 class="text-white font-semibold text-sm mb-5 flex items-center gap-2">
            <span class="w-4 h-0.5 bg-emerald-500 rounded-full inline-block"></span>
            Ressources
          </h3>
          <ul class="space-y-3">
            <li v-for="link in resourceLinks" :key="link.href">
              <a :href="link.href" class="text-slate-400 text-sm hover:text-white transition-colors hover:translate-x-0.5 inline-block">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Footer bottom -->
      <div class="border-t border-white/8 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <img src="/images/logo_UMMISCO.webp" alt="UMMISCO" class="h-6 w-auto object-contain opacity-40" />
          <p class="text-slate-500 text-xs">
            © {{ new Date().getFullYear() }} UMMISCO — UCAD Dakar. Tous droits réservés.
          </p>
        </div>
        <div class="flex items-center gap-4 text-xs text-slate-500">
          <a href="/mentions-legales" class="hover:text-slate-300 transition-colors">Mentions légales</a>
          <a href="/confidentialite" class="hover:text-slate-300 transition-colors">Confidentialité</a>
          <a href="/sitemap.xml" class="hover:text-slate-300 transition-colors">Sitemap</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { ref } from 'vue'
const email = ref('')
const subscribed = ref(false)

const portalLinks = [
  { href: '/publications', label: 'Publications scientifiques' },
  { href: '/axes', label: 'Axes thématiques' },
  { href: '/unite/presentation', label: 'Présentation UMMISCO' },
  { href: '/unite/membres', label: 'Équipe de recherche' },
  { href: '/contact', label: 'Contact' },
]
const resourceLinks = [
  { href: '/recherche', label: 'Recherche avancée' },
  { href: '/actualites', label: 'Actualités' },
  { href: '/auth/login', label: 'Espace membres' },
]

async function subscribe() {
  if (!email.value) return
  await window.axios.post('/newsletter/subscribe', { email: email.value })
  subscribed.value = true
  email.value = ''
}
</script>
