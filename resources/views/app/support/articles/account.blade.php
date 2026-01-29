@extends('layouts.templates.app')

@section('new-content')
    <section class="mt-6">

        <div class="mb-4">
            <h2 class="text-xl font-semibold">Como usar a tela de Contas bancárias</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Veja como cadastrar e gerenciar suas contas para acompanhar os saldos dentro do app.
            </p>
        </div>

        <div
            class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4 space-y-4 text-sm text-neutral-800 dark:text-neutral-200">

            {{-- Passo 1 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">1. Acessando a tela de contas</h3>
                <p>
                    Na tela <strong>Contas bancárias</strong> você verá o botão com o ícone
                    <strong>“+”</strong> no canto inferior direito da tela. É por ele que você
                    adiciona uma nova conta.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/accounts-list.png') }}"
                         alt="Tela de contas bancárias com botão de adicionar"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 1 – Tela de contas bancárias com o botão “+” para adicionar nova conta.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 2 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">2. Cadastrando uma nova conta</h3>
                <p>
                    Ao tocar no botão <strong>“+”</strong>, será aberta a tela
                    <strong>“Nova conta bancária”</strong>. Nela, você precisa informar:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Nome do banco</strong> – por exemplo: <em>Banco do Norte</em>.</li>
                    <li><strong>Valor em conta (R$)</strong> – o saldo atual dessa conta no momento do cadastro.</li>
                </ul>

                <p>
                    Depois de preencher os campos, toque em <strong>“Salvar”</strong> para concluir.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/accounts-new.png') }}"
                         alt="Tela de nova conta bancária"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 2 – Formulário de nova conta bancária.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 3 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">3. Conta criada com sucesso</h3>
                <p>
                    Após tocar em <strong>“Salvar”</strong>, sua conta bancária será criada e
                    aparecerá na lista de contas, já com o saldo informado.
                </p>
                <p>
                    Na tela, você verá um cartão com:
                </p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Nome da conta/banco</strong>;</li>
                    <li><strong>Saldo total</strong> dessa conta;</li>
                    <li>Quanto está <strong>em conta</strong> e quanto está em <strong>cofrinhos</strong>, quando você usar essa funcionalidade.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/accounts-created.png') }}"
                         alt="Tela de contas bancárias com conta criada"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 3 – Exemplo de conta bancária já criada na lista.
                    </figcaption>
                </figure>

                <p>
                    A partir daí, você pode usar essa conta para registrar transações, acompanhar saldos
                    e acessar o extrato dentro do app.
                </p>
            </div>

            {{-- Passo 4 --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">4. Opções da conta (menu de três pontinhos)</h3>
                <p>
                    Em cada conta, no canto superior direito do cartão, existe um botão com
                    <strong>três pontinhos</strong> (<em>menu de opções</em>). Ao tocar nele,
                    será aberto um painel com algumas ações para essa conta.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/accounts-options.png') }}"
                         alt="Painel de opções da conta: editar, transferir, ver extrato, excluir"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 4 – Painel de opções da conta.
                    </figcaption>
                </figure>

                <p>As opções são:</p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Editar</strong> – permite alterar o <strong>nome do banco</strong> e o
                        <strong>saldo da conta</strong>, caso você precise corrigir alguma informação.
                    </li>
                    <li>
                        <strong>Transferir</strong> – você pode transferir o saldo de uma conta para outra
                        (disponível quando você tiver duas contas ou mais cadastradas).
                    </li>
                    <li>
                        <strong>Ver extrato</strong> – leva você para a área de <strong>Transações</strong>,
                        mostrando os lançamentos relacionados àquela conta.
                    </li>
                    <li>
                        <strong>Excluir</strong> – remove definitivamente essa conta do app.
                        Use essa opção com cuidado, pois a conta deixará de aparecer na lista.
                    </li>
                </ul>
            </div>

            {{-- Link de vídeo --}}
            <div class="pt-3 border-t border-dashed border-neutral-200 dark:border-neutral-700 mt-2">
                <p class="text-xs text-neutral-500 mb-1">
                    Prefere ver em vídeo?
                </p>
                <a href="https://www.youtube.com/seu-video-contas"
                   target="_blank"
                   class="inline-flex items-center gap-1 text-xs text-brand-600 hover:underline">
                    Assistir tutorial de contas bancárias no YouTube
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 17L17 7M7 7h10v10"/>
                    </svg>
                </a>
            </div>
        </div>

    </section>
@endsection
