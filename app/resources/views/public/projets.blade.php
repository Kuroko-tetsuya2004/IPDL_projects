@extends('layouts.app')

@section('title', 'Projets en cours')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 border-b pb-4">Projets de recherche en cours</h1>
        <p class="mt-4 text-gray-600">Découvrez les projets de recherche actuellement menés par l'UMMISCO, en collaboration avec nos partenaires internationaux.</p>
    </div>

    @if($projets->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
            Aucun projet en cours n'est disponible pour le moment.
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projets as $projet)
                <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-blue-700 mb-3">{{ $projet->titre }}</h2>
                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($projet->description, 150) }}</p>
                        
                        @if($projet->url_externe)
                            <a href="{{ $projet->url_externe }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                                En savoir plus
                                <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
