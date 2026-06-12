import './bootstrap'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp, Head, Link } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from 'ziggy-js'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

// ── NProgress sur les transitions Inertia ────────────────────────────────────
import { router } from '@inertiajs/vue3'
NProgress.configure({ showSpinner: false, minimum: 0.1 })
router.on('start',  () => NProgress.start())
router.on('finish', (event) => {
  if (event.detail.visit.completed) NProgress.done()
  else if (event.detail.visit.interrupted) NProgress.set(1)
  else if (event.detail.visit.cancelled) { NProgress.done(); NProgress.remove() }
})

// ── Application Inertia (PUBLIC) ──────────────────────────────────────────────
createInertiaApp({
  title: (title) => title ? `${title} — Portail UMMISCO` : 'Portail UMMISCO',
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/{PublicPortal,Search,Auth}/**/*.vue')),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .component('Head', Head)
      .component('Link', Link)
      .mount(el)
  },
  progress: false,
})
