<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  Bars3Icon, XMarkIcon, MagnifyingGlassIcon,
  BellIcon, ChevronDownIcon, LanguageIcon,
  UserCircleIcon, ArrowRightOnRectangleIcon,
  SunIcon, MoonIcon, HomeIcon, GlobeAltIcon
} from '@heroicons/vue/24/outline'

const page     = usePage()
const user     = computed(() => page.props.auth?.user)
const locale   = computed(() => page.props.locale ?? 'fr')
const mobileOpen = ref(false)
const userMenuOpen = ref(false)

const isDark = ref(true)

onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark')
})

function toggleTheme() {
  if (document.documentElement.classList.contains('dark')) {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
    isDark.value = false
  } else {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
    isDark.value = true
  }
}

const navLinks = [
  { label: 'Accueil',       labelEn: 'Home',         href: '/'},
  { label: 'Publications',  labelEn: 'Publications',  href: '/publications'},
  { label: 'Axes',          labelEn: 'Research Areas',href: '/axes'},
  { label: 'Données',       labelEn: 'Datasets',      href: '/datasets'},
  { label: 'Équipe',        labelEn: 'Team',          href: '/equipe'},
  { label: 'Contact',       labelEn: 'Contact',       href: '/contact'},
]

function label(link) {
  return locale.value === 'en' && link.labelEn ? link.labelEn : link.label
}

function toggleLocale() {
  const next = locale.value === 'fr' ? 'en' : 'fr'
  // POST /langue/{lang}
  window.axios.post(`/langue/${next}`).then(() => window.location.reload())
}
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50">
    <!-- Bande gradient translucide -->
    <div class="absolute inset-0 bg-surface-900/80 backdrop-blur-xl border-b border-white/8" />

    <nav class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <!-- Logo -->
      <Link href="/" class="flex items-center gap-3 group shrink-0">
        <svg viewBox="0 0 100 100" class="w-9 h-9 filter drop-shadow(0 2px 8px rgba(14, 165, 233, 0.15))" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Outer glowing rings -->
          <circle cx="50" cy="50" r="45" stroke="url(#logo-grad)" stroke-width="2" stroke-dasharray="10 6" />
          <circle cx="50" cy="50" r="38" stroke="url(#logo-grad2)" stroke-width="1" stroke-opacity="0.3" />
          
          <!-- Interconnected complexity nodes forming an abstract 'U' -->
          <path d="M30 35 V60 C30 71 39 80 50 80 C61 80 70 71 70 60 V35" stroke="url(#logo-grad)" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M40 40 V60 C40 65 44 70 50 70 C56 70 60 65 60 60 V40" stroke="url(#logo-grad2)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="4 2" />
          
          <!-- Node dots representing agents/complexity -->
          <circle cx="30" cy="35" r="4" fill="#0ea5e9" />
          <circle cx="70" cy="35" r="4" fill="#0ea5e9" />
          <circle cx="50" cy="80" r="5" fill="#2563eb" />
          <circle cx="40" cy="65" r="3" fill="#ffffff" />
          <circle cx="60" cy="65" r="3" fill="#ffffff" />
          <circle cx="50" cy="50" r="3.5" fill="#38bdf8" />
          
          <!-- Connecting network links -->
          <line x1="30" y1="35" x2="50" y2="50" stroke="#0ea5e9" stroke-width="1.5" stroke-opacity="0.6" />
          <line x1="70" y1="35" x2="50" y2="50" stroke="#0ea5e9" stroke-width="1.5" stroke-opacity="0.6" />
          <line x1="50" y1="50" x2="50" y2="80" stroke="#2563eb" stroke-width="1.5" stroke-opacity="0.6" />

          <defs>
            <linearGradient id="logo-grad" x1="0" y1="0" x2="100" y2="100" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#0ea5e9" />
              <stop offset="100%" stop-color="#2563eb" />
            </linearGradient>
            <linearGradient id="logo-grad2" x1="100" y1="0" x2="0" y2="100" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#38bdf8" />
              <stop offset="100%" stop-color="#4f46e5" />
            </linearGradient>
          </defs>
        </svg>
        <div class="hidden sm:block leading-none">
          <span class="text-white font-extrabold text-sm tracking-wide bg-gradient-to-r from-white to-slate-300 bg-clip-text text-transparent">UMMISCO</span>
          <span class="block text-slate-500 text-[10px] uppercase font-semibold tracking-wider mt-0.5">Portail de recherche</span>
        </div>
      </Link>

      <!-- Nav desktop -->
      <div class="hidden lg:flex items-center gap-1">
        <Link v-for="link in navLinks" :key="link.href" :href="link.href"
          class="nav-link text-xs font-medium">
          {{ label(link) }}
        </Link>
      </div>

      <!-- Actions droite -->
      <div class="flex items-center gap-2">
        <!-- Recherche -->
        <Link href="/recherche" class="btn-ghost btn-sm rounded-lg hidden sm:flex">
          <MagnifyingGlassIcon class="w-4 h-4" />
        </Link>

        <!-- Langue -->
        <button @click="toggleLocale"
          class="btn-ghost btn-sm rounded-lg hidden sm:flex items-center gap-1 text-xs font-semibold uppercase"
          :title="locale === 'fr' ? 'Switch to English' : 'Passer en français'">
          <LanguageIcon class="w-4 h-4" />
          {{ locale === 'fr' ? 'EN' : 'FR' }}
        </button>

        <!-- Thème (Sombre / Clair) -->
        <button @click="toggleTheme"
          class="btn-ghost btn-sm rounded-lg flex items-center justify-center"
          :title="isDark ? 'Passer au mode clair' : 'Passer au mode sombre'">
          <SunIcon v-if="isDark" class="w-4 h-4 text-amber-400" />
          <MoonIcon v-else class="w-4 h-4 text-slate-500" />
        </button>

        <!-- User connecté -->
        <template v-if="user">
          <!-- Notifications -->
          <Link href="/notifications" class="btn-ghost btn-sm rounded-lg relative">
            <BellIcon class="w-4 h-4" />
            <span v-if="page.props.unread_count > 0"
              class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 text-white text-[10px]
                     font-bold rounded-full flex items-center justify-center">
              {{ Math.min(page.props.unread_count, 9) }}
            </span>
          </Link>

          <!-- Menu user -->
          <div class="relative">
            <button @click="userMenuOpen = !userMenuOpen"
              class="flex items-center gap-2 btn-secondary btn-sm rounded-xl">
              <img v-if="user.photo_url" :src="user.photo_url" :alt="user.prenom"
                class="w-5 h-5 rounded-full object-cover" />
              <UserCircleIcon v-else class="w-5 h-5 text-slate-400" />
              <span class="hidden sm:block text-xs max-w-[100px] truncate">{{ user.prenom }}</span>
              <ChevronDownIcon class="w-3 h-3 text-slate-400 transition-transform"
                :class="{ 'rotate-180': userMenuOpen }" />
            </button>

            <!-- Dropdown -->
            <Transition name="slide-down">
              <div v-if="userMenuOpen"
                class="absolute right-0 top-full mt-2 w-52 card border border-white/10 shadow-xl py-1 z-50"
                @click.outside="userMenuOpen = false">
                <div class="px-4 py-3 border-b border-white/8">
                  <p class="text-sm font-semibold text-white truncate">{{ user.prenom }} {{ user.nom }}</p>
                  <p class="text-xs text-slate-500 truncate">{{ user.email }}</p>
                  <span class="badge-blue mt-1 text-[10px]">{{ user.role.replace('_', ' ') }}</span>
                </div>
                <nav class="py-1">
                  <Link href="/dashboard" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5">
                    <HomeIcon class="w-4 h-4" /> Tableau de bord
                  </Link>
                  <Link href="/profile" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5">
                    <UserCircleIcon class="w-4 h-4" /> Mon profil
                  </Link>
                  <a href="/" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5">
                    <GlobeAltIcon class="w-4 h-4" /> Portail public
                  </a>
                  <Link v-if="['axe_admin','super_admin'].includes(user.role)"
                    href="/admin" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5">
                    Administration
                  </Link>
                  <div class="border-t border-white/8 mt-1 pt-1">
                    <Link href="/auth/logout" method="post" as="button"
                      class="w-full flex items-center gap-2 px-4 py-2 text-sm text-rose-400 hover:text-rose-300 hover:bg-rose-500/5">
                      <ArrowRightOnRectangleIcon class="w-4 h-4" /> Déconnexion
                    </Link>
                  </div>
                </nav>
              </div>
            </Transition>
          </div>
        </template>

        <!-- Non connecté -->
        <a v-else href="/auth/login"
          class="btn-primary btn-sm rounded-xl">
          Connexion SSO
        </a>

        <!-- Burger mobile -->
        <button @click="mobileOpen = !mobileOpen" class="btn-ghost btn-sm rounded-lg lg:hidden">
          <XMarkIcon v-if="mobileOpen" class="w-5 h-5" />
          <Bars3Icon v-else class="w-5 h-5" />
        </button>
      </div>
    </nav>

    <!-- Menu mobile -->
    <Transition name="slide-down">
      <div v-if="mobileOpen"
        class="lg:hidden relative bg-surface-800/95 backdrop-blur-xl border-b border-white/8 px-4 pb-4">
        <nav class="flex flex-col gap-1 pt-2">
          <Link v-for="link in navLinks" :key="link.href" :href="link.href"
            @click="mobileOpen = false"
            class="nav-link text-sm">
            {{ label(link) }}
          </Link>
          <div class="border-t border-white/8 mt-2 pt-2 flex gap-2">
            <Link href="/recherche" class="btn-secondary btn-sm flex-1 justify-center">
              <MagnifyingGlassIcon class="w-4 h-4" /> Recherche
            </Link>
            <button @click="toggleLocale" class="btn-secondary btn-sm px-3 uppercase text-xs font-bold">
              {{ locale === 'fr' ? 'EN' : 'FR' }}
            </button>
          </div>
        </nav>
      </div>
    </Transition>
  </header>
</template>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: all 0.2s ease; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
