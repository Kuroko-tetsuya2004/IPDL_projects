<template>
  <footer class="border-t border-white/8 bg-surface-800/50 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
        <!-- Brand -->
        <div class="md:col-span-2">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 flex items-center justify-center shadow-glow-blue">
              <span class="text-white font-black">U</span>
            </div>
            <div>
              <p class="text-white font-bold">UMMISCO</p>
              <p class="text-slate-500 text-xs">Unité de Modélisation Mathématique et Informatique des Systèmes Complexes</p>
            </div>
          </div>
          <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
            Laboratoire de recherche CNRS / IRD / Sorbonne Université / UCAD — Dakar, Sénégal.
          </p>
          <!-- Newsletter -->
          <form @submit.prevent="subscribe" class="flex gap-2 mt-5">
            <input v-model="email" type="email" placeholder="votre@email.com"
              class="input text-sm flex-1 py-2" />
            <button type="submit" class="btn-primary btn-sm px-4 shrink-0">
              S'abonner
            </button>
          </form>
          <p v-if="subscribed" class="text-emerald-400 text-xs mt-2">✓ Abonnement confirmé !</p>
        </div>

        <!-- Navigation -->
        <div>
          <h3 class="text-white font-semibold text-sm mb-4">Portail</h3>
          <ul class="space-y-2">
            <li v-for="link in portalLinks" :key="link.href">
              <a :href="link.href" class="text-slate-400 text-sm hover:text-white transition-colors">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Resources -->
        <div>
          <h3 class="text-white font-semibold text-sm mb-4">Ressources</h3>
          <ul class="space-y-2">
            <li v-for="link in resourceLinks" :key="link.href">
              <a :href="link.href" class="text-slate-400 text-sm hover:text-white transition-colors">
                {{ link.label }}
              </a>
            </li>
          </ul>
        </div>
      </div>

      <div class="border-t border-white/8 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <p class="text-slate-500 text-xs">
          © {{ new Date().getFullYear() }} UMMISCO — UCAD Dakar. Tous droits réservés.
        </p>
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
  { href: '/publications', label: 'Publications' },
  { href: '/datasets', label: 'Données ouvertes' },
  { href: '/axes', label: 'Axes thématiques' },
  { href: '/equipe', label: 'Équipe de recherche' },
  { href: '/evenements', label: 'Événements' },
  { href: '/contact', label: 'Contact' },
]
const resourceLinks = [
  { href: '/outils', label: 'Outils doctoraux' },
  { href: '/recherche', label: 'Recherche avancée' },
  { href: '/chatbot', label: 'Assistant IA' },
  { href: '/collaboration', label: 'Collaborer avec nous' },
]

async function subscribe() {
  if (!email.value) return
  await window.axios.post('/newsletter/subscribe', { email: email.value })
  subscribed.value = true
  email.value = ''
}
</script>
