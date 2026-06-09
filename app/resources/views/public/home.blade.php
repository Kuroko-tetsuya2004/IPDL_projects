@extends('layouts.app')

@section('title', 'Portail UMMISCO — Accueil')
@section('description', 'Portail web institutionnel du laboratoire UMMISCO — Recherche en modélisation des systèmes complexes')

@section('content')
{{-- Hero Section --}}
<section style="position: relative; overflow: hidden; color: white; padding: 5rem 2rem; border-radius: var(--radius); margin: 0 1rem 3rem; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.25); min-height: 480px; display: flex; align-items: center;">
    
    <!-- Background Slide 1: Global network & systems -->
    <div class="hero-bg-slide active" style="position: absolute; inset: 0; background-image: url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1600&q=80'); background-size: cover; background-position: center; opacity: 1; transition: opacity 1.5s ease-in-out; z-index: 1;"></div>
    <!-- Background Slide 2: Math modeling / abstract -->
    <div class="hero-bg-slide" style="position: absolute; inset: 0; background-image: url('https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=1600&q=80'); background-size: cover; background-position: center; opacity: 0; transition: opacity 1.5s ease-in-out; z-index: 1;"></div>
    <!-- Background Slide 3: Epidemiology / scientific research -->
    <div class="hero-bg-slide" style="position: absolute; inset: 0; background-image: url('https://images.unsplash.com/photo-1576086213369-97a306d36557?auto=format&fit=crop&w=1600&q=80'); background-size: cover; background-position: center; opacity: 0; transition: opacity 1.5s ease-in-out; z-index: 1;"></div>
    <!-- Background Slide 4: Ecology / environmental modeling -->
    <div class="hero-bg-slide" style="position: absolute; inset: 0; background-image: url('https://images.unsplash.com/photo-1500485035595-cbe6f645feb1?auto=format&fit=crop&w=1600&q=80'); background-size: cover; background-position: center; opacity: 0; transition: opacity 1.5s ease-in-out; z-index: 1;"></div>

    <!-- Blue gradient overlay with semi-transparency -->
    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(30, 58, 138, 0.85) 50%, rgba(30, 58, 138, 0.78) 100%); z-index: 2;"></div>
    <div style="position: absolute; inset: 0; opacity: 0.04; background-image: radial-gradient(white 1px, transparent 1px); background-size: 24px 24px; pointer-events: none; z-index: 2;"></div>

    <div class="container hero-grid" style="display: grid; grid-template-columns: 1fr; gap: 3rem; align-items: center; max-width: 1200px; margin: 0 auto; position: relative; z-index: 3; width: 100%;">
        <!-- Left Side: Text and Buttons -->
        <div style="text-align: left;">
            <span style="font-size: 0.85rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.1em; background: rgba(255,255,255,0.12); padding: 0.35rem 0.85rem; border-radius: 100px; display: inline-block; margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
                Unité Mixte Internationale
            </span>
            <h1 style="font-size: 3.5rem; font-weight: 800; margin: 0 0 1rem; color: white; letter-spacing: -0.02em; line-height: 1.1; text-shadow: 0 2px 10px rgba(0,0,0,0.25);">UMMISCO</h1>
            <p style="font-size: 1.25rem; opacity: 0.95; font-weight: 500; line-height: 1.6; margin-bottom: 1.5rem; text-shadow: 0 1px 5px rgba(0,0,0,0.2);">
                Modélisation Mathématique et Informatique des Systèmes Complexes
            </p>
            <p style="font-size: 0.95rem; opacity: 0.85; font-weight: 500; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 0.5rem; text-shadow: 0 1px 3px rgba(0,0,0,0.25);">
                📍 Dakar, Sénégal · Partenariat CNRS / IRD / UCAD au service du développement durable.
            </p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="{{ route('publications') }}" class="btn" style="background: white; color: var(--color-primary-light); font-weight: 700; box-shadow: 0 10px 20px rgba(0,0,0,0.15); border-radius: 8px; padding: 0.75rem 1.5rem; transition: all 0.3s ease;">
                    📚 Découvrir nos publications
                </a>
                <a href="{{ route('axes') }}" class="btn" style="background: rgba(255,255,255,0.12); color: white; border: 1.5px solid rgba(255,255,255,0.25); font-weight: 600; border-radius: 8px; padding: 0.75rem 1.5rem; transition: all 0.3s ease; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
                    🔬 Axes de recherche
                </a>
            </div>
        </div>

        <!-- Right Side: Glassmorphic Mission Card -->
        <div style="width: 100%; display: flex; justify-content: center;">
            <div style="width: 100%; max-width: 420px; background: rgba(255, 255, 255, 0.07); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25); padding: 2rem; position: relative;">
                <!-- Glowing corner accent -->
                <div style="position: absolute; top: -1px; left: 10%; right: 10%; height: 1px; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);"></div>
                
                <h3 style="font-size: 1.25rem; font-weight: 800; color: white; margin-top: 0; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    💡 Rôle & Missions
                </h3>
                <p style="font-size: 0.9rem; line-height: 1.6; opacity: 0.9; margin-bottom: 1.5rem;">
                    UMMISCO contribue à la recherche scientifique de pointe par l'analyse et la modélisation mathématique et informatique de systèmes réels.
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <span style="font-size: 1.25rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));">🔬</span>
                        <div>
                            <h4 style="font-size: 0.85rem; font-weight: 700; margin: 0; color: white;">Modélisation Complexe</h4>
                            <p style="font-size: 0.75rem; margin: 0; opacity: 0.75; line-height: 1.4;">Conception de modèles de systèmes biologiques et sociaux.</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <span style="font-size: 1.25rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));">🏥</span>
                        <div>
                            <h4 style="font-size: 0.85rem; font-weight: 700; margin: 0; color: white;">Santé & Épidémiologie</h4>
                            <p style="font-size: 0.75rem; margin: 0; opacity: 0.75; line-height: 1.4;">Simulation et aide à la décision pour le contrôle de maladies.</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <span style="font-size: 1.25rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));">🌿</span>
                        <div>
                            <h4 style="font-size: 0.85rem; font-weight: 700; margin: 0; color: white;">Écologie & Ressources</h4>
                            <p style="font-size: 0.75rem; margin: 0; opacity: 0.75; line-height: 1.4;">Gestion durable des milieux face au changement climatique.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Active topic badge at the bottom of the card -->
                <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center;">
                    <span id="carousel-topic-title" style="font-size: 0.75rem; font-weight: 700; color: #a5f3fc; transition: opacity 0.3s ease; text-transform: uppercase; letter-spacing: 0.05em;">
                        Modélisation Globale
                    </span>
                    <!-- Miniature Carousel Indicators -->
                    <div style="display: flex; gap: 0.35rem;">
                        <span class="carousel-dot active" style="width: 6px; height: 6px; border-radius: 50%; background: white; opacity: 1; transition: all 0.3s; cursor: pointer;" onclick="setSlide(0)"></span>
                        <span class="carousel-dot" style="width: 6px; height: 6px; border-radius: 50%; background: white; opacity: 0.4; transition: all 0.3s; cursor: pointer;" onclick="setSlide(1)"></span>
                        <span class="carousel-dot" style="width: 6px; height: 6px; border-radius: 50%; background: white; opacity: 0.4; transition: all 0.3s; cursor: pointer;" onclick="setSlide(2)"></span>
                        <span class="carousel-dot" style="width: 6px; height: 6px; border-radius: 50%; background: white; opacity: 0.4; transition: all 0.3s; cursor: pointer;" onclick="setSlide(3)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Carousel Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-bg-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        const topicTitle = document.getElementById('carousel-topic-title');
        const totalSlides = slides.length;

        const slideTitles = [
            "Modélisation Globale",
            "IA & Mathématiques",
            "Épidémiologie Numérique",
            "Écologie & Ressources"
        ];

        function showSlide(index) {
            slides.forEach((slide, i) => {
                if (i === index) {
                    slide.style.opacity = '1';
                    slide.classList.add('active');
                    if (dots[i]) {
                        dots[i].style.opacity = '1';
                        dots[i].style.transform = 'scale(1.2)';
                    }
                } else {
                    slide.style.opacity = '0';
                    slide.classList.remove('active');
                    if (dots[i]) {
                        dots[i].style.opacity = '0.4';
                        dots[i].style.transform = 'scale(1)';
                    }
                }
            });
            
            // Fade transition for topic title text
            if (topicTitle) {
                topicTitle.style.opacity = '0';
                setTimeout(() => {
                    topicTitle.textContent = slideTitles[index];
                    topicTitle.style.opacity = '1';
                }, 300);
            }
            
            currentSlide = index;
        }

        window.setSlide = function(index) {
            showSlide(index);
        };

        function nextSlide() {
            let next = (currentSlide + 1) % totalSlides;
            showSlide(next);
        }

        // Auto transition every 5.5 seconds
        setInterval(nextSlide, 5500);
    });
</script>

<!-- Responsive layout adjustments -->
<style>
    @media (min-width: 992px) {
        .hero-grid {
            grid-template-columns: 1.2fr 0.8fr !important;
        }
    }
</style>

{{-- Statistiques --}}
@if($stats)
<section style="padding: 1.5rem 0; margin-bottom: 3rem;">
    <div class="container">
        <div class="card" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 2rem; text-align: center; background: white; border-radius: var(--radius);">
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-primary-light);">{{ $stats->total_publications ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Publications</div>
            </div>
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-success);">{{ $stats->total_datasets ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Datasets</div>
            </div>
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-accent);">{{ $stats->total_chercheurs ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Chercheurs</div>
            </div>
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-warning);">{{ $stats->total_doctorants ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Doctorants</div>
            </div>
            <div>
                <div style="font-size: 2.25rem; font-weight: 800; color: #6366f1;">{{ $stats->total_axes ?? 0 }}</div>
                <div class="text-muted" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Axes thématiques</div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Axes de recherche --}}
<section style="padding: 2rem 0 3rem;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary);">🔬 Axes de recherche</h2>
            <a href="{{ route('axes') }}" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Voir tous les axes →</a>
        </div>
        <div class="grid-3">
            @foreach($axes as $axe)
            <div class="card" style="border-top: 4px solid {{ $axe->couleur_hex ?? '#1E3A8A' }}; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: {{ $axe->couleur_hex ?? 'var(--color-primary)' }}; margin-bottom: 0.25rem;">
                        {{ $axe->nom_fr }}
                    </h3>
                    @if($axe->nom_en)
                        <p class="text-muted" style="font-size: 0.8rem; font-style: italic; margin-bottom: 0.75rem;">{{ $axe->nom_en }}</p>
                    @endif
                    <p style="font-size: 0.875rem; color: var(--color-text-muted); line-height: 1.6;">
                        {{ Str::limit($axe->description_fr, 140) }}
                    </p>
                </div>
                <div style="margin-top: 1.25rem; display: flex; justify-content: space-between; align-items: center;">
                    <span class="badge" style="background: {{ ($axe->couleur_hex ?? '#2563eb') }}10; color: {{ $axe->couleur_hex ?? '#2563eb' }}; font-weight: 700;">{{ $axe->code }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Publications récentes --}}
@if($recentPublications->count() > 0)
<section style="padding: 3rem 0; background: rgba(30, 58, 138, 0.02); border-top: 1px solid var(--color-border); border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary);">📚 Publications récentes</h2>
            <a href="{{ route('publications') }}" class="btn btn-outline" style="font-size: 0.85rem; padding: 0.5rem 1rem;">Voir toutes les publications →</a>
        </div>
        <div class="grid-3">
            @foreach($recentPublications as $pub)
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; background: white;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        @php
                        $singularTypes = [
                            'article' => 'Article',
                            'document' => 'Recherche en cours',
                            'event' => 'Événement',
                            'dataset' => 'Dataset',
                            'news' => 'Actualité',
                            'thesis' => 'Thèse',
                            'report' => 'Rapport',
                            'presentation' => 'Présentation'
                        ];
                        @endphp
                        <span class="badge badge-primary">{{ $singularTypes[$pub->type] ?? ucfirst($pub->type) }}</span>
                        @if($pub->axe)
                            <span class="badge" style="background: {{ $pub->axe->couleur_hex ?? '#2563eb' }}10; color: {{ $pub->axe->couleur_hex ?? '#2563eb' }}; border: 1px solid {{ $pub->axe->couleur_hex ?? '#2563eb' }}20;">
                                {{ $pub->axe->code }}
                            </span>
                        @endif
                    </div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-text); margin-bottom: 0.5rem; line-height: 1.4;">{{ Str::limit($pub->titre_fr, 80) }}</h3>
                    <p class="text-muted" style="font-size: 0.85rem; line-height: 1.6; margin-bottom: 1rem;">
                        {{ Str::limit($pub->resume_fr, 110) }}
                    </p>
                </div>
                <div style="border-top: 1px solid var(--color-border); padding-top: 0.75rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.775rem; color: var(--color-text-muted);">
                    <div style="font-weight: 600;">
                        👤 {{ $pub->auteur->prenom ?? '' }} {{ $pub->auteur->nom ?? '' }}
                    </div>
                    @if($pub->date_publication)
                        <div>{{ $pub->date_publication->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
