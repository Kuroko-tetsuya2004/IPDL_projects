<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
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
  { label: 'Accueil',       labelEn: 'Home',          href: '/'},
  { label: 'Publications',  labelEn: 'Publications',   href: '/publications'},
  { label: 'Axes',          labelEn: 'Research Areas', href: '/axes'},
  { label: 'Données',       labelEn: 'Datasets',       href: '/datasets'},
  { label: 'Équipe',        labelEn: 'Team',           href: '/equipe'},
  { label: 'Contact',       labelEn: 'Contact',        href: '/contact'},
]

function label(link) {
  return locale.value === 'en' && link.labelEn ? link.labelEn : link.label
}

function toggleLocale() {
  const next = locale.value === 'fr' ? 'en' : 'fr'
  window.axios.post(`/langue/${next}`).then(() => window.location.reload())
}

// ✅ Logout propre via router.post()
function handleLogout() {
  userMenuOpen.value = false
  router.post('/auth/logout')
}
</script>

<template>
  <header class="fixed top-0 left-0 right-0 z-50">
    <!-- Fond translucide -->
    <div class="absolute inset-0 bg-surface-900/90 backdrop-blur-xl border-b border-white/8" />

    <!-- Bande supérieure partenaires (desktop) -->
    <div class="relative border-b border-white/5 bg-black/30 hidden lg:block">
      <div class="max-w-7xl mx-auto px-6 h-8 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2 opacity-80 hover:opacity-100 transition-opacity">
            <img src="/images/logo_ucad.webp" alt="UCAD" class="h-5 w-auto object-contain filter brightness-110" />
            <span class="text-[10px] text-slate-500 font-medium">Université Cheikh Anta Diop</span>
          </div>
          <span class="text-white/10">|</span>
          <div class="flex items-center gap-2 opacity-80">
            <span class="text-[10px] text-slate-500 font-medium">IRD · CNRS · Sorbonne Université</span>
          </div>
        </div>
        <div class="text-[10px] text-slate-600 font-medium tracking-wide uppercase">
          Portail Scientifique — UMMISCO Dakar
        </div>
      </div>
    </div>

    <nav class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
      <!-- Logo UMMISCO -->
      <Link href="/" class="flex items-center gap-3 group shrink-0">
        <div class="relative">
          <img
            src="/images/logo_UMMISCO.webp"
            alt="UMMISCO"
            class="h-9 w-auto object-contain filter drop-shadow-lg group-hover:scale-105 transition-transform duration-300"
          />
          <div class="absolute inset-0 bg-brand-500/20 blur-xl rounded-full -z-10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
        </div>
        <div class="hidden sm:block leading-none">
          <span class="text-white font-extrabold text-sm tracking-wide bg-gradient-to-r from-white via-slate-200 to-brand-300 bg-clip-text text-transparent">
            UMMISCO
          </span>
          <span class="block text-slate-500 text-[10px] uppercase font-semibold tracking-wider mt-0.5">
            Portail de recherche
          </span>
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

        <!-- Thème -->
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
                class="absolute right-0 top-full mt-2 w-56 card border border-white/10 shadow-xl py-1 z-50"
                @click.outside="userMenuOpen = false">
                <!-- User info -->
                <div class="px-4 py-3 border-b border-white/8">
                  <p class="text-sm font-semibold text-white truncate">{{ user.prenom }} {{ user.nom }}</p>
                  <p class="text-xs text-slate-500 truncate">{{ user.email }}</p>
                  <span class="badge-blue mt-1 text-[10px]">{{ user.role.replace('_', ' ') }}</span>
                </div>
                <nav class="py-1">
                  <Link href="/dashboard"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5">
                    <HomeIcon class="w-4 h-4" /> Tableau de bord
                  </Link>
                  <Link href="/profile"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5">
                    <UserCircleIcon class="w-4 h-4" /> Mon profil
                  </Link>
                  <a href="/"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5">
                    <GlobeAltIcon class="w-4 h-4" /> Portail public
                  </a>
                  <Link v-if="['axe_admin','super_admin'].includes(user.role)"
                    href="/admin"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-white/5">
                    Administration
                  </Link>
                  <div class="border-t border-white/8 mt-1 pt-1">
                    <!-- ✅ Logout via router.post() -->
                    <button
                      @click="handleLogout"
                      class="w-full flex items-center gap-2 px-4 py-2 text-sm text-rose-400 hover:text-rose-300 hover:bg-rose-500/5">
                      <ArrowRightOnRectangleIcon class="w-4 h-4" /> Déconnexion
                    </button>
                  </div>
                </nav>
              </div>
            </Transition>
          </div>
        </template>

        <!-- Non connecté -->
        <a v-else href="/auth/login" class="btn-primary btn-sm rounded-xl">
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
        <!-- Logos mobiles -->
        <div class="flex items-center gap-4 py-3 border-b border-white/5 mb-2">
          <img src="/images/logo_UMMISCO.webp" alt="UMMISCO" class="h-7 w-auto object-contain" />
          <img src="/images/logo_ucad.webp" alt="UCAD" class="h-6 w-auto object-contain opacity-70" />
        </div>
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
