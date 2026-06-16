<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import {
  Bars3Icon, XMarkIcon, MagnifyingGlassIcon,
  BellIcon, ChevronDownIcon, LanguageIcon,
  UserCircleIcon, ArrowRightOnRectangleIcon,
  SunIcon, MoonIcon, HomeIcon, GlobeAltIcon
} from '@heroicons/vue/24/outline'

const page         = usePage()
const user         = computed(() => page.props.auth?.user)
const locale       = computed(() => page.props.locale ?? 'fr')
const mobileOpen   = ref(false)
const userMenuOpen = ref(false)
const isDark       = ref(false)

onMounted(() => {
  // Align with the portal's data-theme system
  isDark.value = document.documentElement.getAttribute('data-theme') === 'dark'
})

function toggleTheme() {
  const html  = document.documentElement
  const current = html.getAttribute('data-theme')
  const next    = current === 'dark' ? 'light' : 'dark'
  html.setAttribute('data-theme', next)
  localStorage.setItem('ummisco-theme', next)
  isDark.value = next === 'dark'
}

const navLinks = [
  { label: 'Accueil',            labelEn: 'Home',             href: '/'},
  { label: 'Publications',       labelEn: 'Publications',      href: '/publications'},
  { label: 'Axes',               labelEn: 'Research Areas',    href: '/axes'},
  { label: 'Données',            labelEn: 'Datasets',          href: '/datasets'},
  { label: 'Membres',            labelEn: 'Members',           href: '/unite/membres'},
  { label: 'Contact',            labelEn: 'Contact',           href: '/contact'},
]

function label(link) {
  return locale.value === 'en' && link.labelEn ? link.labelEn : link.label
}

function toggleLocale() {
  const next = locale.value === 'fr' ? 'en' : 'fr'
  window.axios.post(`/langue/${next}`).then(() => window.location.reload())
}

function handleLogout() {
  userMenuOpen.value = false
  router.post('/auth/logout')
}
</script>

<template>
  <!-- ═══ NAVBAR — aligned with public portal design ═══ -->
  <div class="navbar-wrapper">

    <!-- Gradient accent line (identical to public portal) -->
    <div class="navbar-accent-line"></div>

    <nav class="navbar-inner">

      <!-- ── Brand / Logo ── -->
      <Link href="/" class="navbar-brand">
        <div class="navbar-logos">
          <img src="/images/logo_ummisco.webp" alt="UMMISCO" />
          <img src="/images/logo_ucad.webp"    alt="UCAD"    />
        </div>
        <div class="navbar-label">
          <span class="navbar-label-title">UMMISCO</span>
          <span class="navbar-label-sub">Portail scientifique</span>
        </div>
      </Link>

      <!-- ── Nav links desktop ── -->
      <ul class="navbar-nav" :class="{ 'is-open': mobileOpen }" id="dashNavbarNav">
        <li v-for="link in navLinks" :key="link.href">
          <Link
            :href="link.href"
            class="nav-link"
            :class="{ active: page.url === link.href || (link.href !== '/' && page.url.startsWith(link.href)) }"
            @click="mobileOpen = false"
          >
            {{ label(link) }}
          </Link>
        </li>
      </ul>

      <!-- ── Right actions ── -->
      <div class="navbar-actions">

        <!-- Theme toggle -->
        <button class="theme-toggle" @click="toggleTheme" :title="isDark ? 'Mode clair' : 'Mode sombre'" aria-label="Changer le thème">
          <SunIcon  v-if="isDark"  class="icon-svg" />
          <MoonIcon v-else         class="icon-svg" />
        </button>

        <!-- Language toggle -->
        <button class="lang-btn" @click="toggleLocale" :title="locale === 'fr' ? 'Switch to English' : 'Passer en français'">
          <LanguageIcon class="icon-svg-sm" />
          <span>{{ locale === 'fr' ? 'EN' : 'FR' }}</span>
        </button>

        <!-- Authenticated user -->
        <template v-if="user">
          <!-- Notifications bell -->
          <Link href="/notifications" class="notif-btn">
            <BellIcon class="icon-svg" />
            <span
              v-if="page.props.unread_count > 0"
              class="notif-badge"
            >{{ Math.min(page.props.unread_count, 9) }}</span>
          </Link>

          <!-- User dropdown -->
          <div class="user-menu-wrap">
            <button class="user-btn" @click="userMenuOpen = !userMenuOpen">
              <img v-if="user.photo_url" :src="user.photo_url" :alt="user.prenom" class="user-avatar-img" />
              <UserCircleIcon v-else class="user-avatar-icon" />
              <span class="user-name">{{ user.prenom }}</span>
              <ChevronDownIcon class="chevron-icon" :class="{ rotated: userMenuOpen }" />
            </button>

            <Transition name="dropdown-fade">
              <div v-if="userMenuOpen" class="user-dropdown" @click.outside="userMenuOpen = false">
                <!-- User info header -->
                <div class="dropdown-header">
                  <p class="dropdown-fullname">{{ user.prenom }} {{ user.nom }}</p>
                  <p class="dropdown-email">{{ user.email }}</p>
                  <span class="dropdown-role-badge">{{ user.role.replace('_', ' ') }}</span>
                </div>

                <nav class="dropdown-nav">
                  <Link href="/dashboard" class="dropdown-item" @click="userMenuOpen = false">
                    <HomeIcon class="icon-svg-sm" /> Tableau de bord
                  </Link>
                  <Link href="/profile" class="dropdown-item" @click="userMenuOpen = false">
                    <UserCircleIcon class="icon-svg-sm" /> Mon profil
                  </Link>
                  <a href="/" class="dropdown-item">
                    <GlobeAltIcon class="icon-svg-sm" /> Portail public
                  </a>
                  <Link
                    v-if="['axe_admin','super_admin'].includes(user.role)"
                    href="/admin"
                    class="dropdown-item"
                    @click="userMenuOpen = false"
                  >
                    Administration
                  </Link>

                  <div class="dropdown-divider"></div>

                  <button @click="handleLogout" class="dropdown-item dropdown-logout">
                    <ArrowRightOnRectangleIcon class="icon-svg-sm" /> Déconnexion
                  </button>
                </nav>
              </div>
            </Transition>
          </div>
        </template>

        <!-- Not authenticated -->
        <a v-else href="/auth/login" class="btn-cta">
          Connexion
        </a>

        <!-- Mobile burger -->
        <button class="menu-toggle" @click="mobileOpen = !mobileOpen" aria-label="Menu">
          <XMarkIcon  v-if="mobileOpen" class="icon-svg" />
          <Bars3Icon  v-else            class="icon-svg" />
        </button>

      </div>
    </nav>
  </div>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════════════════
   NavBar — Dashboard  (aligned 1:1 with public portal design)
   Uses the same CSS custom-properties set in dashboard.blade.php
   ═══════════════════════════════════════════════════════════════════ */

/* ── Wrapper ────────────────────────────────────────────────────── */
.navbar-wrapper {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  background: var(--navbar-bg);
  backdrop-filter: saturate(180%) blur(20px);
  -webkit-backdrop-filter: saturate(180%) blur(20px);
  border-bottom: 1px solid var(--navbar-border);
  transition: background 0.35s ease, border-color 0.35s ease;
}

/* Gradient accent line at bottom — identical to public portal */
.navbar-wrapper::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent 0%, var(--primary) 30%, var(--accent) 70%, transparent 100%);
  opacity: 0.3;
}

.navbar-accent-line { display: none; } /* handled by ::after */

/* ── Inner nav ──────────────────────────────────────────────────── */
.navbar-inner {
  max-width: 1320px;
  margin: 0 auto;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
}

/* ── Brand ──────────────────────────────────────────────────────── */
.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  text-decoration: none;
  flex-shrink: 0;
}

.navbar-logos {
  display: flex;
  align-items: center;
  gap: 0.625rem;
}

.navbar-logos img {
  height: 38px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 1px 3px rgba(0,0,0,0.12));
  transition: transform 0.3s ease;
}

.navbar-brand:hover .navbar-logos img { transform: scale(1.04); }

[data-theme="dark"] .navbar-logos img {
  filter: brightness(0.9) drop-shadow(0 0 6px rgba(59, 130, 246, 0.3));
}

.navbar-label {
  display: flex;
  flex-direction: column;
  line-height: 1.15;
  border-left: 1.5px solid var(--border-strong);
  padding-left: 0.875rem;
}

.navbar-label-title {
  font-weight: 800;
  font-size: 1.05rem;
  letter-spacing: -0.025em;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 60%, var(--accent) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.navbar-label-sub {
  font-size: 0.6rem;
  font-weight: 600;
  color: var(--text-subtle);
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

/* ── Nav links list ─────────────────────────────────────────────── */
.navbar-nav {
  display: flex;
  align-items: center;
  gap: 0.125rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.navbar-nav > li { position: relative; }

.nav-link {
  text-decoration: none;
  color: var(--text-muted);
  font-weight: 500;
  font-size: 0.875rem;
  padding: 0.5rem 0.875rem;
  border-radius: var(--radius-sm, 10px);
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  gap: 0.3rem;
  cursor: pointer;
  white-space: nowrap;
  position: relative;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: 4px;
  left: 50%;
  transform: translateX(-50%) scaleX(0);
  width: calc(100% - 1.75rem);
  height: 2px;
  background: linear-gradient(90deg, var(--primary-light), var(--accent));
  border-radius: 1px;
  transition: transform 0.22s ease;
}

.nav-link:hover,
.nav-link.active {
  color: var(--text);
  background: var(--primary-glow);
}

.nav-link:hover::after,
.nav-link.active::after {
  transform: translateX(-50%) scaleX(1);
}

/* ── Right actions ──────────────────────────────────────────────── */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Theme toggle */
.theme-toggle {
  width: 36px; height: 36px;
  border-radius: 10px;
  border: 1px solid var(--border-strong);
  background: var(--surface);
  color: var(--text-muted);
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  flex-shrink: 0;
}

.theme-toggle:hover {
  color: var(--primary-light);
  background: var(--primary-glow);
  border-color: var(--primary-light);
  transform: rotate(15deg);
}

/* Language button */
.lang-btn {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0 0.75rem;
  height: 36px;
  border-radius: 10px;
  border: 1px solid var(--border-strong);
  background: var(--surface);
  color: var(--text-muted);
  font-size: 0.75rem;
  font-weight: 700;
  font-family: inherit;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
}

.lang-btn:hover {
  color: var(--primary-light);
  background: var(--primary-glow);
  border-color: var(--primary-light);
}

/* Notifications */
.notif-btn {
  position: relative;
  width: 36px; height: 36px;
  border-radius: 10px;
  border: 1px solid var(--border-strong);
  background: var(--surface);
  color: var(--text-muted);
  display: flex; align-items: center; justify-content: center;
  text-decoration: none;
  transition: all 0.22s ease;
}

.notif-btn:hover {
  color: var(--primary-light);
  background: var(--primary-glow);
  border-color: var(--primary-light);
}

.notif-badge {
  position: absolute;
  top: -4px; right: -4px;
  width: 16px; height: 16px;
  background: var(--danger, #e11d48);
  color: #fff;
  font-size: 0.6rem;
  font-weight: 700;
  border-radius: 9999px;
  display: flex; align-items: center; justify-content: center;
}

/* User button */
.user-menu-wrap { position: relative; }

.user-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.35rem 0.875rem 0.35rem 0.5rem;
  border-radius: 10px;
  border: 1px solid var(--border-strong);
  background: var(--surface);
  color: var(--text);
  cursor: pointer;
  font-family: inherit;
  font-size: 0.85rem;
  font-weight: 600;
  transition: all 0.22s ease;
}

.user-btn:hover {
  border-color: var(--primary-light);
  background: var(--primary-glow);
}

.user-avatar-img {
  width: 24px; height: 24px;
  border-radius: 50%;
  object-fit: cover;
}

.user-avatar-icon {
  width: 22px; height: 22px;
  color: var(--text-muted);
}

.user-name {
  max-width: 100px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: 0.82rem;
  color: var(--text);
}

.chevron-icon {
  width: 13px; height: 13px;
  color: var(--text-subtle);
  transition: transform 0.2s ease;
}

.chevron-icon.rotated { transform: rotate(180deg); }

/* User dropdown */
.user-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 220px;
  background: var(--surface);
  border: 1px solid var(--border-strong);
  border-radius: var(--radius, 16px);
  box-shadow: var(--shadow-lg, 0 20px 48px rgba(0,0,0,0.08));
  overflow: hidden;
  z-index: 200;
}

.dropdown-header {
  padding: 1rem;
  border-bottom: 1px solid var(--border);
}

.dropdown-fullname {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--text);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-email {
  font-size: 0.75rem;
  color: var(--text-muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-top: 0.15rem;
}

.dropdown-role-badge {
  display: inline-flex;
  margin-top: 0.4rem;
  padding: 0.15rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: capitalize;
  background: var(--primary-glow);
  color: var(--primary-light);
  border: 1px solid rgba(37, 99, 235, 0.2);
}

.dropdown-nav {
  padding: 0.375rem;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.6rem 0.75rem;
  border-radius: var(--radius-sm, 10px);
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-muted);
  text-decoration: none;
  cursor: pointer;
  width: 100%;
  border: none;
  background: transparent;
  font-family: inherit;
  transition: all 0.18s ease;
  text-align: left;
}

.dropdown-item:hover {
  color: var(--primary-light);
  background: var(--primary-glow);
  transform: translateX(2px);
}

.dropdown-logout { color: var(--danger, #e11d48); }
.dropdown-logout:hover { color: var(--danger, #e11d48); background: rgba(225, 29, 72, 0.08); }

.dropdown-divider {
  height: 1px;
  background: var(--border);
  margin: 0.25rem 0;
}

/* CTA button (non-authenticated) */
.btn-cta {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1.125rem;
  height: 36px;
  border-radius: var(--radius-sm, 10px);
  background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent) 100%);
  color: #fff;
  font-size: 0.83rem;
  font-weight: 600;
  text-decoration: none;
  border: none;
  box-shadow: 0 4px 16px var(--primary-glow);
  transition: all 0.22s ease;
}

.btn-cta:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 28px var(--primary-glow);
  filter: brightness(1.1);
}

/* Mobile burger */
.menu-toggle {
  display: none;
  background: none;
  border: 1px solid var(--border-strong);
  color: var(--text-muted);
  cursor: pointer;
  padding: 0.4rem;
  border-radius: var(--radius-sm, 10px);
  transition: all 0.22s ease;
}

.menu-toggle:hover {
  background: var(--primary-glow);
  border-color: var(--primary-light);
  color: var(--primary-light);
}

/* SVG icon sizes */
.icon-svg    { width: 18px; height: 18px; }
.icon-svg-sm { width: 16px; height: 16px; }

/* ── Dropdown transition ─────────────────────────────────────────── */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.dropdown-fade-enter-from,
.dropdown-fade-leave-to     { opacity: 0; transform: translateY(-6px); }

/* ── Responsive ─────────────────────────────────────────────────── */
@media (max-width: 1024px) {
  .menu-toggle { display: flex; }

  .navbar-nav {
    display: none;
  }

  .navbar-nav.is-open {
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 70px; left: 0; right: 0;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-strong);
    padding: 1rem 1.5rem 1.5rem;
    gap: 0.25rem;
    box-shadow: var(--shadow-lg);
    z-index: 999;
  }

  .navbar-nav.is-open .nav-link { width: 100%; }

  .lang-btn span { display: none; }
}

@media (max-width: 768px) {
  .navbar-inner { padding: 0 1rem; }
  .navbar-label { display: none; }
  .user-name    { display: none; }
}
</style>
