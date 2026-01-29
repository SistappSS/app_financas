<nav aria-label="Navegação inferior" class="md:hidden fixed inset-x-0 bottom-0 left-0 right-0 z-40 border-t border-neutral-200/70 dark:border-neutral-800/70 bg-white/70 dark:bg-neutral-950/80 backdrop-blur pb-[max(10px, env(safe-area-inset-bottom))] [transform:translateZ(0)]">
    <ul class="grid grid-cols-4 pb-2">
        <li>
            <a href="{{route('dashboard')}}" data-tab
               class="group flex flex-col items-center gap-1 py-3 text-xs font-medium aria-[current=page]:text-brand-600">
                <svg class="size-5 opacity-70 group-aria-[current=page]:opacity-100" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M3 12l2-2 4 4 8-8 4 4"></path>
                </svg>
                Home
            </a>
        </li>
        <li>
            <a href="{{route('transaction-view.index')}}" data-tab
               class="group flex flex-col items-center gap-1 py-3 text-xs font-medium">
                <svg class="size-5 opacity-70 group-aria-[current=page]:opacity-100" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M20 13V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6"/>
                    <rect x="2" y="13" width="20" height="8" rx="2"/>
                </svg>
                Transações
            </a>
        </li>
        <li>
            <a href="{{route('projection-view.index')}}" data-tab
               class="group flex flex-col items-center gap-1 py-3 text-xs font-medium">
                <svg class="size-5 opacity-70 group-aria-[current=page]:opacity-100" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M3 3v18h18"/>
                    <path d="M7 13v5M12 9v9M17 5v13"/>
                </svg>
                Projeções
            </a>
        </li>
        <li>
            <a href="{{route('user-view.index')}}" data-tab class="group flex flex-col items-center gap-1 py-3 text-xs font-medium">
                <svg class="size-5 opacity-70 group-aria-[current=page]:opacity-100" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round">
                    <circle cx="12" cy="7" r="4"/>
                    <path d="M5.5 21a8.38 8.38 0 0 1 13 0"/>
                </svg>
                Perfil
            </a>
        </li>
    </ul>
</nav>
