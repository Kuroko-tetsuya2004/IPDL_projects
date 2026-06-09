<script setup>
import NavBar from '@components/Shared/NavBar.vue'
import AppFooter from '@components/Shared/AppFooter.vue'
import FlashMessages from '@components/Shared/FlashMessages.vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
</script>

<template>
  <div class="min-h-screen flex flex-col">
    <NavBar />
    <FlashMessages />
    <main class="flex-1 pt-16">
      <slot />
    </main>
    <AppFooter />
  </div>
</template>
