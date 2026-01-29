@extends('layouts.templates.app')

@section('new-content')
    <section class="mt-6">

        <div class="mb-4">
            <h2 class="text-xl font-semibold">Investimentos (cofrinhos)</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                Entenda como criar cofrinhos, aplicar dinheiro e acompanhar o rendimento dos seus investimentos.
            </p>
        </div>

        <div
            class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4 space-y-4 text-sm text-neutral-800 dark:text-neutral-200">

            {{-- Passo 1 – Tela de investimentos --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">1. Acessando a tela de investimentos</h3>
                <p>
                    Na aba <strong>Investimentos</strong> você consegue gerenciar seus cofrinhos e reservas.
                    Quando ainda não houver nenhum investimento cadastrado, a tela ficará vazia e você verá
                    o botão com o ícone <strong>“+”</strong> no canto inferior direito.
                </p>
                <p>
                    Toque nesse botão para criar um novo investimento/cofrinho.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/investments-screen.png') }}"
                         alt="Tela de investimentos sem cofrinhos cadastrados"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 1 – Tela de Investimentos antes do primeiro cofrinho.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 2 – Cadastro do cofrinho --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">2. Cadastrando um novo investimento/cofrinho</h3>
                <p>
                    Ao tocar em <strong>“+”</strong>, será aberta a tela de cadastro do cofrinho.
                    Nela você precisa informar:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        <strong>Nome</strong> – como você quer chamar esse investimento
                        (ex.: <em>Reserva de emergência</em>, <em>Viagem</em>, <em>Cofrinho</em> etc.).
                    </li>
                    <li>
                        <strong>Conta debitada</strong> – selecione o banco/conta de onde o dinheiro
                        será retirado quando você fizer depósitos nesse cofrinho.
                    </li>
                    <li>
                        <strong>% do CDI</strong> – muito importante para que o cálculo de rendimento
                        funcione corretamente. Por exemplo, <em>102</em> significa 102% do CDI.
                    </li>
                    <li>
                        <strong>Início</strong> – a data em que o investimento começa a valer.
                    </li>
                    <li>
                        <strong>Cor do cartão</strong> – a cor que vai identificar esse cofrinho na lista.
                    </li>
                    <li>
                        <strong>Observações</strong> – campo opcional para comentários extras
                        (ex.: objetivo, prazo, regras pessoais etc.).
                    </li>
                </ul>

                <p>
                    Depois de preencher todos os campos, toque em <strong>“Salvar”</strong>.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/investment-new.png') }}"
                         alt="Formulário de cadastro de cofrinho com conta, CDI, data e cor"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 2 – Formulário de cadastro/edição do cofrinho.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 3 – Cofrinho criado --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">3. Visualizando o cofrinho criado</h3>
                <p>
                    Após salvar, o cofrinho passa a aparecer na tela de <strong>Investimentos</strong>
                    em formato de cartão, mostrando:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>Nome do investimento/cofrinho;</li>
                    <li>Conta de origem (onde o dinheiro será debitado);</li>
                    <li><strong>Valor total</strong> aplicado;</li>
                    <li><strong>Rendimento acumulado</strong> até o momento;</li>
                    <li>Percentual de CDI configurado;</li>
                    <li>Data em que foi criado.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/investment-card-empty.png') }}"
                         alt="Cartão de cofrinho criado na tela de investimentos"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 3 – Exemplo de cofrinho criado.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 4 – Menu do cofrinho --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">4. Opções do cofrinho (depósito, saque, editar, excluir)</h3>
                <p>
                    Em cada cofrinho existe um botão com <strong>três pontinhos</strong> no canto superior direito.
                    Ao tocar nele, será aberto um painel com as seguintes opções:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Fazer depósito</strong> – para aplicar mais dinheiro naquele investimento.</li>
                    <li><strong>Fazer saque</strong> – para retirar parte ou todo o valor aplicado.</li>
                    <li><strong>Editar</strong> – para alterar nome, conta, % do CDI, cor ou observações.</li>
                    <li><strong>Excluir</strong> – para remover o cofrinho (use com cuidado).</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/investment-menu.png') }}"
                         alt="Menu do cofrinho com opções de depósito, saque, editar e excluir"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 4 – Opções disponíveis ao tocar nos três pontinhos do cofrinho.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 5 – Depósito no investimento --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">5. Fazendo depósito no cofrinho</h3>
                <p>
                    Ao escolher <strong>Fazer depósito</strong>, você informa o valor que deseja aplicar.
                    É importante lembrar que:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li>
                        Você só consegue depositar até o valor que estiver disponível na
                        <strong>conta debitada</strong>. Se tentar um valor acima do saldo,
                        o app não permite concluir.
                    </li>
                    <li>
                        Depois do depósito, o cartão do cofrinho passa a mostrar o
                        <strong>valor aplicado</strong> atualizado.
                    </li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/investment-card-applied.png') }}"
                         alt="Cofrinho com valor aplicado e próximo aniversário"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 5 – Cofrinho com R$ aplicado e informações de rendimento.
                    </figcaption>
                </figure>

                <p>
                    Ao mesmo tempo, a tela de <strong>Contas bancárias</strong> é atualizada, mostrando
                    que parte do saldo foi transferida para os cofrinhos.
                </p>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/accounts-after-investment.png') }}"
                         alt="Conta bancária mostrando saldo em conta e em cofrinhos"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 6 – Conta bancária dividindo quanto está em conta e quanto está em cofrinhos.
                    </figcaption>
                </figure>
            </div>

            {{-- Passo 6 – CDI e rendimento --}}
            <div class="space-y-2">
                <h3 class="text-sm font-semibold">6. CDI e cálculo de rendimento</h3>
                <p>
                    A taxa <strong>% do CDI</strong> que você informa no cadastro é essencial para o
                    cálculo do rendimento. O app usa essa informação para simular, diariamente,
                    quanto o seu investimento estaria rendendo na prática.
                </p>
                <p>
                    Por exemplo, se você configurar <strong>102% do CDI</strong>, o sistema vai calcular
                    o rendimento diário usando a taxa real do CDI, aplicando esse percentual.
                </p>
                <p>
                    Na tela do cofrinho você pode acompanhar:
                </p>

                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Aplicado</strong> – quanto você já colocou nesse investimento.</li>
                    <li><strong>Rendimento acumulado</strong> – quanto esse valor já rendeu até hoje.</li>
                    <li><strong>Próx. aniversário</strong> – a próxima data importante para o cálculo de rendimento.</li>
                </ul>

                <figure class="mt-2">
                    <img src="{{ asset('assets/img/support/investment-card-applied.png') }}"
                         alt="Detalhes do cofrinho com aplicado, rendimento acumulado e CDI"
                         class="w-full max-w-xs rounded-xl border border-neutral-200 dark:border-neutral-700">
                    <figcaption class="mt-1 text-[11px] text-neutral-500">
                        Imagem 7 – Detalhes do investimento com % do CDI e rendimento.
                    </figcaption>
                </figure>
            </div>

            {{-- Link de vídeo --}}
            <div class="pt-3 border-t border-dashed border-neutral-200 dark:border-neutral-700 mt-2">
                <p class="text-xs text-neutral-500 mb-1">
                    Prefere ver tudo em vídeo?
                </p>
                <a href="https://www.youtube.com/seu-video-investimentos"
                   target="_blank"
                   class="inline-flex items-center gap-1 text-xs text-brand-600 hover:underline">
                    Como usar os cofrinhos e investimentos no app
                    <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 17L17 7M7 7h10v10"/>
                    </svg>
                </a>
            </div>
        </div>

    </section>
@endsection
