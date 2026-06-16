<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import NavBar from '@components/Shared/NavBar.vue'
import FlashMessages from '@components/Shared/FlashMessages.vue'
import {
  HomeIcon, DocumentTextIcon, CircleStackIcon,
  UsersIcon, Cog6ToothIcon, ChartBarIcon,
  ShieldCheckIcon, BellIcon, ClipboardDocumentCheckIcon,
  ArrowRightOnRectangleIcon, ChevronLeftIcon, GlobeAltIcon,
  DocumentDuplicateIcon, CloudArrowDownIcon
} from '@heroicons/vue/24/outline'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const sidebarCollapsed = ref(false)

const roleLabel = {
  visitor:          'Visiteur',
  researcher:       'Chercheur',
  doctoral_student: 'Doctorant',
  partner:          'Partenaire',
  axe_admin:        'Admin d\'axe',
  super_admin:      'Super Admin',
}

const roleColor = {
  super_admin:      '#e11d48',
  axe_admin:        '#7c3aed',
  researcher:       '#2563eb',
  doctoral_student: '#0891b2',
  partner:          '#059669',
  visitor:          '#64748b',
}

const userColor = computed(() => roleColor[user.value?.role] ?? '#2563eb')

const adminLinks = computed(() => {
  const role = user.value?.role
  const all = [
    { href: '/dashboard',          icon: HomeIcon,                    label: 'Tableau de bord',    section: 'main' },
    { href: '/mes-publications',   icon: DocumentTextIcon,            label: 'Mes publications',    section: 'main' },
    { href: '/mes-datasets',       icon: CircleStackIcon,             label: 'Mes datasets',        section: 'main' },
    { href: '/soumissions',        icon: ClipboardDocumentCheckIcon,  label: 'Soumissions',         section: 'admin', roles: ['axe_admin','super_admin'] },
    { href: '/admin/membres',      icon: UsersIcon,                   label: 'Membres',             section: 'admin', roles: ['axe_admin','super_admin'] },
    { href: '/admin/datasets',     icon: CircleStackIcon,             label: 'Gérer les datasets',  section: 'admin', roles: ['axe_admin','super_admin'] },
    { href: '/admin/acl',          icon: ShieldCheckIcon,             label: 'Droits ACL',          section: 'admin', roles: ['super_admin'] },
    { href: '/admin/statistiques', icon: ChartBarIcon,                label: 'Statistiques',        section: 'admin', roles: ['axe_admin','super_admin'] },
    { href: '/admin/parametres',   icon: Cog6ToothIcon,               label: 'Paramètres',          section: 'admin', roles: ['super_admin'] },
    { href: '/admin/documents',    icon: DocumentDuplicateIcon,       label: 'Documents admin.',    section: 'admin', roles: ['super_admin'] },
    { href: '/',                   icon: GlobeAltIcon,                label: 'Portail public',      section: 'portal', isExternal: true },
  ]
  return all.filter(l => !l.roles || l.roles.includes(role))
})

const mainLinks       = computed(() => adminLinks.value.filter(l => l.section === 'main'))
const adminOnlyLinks  = computed(() => adminLinks.value.filter(l => l.section === 'admin'))
const portalLinks     = computed(() => adminLinks.value.filter(l => l.section === 'portal'))

function isActive(href) {
  return page.url === href || (href !== '/' && page.url.startsWith(href))
}
</script>

<template>
  <div class="dash-root">

    <!-- ── Top Navbar (aligned with public portal) ── -->
    <NavBar />

    <div class="dash-body">

      <!-- ═══ SIDEBAR ═══ -->
      <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">

        <!-- Top gradient stripe — same as portal hero -->
        <div class="sidebar-stripe"></div>

        <!-- User card -->
        <div class="sidebar-user" v-if="!sidebarCollapsed">
          <div
            class="sidebar-avatar"
            :style="{ background: `${userColor}20`, border: `1.5px solid ${userColor}40`, color: userColor }"
          >
            {{ (user?.prenom?.[0] ?? '') }}{{ (user?.nom?.[0] ?? '') }}
          </div>
          <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ user?.prenom }} {{ user?.nom }}</div>
            <span
              class="sidebar-role-badge"
              :style="{ background: `${userColor}15`, color: userColor, border: `1px solid ${userColor}30` }"
            >
              {{ roleLabel[user?.role] ?? user?.role }}
            </span>
          </div>
        </div>

        <!-- Collapsed avatar -->
        <div class="sidebar-user-collapsed" v-else>
          <div
            class="sidebar-avatar-sm"
            :style="{ background: `${userColor}20`, border: `1.5px solid ${userColor}40`, color: userColor }"
          >
            {{ (user?.prenom?.[0] ?? '') }}{{ (user?.nom?.[0] ?? '') }}
          </div>
        </div>

        <!-- Nav links -->
        <div class="sidebar-nav">

          <!-- Main links -->
          <div class="sidebar-section">
            <div v-if="!sidebarCollapsed" class="sidebar-section-label">Espace personnel</div>
            <Link
              v-for="link in mainLinks"
              :key="link.href"
              :href="link.href"
              class="sidebar-link"
              :class="{ 'sidebar-link--active': isActive(link.href) }"
            >
              <component :is="link.icon" class="sidebar-icon" />
              <span v-if="!sidebarCollapsed" class="sidebar-link-label">{{ link.label }}</span>
            </Link>
          </div>

          <!-- Admin links -->
          <div class="sidebar-section" v-if="adminOnlyLinks.length > 0">
            <div class="sidebar-divider"></div>
            <div v-if="!sidebarCollapsed" class="sidebar-section-label">Administration</div>
            <Link
              v-for="link in adminOnlyLinks"
              :key="link.href"
              :href="link.href"
              class="sidebar-link"
              :class="{ 'sidebar-link--active': isActive(link.href) }"
            >
              <component :is="link.icon" class="sidebar-icon" />
              <span v-if="!sidebarCollapsed" class="sidebar-link-label">{{ link.label }}</span>
            </Link>
          </div>

        </div>

        <!-- Footer: portal link + collapse toggle -->
        <div class="sidebar-footer">
          <a
            v-for="link in portalLinks"
            :key="link.href"
            :href="link.href"
            class="sidebar-link sidebar-portal-link"
          >
            <component :is="link.icon" class="sidebar-icon" />
            <span v-if="!sidebarCollapsed" class="sidebar-link-label">{{ link.label }}</span>
          </a>

          <button class="sidebar-collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
            <ChevronLeftIcon :style="{ width:'14px', height:'14px', transition:'transform 0.3s', transform: sidebarCollapsed ? 'rotate(180deg)' : 'rotate(0deg)' }" />
          </button>
        </div>

      </aside>

      <!-- ═══ MAIN CONTENT ═══ -->
      <main class="dash-main">
        <FlashMessages class="dash-flash" />
        <slot />
      </main>

    </div>
  </div>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════════════════
   DashboardLayout — aligned with public portal design system
   CSS custom-properties defined in dashboard.blade.php
   ═══════════════════════════════════════════════════════════════════ */

.dash-root {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg);
  color: var(--text);
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.dash-body {
  display: flex;
  flex: 1;
  padding-top: 70px; /* navbar height */
}

/* ── Sidebar ────────────────────────────────────────────────────── */
.sidebar {
  width: 240px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  background: var(--sidebar-bg);
  border-right: 1px solid var(--sidebar-border);
  position: sticky;
  top: 70px;
  height: calc(100vh - 70px);
  overflow: hidden;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar.collapsed { width: 64px; }

/* Top stripe (gradient like portal hero) */
.sidebar-stripe {
  height: 3px;
  background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 60%, var(--accent) 100%);
  flex-shrink: 0;
}

/* User card */
.sidebar-user {
  padding: 1.125rem 1rem 0.875rem;
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sidebar-avatar {
  width: 36px; height: 36px;
  border-radius: 50%;
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.72rem; font-weight: 800;
}

.sidebar-user-info { min-width: 0; overflow: hidden; }

.sidebar-user-name {
  font-size: 0.8rem;
  font-weight: 700;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: var(--text);
}

.sidebar-role-badge {
  display: inline-flex;
  padding: 0.1rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.62rem;
  font-weight: 700;
  margin-top: 0.2rem;
}

.sidebar-user-collapsed {
  padding: 0.875rem 0;
  display: flex;
  justify-content: center;
  border-bottom: 1px solid var(--border);
}

.sidebar-avatar-sm {
  width: 32px; height: 32px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.65rem; font-weight: 800;
}

/* Nav */
.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem 0.5rem;
}

.sidebar-section { margin-bottom: 0.25rem; }

.sidebar-section-label {
  font-size: 0.6rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--text-subtle);
  padding: 0.625rem 0.75rem 0.375rem;
}

.sidebar-divider {
  height: 1px;
  background: var(--border);
  margin: 0.5rem 0;
}

/* Sidebar link */
.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.55rem 0.75rem;
  border-radius: 12px;
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--text-muted);
  text-decoration: none;
  margin-bottom: 2px;
  border-left: 3px solid transparent;
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-link:hover {
  background: var(--bg-tertiary);
  color: var(--text);
}

.sidebar-link--active {
  background: var(--primary-glow) !important;
  color: var(--primary-light) !important;
  border-left-color: var(--primary-light) !important;
}

.sidebar-portal-link {
  color: var(--text-muted);
  margin-bottom: 0.375rem;
}

.sidebar-portal-link:hover {
  background: var(--primary-glow);
  color: var(--primary-light);
}

.sidebar-icon {
  width: 17px; height: 17px;
  flex-shrink: 0;
}

.sidebar-link-label {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Footer */
.sidebar-footer {
  border-top: 1px solid var(--border);
  padding: 0.5rem;
  flex-shrink: 0;
}

.sidebar-collapse-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  border-radius: 10px;
  border: 1px solid var(--border);
  background: transparent;
  cursor: pointer;
  color: var(--text-subtle);
  transition: all 0.22s ease;
  margin-top: 0.25rem;
}

.sidebar-collapse-btn:hover {
  background: var(--bg-tertiary);
  color: var(--text);
  border-color: var(--border-strong);
}

/* ── Main content ───────────────────────────────────────────────── */
.dash-main {
  flex: 1;
  min-width: 0;
  overflow: auto;
  background: var(--bg);
}

.dash-flash { margin: 1rem; }

/* ── Responsive ─────────────────────────────────────────────────── */
@media (max-width: 768px) {
  .sidebar { display: none; }
}
</style>
