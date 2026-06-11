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
  DocumentDuplicateIcon
} from '@heroicons/vue/24/outline'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const sidebarCollapsed = ref(false)

const adminLinks = computed(() => {
  const role = user.value?.role
  const all = [
    { href: '/dashboard',          icon: HomeIcon,                    label: 'Tableau de bord' },
    { href: '/mes-publications',   icon: DocumentTextIcon,            label: 'Mes publications' },
    { href: '/mes-datasets',       icon: CircleStackIcon,             label: 'Mes datasets' },
    { href: '/soumissions',        icon: ClipboardDocumentCheckIcon,  label: 'Soumissions', roles: ['axe_admin','super_admin'] },
    { href: '/admin/membres',      icon: UsersIcon,                   label: 'Membres',     roles: ['axe_admin','super_admin'] },
    { href: '/admin/datasets',     icon: CircleStackIcon,             label: 'Gérer les datasets', roles: ['axe_admin','super_admin'] },
    { href: '/admin/acl',          icon: ShieldCheckIcon,             label: 'Droits ACL',  roles: ['super_admin'] },
    { href: '/admin/statistiques', icon: ChartBarIcon,                label: 'Statistiques',roles: ['axe_admin','super_admin'] },
    { href: '/admin/parametres',   icon: Cog6ToothIcon,               label: 'Paramètres',  roles: ['super_admin'] },
    { href: '/admin/documents',     icon: DocumentDuplicateIcon,       label: 'Documents admin.', roles: ['super_admin'] },
    { href: '/',                   icon: GlobeAltIcon,                label: 'Portail public', isExternal: true },
  ]
  return all.filter(l => !l.roles || l.roles.includes(role))
})
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <NavBar />
    <div class="flex flex-1 pt-16">
      <!-- Sidebar -->
      <aside :class="['hidden md:flex flex-col border-r border-white/8 bg-surface-800/40 transition-all duration-300',
                      sidebarCollapsed ? 'w-16' : 'w-56']">
        <div class="flex-1 overflow-y-auto py-4 px-2">
          <nav class="space-y-1">
            <template v-for="link in adminLinks" :key="link.href">
              <a v-if="link.isExternal" :href="link.href"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-white/5 transition-all">
                <component :is="link.icon" class="w-5 h-5 shrink-0" />
                <span v-if="!sidebarCollapsed" class="truncate">{{ link.label }}</span>
              </a>
              <Link v-else :href="link.href"
                :class="['flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all',
                         $page.url.startsWith(link.href)
                           ? 'bg-brand-600/20 text-white border border-brand-500/30'
                           : 'text-slate-400 hover:text-white hover:bg-white/5']">
                <component :is="link.icon" class="w-5 h-5 shrink-0" />
                <span v-if="!sidebarCollapsed" class="truncate">{{ link.label }}</span>
              </Link>
            </template>
          </nav>
        </div>

        <!-- Toggle collapse -->
        <button @click="sidebarCollapsed = !sidebarCollapsed"
          class="p-3 border-t border-white/8 text-slate-500 hover:text-white transition-colors flex justify-center">
          <ChevronLeftIcon :class="['w-4 h-4 transition-transform', sidebarCollapsed && 'rotate-180']" />
        </button>
      </aside>

      <!-- Content -->
      <main class="flex-1 min-w-0 overflow-auto">
        <FlashMessages class="m-4" />
        <slot />
      </main>
    </div>
  </div>
</template>
