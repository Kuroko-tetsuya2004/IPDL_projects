<!DOCTYPE html>
<html lang="{{ current_locale() }}" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portail UMMISCO')</title>
    <meta name="description" content="@yield('description', 'Portail web institutionnel du laboratoire UMMISCO — CNRS/IRD/UCAD')">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── Design System UMMISCO — Premium Light Mode ───────────────────────────── */
        :root {
            --color-primary: #1e3a8a;
            --color-primary-light: #2563eb;
            --color-accent: #0ea5e9;
            --color-success: #0d9488;
            --color-warning: #d97706;
            --color-danger: #e11d48;
            --color-bg: #f8fafc;
            --color-surface: #ffffff;
            --color-text: #0f172a;
            --color-text-muted: #64748b;
            --color-border: #f1f5f9;
            --color-border-hover: #e2e8f0;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -2px rgba(15, 23, 42, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(15, 23, 42, 0.03), 0 4px 6px -4px rgba(15, 23, 42, 0.03), 0 0 40px rgba(37, 99, 235, 0.03);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            color: var(--color-primary);
            font-weight: 700;
        }

        .navbar-container {
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: center;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 30px rgba(15, 23, 42, 0.03), inset 0 1px 0 rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            max-width: 1280px;
            width: 100%;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            transition: var(--transition);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.4rem;
            font-family: 'Outfit', sans-serif;
            color: var(--color-primary);
        }

        .navbar-brand span {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 50%, var(--color-accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.03em;
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .navbar-nav > li { position: relative; }

        .nav-link {
            text-decoration: none;
            color: var(--color-text-muted);
            font-weight: 600;
            font-size: 0.925rem;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.25rem;
            cursor: pointer;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--color-primary-light);
            background: rgba(37, 99, 235, 0.05);
        }

        .nav-link svg.chevron {
            transition: transform 0.2s ease;
            width: 14px;
            height: 14px;
            stroke-width: 2.5;
        }

        .nav-link:hover svg.chevron { transform: translateY(1px); }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%) translateY(12px);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: var(--radius-sm);
            box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
            padding: 0.5rem;
            min-width: 230px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1010;
        }

        .dropdown-menu::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            width: 10px;
            height: 10px;
            background: #ffffff;
            border-left: 1px solid rgba(15, 23, 42, 0.06);
            border-top: 1px solid rgba(15, 23, 42, 0.06);
        }

        .navbar-nav > li:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateX(-50%) translateY(4px);
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--color-text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.6rem 1rem;
            border-radius: 8px;
            transition: var(--transition);
        }

        .dropdown-menu a:hover, .dropdown-menu a.active {
            color: var(--color-primary-light);
            background: rgba(37, 99, 235, 0.05);
            transform: translateX(2px);
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.25rem;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
            opacity: 0.95;
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--color-border-hover);
            color: var(--color-text);
        }

        .btn-outline:hover {
            border-color: var(--color-primary-light);
            color: var(--color-primary-light);
            background: rgba(37, 99, 235, 0.02);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-primary { background: rgba(37, 99, 235, 0.08); color: var(--color-primary-light); }
        .badge-success { background: rgba(13, 148, 136, 0.08); color: var(--color-success); }
        .badge-warning { background: rgba(217, 119, 6, 0.08); color: var(--color-warning); }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--color-text);
            cursor: pointer;
            padding: 0.25rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            width: 100%;
        }

        main { flex: 1; padding: 1.5rem 0 4rem; }

        .card {
            background: var(--color-surface);
            border-radius: var(--radius);
            border: 1px solid var(--color-border);
            padding: 1.75rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--color-border-hover);
            transform: translateY(-2px);
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid transparent;
        }

        .alert-success { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .alert-error   { background: #fef2f2; color: #991b1b; border-color: #fecaca; }

        .form-group { margin-bottom: 1.5rem; }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            color: var(--color-text);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--color-border-hover);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-family: inherit;
            font-weight: 500;
            transition: var(--transition);
            background: var(--color-surface);
            color: var(--color-text);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primary-light);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        textarea.form-control { min-height: 140px; resize: vertical; }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 14px 10px;
            padding-right: 2.5rem;
        }

        .text-muted { color: var(--color-text-muted); }
        .text-center { text-align: center; }
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mb-2 { margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 1.5rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 1.5rem; }

        .footer {
            background: var(--color-surface);
            border-top: 1px solid var(--color-border);
            color: var(--color-text-muted);
            padding: 4rem 2rem 2.5rem;
            margin-top: auto;
        }

        .footer a {
            color: var(--color-text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .footer a:hover { color: var(--color-primary-light); }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 3rem;
        }

        .footer-section { flex: 1; min-width: 250px; }

        .footer-section h4 {
            color: var(--color-primary);
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .footer-section p,
        .footer-section li {
            font-size: 0.875rem;
            line-height: 1.8;
            margin-bottom: 0.5rem;
        }

        .footer-section ul { list-style: none; }

        .footer-bottom {
            max-width: 1200px;
            margin: 3rem auto 0;
            padding-top: 1.5rem;
            border-top: 1px solid var(--color-border);
            text-align: center;
            font-size: 0.8rem;
        }

        @media (max-width: 992px) {
            .menu-toggle { display: block; }

            .navbar-nav {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 76px;
                left: 1.5rem;
                right: 1.5rem;
                background: #ffffff;
                border: 1px solid var(--color-border);
                border-radius: var(--radius-sm);
                box-shadow: var(--shadow-lg);
                padding: 1rem;
                gap: 0.5rem;
                align-items: stretch;
                z-index: 999;
            }

            .navbar-nav.active { display: flex; }

            .navbar-nav > li:hover .dropdown-menu {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                position: static;
                transform: none;
                box-shadow: none;
                border: none;
                background: rgba(15, 23, 42, 0.02);
                border-left: 2px solid var(--color-primary-light);
                border-radius: 0;
                margin-left: 1rem;
                margin-top: 0.25rem;
                padding-left: 0.5rem;
            }

            .dropdown-menu::before { display: none; }
            .grid-2 { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .navbar-container { padding: 0.5rem 1rem; }
            .container { padding: 0 1rem; }
            .grid-3 { grid-template-columns: 1fr; }
            .footer-content { flex-direction: column; }
        }

        .btn-dashboard-premium {
            background: linear-gradient(135deg, var(--color-primary-light) 0%, var(--color-accent) 100%);
            color: white !important;
            padding: 0.5rem 1.25rem;
            font-size: 0.8rem;
            font-weight: 700;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            height: 36px;
            border: none;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-dashboard-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            filter: brightness(1.05);
        }

        .btn-dashboard-premium svg { transition: transform 0.25s ease; }
        .btn-dashboard-premium:hover svg { transform: rotate(15deg) scale(1.1); }
    </style>
    @yield('styles')
</head>
<body>
    {{-- Navigation --}}
    <div class="navbar-container">
        <nav class="navbar">
            <a href="{{ route('home') }}" class="navbar-brand">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="{{ asset('images/logo_ummisco.webp') }}" alt="UMMISCO" style="height: 42px; width: auto; object-fit: contain;">
                    <img src="{{ asset('images/logo_ucad.webp') }}" alt="UCAD" style="height: 38px; width: auto; object-fit: contain; margin-left: 0.25rem;">
                </div>
                <div style="display: flex; flex-direction: column; line-height: 1.15; margin-left: 0.5rem;">
                    <span style="font-weight: 800; font-size: 1.15rem; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-light) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">UMMISCO</span>
                    <span style="font-weight: 500; font-size: 0.65rem; color: var(--color-text-muted); letter-spacing: 0.05em; text-transform: uppercase;">Portail public</span>
                </div>
            </a>

            <ul class="navbar-nav" id="navbarNav">
                <li>
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Accueil</a>
                </li>
                <li class="has-dropdown">
                    <span class="nav-link {{ request()->routeIs('unite.*') ? 'active' : '' }}">
                        Unité
                        <svg class="chevron" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                    <div class="dropdown-menu">
                        <a href="{{ route('unite.presentation') }}" class="{{ request()->routeIs('unite.presentation') ? 'active' : '' }}">Présentation</a>
                        <a href="{{ route('unite.priorites') }}" class="{{ request()->routeIs('unite.priorites') ? 'active' : '' }}">Priorités scientifiques</a>
                        <a href="{{ route('unite.membres') }}" class="{{ request()->routeIs('unite.membres') ? 'active' : '' }}">Membres</a>
                    </div>
                </li>
                <li class="has-dropdown">
                    <span class="nav-link {{ request()->routeIs('recherches.*') ? 'active' : '' }}">
                        Recherches
                        <svg class="chevron" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                    <div class="dropdown-menu">
                        <a href="{{ route('recherches.modelisation') }}" class="{{ request()->routeIs('recherches.modelisation') ? 'active' : '' }}">Modélisation à base d'agents</a>
                        <a href="{{ route('recherches.milieux') }}" class="{{ request()->routeIs('recherches.milieux') ? 'active' : '' }}">Milieux & Ressources vivantes</a>
                    </div>
                </li>
                <li class="has-dropdown">
                    <span class="nav-link {{ request()->routeIs('productions.*') || request()->routeIs('publications*') ? 'active' : '' }}">
                        Productions
                        <svg class="chevron" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" fill="none"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                    <div class="dropdown-menu">
                        <a href="{{ route('publications') }}" class="{{ request()->routeIs('publications') ? 'active' : '' }}">Publications</a>
                        <a href="{{ route('productions.presentations') }}" class="{{ request()->routeIs('productions.presentations') ? 'active' : '' }}">Présentations</a>
                        <a href="{{ route('productions.autres') }}" class="{{ request()->routeIs('productions.autres') ? 'active' : '' }}">Autres productions</a>
                    </div>
                </li>
                <li>
                    <a href="{{ route('actualites') }}" class="nav-link {{ request()->routeIs('actualites') ? 'active' : '' }}">Actualités</a>
                </li>
                <li>
                    <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                </li>
            </ul>

            <div class="navbar-actions">
                @if(is_authenticated())
                    <span class="badge badge-primary" style="margin-right: 0.5rem;">{{ session('user_name') }}</span>
                    <a href="{{ route('dashboard') }}" class="btn-dashboard-premium">
                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                            <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                            <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                            <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                        </svg>
                        Mon Espace
                    </a>
                    {{-- ✅ CORRIGÉ — formulaire sans @csrf car /auth/logout est exclu du CSRF --}}
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        <button type="submit" class="btn btn-outline" style="padding:0.4rem 0.85rem; font-size:0.8rem; border-radius:8px; height:36px; margin-left: 0.5rem;">
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-dashboard-premium">
                        Connexion
                    </a>
                @endif

                <button class="menu-toggle" id="menuToggle">
                    <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
        </nav>
    </div>

    {{-- Alerts --}}
    <div class="container" style="margin-top: 1rem;">
        @if(session('success'))
            <div class="alert alert-success">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Contenu principal --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4>UMMISCO</h4>
                <p style="font-weight: 500; color: var(--color-text);">Unité Mixte Internationale de Modélisation Mathématique et Informatique des Systèmes Complexes</p>
                <p class="mt-1" style="font-size: 0.8rem;">CNRS / IRD / UCAD — Dakar, Sénégal</p>
            </div>
            <div class="footer-section" style="padding-left: 2rem;">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="{{ route('home') }}">Accueil</a></li>
                    <li><a href="{{ route('unite.presentation') }}">Présentation</a></li>
                    <li><a href="{{ route('publications') }}">Publications</a></li>
                    <li><a href="{{ route('axes') }}">Axes de recherche</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contact & Secrétariat</h4>
                <p style="font-weight: 600; color: var(--color-text);">admin@ummisco.ucad.sn</p>
                <p>Université Cheikh Anta Diop (UCAD)</p>
                <p>BP 5005, Dakar-Fann, Sénégal</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} UMMISCO. Tous droits réservés. Conçu avec excellence pour la recherche scientifique.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const navbarNav = document.getElementById('navbarNav');

            if (menuToggle && navbarNav) {
                menuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navbarNav.classList.toggle('active');
                });

                document.addEventListener('click', function() {
                    navbarNav.classList.remove('active');
                });

                navbarNav.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
