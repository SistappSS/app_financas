@extends('layouts.templates.app')

@section('new-content')
    <section class="mt-6">

        <div class="mb-4">
            <h2 class="text-xl font-semibold">Categorias de transação</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Aprenda a criar e organizar categorias para suas entradas e despesas.
            </p>
        </div>

        <div
            class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4 space-y-4 text-sm text-neutral-800 dark:text-neutral-200">

            {{-- Passo 1 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">1. Acessando as categorias pela tela de transações</h3>
                <p>
                    Na tela de <strong>Transações</strong> você verá dois botões flutuantes no canto inferior direito:
                    um com o ícone <strong>“+”</strong> e outro, logo acima, com o ícone de
                    <strong>três risquinhos</strong> (lista).
                </p>
                <p>
                    Toque no botão de <strong>três risquinhos</strong> para ser redirecionado para a tela
                    de <strong>Categorias de transação</strong>.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transactions-screen.png') }}"
                         alt="Tela de transações com botão de categorias"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 1 – Tela de Transações com o botão de categorias acima do “+”.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 2 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">2. Tela de categorias de transação</h3>
                <p>
                    Na tela <strong>Categorias de transação</strong> você pode cadastrar e gerenciar todas
                    as categorias que serão usadas nas suas entradas e despesas.
                </p>
                <p>
                    Quando ainda não houver nenhuma categoria cadastrada, a tela ficará vazia e você verá
                    o botão com o ícone <strong>“+”</strong> no canto inferior direito. Toque nesse botão
                    para criar uma nova categoria.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-categories-empty.png') }}"
                         alt="Tela de categorias de transação vazia"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 2 – Tela de Categorias de transação sem categorias cadastradas.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 3 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">3. Cadastrando uma nova categoria</h3>
                <p>
                    Ao tocar no botão <strong>“+”</strong> será aberto o formulário
                    <strong>“Nova categoria”</strong>. Aqui você configura todos os detalhes da categoria:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Categoria</strong> – o nome da categoria, por exemplo:
                        <em>Salário, Aluguel, Água, Luz, Mercado, Transporte</em> etc.
                    </li>
                    <li>
                        <strong>Cor</strong> – escolha uma cor para identificar visualmente essa categoria
                        nos lançamentos, gráficos e projeções.
                    </li>
                    <li>
                        <strong>Tipo</strong> – é muito importante escolher corretamente:
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>Entrada</strong> – para dinheiro que entra (ex.: salário, vendas).</li>
                            <li><strong>Despesa</strong> – para dinheiro que sai (ex.: aluguel, água, luz, mercado).</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Limite mensal</strong> – você pode ativar se essa categoria tiver
                        um limite de gasto. Isso é útil para acompanhar, por exemplo, quanto
                        você pretende gastar por mês em <em>Mercado</em> ou <em>Lazer</em>.
                    </li>
                    <li>
                        <strong>Ícone</strong> – escolha um ícone que represente bem a categoria
                        (por exemplo, um ícone de etiqueta para “Mercado” ou um cifrão para “Salário”).
                    </li>
                </ul>

                <p>
                    Depois de configurar tudo, toque em <strong>“Salvar”</strong> para criar a categoria.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-category-new.png') }}"
                         alt="Formulário de nova categoria de transação"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 3 – Formulário de cadastro de uma nova categoria.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 4 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">4. Visualizando e organizando suas categorias</h3>
                <p>
                    Depois de salvar, suas categorias aparecerão listadas na tela de
                    <strong>Categorias de transação</strong>. Cada cartão mostra:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Nome da categoria</strong> (ex.: Salário, Mercado).</li>
                    <li><strong>Tipo</strong> (Entrada, Despesa ou Investimento).</li>
                    <li><strong>Limite mensal</strong>, quando configurado.</li>
                    <li><strong>Cor</strong> e <strong>ícone</strong> escolhidos.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-categories-list.png') }}"
                         alt="Lista de categorias de transação criadas"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 4 – Exemplo de categorias de Entrada e Despesa já cadastradas.
                    </figcaption>
                </figure>

                <p>
                    O ideal é criar <strong>categorias diferentes</strong> para suas
                    <strong>entradas</strong> e <strong>despesas</strong>. Isso vai te ajudar
                    muito depois, quando você olhar os <strong>gráficos</strong> e
                    <strong>projeções</strong> do app, entendendo exatamente para onde está indo
                    o seu dinheiro.
                </p>
            </div>

            {{-- Link de vídeo --}}
            <div class="pt-3 border-t border-dashed border-neutral-200 dark:border-neutral-700 mt-2">
                <p class="text-xs text-neutral-500 mb-1">
                    Prefere ver em vídeo?
                </p>
                <a href="https://www.youtube.com/seu-video-categorias"
                   target="_blank"
                   class="inline-flex items-center gap-1 text-xs text-brand-600 hover:underline">
                    Tutorial de categorias de transação
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 17L17 7M7 7h10v10"/>
                    </svg>
                </a>
            </div>
        </div>

    </section>
@endsection
