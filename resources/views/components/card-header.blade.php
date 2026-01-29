@props([
    'prevRoute'    => null,
    'iconRight'    => null,
    'title'        => '',
    'description'  => '',
])

<div class="my-4">
    <div class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-2">
        {{-- prev icon --}}
        @if($prevRoute)
            <a href="{{ $prevRoute }}"
               class="inline-flex items-center justify-center size-9 rounded-full
                      border border-neutral-200/70 dark:border-neutral-800/70
                      bg-white dark:bg-neutral-900
                      text-neutral-700 dark:text-neutral-100
                      hover:bg-neutral-50 dark:hover:bg-neutral-800
                      transition">
                <span class="sr-only">Voltar</span>
                <svg viewBox="0 0 24 24" class="size-4" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </a>
        @else
            <span class="size-9"></span>
        @endif

        <h1 class="text-xl font-semibold text-center">
            {{ $title }}
        </h1>

        <div class="hidden md:flex items-center gap-2">
            <button data-open-modal="acc" class="inline-flex items-center gap-2 p-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                <i class="fa-solid fa-plus-minus fs-3"></i>
            </button>
        </div>

        @if($iconRight)
            <span
                class="md:hidden inline-flex items-center justify-center size-9 rounded-full
                       border border-neutral-200/70 dark:border-neutral-800/70
                       bg-white dark:bg-neutral-900
                       text-neutral-700 dark:text-neutral-100
                       hover:bg-neutral-50 dark:hover:bg-neutral-800
                       transition">
                <i class="fa-solid fa-{{ $iconRight }} text-[14px]"></i>
            </span>
        @else
            <span class="size-9"></span>
        @endif
    </div>

    @if($description)
        <p class="m-2 text-sm text-center text-neutral-500 dark:text-neutral-400">
            {{ $description }}
        </p>
    @endif
</div>
