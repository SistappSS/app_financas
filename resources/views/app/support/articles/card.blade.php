@extends('layouts.templates.app')

@section('new-content')
    <section class="mt-6">

        <div class="mb-4">
            <h2 class="text-xl font-semibold">Cartões e faturas</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Veja como cadastrar seus cartões, acompanhar limite e controlar as faturas dentro do app.
            </p>
        </div>

        <div
            class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4 space-y-4 text-sm text-neutral-800 dark:text-neutral-200">

            {{-- Passo 1 – Tela de cartões --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">1. Acessando a tela de cartões</h3>
                <p>
                    Abra o menu <strong>Cartões</strong>. Quando ainda não houver nenhum cartão cadastrado,
                    a tela ficará em branco e você verá o botão com o ícone <strong>“+”</strong>
                    no canto inferior direito.
                </p>
                <p>
                    Toque nesse botão para cadastrar o seu primeiro cartão.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/cards-screen.png') }}"
                         alt="Tela de cartões sem cartões cadastrados"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 1 – Tela de Cartões antes do primeiro cadastro.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 2 – Cadastro do cartão --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">2. Cadastrando um novo cartão</h3>
                <p>
                    Ao tocar em <strong>“+”</strong>, será aberto o formulário
                    <strong>“Novo cartão”</strong>. Nessa tela você informa os dados principais do cartão:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Banco vinculado</strong> – escolha a conta bancária que está
                        associada a esse cartão. Ela será usada depois para pagar as faturas.
                    </li>
                    <li>
                        <strong>Titular</strong> – o nome do titular do cartão (ex.: o seu nome).
                    </li>
                    <li>
                        <strong>Últimos 4 dígitos</strong> – apenas para identificação visual do cartão no app.
                    </li>
                    <li>
                        <strong>Bandeira</strong> – Visa, Mastercard, etc.
                    </li>
                    <li>
                        <strong>Cor do cartão</strong> – escolha uma cor para o cartão dentro do app.
                    </li>
                    <li>
                        <strong>Limite (R$)</strong> – o limite total disponível naquele cartão.
                    </li>
                    <li>
                        <strong>Dia de fechamento</strong> – dia em que a fatura é fechada
                        (data em que o app para de somar compras para aquela fatura).
                    </li>
                    <li>
                        <strong>Dia de vencimento</strong> – dia em que a fatura deve ser paga.
                    </li>
                </ul>

                <p>
                    Depois de preencher tudo, toque em <strong>“Salvar”</strong> para concluir o cadastro.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/card-new.png') }}"
                         alt="Formulário de novo cartão com banco, titular, limite e datas"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 2 – Formulário de cadastro de cartão.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 3 – Cartão criado --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">3. Visualizando o cartão criado</h3>
                <p>
                    Após salvar, o cartão passa a aparecer na tela de <strong>Cartões</strong>
                    em formato de cartão digital, mostrando:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>Nome que você informou;</li>
                    <li>Bandeira (Visa, Mastercard, etc.);</li>
                    <li>Últimos 4 dígitos;</li>
                    <li>Dia de fechamento e dia de vencimento;</li>
                    <li><strong>Limite</strong> cadastrado.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/cards-list.png') }}"
                         alt="Cartão criado na lista de cartões com limite e datas"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 3 – Exemplo de cartão cadastrado na tela de Cartões.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 4 – Menu do cartão --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">4. Opções do cartão (faturas, editar, excluir)</h3>
                <p>
                    Em cada cartão existe um botão com <strong>três pontinhos</strong> no canto superior direito.
                    Ao tocar nele, é aberto um painel com as opções:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Visualizar faturas</strong> – abre a tela de faturas desse cartão.</li>
                    <li><strong>Editar</strong> – permite alterar os dados do cartão (limite, datas etc.).</li>
                    <li><strong>Excluir</strong> – remove o cartão do app. Use com cuidado.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/card-options.png') }}"
                         alt="Menu com opções visualizar faturas, editar e excluir"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 4 – Opções disponíveis ao tocar nos três pontinhos do cartão.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 5 – Tela de faturas --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">5. Tela de faturas do cartão</h3>
                <p>
                    Ao tocar em <strong>Visualizar faturas</strong>, você é redirecionado para a tela
                    de <strong>Faturas</strong> desse cartão.
                </p>
                <p>
                    Tudo que você cadastrar como <strong>transação</strong> escolhendo esse cartão
                    como forma de pagamento vai aparecer aqui, organizado por mês.
                </p>
                <p>
                    No topo, cada mês mostra o valor da fatura e a cor indica o status:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Laranja</strong> – fatura em aberto (ainda não foi paga).</li>
                    <li><strong>Verde</strong> – fatura paga.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/card-invoices.png') }}"
                         alt="Tela de faturas do cartão com meses e valores"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 5 – Tela de faturas do cartão, com meses, valores e status.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 6 – Pagando a fatura --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">6. Pagando uma fatura</h3>
                <p>
                    Ao selecionar um mês, você vê o <strong>resumo da fatura</strong> com:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>Valor total da fatura;</li>
                    <li>Status (em aberto ou paga);</li>
                    <li>Limite disponível;</li>
                    <li>Data de fechamento e data de vencimento;</li>
                    <li>Botão <strong>“Pagar fatura”</strong>.</li>
                </ul>

                <p>
                    Para pagar uma fatura, basta tocar em <strong>“Pagar fatura”</strong>. O app
                    registra o pagamento usando o saldo da <strong>conta do banco vinculada</strong>
                    ao cartão. Assim, o limite é liberado e a fatura passa para o status de paga.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/card-invoice-header.png') }}"
                         alt="Resumo da fatura com botão pagar fatura"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 6 – Resumo da fatura e botão para pagar.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 7 – Itens da fatura --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">7. Itens da fatura (lançamentos)</h3>
                <p>
                    Abaixo do resumo, você vê a lista de <strong>itens da fatura</strong>, ou seja,
                    cada compra/lançamento que foi feito no cartão.
                </p>
                <p>
                    Em cada item você consegue:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>Ver o nome da categoria e a data do lançamento;</li>
                    <li>Acompanhar o valor total e, quando for parcelado, qual parcela está sendo exibida
                        (por exemplo: <strong>2/3 R$ 33,33</strong>);</li>
                    <li><strong>Editar</strong> o item, se precisar corrigir alguma informação;</li>
                    <li><strong>Excluir</strong> um lançamento específico da fatura.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/card-invoice-item.png') }}"
                         alt="Item de fatura com quantidade de parcelas e ações de edição e exclusão"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 7 – Exemplo de item dentro da fatura do cartão.
                    </figcaption>
                </figure>
            </div>

            {{-- Link de vídeo --}}
            <div class="pt-3 border-t border-dashed border-neutral-200 dark:border-neutral-700 mt-2">
                <p class="text-xs text-neutral-500 mb-1">
                    Prefere ver em vídeo?
                </p>
                <a href="https://www.youtube.com/seu-video-cartao-faturas"
                   target="_blank"
                   class="inline-flex items-center gap-1 text-xs text-brand-600 hover:underline">
                    Como usar cartões e faturas no app
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 17L17 7M7 7h10v10"/>
                    </svg>
                </a>
            </div>
        </div>

    </section>
@endsection
