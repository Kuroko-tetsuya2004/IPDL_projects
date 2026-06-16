<script setup>
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@layouts/PublicLayout.vue'
import { MapPinIcon, ArrowTopRightOnSquareIcon } from '@heroicons/vue/24/outline'

defineOptions({ layout: PublicLayout })

const props = defineProps({
  centres: Array
})
</script>

<template>
  <Head title="Centres Régionaux - UMMISCO" />

  <div class="py-12 md:py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 animate-fade-in">
    <!-- En-tête -->
    <div class="text-center mb-16">
      <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
        Centres Régionaux <span class="text-brand-600">UMMISCO</span>
      </h1>
      <p class="text-lg text-slate-600 max-w-3xl mx-auto leading-relaxed">
        Les activités de recherche et de formation de l'UMMISCO sont déployées au sein de cinq centres régionaux internationaux.
      </p>
    </div>

    <!-- Grille des centres -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div 
        v-for="centre in centres" 
        :key="centre.id"
        class="bg-white rounded-2xl p-8 border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group"
      >
        <div class="w-14 h-14 bg-brand-50 rounded-xl flex items-center justify-center mb-6 text-brand-600 group-hover:scale-110 transition-transform">
          <MapPinIcon class="w-7 h-7" />
        </div>
        
        <h2 class="text-xl font-bold text-slate-900 mb-3">{{ centre.nom }}</h2>
        
        <p class="text-slate-600 text-sm flex-grow mb-6">
          {{ centre.description_courte || "Centre de recherche régional collaborant avec les institutions locales pour promouvoir la modélisation et la simulation de systèmes complexes." }}
        </p>

        <a 
          v-if="centre.url" 
          :href="centre.url" 
          target="_blank"
          class="inline-flex items-center gap-2 text-brand-600 font-semibold hover:text-brand-700 transition-colors"
        >
          Visiter la page
          <ArrowTopRightOnSquareIcon class="w-4 h-4" />
        </a>
      </div>
    </div>
    
    <!-- Empty state -->
    <div v-if="centres.length === 0" class="text-center py-20">
      <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-400 mb-4">
        <MapPinIcon class="w-8 h-8" />
      </div>
      <h3 class="text-lg font-medium text-slate-900">Aucun centre disponible</h3>
      <p class="mt-1 text-slate-500">Les données n'ont pas encore été synchronisées depuis ummisco.fr</p>
    </div>
  </div>
</template>
