<header class="sticky top-0 z-40 border-b border-neutral-200/70 dark:border-neutral-800/70 bg-white/70 dark:bg-neutral-950/60 backdrop-blur supports-[backdrop-filter]:bg-white/50 supports-[backdrop-filter]:dark:bg-neutral-950/50">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3 px-4 py-3 md:py-4">
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-2">
                <button
                    type="button"
                    id="sidebarToggleDesktop"
                    class="inline-flex items-center justify-center rounded-xl border border-neutral-200/70 dark:border-neutral-700/70 bg-white/80 dark:bg-neutral-900/80 text-neutral-700 dark:text-neutral-100 px-2.5 py-2 shadow-soft focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-950"
                >
                    <span class="sr-only">Alternar menu lateral</span>
                    <svg id="sidebarToggleIcon" viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor"
                         stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <!-- estado inicial: chevron apontando pra esquerda (fechar) -->
                        <path d="M10 6 6 12l4 6" />
                        <path d="M14 6l-4 6 4 6" />
                    </svg>
                </button>
            </div>
            <div class="size-9 grid place-items-center md:hidden rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-white shadow-soft">
                <svg viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                    <path fill="currentColor" d="M12 2l9 5v10l-9 5-9-5V7z"/>
                </svg>
            </div>
            @if(auth()->check())
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Bem-vindo(a),</p>
                    <p class="font-semibold">{{ auth()->user()->name }}</p>
                </div>
            @else
                <div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Bem-vindo(a)</p>
                    <p class="font-semibold">Visitante</p>
                </div>
            @endif

        </div>

        <div class="flex items-center gap-2">
            <button id="btnTheme"
                    class="grid place-items-center size-10 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-900 transition"
                    aria-label="Alternar tema" aria-pressed="false">
                <svg id="iconSun" class="size-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="5"/>
                    <path
                        d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
                <svg id="iconMoon" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/>
                </svg>
            </button>
            @if(auth()->check())

{{--                <button--}}
{{--                    class="relative grid place-items-center size-10 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-900 transition"--}}
{{--                    aria-label="Notificações">--}}
{{--                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"--}}
{{--                         stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">--}}
{{--                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 5 3 9H3c0-4 3-2 3-9"/>--}}
{{--                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>--}}
{{--                    </svg>--}}
{{--                    <span--}}
{{--                        class="absolute -top-1 -right-1 size-4 rounded-full bg-brand-500 text-white text-[10px] grid place-items-center">3</span>--}}
{{--                </button>--}}

                <button
                    type="button"
                    id="appMenuToggle"
                    class="md:hidden inline-flex items-center justify-center rounded-xl border border-neutral-200/70 dark:border-neutral-700/70 bg-white/80 dark:bg-neutral-900/80 px-2.5 py-2 text-neutral-700 dark:text-neutral-100 shadow-soft focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-950"
                    aria-expanded="false"
                    aria-controls="mobile-app-menu"
                >
                    <span class="sr-only">Abrir menu de abas</span>
                    <!-- Ícone grid -->
                    <svg viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                        <path fill="currentColor"
                              d="M4 4h6v6H4V4zm10 0h6v6h-6V4zM4 14h6v6H4v-6zm10 0h6v6h-6v-6z"/>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</header>
