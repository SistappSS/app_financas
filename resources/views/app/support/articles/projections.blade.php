@extends('layouts.templates.app')

@section('new-content')
    <section class="mt-6">

        <div class="mb-4">
            <h2 class="text-xl font-semibold">Projeções</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Use a tela de projeções para simular como ficará seu saldo ao longo do tempo,
                considerando entradas, saídas, recorrências e investimentos.
            </p>
        </div>

        <div
            class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4 space-y-4 text-sm text-neutral-800 dark:text-neutral-200">

            {{-- 1. Configurando período e cofrinhos --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">1. Escolhendo o período da projeção</h3>
                <p>
                    Na tela de <strong>Projeções</strong> você pode definir o intervalo de tempo que deseja simular.
                    Existem duas formas:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        Informar manualmente a <strong>data de início</strong> e a <strong>data de fim</strong>;
                    </li>
                    <li>
                        Usar os atalhos prontos: <strong>+1 mês, +3 meses, +6 meses, +12 meses ou +15 meses</strong>.
                        Eles preenchem automaticamente a data final contando a partir da data de início.
                    </li>
                </ul>

                <p>
                    Logo abaixo você encontra a opção
                    <strong>“Incluir valores dos cofrinhos?”</strong>. Se estiver ativada:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        Os valores aplicados em <strong>investimentos/cofrinhos</strong> serão somados ao
                        <strong>saldo inicial da projeção</strong>.
                    </li>
                    <li>
                        Se você tiver mais de um investimento, poderá marcar quais cofrinhos deseja incluir.
                    </li>
                </ul>

                <p>
                    Depois de escolher datas e, se quiser, os cofrinhos, toque em
                    <strong>“Aplicar”</strong> para gerar a projeção.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/projections-filter.png') }}"
                         alt="Filtros de projeção com datas, cofrinhos e atalhos de período"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 1 – Configuração do período da projeção e inclusão de cofrinhos.
                    </figcaption>
                </figure>
            </div>

            {{-- 2. Primeiro mês da projeção --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">2. Entendendo o primeiro mês da projeção</h3>
                <p>
                    Depois de aplicar, o app calcula todas as <strong>entradas</strong> e
                    <strong>saídas</strong> dentro do período escolhido, considerando também as
                    <strong>recorrências</strong> cadastradas (mensal, anual, personalizada e parcelas).
                </p>
                <p>
                    Para cada mês é exibido um cartão com o resumo:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Saldo inicial do mês</strong>;</li>
                    <li><strong>Entradas</strong> (somatório das receitas do mês);</li>
                    <li><strong>Saídas</strong> (somatório das despesas do mês);</li>
                    <li><strong>Saldo do mês</strong> (entradas – saídas);</li>
                    <li><strong>Saldo final do mês (projetado)</strong>, que será o saldo inicial do mês seguinte.</li>
                </ul>

                <p>
                    Abaixo do resumo aparece o <strong>extrato detalhado</strong> daquele mês,
                    com cada transação que foi considerada na projeção.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/projections-month-1.png') }}"
                         alt="Primeiro mês da projeção com resumo e extrato detalhado"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 2 – Mês inicial da projeção (mês atual da simulação).
                    </figcaption>
                </figure>
            </div>

            {{-- 3. Segundo mês da projeção --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">3. Meses seguintes (exemplo: segundo mês)</h3>
                <p>
                    Rolando a tela, você verá os meses seguintes um abaixo do outro. O
                    <strong>saldo final projetado</strong> de um mês passa a ser o
                    <strong>saldo inicial</strong> do mês seguinte, mantendo a continuidade da simulação.
                </p>
                <p>
                    Assim, no segundo mês você consegue ver como seu saldo evoluir caso mantenha
                    as mesmas entradas, saídas e recorrências configuradas.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/projections-month-2.png') }}"
                         alt="Segundo mês da projeção com saldo inicial e final projetado"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 3 – Exemplo do segundo mês da projeção.
                    </figcaption>
                </figure>
            </div>

            {{-- 4. Último mês da projeção --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">4. Último mês do período escolhido</h3>
                <p>
                    O último cartão representa o <strong>fim do período</strong> que você selecionou
                    (por exemplo, 6 meses, 12 meses, 15 meses, etc.).
                </p>
                <p>
                    Nele você vê qual seria o <strong>saldo final projetado</strong> caso você mantenha:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>os mesmos <strong>padrões de entrada</strong> (salário, receitas, aportes);</li>
                    <li>as mesmas <strong>despesas</strong> e <strong>faturas</strong> cadastradas;</li>
                    <li>as mesmas <strong>recorrências</strong> ao longo do período;</li>
                    <li>e, se tiver incluído, os <strong>valores dos cofrinhos</strong> no saldo inicial.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/projections-month-3.png') }}"
                         alt="Último mês da projeção com saldo final projetado"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 4 – Último mês da projeção para o período selecionado.
                    </figcaption>
                </figure>

                <p>
                    A projeção é uma <strong>simulação</strong>: quanto mais corretas estiverem
                    suas transações, recorrências e investimentos, mais próxima da realidade
                    será essa visão de futuro.
                </p>
            </div>

            {{-- Link de vídeo (opcional) --}}
            <div class="pt-3 border-t border-dashed border-neutral-200 dark:border-neutral-700 mt-2">
                <p class="text-xs text-neutral-500 mb-1">
                    Prefere ver um exemplo na prática?
                </p>
                <a href="https://www.youtube.com/seu-video-projecoes"
                   target="_blank"
                   class="inline-flex items-center gap-1 text-xs text-brand-600 hover:underline">
                    Como usar a tela de Projeções
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 17L17 7M7 7h10v10"/>
                    </svg>
                </a>
            </div>
        </div>

    </section>
@endsection
