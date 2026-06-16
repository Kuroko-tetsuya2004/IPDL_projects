<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    {{-- Dark/Light mode: lire localStorage AVANT rendu pour éviter le flash --}}
    <script>
        (function() {
            var t = localStorage.getItem('ummisco-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Portail UMMISCO') }} — Espace membre</title>

    {{-- Premium Google Fonts (identiques au portail public) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,700;0,800;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* ═══════════════════════════════════════════════════════════════════
           UMMISCO DASHBOARD — Design System (aligned with public portal)
           Dark / Light Mode via [data-theme] attribute
        ═══════════════════════════════════════════════════════════════════ */

        /* ── Light Mode ─────────────────────────────────────────────── */
        :root,
        [data-theme="light"] {
            --bg:            #f5f7fa;
            --bg-secondary:  #ffffff;
            --bg-tertiary:   #eef2f7;
            --surface:       #ffffff;
            --surface-alt:   #f0f4ff;
            --border:        rgba(30, 58, 138, 0.08);
            --border-strong: rgba(30, 58, 138, 0.16);
            --text:          #0a0f1e;
            --text-muted:    #5a6785;
            --text-subtle:   #8896b3;

            --primary:       #1e3a8a;
            --primary-light: #2563eb;
            --primary-glow:  rgba(37, 99, 235, 0.15);
            --accent:        #0ea5e9;
            --success:       #059669;
            --warning:       #d97706;
            --danger:        #e11d48;
            --purple:        #7c3aed;
            --cyan:          #0891b2;

            --sidebar-bg:    #ffffff;
            --sidebar-border: rgba(30, 58, 138, 0.10);
            --card-bg:       #ffffff;
            --navbar-bg:     rgba(255,255,255,0.88);
            --navbar-border: rgba(30, 58, 138, 0.08);

            --shadow-sm:  0 1px 3px rgba(10, 15, 30, 0.06);
            --shadow:     0 4px 12px rgba(10, 15, 30, 0.07);
            --shadow-md:  0 8px 24px rgba(10, 15, 30, 0.08);
            --shadow-lg:  0 20px 48px rgba(10, 15, 30, 0.10);
            --shadow-colored: 0 8px 24px rgba(37, 99, 235, 0.18);

            --radius-xs: 6px;
            --radius-sm: 10px;
            --radius:    16px;
            --radius-lg: 24px;
            --transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── Dark Mode ──────────────────────────────────────────────── */
        [data-theme="dark"] {
            --bg:            #030712;
            --bg-secondary:  #080f20;
            --bg-tertiary:   #0d1730;
            --surface:       #0d1730;
            --surface-alt:   #111d38;
            --border:        rgba(148, 163, 184, 0.08);
            --border-strong: rgba(148, 163, 184, 0.15);
            --text:          #f0f6ff;
            --text-muted:    #94a3b8;
            --text-subtle:   #64748b;

            --primary:       #3b82f6;
            --primary-light: #60a5fa;
            --primary-glow:  rgba(59, 130, 246, 0.20);
            --accent:        #38bdf8;
            --success:       #34d399;
            --warning:       #fbbf24;
            --danger:        #f87171;
            --purple:        #a78bfa;
            --cyan:          #22d3ee;

            --sidebar-bg:    #080f20;
            --sidebar-border: rgba(148, 163, 184, 0.07);
            --card-bg:       #0d1730;
            --navbar-bg:     rgba(3, 7, 18, 0.90);
            --navbar-border: rgba(255,255,255,0.06);

            --shadow-sm:  0 1px 3px rgba(0,0,0,0.4);
            --shadow:     0 4px 12px rgba(0,0,0,0.35);
            --shadow-md:  0 8px 24px rgba(0,0,0,0.45);
            --shadow-lg:  0 20px 48px rgba(0,0,0,0.55);
            --shadow-colored: 0 8px 24px rgba(59, 130, 246, 0.18);
        }

        /* ── Base Reset ─────────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            transition: background 0.35s ease, color 0.35s ease;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            font-weight: 700;
            line-height: 1.25;
        }

        /* ── Tailwind dark/light bridge ─────────────────────────────── */
        /* Permet aux classes Tailwind dark: de fonctionner via data-theme */
        [data-theme="dark"]  { color-scheme: dark; }
        [data-theme="light"] { color-scheme: light; }

        /* ── Scrollbar premium ──────────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-light); }

        /* ── Animations globales ────────────────────────────────────── */
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in { animation: fade-in-up 0.4s ease both; }

        @keyframes pulse-ring {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%       { transform: scale(1.5); opacity: 0; }
        }
    </style>

    @routes
    @vite(['resources/css/app.css', 'resources/js/app_dashboard.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
