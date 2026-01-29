<aside data-sidebar class="hidden md:flex md:flex-col md:gap-4 md:px-4 md:py-6 bg-gradient-to-b from-neutral-50/80 to-white dark:from-neutral-950/80 dark:to-neutral-900/60 border-r border-neutral-200/70 dark:border-neutral-800/70 backdrop-blur supports-[backdrop-filter]:bg-neutral-50/40 supports-[backdrop-filter]:dark:bg-neutral-900/40 transition-transform duration-200">
    <div class="flex items-center gap-2 px-2">
        <div
            class="size-9 grid place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-white shadow-soft">
            <!-- Logo -->
            <svg viewBox="0 0 24 24" class="size-5" aria-hidden="true">
                <path fill="currentColor" d="M12 2l9 5v10l-9 5-9-5V7z"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold">Cliqis Finanças</p>
        </div>
    </div>

    <nav aria-label="Principal" class="mt-4 space-y-1">
        <a href="{{route('dashboard')}}" data-nav="home" aria-current="page"
           class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium bg-brand-50 dark:bg-neutral-800 text-brand-700 dark:text-neutral-100 hover:bg-brand-100/70 dark:hover:bg-neutral-800/70 transition">
                <span
                    class="grid place-items-center size-8 rounded-lg bg-white/70 dark:bg-neutral-900/70 text-brand-600 dark:text-neutral-100 shadow-soft">
                  <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                       stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path
                          d="M3 12l2-2 4 4 8-8 4 4"></path></svg>
                </span>
            Visão geral
        </a>
        <a href="{{route('transaction-view.index')}}" data-nav
           class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                <span class="grid place-items-center size-8 rounded-lg bg-white/70 dark:bg-neutral-900/70 shadow-soft">
                  <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                       stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path
                          d="M20 13V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6"></path><rect x="2" y="13" width="20" height="8"
                                                                                    rx="2"></rect></svg>
                </span>
            Transações
        </a>

        <a href="{{route('projection-view.index')}}" data-nav
           class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                <span class="grid place-items-center size-8 rounded-lg bg-white/70 dark:bg-neutral-900/70 shadow-soft">
                  <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                       stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path
                          d="M7 13v5M12 9v9M17 5v13"/></svg>
                </span>
            Projeções
        </a>
        <a href="{{route('user-view.index')}}" data-nav
           class="group flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                <span class="grid place-items-center size-8 rounded-lg bg-white/70 dark:bg-neutral-900/70 shadow-soft">
                  <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                       stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="7" r="4"/><path
                          d="M5.5 21a8.38 8.38 0 0 1 13 0"/></svg>
                </span>
            Perfil
        </a>
    </nav>

    {{--    <div class="mt-auto p-3 rounded-xl bg-gradient-to-br from-brand-50 to-white dark:from-neutral-800 dark:to-neutral-900 border border-neutral-200/70 dark:border-neutral-800/70">--}}
    {{--        <p class="text-sm font-medium">Meta do mês</p>--}}
    {{--        <p class="text-xs text-neutral-500 dark:text-neutral-400">Economizar R$ 1.500</p>--}}
    {{--        <div class="mt-3 h-2 rounded-full bg-neutral-200 dark:bg-neutral-800 overflow-hidden">--}}
    {{--            <div class="h-full w-[62%] bg-brand-500 rounded-full"></div>--}}
    {{--        </div>--}}
    {{--        <p class="mt-2 text-xs text-neutral-600 dark:text-neutral-400"><span class="font-semibold">62%</span> atingido</p>--}}
    {{--    </div>--}}
</aside>
