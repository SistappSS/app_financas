@extends('layouts.templates.app')

@section('new-content')
    <section class="mt-6">

        <div class="mb-4">
            <h2 class="text-xl font-semibold">Transações (lançamentos)</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Veja como registrar entradas e saídas de dinheiro, definir forma de pagamento e recorrência.
            </p>
        </div>

        <div
            class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4 space-y-4 text-sm text-neutral-800 dark:text-neutral-200">

            {{-- Passo 1 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">1. Acessando a tela de transações</h3>
                <p>
                    Depois de cadastrar suas <strong>categorias de transação</strong>, acesse a tela
                    <strong>Transações</strong> pelo menu inferior do app.
                </p>
                <p>
                    Para cadastrar uma nova transação, toque no botão com o ícone <strong>“+”</strong>
                    no canto inferior direito da tela.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transactions-screen.png') }}"
                         alt="Tela de transações com botão para adicionar"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 1 – Tela de Transações com o botão “+” para criar um novo lançamento.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 2 – dados principais --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">2. Preenchendo os dados principais</h3>
                <p>
                    Ao tocar em <strong>“+”</strong>, será aberta a tela <strong>Nova transação</strong>.
                    Nessa primeira parte você informa:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Título</strong> – uma descrição rápida da transação,
                        por exemplo: <em>Pagamento aluguel</em>, <em>Salário</em>, <em>Supermercado</em>.
                    </li>
                    <li>
                        <strong>Categoria</strong> – selecione uma das categorias que você já criou em
                        <strong>Categorias de transação</strong>.
                    </li>
                    <li>
                        <strong>Valor</strong> – o valor da transação (quanto entrou ou saiu).
                    </li>
                    <li>
                        <strong>Data (início)</strong> – a data em que o lançamento deve ser considerado.
                    </li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-new-basic.png') }}"
                         alt="Formulário de nova transação com título, categoria, valor e data"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 2 – Parte inicial do cadastro de uma nova transação.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 3 – forma de pagamento / Pix --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">3. Forma de pagamento: Pix</h3>
                <p>
                    Mais abaixo você escolhe a <strong>Forma de pagamento</strong>. Ao selecionar
                    <strong>Pix</strong>, você deverá informar:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Conta (Pix/Dinheiro)</strong> – de qual conta bancária esse valor
                        está saindo ou entrando.
                    </li>
                    <li>
                        <strong>Recorrência</strong> – se essa transação acontece apenas uma vez
                        ou se repete (mensal, anual ou personalizada).
                    </li>
                    <li>
                        <strong>Parcelar valor?</strong> – defina se o valor será lançado de uma vez
                        só ou dividido em parcelas.
                    </li>
                    <li>
                        <strong>Parcelas</strong> – caso escolha parcelar, informe em quantas vezes
                        o valor será dividido.
                    </li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-form-pix.png') }}"
                         alt="Formulário de nova transação com forma de pagamento Pix"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 3 – Exemplo de transação com forma de pagamento Pix.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 4 – Cartão --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">4. Forma de pagamento: Cartão</h3>
                <p>
                    Ao escolher <strong>Cartão</strong> como forma de pagamento, a lógica é parecida,
                    mas com alguns campos específicos:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Tipo de cartão</strong> – escolha se é <strong>Crédito</strong>
                        ou <strong>Débito</strong>.
                    </li>
                    <li>
                        <strong>Cartão vinculado</strong> – selecione qual cartão de crédito/débito
                        cadastrado será usado nessa transação.
                    </li>
                    <li>
                        <strong>Recorrência</strong> e <strong>Parcelar valor?</strong> –
                        funcionam da mesma forma que no Pix: você define se será um pagamento único,
                        mensal, anual ou personalizado e se terá parcelas.
                    </li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-form-card.png') }}"
                         alt="Formulário de nova transação com forma de pagamento Cartão"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 4 – Exemplo de transação com forma de pagamento Cartão.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 5 – Dinheiro --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">5. Forma de pagamento: Dinheiro</h3>
                <p>
                    Ao selecionar <strong>Dinheiro</strong>, você indica que o pagamento foi feito
                    em espécie. Nesse caso, você precisa:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        Escolher de qual <strong>banco/conta</strong> esse dinheiro está saindo
                        (para manter o saldo da conta correto).
                    </li>
                    <li>
                        Definir a <strong>Recorrência</strong> (única, mensal, anual ou personalizada).
                    </li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-form-cash.png') }}"
                         alt="Formulário de nova transação com forma de pagamento Dinheiro"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 5 – Exemplo de transação com forma de pagamento Dinheiro.
                    </figcaption>
                </figure>
            </div>

            {{-- Recorrência / Parcelas – Única --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">6. Recorrência e parcelas: Única</h3>
                <p>
                    Quando a <strong>Recorrência</strong> está em <strong>Única</strong>, a transação
                    será lançada apenas uma vez, na data escolhida.
                </p>
                <p>
                    Se você ativar <strong>“Parcelar valor?”</strong> e informar, por exemplo,
                    <strong>3 parcelas</strong>, o valor total será dividido em 3 partes iguais
                    e o app vai gerar as parcelas conforme a regra que você definir (por exemplo,
                    3 lançamentos mensais).
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-form-installments.png') }}"
                         alt="Configuração de transação parcelada"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 6 – Exemplo de transação com valor parcelado.
                    </figcaption>
                </figure>
            </div>

            {{-- Recorrência Mensal --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">7. Recorrência mensal</h3>
                <p>
                    Ao selecionar <strong>Mensal</strong>, o app repetirá essa transação
                    todo mês na mesma data (ou conforme a sua lógica interna).
                </p>
                <p>
                    Você pode escolher:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Sem término</strong> – a transação continuará sendo repetida
                        indefinidamente até você parar manualmente.
                    </li>
                    <li>
                        <strong>Com término</strong> – você informa o <strong>Nº de ocorrências</strong>,
                        por exemplo “12”, e o app repete essa transação por 12 meses.
                    </li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-rec-monthly.png') }}"
                         alt="Recorrência mensal com término"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 7 – Exemplo de recorrência mensal com término definido.
                    </figcaption>
                </figure>
            </div>

            {{-- Recorrência Anual --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">8. Recorrência anual</h3>
                <p>
                    Na opção <strong>Anual</strong>, a lógica é a mesma da mensal, mas com intervalo de
                    <strong>um ano</strong> entre uma ocorrência e outra. É ideal para despesas e receitas
                    que se repetem uma vez por ano, como:
                    <em>IPVA, seguro, taxas anuais</em> etc.
                </p>
                <p>
                    Você também pode escolher entre <strong>sem término</strong> ou
                    <strong>com término</strong>, informando o número de ocorrências.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-rec-annual.png') }}"
                         alt="Recorrência anual com término"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 8 – Exemplo de recorrência anual.
                    </figcaption>
                </figure>
            </div>

            {{-- Recorrência Personalizada --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">9. Recorrência personalizada</h3>
                <p>
                    Na opção <strong>Personalizado</strong>, você pode configurar exatamente
                    como essa transação vai se repetir:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Intervalo (dias)</strong> – a cada quantos dias a transação deve se repetir
                        (por exemplo, a cada 7 dias).
                    </li>
                    <li>
                        <strong>Contar fim de semana?</strong> – escolha se sábado e domingo entram
                        na conta do intervalo.
                    </li>
                    <li>
                        <strong>Término</strong> – se será <strong>Sem término</strong>
                        ou <strong>Com término</strong> (informando o número de ocorrências).
                    </li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-rec-custom.png') }}"
                         alt="Recorrência personalizada de transação"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 9 – Exemplo de recorrência personalizada.
                    </figcaption>
                </figure>
            </div>

            {{-- Opções da transação na lista --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">10. Editar, ver detalhes e excluir</h3>
                <p>
                    Depois de salvar, as transações serão listadas na tela de <strong>Transações</strong>.
                    Em cada cartão, você encontrará um botão com <strong>três pontinhos</strong> no canto
                    direito.
                </p>
                <p>
                    Ao tocar nesse botão, um painel será aberto com as opções:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Editar</strong> – para alterar os dados da transação.</li>
                    <li><strong>Ver detalhes</strong> – para visualizar todas as informações do lançamento.</li>
                    <li><strong>Excluir</strong> – para remover a transação definitivamente.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/transaction-options.png') }}"
                         alt="Opções de uma transação: editar, ver detalhes, excluir"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 10 – Menu de opções de uma transação.
                    </figcaption>
                </figure>
            </div>

            {{-- Link de vídeo --}}
            <div class="pt-3 border-t border-dashed border-neutral-200 dark:border-neutral-700 mt-2">
                <p class="text-xs text-neutral-500 mb-1">
                    Prefere ver tudo isso em vídeo?
                </p>
                <a href="https://www.youtube.com"
                   target="_blank"
                   class="inline-flex items-center gap-1 text-xs text-brand-600 hover:underline">
                    Como lançar transações no app
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 17L17 7M7 7h10v10"/>
                    </svg>
                </a>
            </div>
        </div>

    </section>
@endsection
