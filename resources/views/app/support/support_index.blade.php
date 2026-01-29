@extends('layouts.templates.app')

@section('new-content')
    <section id="support-page" class="mt-6">

        <!-- HEADER -->
        <div class="mb-4">
            <h2 class="text-xl font-semibold">Ajuda & Suporte</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Escolha um tópico para entender melhor como o app funciona.
            </p>
        </div>

        <!-- LISTA DE CATEGORIAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($categories as $cat)
                <a href="{{ route('support.article', $cat['slug']) }}"
                   class="card-floating block bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4">
                    <h3 class="text-sm font-semibold mb-1">
                        {{ $cat['name'] }}
                    </h3>
                    <p class="text-xs text-neutral-600 dark:text-neutral-400">
                        {{ $cat['description'] }}
                    </p>
                </a>
            @endforeach
        </div>

    </section>
@endsection
