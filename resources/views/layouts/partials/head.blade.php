<head>
    <!-- Meta básicos -->
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Finanças Cliqis</title>

    <!-- PWA / Webapp -->
    <link rel="manifest" href="{{ asset('laravelpwa/manifest.json') }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="color-scheme" content="light dark">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Favicon / ícones -->
    <link rel="icon"
          href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cpath fill='%233b82f6' d='M16 2l14 8v12l-14 8-14-8V10z'/%3E%3Cpath fill='%23fff' d='M16 7l9 5v8l-9 5-9-5v-8z'/%3E%3C/svg%3E">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Dark mode inicial (antes de carregar estilos) -->
    <script>
        (function () {
            try {
                const saved = localStorage.getItem('theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = saved ? saved === 'dark' : prefersDark;
                document.documentElement.classList.toggle('dark', isDark);
            } catch (e) {}
        })();
    </script>

    <!-- Tailwind CDN + config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                    },
                    boxShadow: {
                        soft: '0 2px 10px rgba(0,0,0,.06)',
                        softDark: '0 6px 24px rgba(0,0,0,.35)',
                    },
                },
            },
        };
    </script>

    <!-- CSS global versionado -->
    @php
        $styleCssPath = public_path('assets/css/style.css');
        $styleVersion = file_exists($styleCssPath) ? filemtime($styleCssPath) : time();
    @endphp
    <link href="{{ asset('assets/css/style.css') }}?v={{ $styleVersion }}" rel="stylesheet">

    <!-- Estilos específicos -->
    <style>
        /* Colapso do sidebar no desktop */
        @media (min-width: 768px) {
            #appLayout.sidebar-collapsed {
                grid-template-columns: 0 1fr;
            }

            #appLayout.sidebar-collapsed aside[data-sidebar] {
                transform: translateX(-260px);
                pointer-events: none;
                opacity: 0;
            }
        }

        /* Evitar double-tap zoom em botões/links */
        button,
        a {
            touch-action: manipulation;
        }

        /* Blur do bottom nav em iOS (precisa de id="bottomNav" no <nav>) */
        @supports (-webkit-touch-callout: none) {
            #bottomNav {
                position: fixed;
                -webkit-backdrop-filter: blur(12px);
                backdrop-filter: blur(12px);
                transform: translateZ(0);
                will-change: transform;
            }

            .ios-no-blur #bottomNav {
                -webkit-backdrop-filter: none !important;
                backdrop-filter: none !important;
                background-color: rgba(255, 255, 255, .96) !important;
            }
        }
    </style>

    @stack('styles')
</head>
