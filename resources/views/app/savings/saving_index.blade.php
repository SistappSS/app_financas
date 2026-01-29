@extends('layouts.templates.app')

@section('new-content')
    @push('styles')
        <style>
            /* Skeleton */
            .skel { position:relative; overflow:hidden; border-radius:.5rem; background:#e5e7eb }
            .dark .skel { background:#262626 }
            .skel::after { content:""; position:absolute; inset:0; transform:translateX(-100%); background:linear-gradient(90deg,transparent,rgba(255,255,255,.55),transparent); animation:skel 1.1s infinite }
            @keyframes skel { 100% { transform:translateX(100%) } }

            #savFab { z-index:80 }
            body.ui-modal-open #savFab { pointer-events:none }

            .card-floating { transition:transform .2s ease, box-shadow .2s ease; cursor:pointer; }
            .card-floating:hover { transform:translateY(-4px); box-shadow:0 8px 20px rgba(0,0,0,.25) }
        </style>
    @endpush

    <section id="savings-page" class="mt-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">Investimentos</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Gerencie suas reservas e investimentos.</p>
            </div>

            <button data-open-modal="sav"
                    class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Novo cofrinho
            </button>
        </div>

        <div id="savGrid" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4"></div>

        <!-- MODAL DE CADASTRO/EDIÇÃO -->
        @include('app.savings.partials.saving-form')

        <!-- SHEET DE AÇÕES -->
        @include('app.savings.partials.saving-actions')

    </section>

    <!-- FAB MOBILE -->
    <button id="savFab" type="button" data-open-modal="sav"
            class="md:hidden fixed bottom-20 right-4 z-[80] size-14 rounded-2xl grid place-items-center bg-brand-600 text-white shadow-lg">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
        </svg>
    </button>

    @push('scripts')
        <script src="{{ asset('assets/js/common/crud-model.js') }}"></script>
        @include('app.savings.partials.saving-js')
    @endpush
@endsection
