@extends('layouts.app')

@section('title', 'Milieux & Ressources vivantes — UMMISCO')

@section('content')
<div class="container" style="padding-top: 2rem;">
    {{-- Page Header --}}
    <div style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.03) 0%, rgba(37, 99, 235, 0.05) 100%); border-bottom: 2px solid var(--border); padding: 3rem 2rem; border-radius: var(--radius); margin-bottom: 3rem; text-align: center;">
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 1rem; color: var(--primary);">
            Milieux & Ressources vivantes
        </h1>
        <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 800px; margin: 0 auto;">
            Modéliser la dynamique des populations et l'exploitation rationnelle des ressources naturelles
        </p>
    </div>

    <div class="grid-2" style="margin-bottom: 4rem; align-items: center;">
        <div>
            <h2 style="font-size: 1.5rem; margin-bottom: 1rem; color: var(--primary);">
                Modélisation Écologique & Dynamique Forestière
            </h2>
            <p style="margin-bottom: 1.25rem; color: var(--text); font-weight: 500;">
                Nos travaux de modélisation visent à accompagner une transition écologique et économique durable dans les pays du Sud.
            </p>
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.7;">
                Nous étudions les dynamiques spatiales et démographiques des écosystèmes. Cela comprend l'évaluation du stress hydrique sur les cultures en Afrique sahélienne, le comportement des sols face aux pratiques agricoles intensives, et la résilience des massifs forestiers face à l'augmentation des températures globales et à la déforestation.
            </p>
        </div>
        <div class="card" style="border-left: 5px solid var(--accent);">
            <h3 style="font-size: 1.25rem; margin-bottom: 0.75rem; color: var(--primary);">Modèles Halieutiques & Dynamique des Pêches</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.7;">
                En collaboration étroite avec les instituts de recherche halieutique en Afrique de l'Ouest (ISRA/CRODT au Sénégal, IMROP en Mauritanie), UMMISCO développe des modèles mathématiques déterministes et stochastiques pour simuler l'effort de pêche, estimer les stocks de poissons, et proposer des scénarios de gestion durable et partagée des ressources de la zone économique exclusive (ZEE).
            </p>
        </div>
    </div>

    {{-- Méthodes et outils --}}
    <div style="margin-bottom: 4rem;">
        <h2 style="font-size: 1.5rem; margin-bottom: 2rem; text-align: center; color: var(--primary);">Nos Méthodes Scientifiques</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <div class="card">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📈</div>
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.5rem;">Systèmes Dynamiques</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted);">
                    Équations différentielles ordinaires et aux dérivées partielles pour étudier la viabilité des populations exploitées à long terme.
                </p>
            </div>
            <div class="card">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">📍</div>
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.5rem;">Modèles Spatiaux Continus</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted);">
                    Équations de réaction-diffusion pour représenter la dispersion des espèces et l'efficacité des aires marines protégées.
                </p>
            </div>
            <div class="card">
                <div style="font-size: 2rem; margin-bottom: 0.5rem;">🌾</div>
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.5rem;">Modèles Agro-écologiques</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted);">
                    Modèles de couplage sol-eau-plante pour optimiser les rendements agricoles tout en préservant l'intégrité environnementale locale.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
