@extends('layouts.app')

@section('title', 'Présentation — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Section Hero Simplifiée / Header de Page --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--primary);">
            Présentation de l'Unité
        </h1>
        <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 800px; margin: 0 auto;">
            Unité de Modélisation Mathématique et Informatique des Systèmes Complexes (UMMISCO) — UMI 209
        </p>
    </div>

    <div class="grid-2" style="margin-bottom: 4rem;">
        {{-- Colonne Gauche : Qui sommes-nous --}}
        <div>
            <h2 style="font-size: 1.5rem; margin-bottom: 1rem; position: relative; padding-left: 1rem; display: flex; align-items: center;">
                <span style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--primary-light); border-radius: 2px;"></span>
                Qui sommes-nous ?
            </h2>
            <p style="margin-bottom: 1.25rem; color: var(--text); font-weight: 500;">
                L'UMMISCO est une Unité Mixte Internationale (UMI 209) qui regroupe des chercheurs issus d'institutions prestigieuses en France et dans plusieurs pays partenaires, notamment le Sénégal, le Maroc, le Cameroun et le Vietnam.
            </p>
            <p style="margin-bottom: 1.25rem; color: var(--text-muted); font-size: 0.95rem;">
                Notre recherche s'articule autour de la modélisation mathématique et informatique de systèmes complexes. Nous nous concentrons principalement sur les problématiques de santé (épidémiologie mathématique, aide à la décision en santé publique), de l'écologie (gestion des ressources marines et terrestres), et des dynamiques socio-environnementales.
            </p>
            <p style="color: var(--text-muted); font-size: 0.95rem;">
                En associant les mathématiques appliquées, l'informatique distribuée et l'intelligence artificielle, l'UMMISCO développe des outils innovants comme la plateforme de simulation multi-agents GAMA, reconnue internationalement.
            </p>
        </div>

        {{-- Colonne Droite : Tutelles et Partenaires --}}
        <div class="card" style="background: white; border-radius: var(--radius); display: flex; flex-direction: column; justify-content: center; gap: 1.5rem;">
            <h3 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--primary);">
                Tutelles & Partenariats Académiques
            </h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="padding: 1rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); text-align: center;">
                    <div style="font-weight: 800; font-size: 1.1rem; color: var(--primary-light);">CNRS</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);" class="mt-1">Centre National de la Recherche Scientifique (France)</div>
                </div>
                <div style="padding: 1rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); text-align: center;">
                    <div style="font-weight: 800; font-size: 1.1rem; color: var(--primary-light);">IRD</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);" class="mt-1">Institut de Recherche pour le Développement (France)</div>
                </div>
                <div style="padding: 1rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); text-align: center;">
                    <div style="font-weight: 800; font-size: 1.1rem; color: var(--primary-light);">Sorbonne</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);" class="mt-1">Sorbonne Université (Paris, France)</div>
                </div>
                <div style="padding: 1rem; border: 1.5px solid var(--border); border-radius: var(--radius-sm); text-align: center;">
                    <div style="font-weight: 800; font-size: 1.1rem; color: var(--primary-light);">UCAD</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);" class="mt-1">Université Cheikh Anta Diop (Dakar, Sénégal)</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Objectifs de l'unité --}}
    <div style="margin-bottom: 4rem;">
        <h2 style="font-size: 1.5rem; margin-bottom: 2rem; text-align: center;">Nos Objectifs Majeurs</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <div class="card" style="border-top: 4px solid var(--primary-light);">
                <div style="font-size: 2rem; margin-bottom: 1rem;">🎯</div>
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem;">Recherche Pluridisciplinaire</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">
                    Appliquer des outils mathématiques et informatiques avancés pour résoudre des problèmes concrets issus des sciences de la vie, de l'environnement et de la société.
                </p>
            </div>
            
            <div class="card" style="border-top: 4px solid var(--accent);">
                <div style="font-size: 2rem; margin-bottom: 1rem;">💻</div>
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem;">Outils de Simulation</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">
                    Concevoir des plateformes logicielles performantes comme GAMA pour permettre aux chercheurs et décideurs de simuler des scénarios complexes de façon intuitive.
                </p>
            </div>
            
            <div class="card" style="border-top: 4px solid var(--success);">
                <div style="font-size: 2rem; margin-bottom: 1rem;">🌍</div>
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.75rem;">Coopération Nord-Sud</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">
                    Promouvoir le co-développement de projets scientifiques en favorisant la mobilité des chercheurs et la co-tutelle de thèses de doctorat.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
