<!DOCTYPE html>
<html lang="{{ current_locale() }}" dir="ltr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portail UMMISCO')</title>
    <meta name="description" content="@yield('description', 'Portail web institutionnel du laboratoire UMMISCO — CNRS/IRD/UCAD')">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════════════════════
           UMMISCO — WORLD-CLASS DESIGN SYSTEM v3
           Dark / Light Mode + Micro-animations + Premium Typographie
        ═══════════════════════════════════════════════════════════════════ */

        /* ── Light Mode Variables ─────────────────────────────────────── */
        :root,
        [data-theme="light"] {
            --bg:            #f5f7fa;
            --bg-secondary:  #ffffff;
            --bg-tertiary:   #eef2f7;
            --surface:       #ffffff;
            --surface-alt:   #f0f4ff;
            --border:        rgba(30, 58, 138, 0.08);
            --border-strong: rgba(30, 58, 138, 0.15);
            --text:          #0a0f1e;
            --text-muted:    #5a6785;
            --text-subtle:   #8896b3;

            --primary:       #1e3a8a;
            --primary-light: #2563eb;
            --primary-glow:  rgba(37, 99, 235, 0.18);
            --accent:        #0ea5e9;
            --accent-glow:   rgba(14, 165, 233, 0.18);
            --success:       #059669;
            --warning:       #d97706;
            --danger:        #e11d48;
            --purple:        #7c3aed;

            --navbar-bg:     rgba(255, 255, 255, 0.82);
            --navbar-border: rgba(30, 58, 138, 0.08);
            --card-bg:       #ffffff;
            --card-hover-bg: #fafbff;
            --footer-bg:     #0a0f1e;
            --footer-text:   #94a3b8;

            --hero-grad-1:   #1e3a8a;
            --hero-grad-2:   #2563eb;
            --hero-grad-3:   #0ea5e9;

            --shadow-sm:  0 1px 3px rgba(10, 15, 30, 0.06), 0 1px 2px rgba(10, 15, 30, 0.04);
            --shadow:     0 4px 12px rgba(10, 15, 30, 0.06), 0 2px 6px rgba(10, 15, 30, 0.04);
            --shadow-md:  0 8px 24px rgba(10, 15, 30, 0.07), 0 4px 10px rgba(10, 15, 30, 0.05);
            --shadow-lg:  0 20px 48px rgba(10, 15, 30, 0.08), 0 8px 20px rgba(10, 15, 30, 0.05);
            --shadow-glow: 0 0 30px rgba(37, 99, 235, 0.15);
            --inset-highlight: inset 0 1px 0 rgba(255, 255, 255, 0.8);

            --radius-xs: 6px;
            --radius-sm: 10px;
            --radius:    16px;
            --radius-lg: 24px;
            --radius-xl: 32px;
            --transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-spring: all 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* ── Dark Mode Variables ──────────────────────────────────────── */
        [data-theme="dark"] {
            --bg:            #030712;
            --bg-secondary:  #080f20;
            --bg-tertiary:   #0d1730;
            --surface:       #0d1730;
            --surface-alt:   #111d38;
            --border:        rgba(148, 163, 184, 0.08);
            --border-strong: rgba(148, 163, 184, 0.14);
            --text:          #f0f6ff;
            --text-muted:    #94a3b8;
            --text-subtle:   #64748b;

            --primary:       #3b82f6;
            --primary-light: #60a5fa;
            --primary-glow:  rgba(59, 130, 246, 0.22);
            --accent:        #38bdf8;
            --accent-glow:   rgba(56, 189, 248, 0.18);
            --success:       #34d399;
            --warning:       #fbbf24;
            --danger:        #f87171;
            --purple:        #a78bfa;

            --navbar-bg:     rgba(3, 7, 18, 0.85);
            --navbar-border: rgba(255, 255, 255, 0.06);
            --card-bg:       #0d1730;
            --card-hover-bg: #111d38;
            --footer-bg:     #030712;
            --footer-text:   #475569;

            --hero-grad-1:   #030712;
            --hero-grad-2:   #080f20;
            --hero-grad-3:   #0d1a40;

            --shadow-sm:  0 1px 3px rgba(0, 0, 0, 0.4), 0 1px 2px rgba(0, 0, 0, 0.3);
            --shadow:     0 4px 12px rgba(0, 0, 0, 0.35), 0 2px 6px rgba(0, 0, 0, 0.25);
            --shadow-md:  0 8px 24px rgba(0, 0, 0, 0.4), 0 4px 10px rgba(0, 0, 0, 0.3);
            --shadow-lg:  0 20px 48px rgba(0, 0, 0, 0.5), 0 8px 20px rgba(0, 0, 0, 0.35);
            --shadow-glow: 0 0 40px rgba(59, 130, 246, 0.2);
            --inset-highlight: inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        /* ── Base Reset ───────────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            transition: background 0.35s ease, color 0.35s ease;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            font-weight: 700;
            line-height: 1.25;
        }

        .font-serif { font-family: 'Playfair Display', serif; }
        .font-mono  { font-family: 'JetBrains Mono', monospace; }

        /* ── Navbar ───────────────────────────────────────────────────── */
        .navbar-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: var(--navbar-bg);
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            border-bottom: 1px solid var(--navbar-border);
            transition: background 0.35s ease, border-color 0.35s ease;
        }

        .navbar-wrapper::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, var(--primary) 30%, var(--accent) 70%, transparent 100%);
            opacity: 0.3;
        }

        .navbar {
            max-width: 1320px;
            margin: 0 auto;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-decoration: none;
            flex-shrink: 0;
        }

        .navbar-logos {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .navbar-logos img {
            height: 38px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 1px 3px rgba(0,0,0,0.1));
        }

        [data-theme="dark"] .navbar-logos img {
            filter: brightness(0.9) drop-shadow(0 0 6px rgba(59, 130, 246, 0.3));
        }

        .navbar-label {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
            border-left: 1.5px solid var(--border-strong);
            padding-left: 0.875rem;
        }

        .navbar-label-title {
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: -0.025em;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 60%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-label-sub {
            font-size: 0.6rem;
            font-weight: 600;
            color: var(--text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        /* Nav Links */
        .navbar-nav {
            display: flex;
            align-items: center;
            gap: 0.125rem;
            list-style: none;
        }

        .navbar-nav > li { position: relative; }

        .nav-link {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 0.875rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.3rem;
            cursor: pointer;
            white-space: nowrap;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: calc(100% - 1.75rem);
            height: 2px;
            background: linear-gradient(90deg, var(--primary-light), var(--accent));
            border-radius: 1px;
            transition: transform 0.22s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text);
            background: rgba(var(--primary-rgb, 37, 99, 235), 0.06);
        }

        .nav-link:hover::after, .nav-link.active::after {
            transform: translateX(-50%) scaleX(1);
        }

        .nav-chevron {
            width: 13px; height: 13px;
            stroke-width: 2.5;
            transition: transform 0.2s ease;
        }

        .has-dropdown:hover .nav-chevron { transform: rotate(180deg); }

        /* Dropdown */
        .dropdown-panel {
            position: absolute;
            top: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            background: var(--surface);
            border: 1px solid var(--border-strong);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 0.5rem;
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
            transform: translateX(-50%) translateY(-6px);
        }

        .has-dropdown:hover .dropdown-panel {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transform: translateX(-50%) translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.625rem 0.875rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .dropdown-item:hover, .dropdown-item.active {
            color: var(--primary-light);
            background: var(--primary-glow);
            transform: translateX(2px);
        }

        .dropdown-item-icon {
            width: 28px; height: 28px;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem;
            background: var(--primary-glow);
            flex-shrink: 0;
        }

        /* Navbar Actions */
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        /* Dark Mode Toggle */
        .theme-toggle {
            width: 38px; height: 38px;
            border-radius: 10px;
            border: 1px solid var(--border-strong);
            background: var(--surface);
            color: var(--text-muted);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transition);
            flex-shrink: 0;
        }

        .theme-toggle:hover {
            color: var(--primary-light);
            background: var(--primary-glow);
            border-color: var(--primary-light);
            transform: rotate(15deg);
        }

        .theme-toggle svg { width: 17px; height: 17px; }
        .theme-toggle .icon-sun  { display: none; }
        .theme-toggle .icon-moon { display: block; }
        [data-theme="dark"] .theme-toggle .icon-sun  { display: block; }
        [data-theme="dark"] .theme-toggle .icon-moon { display: none; }

        /* Buttons */
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
            white-space: nowrap;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: #fff;
            box-shadow: 0 4px 14px var(--primary-glow);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px var(--primary-glow);
            filter: brightness(1.08);
        }

        .btn-primary:active { transform: translateY(0); }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--border-strong);
            color: var(--text-muted);
        }

        .btn-outline:hover {
            border-color: var(--primary-light);
            color: var(--primary-light);
            background: var(--primary-glow);
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: none;
        }

        .btn-ghost:hover {
            background: var(--surface);
            color: var(--text);
        }

        .btn-cta {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent) 100%);
            color: #fff;
            padding: 0.5rem 1.125rem;
            font-size: 0.825rem;
            height: 38px;
            border-radius: var(--radius-sm);
            box-shadow: 0 4px 16px var(--primary-glow);
            border: none;
        }

        .btn-cta:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 28px var(--primary-glow);
            filter: brightness(1.1);
        }

        /* ── Badges ───────────────────────────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .badge-primary { background: var(--primary-glow); color: var(--primary-light); border: 1px solid rgba(37,99,235,0.2); }
        .badge-success { background: rgba(5,150,105,0.1); color: var(--success); border: 1px solid rgba(5,150,105,0.2); }
        .badge-warning { background: rgba(217,119,6,0.1); color: var(--warning); border: 1px solid rgba(217,119,6,0.2); }
        .badge-danger  { background: rgba(225,29,72,0.1); color: var(--danger); border: 1px solid rgba(225,29,72,0.2); }
        .badge-purple  { background: rgba(124,58,237,0.1); color: var(--purple); border: 1px solid rgba(124,58,237,0.2); }

        /* ── Cards ────────────────────────────────────────────────────── */
        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 1.75rem;
            box-shadow: var(--shadow);
            transition: box-shadow 0.3s ease, border-color 0.3s ease, transform 0.3s ease, background 0.35s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
            border-color: var(--border-strong);
            transform: translateY(-3px);
            background: var(--card-hover-bg);
        }

        .card-flat {
            background: var(--card-bg);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
            transition: background 0.35s ease;
        }

        /* ── Form Controls ────────────────────────────────────────────── */
        .form-group { margin-bottom: 1.5rem; }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border-strong);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-family: inherit;
            font-weight: 400;
            transition: var(--transition);
            background: var(--surface);
            color: var(--text);
            outline: none;
        }

        .form-control::placeholder { color: var(--text-subtle); }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px var(--primary-glow);
            background: var(--card-bg);
        }

        textarea.form-control { min-height: 140px; resize: vertical; }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 14px 10px;
            padding-right: 2.5rem;
        }

        /* ── Layout ───────────────────────────────────────────────────── */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            width: 100%;
        }

        .container-wide {
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 2rem;
            width: 100%;
        }

        main {
            flex: 1;
            padding-top: 70px; /* Navbar height */
        }

        .grid-1 { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fill, minmax(420px, 1fr)); gap: 1.5rem; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.25rem; }

        /* ── Alert ────────────────────────────────────────────────────── */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid transparent;
        }

        .alert-success { background: rgba(5,150,105,0.08); color: var(--success); border-color: rgba(5,150,105,0.2); }
        .alert-error   { background: rgba(225,29,72,0.08);  color: var(--danger);  border-color: rgba(225,29,72,0.2); }

        /* ── Utilities ────────────────────────────────────────────────── */
        .text-muted    { color: var(--text-muted); }
        .text-subtle   { color: var(--text-subtle); }
        .text-center   { text-align: center; }
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--primary-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .section-label::before {
            content: '';
            width: 20px; height: 2px;
            background: linear-gradient(90deg, var(--primary-light), var(--accent));
            border-radius: 1px;
        }

        /* ── Dividers ─────────────────────────────────────────────────── */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, var(--border-strong) 50%, transparent 100%);
            margin: 2rem 0;
        }

        /* ── Mobile Menu ──────────────────────────────────────────────── */
        .menu-toggle {
            display: none;
            background: none;
            border: 1px solid var(--border-strong);
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.4rem;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .menu-toggle:hover {
            background: var(--primary-glow);
            border-color: var(--primary-light);
            color: var(--primary-light);
        }

        /* ── Skeleton Loader ──────────────────────────────────────────── */
        .skeleton {
            background: linear-gradient(90deg, var(--bg-tertiary) 25%, var(--surface-alt) 50%, var(--bg-tertiary) 75%);
            background-size: 200% 100%;
            animation: skeleton-wave 1.6s ease-in-out infinite;
            border-radius: var(--radius-sm);
        }

        @keyframes skeleton-wave {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* ── Glow effects ─────────────────────────────────────────────── */
        .glow-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 12px var(--accent-glow);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { box-shadow: 0 0 8px var(--accent-glow); opacity: 1; }
            50%       { box-shadow: 0 0 20px var(--accent-glow); opacity: 0.7; }
        }

        /* ── Footer ───────────────────────────────────────────────────── */
        .footer {
            background: var(--footer-bg);
            color: var(--footer-text);
            border-top: 1px solid rgba(255,255,255,0.04);
            padding: 5rem 2rem 2rem;
            margin-top: auto;
            transition: background 0.35s ease;
        }

        .footer-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
        }

        .footer-brand-name {
            font-family: 'Inter', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.75rem;
        }

        .footer-desc {
            font-size: 0.85rem;
            line-height: 1.75;
            color: #64748b;
            margin-bottom: 1.5rem;
        }

        .footer-partner-logos {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .footer-partner-logos img {
            height: 28px;
            width: auto;
            filter: brightness(0.6) saturate(0);
            transition: filter 0.2s ease;
        }

        .footer-partner-logos img:hover { filter: brightness(0.85) saturate(0.5); }

        .footer-col-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #475569;
            margin-bottom: 1.25rem;
        }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .footer-links a {
            font-size: 0.85rem;
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 400;
        }

        .footer-links a:hover { color: #94a3b8; }

        .footer-bottom {
            max-width: 1200px;
            margin: 3.5rem auto 0;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-bottom p { font-size: 0.78rem; color: #334155; }

        .footer-bottom-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-bottom-links a {
            font-size: 0.78rem;
            color: #334155;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-bottom-links a:hover { color: #64748b; }

        /* ── Scroll to top ────────────────────────────────────────────── */
        .scroll-top {
            position: fixed;
            bottom: 2rem; right: 2rem;
            width: 44px; height: 44px;
            border-radius: 12px;
            background: var(--primary-light);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 20px var(--primary-glow);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease, transform 0.3s ease;
            z-index: 900;
        }

        .scroll-top.visible { opacity: 1; pointer-events: auto; }
        .scroll-top:hover { transform: translateY(-3px); }

        /* ── Responsive ───────────────────────────────────────────────── */
        @media (max-width: 1024px) {
            .menu-toggle { display: flex; }
            .navbar-nav { display: none; }
            .navbar-nav.is-open {
                display: flex;
                flex-direction: column;
                position: fixed;
                top: 70px; left: 0; right: 0;
                background: var(--bg-secondary);
                border-bottom: 1px solid var(--border-strong);
                padding: 1rem 1.5rem 1.5rem;
                gap: 0.25rem;
                box-shadow: var(--shadow-lg);
                z-index: 999;
            }
            .navbar-nav.is-open .nav-link { width: 100%; }
            .has-dropdown:hover .dropdown-panel {
                position: static;
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
                transform: none;
                box-shadow: none;
                border: none;
                background: rgba(37,99,235,0.04);
                border-left: 2px solid var(--primary-light);
                border-radius: 0;
                margin-left: 1rem;
                padding-left: 0.5rem;
            }
            .dropdown-panel { min-width: unset; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .navbar { padding: 0 1rem; }
            .navbar-label { display: none; }
            .container, .container-wide { padding: 0 1rem; }
            .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
            .footer-bottom { flex-direction: column; text-align: center; }
        }

        /* ── Page transition ──────────────────────────────────────────── */
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-in { animation: fade-in-up 0.5s cubic-bezier(0.23, 1, 0.32, 1) both; }
        .delay-1    { animation-delay: 0.1s; }
        .delay-2    { animation-delay: 0.2s; }
        .delay-3    { animation-delay: 0.3s; }
        .delay-4    { animation-delay: 0.4s; }

        /* ── Pagination ───────────────────────────────────────────────── */
        .pagination { display: flex; gap: 0.375rem; justify-content: center; flex-wrap: wrap; }
        .pagination span, .pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px; height: 38px;
            padding: 0 0.625rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid var(--border);
            color: var(--text-muted);
            background: var(--surface);
        }
        .pagination a:hover { border-color: var(--primary-light); color: var(--primary-light); background: var(--primary-glow); }
        .pagination span[aria-current="page"] { background: var(--primary-light); color: #fff; border-color: var(--primary-light); box-shadow: 0 4px 12px var(--primary-glow); }
        .pagination span.disabled { opacity: 0.4; cursor: not-allowed; }

        /* ── Input search bar ─────────────────────────────────────────── */
        .search-container {
            position: relative;
        }
        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-subtle);
            pointer-events: none;
        }
        .search-input {
            padding-left: 3rem !important;
        }

    </style>
    @yield('styles')
</head>
<body>

    {{-- ═══ NAVBAR ═══ --}}
    <div class="navbar-wrapper">
        <nav class="navbar">
            <a href="{{ route('home') }}" class="navbar-brand">
                <div class="navbar-logos">
                    <img src="{{ asset('images/logo_ummisco.webp') }}" alt="UMMISCO">
                    <img src="{{ asset('images/logo_ucad.webp') }}" alt="UCAD">
                </div>
                <div class="navbar-label">
                    <span class="navbar-label-title">UMMISCO</span>
                    <span class="navbar-label-sub">Portail scientifique</span>
                </div>
            </a>

            <ul class="navbar-nav" id="navbarNav">
                <li>
                    <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                        Accueil
                    </a>
                </li>
                <li class="has-dropdown">
                    <span class="nav-link {{ request()->routeIs('recherches.*') ? 'active' : '' }}">
                        Recherches
                        <svg class="nav-chevron" viewBox="0 0 24 24" stroke="currentColor" fill="none"><path d="m6 9 6 6 6-6"/></svg>
                    </span>
                    <div class="dropdown-panel">
                        <a href="{{ route('recherches.modelisation') }}" class="dropdown-item {{ request()->routeIs('recherches.modelisation') ? 'active' : '' }}">
                            <div class="dropdown-item-icon">🤖</div>
                            <div>
                                <div style="font-weight:600;color:var(--text);font-size:0.85rem;">Modélisation à base d'agents</div>
                                <div style="font-size:0.75rem;color:var(--text-subtle);">Systèmes complexes adaptatifs</div>
                            </div>
                        </a>
                        <a href="{{ route('recherches.milieux') }}" class="dropdown-item {{ request()->routeIs('recherches.milieux') ? 'active' : '' }}">
                            <div class="dropdown-item-icon">🌿</div>
                            <div>
                                <div style="font-weight:600;color:var(--text);font-size:0.85rem;">Milieux & Ressources vivantes</div>
                                <div style="font-size:0.75rem;color:var(--text-subtle);">Écosystèmes & biodiversité</div>
                            </div>
                        </a>
                    </div>
                </li>
                <li>
                    <a href="{{ route('axes') }}" class="nav-link {{ request()->routeIs('axes') ? 'active' : '' }}">Axes</a>
                </li>
                <li>
                    <a href="{{ route('publications') }}" class="nav-link {{ request()->routeIs('publications*') ? 'active' : '' }}">Publications</a>
                </li>
                <li>
                    <a href="{{ route('actualites') }}" class="nav-link {{ request()->routeIs('actualites') ? 'active' : '' }}">Actualités</a>
                </li>
                <li>
                    <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                </li>
            </ul>

            <div class="navbar-actions">
                {{-- Dark/Light Toggle --}}
                <button class="theme-toggle" id="themeToggle" title="Changer le thème" aria-label="Changer le thème">
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                        <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                    </svg>
                </button>

                @if(is_authenticated())
                    <span class="badge badge-primary" style="font-size:0.7rem;">{{ session('user_name') }}</span>
                    <a href="{{ route('dashboard') }}" class="btn-cta btn">
                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none">
                            <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/>
                            <rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
                        </svg>
                        Mon Espace
                    </a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        <button type="submit" class="btn btn-ghost" style="font-size:0.8rem;height:36px;padding:0 0.875rem;">
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-cta btn">
                        <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2.5" fill="none">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M14 12H3"/>
                        </svg>
                        Connexion
                    </a>
                @endif

                <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.5" fill="none">
                        <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
            </div>
        </nav>
    </div>

    {{-- Alerts --}}
    <div class="container" style="margin-top: 1.25rem;">
        @if(session('success'))
            <div class="alert alert-success animate-in">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error animate-in">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Contenu principal --}}
    <main>
        @yield('content')
    </main>

    {{-- ═══ FOOTER ═══ --}}
    <footer class="footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand-name">UMMISCO</div>
                <p class="footer-desc">
                    Unité Mixte Internationale de Modélisation Mathématique et Informatique des Systèmes Complexes.<br>
                    Excellence scientifique depuis 1999 — Dakar, Sénégal.
                </p>
                <div class="footer-partner-logos">
                    <img src="{{ asset('images/logo_ummisco.webp') }}" alt="UMMISCO">
                    <img src="{{ asset('images/logo_ucad.webp') }}" alt="UCAD">
                </div>
            </div>
            <div>
                <div class="footer-col-title">Navigation</div>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Accueil</a></li>
                    <li><a href="{{ route('publications') }}">Publications</a></li>
                    <li><a href="{{ route('axes') }}">Axes de recherche</a></li>
                    <li><a href="{{ route('projets') }}">Projets en cours</a></li>
                    <li><a href="{{ route('actualites') }}">Actualités</a></li>
                </ul>
            </div>
            <div>
                <div class="footer-col-title">Recherches</div>
                <ul class="footer-links">
                    <li><a href="{{ route('recherches.modelisation') }}">Modélisation agents</a></li>
                    <li><a href="{{ route('recherches.milieux') }}">Milieux & Ressources</a></li>
                    <li><a href="{{ route('publications') }}?type=dataset">Datasets ouverts</a></li>
                    <li><a href="{{ route('publications') }}?type=thesis">Thèses & Mémoires</a></li>
                </ul>
            </div>
            <div>
                <div class="footer-col-title">Contact</div>
                <ul class="footer-links">
                    <li><a href="mailto:admin@ummisco.ucad.sn">admin@ummisco.ucad.sn</a></li>
                    <li><a href="{{ route('contact') }}">Formulaire de contact</a></li>
                </ul>
                <div style="margin-top:1.25rem;">
                    <div style="font-size:0.78rem;color:#475569;line-height:1.7;">
                        Université Cheikh Anta Diop (UCAD)<br>
                        BP 5005, Dakar-Fann, Sénégal
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} UMMISCO — CNRS · IRD · UCAD. Tous droits réservés.</p>
            <div class="footer-bottom-links">
                <a href="#">Mentions légales</a>
                <a href="#">Politique de confidentialité</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
        </div>
    </footer>

    {{-- Scroll to top --}}
    <button class="scroll-top" id="scrollTop" aria-label="Retour en haut">
        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>

    <script>
    (function() {
        // ── Theme persistence ──────────────────────────────────────────
        const html = document.documentElement;
        const saved = localStorage.getItem('ummisco-theme');
        const preferred = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        html.setAttribute('data-theme', saved || preferred);

        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle
            const toggle = document.getElementById('themeToggle');
            if (toggle) {
                toggle.addEventListener('click', function() {
                    const current = html.getAttribute('data-theme');
                    const next = current === 'dark' ? 'light' : 'dark';
                    html.setAttribute('data-theme', next);
                    localStorage.setItem('ummisco-theme', next);
                });
            }

            // Mobile menu
            const menuToggle = document.getElementById('menuToggle');
            const navbarNav  = document.getElementById('navbarNav');
            if (menuToggle && navbarNav) {
                menuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navbarNav.classList.toggle('is-open');
                });
                document.addEventListener('click', function() {
                    navbarNav.classList.remove('is-open');
                });
                navbarNav.addEventListener('click', function(e) { e.stopPropagation(); });
            }

            // Scroll to top
            const scrollBtn = document.getElementById('scrollTop');
            if (scrollBtn) {
                window.addEventListener('scroll', function() {
                    scrollBtn.classList.toggle('visible', window.scrollY > 400);
                }, { passive: true });
                scrollBtn.addEventListener('click', function() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }
        });
    })();
    </script>

    @yield('scripts')
</body>
</html>
