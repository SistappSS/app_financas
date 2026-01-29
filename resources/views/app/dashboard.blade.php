@extends('layouts.templates.app')

@section('new-content')
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

        <style>
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
    @endpush

    <section class="mt-6 space-y-4">
        <div
            class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            <button
                class="w-full mb-3 hidden items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-3 py-2"
                style="letter-spacing:.75px;font-size:12px;font-weight:600" data-install>
                <i class="fa fa-download"></i> Baixe o aplicativo
            </button>

            <div id="ios-a2hs"
                 class="hidden rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-neutral-50 dark:bg-neutral-800 p-3 mb-2"
                 role="alert">
                No iPhone: toque <strong>Compartilhar</strong> → <strong>Adicionar à Tela de Início</strong> para
                instalar o app.
            </div>

            <button id="ios-enable-push"
                    class="hidden inline-flex items-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white px-3 py-2 text-sm">
                Ativar notificações
            </button>

            <div class="flex items-center justify-between mb-2">
                <h1 class="text-xl font-semibold m-0">Tela inicial</h1>
            </div>

            <form id="monthForm" class="flex items-center justify-between w-full gap-2">
                <button type="button"
                        class="inline-flex items-center justify-center rounded-full border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft size-9"
                        onclick="changeMonth(-1)">
                    <i class="fa fa-chevron-left"></i>
                </button>

                <input type="month" name="month" id="monthPicker"
                       class="w-full max-w-xs text-center font-semibold rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                       value="{{ $startOfMonth->format('Y-m') }}">

                <button type="button"
                        class="inline-flex items-center justify-center rounded-full border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft size-9"
                        onclick="changeMonth(1)">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </form>
        </div>

        <section id="saldo" class="mt-4 md:mt-0">
            <div
                class="relative overflow-hidden rounded-2xl p-5 pb-0 md:p-6 bg-gradient-to-br from-brand-400 to-brand-600 dark:from-neutral-900 dark:to-neutral-800 text-white shadow-soft dark:shadow-softDark">
                <div
                    class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-white/15 dark:bg-neutral-800/80 blur-2xl"></div>

                <div class="flex items-start justify-between gap-1">
                    <div>
                        <p class="text-white/80 text-sm">Saldo total</p>
                        <p id="kpi-balanco" class="mt-1 text-3xl md:text-4xl font-semibold tracking-tight"
                           aria-live="polite">—</p>
                    </div>
                    <a href="#" id="btnRefreshKpis">
    <i class="fa-solid fa-rotate-right mx-1"></i>
</a>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 text-sm/5">
                    <div class="rounded-xl bg-white/10 p-3">
                        <p class="text-white/80">Saldo em contas</p>
                        <div class="shimmer is-loading">
                            <p id="kpi-contas" class="font-medium"></p>
                        </div>
                    </div>
                    <div class="rounded-xl bg-white/10 p-3">
                        <p class="text-white/80">A receber</p>
                        <div class="shimmer is-loading">
                            <span id="kpi-receber" class="font-medium"></span>
                        </div>
                    </div>
                    <div class="rounded-xl bg-white/10 p-3">
                        <p class="text-white/80">A pagar</p>
                        <div class="shimmer is-loading">
                            <span id="kpi-pagar" class="font-medium"></span>
                        </div>
                    </div>
                </div>

                <svg class="w-full h-16 md:h-20 opacity-90" viewBox="0 0 400 80" aria-hidden="true">
                    <defs>
                        <linearGradient id="grad" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="rgba(255,255,255,0.9)"/>
                            <stop offset="100%" stop-color="rgba(255,255,255,0.1)"/>
                        </linearGradient>
                    </defs>
                    <path d="M0 50 C 40 20, 80 70, 120 40 S 200 20, 240 45 S 320 60, 360 35 S 400 70, 400 70"
                          stroke="white" stroke-width="2" fill="url(#grad)"></path>
                </svg>
            </div>
        </section>

        <div id="atalhos" class="mt-5 grid grid-cols-4 gap-2 md:gap-3">
            <a href="{{ route('account-view.index') }}"
               class="group flex flex-col items-center gap-2 p-1 py-2 rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900">
                <span
                    class="grid place-items-center size-10 rounded-xl shadow-brand-600/30 bg-gradient-to-br from-brand-500 to-brand-700 active:scale-95">
                    <i class="fas fa-landmark text-white"></i>
                </span>
                <span class="text-xs">Contas</span>
            </a>
            <a href="{{ route('transaction-view.index') }}"
               class="group flex flex-col items-center gap-2 p-1 py-2 rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900">
                <span
                    class="grid place-items-center size-10 rounded-xl shadow-brand-600/30 bg-gradient-to-br from-brand-500 to-brand-700 active:scale-95">
                    <i class="fa-solid fa-cart-plus text-white"></i>
                </span>
                <span class="text-xs">Transações</span>
            </a>
            <a href="{{ route('card-view.index') }}"
               class="group flex flex-col items-center gap-2 p-1 py-2 rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900">
                <span
                    class="grid place-items-center size-10 rounded-xl shadow-brand-600/30 bg-gradient-to-br from-brand-500 to-brand-700 active:scale-95">
                     <i class="fas fa-credit-card text-white"></i>
                </span>
                <span class="text-xs">Cartões</span>
            </a>
            <a href="{{ route('saving-view.index') }}"
               class="group flex flex-col items-center gap-2 p-1 py-2 rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900">
                <span
                    class="grid place-items-center size-10 rounded-xl shadow-brand-600/30 bg-gradient-to-br from-brand-500 to-brand-700 active:scale-95">
                    <i class="fas fa-chart-line text-white"></i>
                </span>
                <span class="text-xs">Investimentos</span>
            </a>
        </div>

        <!-- Calendar -->
        <div
            class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-3">
            <div id="calendar"></div>
        </div>

        <div id="calendar-results"
             class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-3"></div>

        <!-- Chart Despesas -->
        <div class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4 md:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                    <!-- Voltar -->
                    <button id="pieBack"
                            class="hidden inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-neutral-200/70 dark:border-neutral-800/70">
                        <i class="fa fa-arrow-left text-xs"></i><span>Voltar</span>
                    </button>

                    <!-- Breadcrumbs (rola na horizontal) -->
                    <nav class="relative flex-1 min-w-0 order-2 sm:order-none" aria-label="Breadcrumb">
                        <ol id="pieCrumbs"
                            class="flex items-center gap-1 overflow-x-auto whitespace-nowrap no-scrollbar pr-6 min-w-0">
                            <!-- JS injeta os crumbs -->
                        </ol>
                        <!-- fade da direita (opcional) -->
                        <div class="pointer-events-none absolute right-0 top-0 h-full w-6
                bg-gradient-to-l from-white dark:from-neutral-900 to-transparent"></div>
                    </nav>

                    <!-- Título (não quebra, trunca) -->
                    <span id="pieTitle"
                          class="ml-auto text-sm font-semibold whitespace-nowrap truncate max-w-full sm:max-w-[40%]">
                        <!-- JS injeta o texto -->
                    </span>
                </div>

                <!-- ====== Filtros do Gráfico ====== -->
                <div class="flex items-center gap-2 ml-auto">
                    <label class="sr-only" for="pieTypeFilter">Tipo</label>
                    <select id="pieTypeFilter"
                            class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-2 py-1 text-sm">
                        <option value="all">Todos os tipos</option>
                        <option value="entrada">Entradas</option>
                        <option value="despesa">Despesas</option>
                        <option value="investimento">Investimentos</option>
                    </select>

                    <label class="sr-only" for="pieStatus">Status</label>
                    <select id="pieStatus"
                            class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-2 py-1 text-sm">
                        <option value="all">Todos</option>
                        <option value="paid">Pagos</option>
                        <option value="unpaid">Não pagos</option>
                    </select>
                </div>
                <!-- ====== /Filtros do Gráfico ====== -->
            </div>

            <div class="mt-3 grid md:grid-cols-[360px_1fr] gap-4 items-start">
                <ul id="pieLegend" class="flex flex-wrap gap-3 max-h-24 overflow-auto"></ul>

                <div class="rounded-xl">
                    <canvas id="pieChart" class="w-full h-[280px]"></canvas>
                </div>
                <div>
                    <ul id="pieList" class="divide-y divide-neutral-200/70 dark:divide-neutral-800/70"></ul>
                </div>
            </div>
        </div>

        <!-- Transactions Recent -->
        <div
            class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4 md:p-5">
            <div class="flex items-center justify-between">
                <p class="font-medium">Transações recentes</p>
            </div>

            <ul class="mt-3 divide-y divide-neutral-200/70 dark:divide-neutral-800/70">
                @forelse($recentTransactions as $transaction)
                    @php
                        $categoryType = optional($transaction->transactionCategory)->type;
                        $categoryName = optional($transaction->transactionCategory)->name;
                        $icon = optional($transaction->transactionCategory)->icon;
                        $color = optional($transaction->transactionCategory)->color;
                    @endphp

                    <li class="group grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 transaction-card">
                        <span class="size-10 grid place-items-center rounded-xl text-white"
                              style="background: {{ $color }}">
                            <i class="{{ $icon }}"></i>
                        </span>
                        <div>
                            <p class="text-sm font-medium">{{ ucwords($transaction->title) ?? ucwords($categoryName) }}</p>
                            @if($transaction->type == 'card')
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">No cartão</p>
                            @endif
                            @if($transaction->date)
                                <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">
                                {{ $categoryType === 'despesa' ? '-' : '+' }} {{ brlPrice($transaction->amount) }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3">
                        <span class="size-10 grid place-items-center rounded-xl bg-neutral-100 dark:bg-neutral-800">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </span>
                        <div class="text-sm text-neutral-500">Nenhuma transação encontrada</div>
                        <div></div>
                    </li>
                @endforelse
            </ul>
        </div>

        <!-- Actual Invoices -->
        <h2 class="text-lg font-semibold">Faturas atuais</h2>
        <div
            class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            @if($cardTip)
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-credit-card" style="color: {{ $cardTip['color'] }}"></i>
                    <small class="text-neutral-700 dark:text-neutral-300">{{ $cardTip['label'] }}</small>
                </div>
            @endif
        </div>

        @foreach($currentInvoices as $inv)
            <div
                class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
                <div class="flex items-center justify-between">
                    <span>{{ $inv['title'] }}</span>
                </div>
                <strong class="text-lg">{{ $inv['total_brl'] }}</strong>

                <div class="flex items-center justify-between mt-1">
                    <span>Vence em <b>{{ $inv['due_label'] }}.</b></span>
                </div>
            </div>
        @endforeach
    </section>

    <!-- Check Payment -->
<x-modal id="paymentModal" titleCreate="Registrar pagamento" titleEdit="Registrar pagamento" titleShow="Registrar pagamento" submitLabel="Salvar">
    @csrf

    <input type="hidden" name="transaction_id" id="payment_transaction_id">
    <input type="hidden" name="due_date" id="payment_due_date"> {{-- IMPORTANTE --}}

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <label class="block">
            <span class="text-xs text-neutral-500 dark:text-neutral-400">Valor pago</span>
            <input type="text" inputmode="decimal" name="amount" id="payment_amount"
                   class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                   required>
        </label>
        <label class="block">
            <span class="text-xs text-neutral-500 dark:text-neutral-400">Data do pagamento</span>
            <input type="date" name="payment_date" id="payment_date"
                   class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                   required>
        </label>
    </div>
</x-modal>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

        <script>
            function startLoading(...ids) {
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    el?.closest('.shimmer')?.classList.add('is-loading');
                    el?.closest('.shimmer')?.classList.remove('is-loaded');
                });
            }

            function finishLoading(...ids) {
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    el?.closest('.shimmer')?.classList.remove('is-loading');
                    el?.closest('.shimmer')?.classList.add('is-loaded');
                });
            }

            const PAY_TPL = @json(route('transaction-payment', ['transaction' => '__ID__']));
            const INVOICE_PAY_TPL = @json(route('invoice-payment.update', ['cardId' => '__CARD__', 'ym' => '__YM__']));
            let CURRENT_TX_CARD = null;
            let CURRENT_ID = null, CURRENT_TITLE = null, CURRENT_DUE_DATE = null;

            function getPaymentEls() {
    const modal = document.getElementById('paymentModal');
    const form  = modal?.querySelector('form');
    const inAmt = modal?.querySelector('#payment_amount,[name="amount"]');
    const inDate= modal?.querySelector('#payment_date,[name="payment_date"],[name="date"]');
    const dueHidden = modal?.querySelector('#payment_due_date,[name="due_date"]'); // NOVO
    return {modal, form, inAmt, inDate, dueHidden};
}



            function showPaymentModal() {
                const {modal} = getPaymentEls();
                modal?.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'ui-modal-open');
            }

            function hidePaymentModal() {
                const {modal} = getPaymentEls();
                modal?.classList.add('hidden');
                document.body.classList.remove('overflow-hidden', 'ui-modal-open');
            }

            function parseMoneyBR(input) {
                if (typeof input === 'number') return input;
                let s = String(input || '').trim();
                s = s.replace(/[^\d.,-]/g, '');
                const lastComma = s.lastIndexOf(','), lastDot = s.lastIndexOf('.');
                if (lastComma > -1 && lastDot > -1) {
                    if (lastComma > lastDot) s = s.replace(/\./g, '').replace(',', '.');
                    else s = s.replace(/,/g, '');
                } else if (lastComma > -1) {
                    s = s.replace(',', '.');
                }
                return Number(s || 0);
            }

            const formatBRL = v => Number(v || 0).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});

            function renderBreakdown(elId, k, root, label = 'atrasados') {
                const el = document.getElementById(elId);
                if (!el) return;
                const mes = k[`${root}_mes_brl`];
                const extra = k[`${root}_atrasados_brl`];
                if (typeof mes === 'string' && typeof extra === 'string') {
                    el.innerHTML = `${mes}<span class="price-default mx-1"><span class="late"> + ${extra} ${label}</span></span>`;
                } else {
                    const tot = k[`${root}_brl`];
                    if (typeof tot === 'string') el.textContent = tot;
                }
            }

            function renderSaldoCofrinhos(elId, contasBRL, cofrinhosBRL) {
                const el = document.getElementById(elId);
                if (!el) return;
                const contas = (typeof contasBRL === 'string') ? contasBRL : '';
                const cofr = (typeof cofrinhosBRL === 'string') ? cofrinhosBRL : '';
                el.innerHTML = cofr
                    ? `${contas}<span class="price-default mx-1"><span class="late"> +  ${cofr} cofrinhos</span></span>`
                    : contas;
            }

            function applyKpis(k) {
                renderSaldoCofrinhos('kpi-contas', k.accountsBalance_brl, k.savingsBalance_brl);
                renderBreakdown('kpi-receber', k, 'aReceber', 'pendentes');
                renderBreakdown('kpi-pagar', k, 'aPagar', 'atrasados');

                const total = (k.saldoPrevisto_brl || k.saldoMes_brl || k.saldoReal_brl || '');
                const saldoEl = document.getElementById('saldoValor');
                if (saldoEl) saldoEl.textContent = total;

                const bal = document.getElementById('kpi-balanco');
                if (bal) bal.textContent = total; // compat se existir
            }

            document.addEventListener('click', (e) => {
    const btnPayTx = e.target.closest('[data-open-payment]');
    if (btnPayTx) {
        const {form, inAmt, inDate, dueHidden} = getPaymentEls();
        if (!form) {
            alert('Formulário do pagamento não encontrado.');
            return;
        }

        CURRENT_TX_CARD = btnPayTx.closest('.transaction-card') || null;
        CURRENT_ID      = btnPayTx.dataset.id;
        CURRENT_TITLE   = btnPayTx.dataset.title || 'Pagamento';
        CURRENT_DUE_DATE = (btnPayTx.dataset.date || '').slice(0, 10);

form.action = PAY_TPL.replace('__ID__', CURRENT_ID);
if (inAmt)      inAmt.value = btnPayTx.dataset.amount || '0';
if (inDate)     inDate.value = CURRENT_DUE_DATE;
if (dueHidden)  dueHidden.value = CURRENT_DUE_DATE; // aqui ele passa 29/12/2025
        showPaymentModal();
        return;
    }

    const btnPayInv = e.target.closest('[data-pay-invoice]');
    if (btnPayInv) payInvoice(btnPayInv);
});

            async function payInvoice(btn) {
                const cardEl = btn.closest('.transaction-card');
                const cardId = btn.dataset.card;
                const ym = btn.dataset.month;
                const amt = Number(btn.dataset.amount || 0);
                const title = btn.dataset.title || 'Fatura paga';
                const url = INVOICE_PAY_TPL.replace('__CARD__', cardId).replace('__YM__', ym);

                btn.disabled = true;
                try {
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json'},
                        credentials: 'same-origin'
                    });
                    if (!resp.ok) throw new Error(await resp.text());

                    const currentMonth = document.getElementById('monthPicker')?.value || '';
                    if (currentMonth) {
                        const r = await fetch(`{{ route('dashboard.kpis') }}?month=${currentMonth}`, {headers: {Accept: 'application/json'}});
                        if (r.ok) applyKpis(await r.json());
                    }

                    if (cardEl) cardEl.remove();

                    const cal = window.__cal;
                    if (cal) {
                        Object.keys(cal.eventosCache).forEach(day => {
                            const map = cal.eventosCache[day];
                            if (!map) return;
                            const toDel = [];
                            map.forEach((ev, key) => {
                                if (ev.is_invoice && !ev.paid && ev.card_id == cardId && ev.current_month == ym) {
                                    toDel.push(key);
                                }
                            });
                            toDel.forEach(k => map.delete(k));
                        });

                        const todayIso = cal.iso(new Date());
                        const mapToday = cal.eventosCache[todayIso] ?? (cal.eventosCache[todayIso] = new Map());
                        const id = `invpay_local_${cardId}_${ym}_${Date.now()}`;
                        mapToday.set(id, {
                            id,
                            tipo: 'payment',
                            color: '#0ea5e9',
                            icon: 'fa-regular fa-circle-check',
                            descricao: title,
                            valor: Math.abs(amt),
                            valor_brl: Number(Math.abs(amt)).toLocaleString('pt-BR', {
                                style: 'currency',
                                currency: 'BRL'
                            }),
                            is_invoice: true,
                            paid: true,
                            card_id: cardId,
                            current_month: ym
                        });

                        cal.fp.redraw();
                        const sel = cal.fp.selectedDates?.[0] ? cal.iso(cal.fp.selectedDates[0]) : todayIso;
                        cal.exibirEventos(sel);
                    }
                } catch (err) {
                    alert('Erro ao pagar fatura: ' + (err.message || ''));
                    btn.disabled = false;
                }
            }

           document.getElementById('paymentModal')?.addEventListener('submit', async (e) => {
    const form = e.target;
    if (form.tagName !== 'FORM') return;
    e.preventDefault();

    const {inAmt, inDate, dueHidden} = getPaymentEls(); // <-- pega o hidden também
    const fd = new FormData(form);

    // garante que a data da ocorrência vá pro back
    if (dueHidden && dueHidden.value) {
        fd.set('due_date', dueHidden.value);           // <<< ESSA LINHA É O PONTO
    }

    const currentMonth = document.getElementById('monthPicker')?.value || '';
    if (currentMonth) fd.set('month', currentMonth);


                try {
                    const resp = await fetch(form.action, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json'},
                        body: fd,
                        credentials: 'same-origin'
                    });

                    if (resp.status === 422) {
                        const j = await resp.json();
                        throw new Error(Object.values(j.errors || {})[0]?.[0] || 'Dados inválidos.');
                    }
                    if (!resp.ok) throw new Error(await resp.text());

                    const payDate = (inDate?.value || '').slice(0, 10);
                    const dueDate = (CURRENT_DUE_DATE || payDate);
                    const payAmount = parseMoneyBR(inAmt?.value || 0);

                    let data = null;
                    try {
                        data = await resp.json();
                    } catch (_) {
                    }

                    if (data && data.ok) {
                        applyKpis(data);
                    } else if (currentMonth) {
                        const r = await fetch(`{{ route('dashboard.kpis') }}?month=${currentMonth}`, {headers: {Accept: 'application/json'}});
                        if (r.ok) applyKpis(await r.json());
                    }

                    hidePaymentModal();

                    if (CURRENT_TX_CARD) {
                        CURRENT_TX_CARD.remove();
                        CURRENT_TX_CARD = null;
                    }
                    form.reset();

                    const cal = window.__cal;
                    if (cal && cal.eventosCache) {
                        const mapDue = cal.eventosCache[dueDate];
                        if (mapDue) {
                            const toDel = [];
                            mapDue.forEach((ev, key) => {
                                if (ev.tx_id === CURRENT_ID && (ev.tipo === 'despesa' || ev.tipo === 'entrada')) {
                                    toDel.push(key);
                                }
                            });
                            toDel.forEach(k => mapDue.delete(k));
                        }

                        if (payDate && payDate !== dueDate) {
                            const mapPayRed = cal.eventosCache[payDate];
                            if (mapPayRed) {
                                const toDel2 = [];
                                mapPayRed.forEach((ev, key) => {
                                    if (ev.tx_id === CURRENT_ID && (ev.tipo === 'despesa' || ev.tipo === 'entrada')) {
                                        toDel2.push(key);
                                    }
                                });
                                toDel2.forEach(k => mapPayRed.delete(k));
                            }
                        }

                        const mapPay = cal.eventosCache[payDate] ?? (cal.eventosCache[payDate] = new Map());
                        const pid = `localpay_${CURRENT_ID}_${payDate}`;
                        mapPay.set(pid, {
                            id: pid,
                            tipo: 'payment',
                            color: '#0ea5e9',
                            icon: 'fa-regular fa-circle-check',
                            descricao: (CURRENT_TITLE || 'Pagamento'),
                            valor: Math.abs(payAmount),
                            valor_brl: formatBRL(Math.abs(payAmount)),
                            is_invoice: false,
                            paid: true,
                            card_id: null,
                            current_month: null,
                            invoice_id: null,
                            tx_id: CURRENT_ID
                        });

                        cal.fp.redraw();
                        const sel = cal.fp.selectedDates?.[0] ? cal.iso(cal.fp.selectedDates[0]) : payDate;
                        cal.exibirEventos(sel);
                    }

                    CURRENT_ID = null;
                    CURRENT_TITLE = null;
                    CURRENT_DUE_DATE = null;
                } catch (err) {
                    alert(err.message || 'Erro ao registrar pagamento.');
                }
            });

            document.getElementById('btnRefreshKpis')?.addEventListener('click', async () => {
                const ym = document.getElementById('monthPicker')?.value || '';
                if (ym) {
                    const r = await fetch(`{{ route('dashboard.kpis') }}?month=${ym}&cumulative=1`, {headers: {Accept: 'application/json'}});
                    if (r.ok) applyKpis(await r.json());
                }
            });

            (function calendarBoot() {
                const routeUrl = "{{ route('calendar.events') }}";
                const ym = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
                const iso = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                const br = s => {
                    const [y, m, d] = String(s).slice(0, 10).split('-');
                    return `${d}/${m}/${y}`;
                };
                const brl = n => Number(n || 0).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                const escAttr = s => String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

                const eventosCache = {};
                const loadedWindows = new Set();

                function addEventToCache(ev) {
                    const day = String(ev.start).slice(0, 10);
                    const xp = ev.extendedProps || {};
                    let key = ev.id ?? `${ev.title}-${ev.start}`;

                    const tipo = (xp.type || '').toLowerCase().trim();
                    const isTxLaunch = (tipo === 'entrada' || tipo === 'despesa') && xp.transaction_id;
                    if (isTxLaunch) key = `tx_${xp.transaction_id}_${day}`;

                    const item = {
                        id: key, tipo, color: ev.bg, icon: ev.icon,
                        descricao: ev.title ?? xp.category_name ?? 'Sem descrição',
                        valor: Number(xp.amount ?? 0),
                        valor_brl: xp.amount_brl,
                        is_invoice: !!xp.is_invoice,
                        paid: !!xp.paid,
                        card_id: xp.card_id || null,
                        current_month: xp.current_month || null,
                        invoice_id: xp.invoice_id || null,
                        tx_id: xp.transaction_id || null,
                    };
                    const map = eventosCache[day] ?? (eventosCache[day] = new Map());
                    if (!map.has(key)) map.set(key, item);
                }

                async function loadWindow(ymStr, months = 2) {
                    const key = `${ymStr}:${months}`;
                    if (loadedWindows.has(key)) return;
                    const resp = await fetch(`${routeUrl}?start=${ymStr}&months=${months}`, {headers: {'Accept': 'application/json'}});
                    if (!resp.ok) return;
                    (await resp.json()).forEach(addEventToCache);
                    loadedWindows.add(key);
                }

              const eventosDoDia = dateStr => {
    const map = eventosCache[dateStr];
    if (!map) return [];

    // ignora pagamentos com valor 0 (adiantamento / ajuste)
    return Array.from(map.values()).filter(ev => {
        const isZeroPayment =
            ev.tipo === 'payment' && Number(ev.valor || 0) === 0;

        return !isZeroPayment;
    });
};


                function exibirEventos(dateStr) {
                    const c = document.getElementById('calendar-results');
                    const eventos = eventosDoDia(dateStr);

                    let html = `<h2 class="mt-3 text-lg font-semibold">Lançamentos do dia ${br(dateStr)}</h2>`;
                    if (!eventos.length) {
                        html += `<div class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 transaction-card ">
                 <span class="size-10 grid place-items-center rounded-xl bg-neutral-100 dark:bg-neutral-800"><i class="fa-solid fa-sack-dollar"></i></span>
                 <div class="text-sm">Nenhum lançamento.</div><div></div>
               </div>`;
                        c.innerHTML = html;
                        return;
                    }

                    for (const ev of eventos) {
                        // esconder pagamentos de valor 0 (não faz sentido mostrar)
    const isZeroPayment = ev.tipo === 'payment'
        && !ev.is_invoice
        && Math.abs(ev.valor || 0) < 0.005;

    if (isZeroPayment) {
        continue;
    }

                        const isPaidInv = ev.is_invoice && ev.paid === true;
                        const iconCls = isPaidInv ? 'fa-regular fa-circle-check' : (ev.icon || 'fa-solid fa-file-invoice-dollar');
                        const bgColor = isPaidInv ? '#0ea5e9' : (ev.color || '#999');

                        let amountHtml = ev.valor_brl, sinal = '';
                        if (!isPaidInv) {
                            sinal = ev.tipo === 'despesa' ? '-' : (ev.tipo === 'entrada' ? '+' : '');
                        } else {
                            amountHtml = brl(Math.abs(ev.valor || 0));
                        }

                        let action = '';
                        if (ev.is_invoice && ev.card_id && ev.current_month && !ev.paid) {
                            action = `<button type="button" class="bg-transparent border-0"
                    data-pay-invoice data-card="${ev.card_id}" data-month="${ev.current_month}"
                    data-amount="${Math.abs(ev.valor || 0)}" data-title="${escAttr(ev.descricao)}">
                    <i class="fa-solid fa-check text-green-600"></i></button>`;
                        } else if ((ev.tipo === 'despesa' || ev.tipo === 'entrada') && ev.tx_id && !ev.paid) {
                            action = `<button type="button" class="bg-transparent border-0"
                    data-open-payment data-id="${ev.tx_id}"
                    data-amount="${Math.abs(ev.valor)}" data-date="${dateStr}" data-title="${escAttr(ev.descricao)}">
                    <i class="fa-solid fa-check-to-slot text-green-600"></i></button>`;
                        }

                        html += `
        <div class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 transaction-card ">
          <span class="size-10 grid place-items-center rounded-xl text-white" style="background-color:${bgColor}">
            <i class="${iconCls}"></i>
          </span>
          <div>
            <p class="text-sm font-medium">${ev.descricao}</p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">${br(dateStr)}</p>
          </div>
          <div class="text-right flex items-center gap-3">
            <p class="text-sm font-semibold">${sinal} ${amountHtml}</p>
            ${action}
          </div>
        </div>`;
                    }
                    c.innerHTML = html;
                }

                async function atualizarKpisDoMes(ymStr) {
                    startLoading('kpi-contas', 'kpi-receber', 'kpi-pagar', 'kpi-balanco', 'saldoValor');
                    try {
                        const url = `{{ route('dashboard.kpis') }}?month=${encodeURIComponent(ymStr)}&cumulative=1`;
                        const r = await fetch(url, {headers: {Accept: 'application/json'}});
                        if (!r.ok) throw new Error('Falha ao carregar KPIs');
                        const k = await r.json();
                        renderSaldoCofrinhos('kpi-contas', k.accountsBalance_brl, k.savingsBalance_brl);
                        renderBreakdown('kpi-receber', k, 'aReceber', 'pendentes');
                        renderBreakdown('kpi-pagar', k, 'aPagar', 'atrasados');
                        const total = (k.saldoPrevisto_brl || k.saldoMes_brl);
                        const saldoEl = document.getElementById('saldoValor');
                        if (saldoEl) saldoEl.textContent = total || '—';
                        const bal = document.getElementById('kpi-balanco');
                        if (bal) bal.textContent = total || '—';
                    } catch (e) {
                        console.error(e);
                    } finally {
                        finishLoading('kpi-contas', 'kpi-receber', 'kpi-pagar', 'kpi-balanco', 'saldoValor');
                    }
                }

                async function syncMonthUI(ymStr) {
                    const [y, m] = ymStr.split('-').map(Number);
                    const first = new Date(y, m - 1, 1);
                    await loadWindow(ymStr, 2);
                    fp.jumpToDate(first, true);
                    fp.setDate(first, true);
                    fp.redraw();
                    exibirEventos(iso(first));
                    atualizarKpisDoMes(ymStr);
                }

                window.syncMonthUI = syncMonthUI;

                async function changeMonth(delta) {
                    const input = document.getElementById('monthPicker');
                    const [y, m] = input.value.split('-').map(Number);
                    const next = new Date(y, m - 1 + delta, 1);
                    const ymStr = ym(next);
                    input.value = ymStr;
                    await syncMonthUI(ymStr);

                    window.refreshPie?.(true);
                }

                window.changeMonth = changeMonth;

                let fp;

document.addEventListener('DOMContentLoaded', () => {
    fp = flatpickr('#calendar', {
        locale: flatpickr.l10ns.pt,   // usa pt nativo (começa em SEGUNDA)
        inline: true,
        static: true,
        defaultDate: 'today',
        disableMobile: true,

        onDayCreate: (_, _2, _3, dayElem) => {
            const d = iso(dayElem.dateObj);
            const evs = eventosDoDia(d);
            if (!evs.length) return;

            const hasGreen = evs.some(e => e.tipo === 'entrada');
            const hasRed = evs.some(
                e =>
                    (e.tipo === 'despesa' && !e.is_invoice) ||
                    (e.is_invoice && !e.paid)
            );
            const hasBlue = evs.some(
                e =>
                    e.tipo === 'investimento' ||
                    e.tipo === 'payment' ||
                    (e.is_invoice && e.paid)
            );

            const wrap = document.createElement('div');
            wrap.style.cssText =
                'display:flex;justify-content:center;gap:2px;margin-top:-10px';
            const dot = c => {
                const s = document.createElement('span');
                s.style.cssText =
                    'width:6px;height:6px;background:' +
                    c +
                    ';border-radius:50%';
                wrap.appendChild(s);
            };
            if (hasGreen) dot('green');
            if (hasRed) dot('red');
            if (hasBlue) dot('#0ea5e9');
            if (wrap.childElementCount) dayElem.appendChild(wrap);
        },

        onMonthChange: async (_sd, _ds, inst) => {
            const first = new Date(inst.currentYear, inst.currentMonth, 1);
            const ymStr = ym(first);
            await loadWindow(ymStr, 2);
            inst.redraw();
            document.getElementById('monthPicker').value = ymStr;
            atualizarKpisDoMes(ymStr);
            window.refreshPie?.(true);
        },

        onYearChange: async (_sd, _ds, inst) => {
            const first = new Date(inst.currentYear, inst.currentMonth, 1);
            const ymStr = ym(first);
            await loadWindow(ymStr, 2);
            inst.redraw();
            document.getElementById('monthPicker').value = ymStr;
            atualizarKpisDoMes(ymStr);
            window.refreshPie?.(true);
        },

        onReady: async (sd, _ds, inst) => {
            try {
                const calEl = inst.calendarContainer;
                calEl.classList.add('animate', 'arrowTop');
                if (document.fonts && document.fonts.ready) await document.fonts.ready;
            } catch (_) {}

            const first = new Date(inst.currentYear, inst.currentMonth, 1);
            const ymStr = ym(first);
            await loadWindow(ymStr, 2);

            requestAnimationFrame(() => inst.redraw());
            setTimeout(() => {
                try { inst.redraw(); } catch (_) {}
            }, 120);

            exibirEventos(iso(sd?.[0] ?? new Date()));
            const initialYm = document.getElementById('monthPicker').value || ymStr;
            atualizarKpisDoMes(initialYm);

            window.refreshPie?.(true);
        },

        onChange: sd => {
            if (sd?.[0]) exibirEventos(iso(sd[0]));
        },
    });

    // resto igual
    window.__cal = { fp, eventosCache, exibirEventos, iso };
});
            })();
        </script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            (() => {
                const monthPicker = document.getElementById('monthPicker');
                const statusSel   = document.getElementById('pieStatus');
                const typeSel     = document.getElementById('pieTypeFilter');

                const ctx = document.getElementById('pieChart').getContext('2d');
                const listEl = document.getElementById('pieList');
                const titleEl = document.getElementById('pieTitle');
                const backBtn = document.getElementById('pieBack');
                const crumbsEl = document.getElementById('pieCrumbs');

                const state = {
                    stack: [],
                    current: { level: 'type', params: {} },   // contexto atual do gráfico
                    lastPayload: null,
                    chart: null,
                    ac: null                                   // AbortController
                };

                function textColor() {
                    return document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#111827';
                }

                function currencyBRL(v) {
                    return (v ?? 0).toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});
                }

                function renderCrumbs(bc) {
                    const wrap = document.getElementById('pieCrumbs');
                    wrap.innerHTML = '';
                    if (!bc?.length) return;

                    bc.forEach((b, i) => {
                        const btn = document.createElement('button');
                        btn.className = 'px-2 py-1 rounded hover:bg-neutral-100 dark:hover:bg-neutral-800 shrink-0 max-w-[60vw] truncate';
                        btn.title = b.label; // tooltip
                        btn.textContent = b.label;
                        btn.onclick = () => {
                            state.stack = [];
                            load(b.level, b.params || {});
                        };
                        wrap.appendChild(btn);

                        if (i < bc.length - 1) {
                            const sep = document.createElement('span');
                            sep.className = 'mx-1 shrink-0';
                            sep.textContent = '›';
                            wrap.appendChild(sep);
                        }
                    });
                }

                function renderList(items, visibleMask) {
                    const rows = items
                        .map((i, idx) => ({...i, __visible: visibleMask[idx]}))
                        .filter(i => i.__visible);

                    const total = rows.reduce((s, r) => s + (r.value || 0), 0);

                    listEl.innerHTML = [
                        `<li class="py-2 flex items-center justify-between font-semibold">
                           <span>Total visível</span><span>${currencyBRL(total)}</span>
                         </li>`,
                        ...rows.map(i => `
                          <li class="py-2 flex items-center justify-between">
                            <button class="text-left flex-1 pr-3 hover:underline" data-next='${JSON.stringify(i.next || null)}'>
                              ${i.label}
                            </button>
                            <strong>${currencyBRL(i.value)}</strong>
                          </li>
                        `)
                    ].join('');

                    listEl.querySelectorAll('button[data-next]').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const next = JSON.parse(btn.getAttribute('data-next'));
                            if (next && next.level) pushAndLoad(next.level, next.params || {});
                        });
                    });
                }

                function renderChart(payload) {
                    titleEl.textContent = payload.title || 'Distribuição';
                    renderCrumbs(payload.breadcrumbs || []);
                    backBtn.classList.toggle('hidden', state.stack.length === 0);

                    const labels = payload.items.map(i => i.label);
                    const values = payload.items.map(i => Number(i.value || 0));
                    const total  = values.reduce((s,v)=>s+v,0);

                    if (!values.length || total <= 0) {
                        if (state.chart) state.chart.destroy();
                        state.chart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: ['Sem dados'],
                                datasets: [{
                                    data: [1],
                                    backgroundColor: ['#e5e7eb'],
                                    borderColor: ['#cbd5e1'],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                devicePixelRatio: Math.min(window.devicePixelRatio || 1, 2),
                                elements: { arc: { borderAlign: 'inner', hoverBorderWidth: 0, hoverOffset: 0, borderJoinStyle: 'round' } },
                                cutout: '65%',
                                radius: '85%',
                                plugins: { legend: { display: false }, tooltip: { enabled: false } }
                            }
                        });

                        document.getElementById('pieLegend').innerHTML = '';
                        listEl.innerHTML = `
                          <li class="py-2 flex items-center justify-between font-semibold">
                            <span>Total visível</span><span>R$ 0,00</span>
                          </li>
                          <li class="py-2 text-sm text-neutral-500 dark:text-neutral-400">Nenhum dado neste mês.</li>
                        `;

                        state.lastPayload = payload;
                        return;
                    }

                    const withAlpha = c => {
                        if (!c) return undefined;
                        let h = String(c).replace('#','');
                        if (h.length === 3) h = h.split('').map(x=>x+x).join('');
                        if (h.length === 8) h = h.slice(0,6);
                        return '#'+h+'1a';
                    };
                    const colorsBg     = payload.items.map(i => i.bg ?? withAlpha(i.color));
                    const colorsBorder = payload.items.map(i => i.border ?? i.color ?? '#94a3b8');

                    if (state.chart) state.chart.destroy();
                    state.chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: { labels, datasets: [{ data: values, backgroundColor: colorsBg, borderColor: colorsBorder, borderWidth: 1 }] },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            devicePixelRatio: Math.min(window.devicePixelRatio || 1, 2),
                            elements: { arc: { borderAlign: 'inner', hoverBorderWidth: 0, hoverOffset: 0, borderJoinStyle: 'round' } },
                            cutout: '65%', radius: '85%',
                            plugins: { tooltip: { callbacks: { label: ctx => `${ctx.label}: ${(ctx.parsed ?? 0).toLocaleString('pt-BR',{style:'currency',currency:'BRL'})}` } }, legend: { display: false } }
                        }
                    });

                    const legendEl = document.getElementById('pieLegend');
                    function renderHtmlLegend() {
                        const meta = state.chart.getDatasetMeta(0);
                        legendEl.innerHTML = payload.items.map((it, idx) => `
                          <li data-idx="${idx}" class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <span class="inline-block w-4 h-3 rounded-sm"
                                  style="background:${it.bg ?? it.color+'1a'};border:2px solid ${it.border ?? it.color};"></span>
                            <span class="text-xs">${it.label}</span>
                          </li>`).join('');
                        legendEl.querySelectorAll('[data-idx]').forEach(li => {
                            li.addEventListener('click', () => {
                                const i = +li.dataset.idx;
                                const el = meta.data[i];
                                el.hidden = !el.hidden;
                                state.chart.update();
                                updateListAndTotal();
                                li.classList.toggle('opacity-40', el.hidden);
                            });
                        });
                    }

                    function updateListAndTotal() {
                        const meta = state.chart.getDatasetMeta(0);
                        const visible = payload.items.map((_, i) => !(meta.data[i]?.hidden));
                        renderList(payload.items, visible);
                    }

                    document.getElementById('pieChart').onclick = (evt) => {
                        const points = state.chart.getElementsAtEventForMode(evt, 'nearest', {intersect: true}, true);
                        if (!points.length) return;
                        const i = points[0].index;
                        const next = payload.items[i].next;
                        if (next && next.level) pushAndLoad(next.level, next.params || {});
                    };

                    state.lastPayload = payload;
                    window.updatePieListAndTotal = updateListAndTotal;

                    renderHtmlLegend();
                    updateListAndTotal();
                }

                function pushAndLoad(level, params) {
                    state.stack.push({ ...state.current });
                    load(level, params);
                }

                async function load(level = 'type', params = {}) {
                    state.current = { level, params };

                    if (state.ac) state.ac.abort();
                    state.ac = new AbortController();
                    const { signal } = state.ac;

                    const month  = monthPicker?.value;
                    const status = statusSel?.value || 'all';

                    const searchParams = new URLSearchParams({ level, month, status, ...params });

                    try {
                        const r = await fetch(`{{ route('analytics.pie') }}?` + searchParams.toString(), { signal });
                        const payload = await r.json();
                        if (signal.aborted) return;
                        renderChart(payload);
                    } catch (e) {
                        if (signal.aborted) return;
                        renderChart({ level: 'type', title: 'Sem dados', breadcrumbs: [], items: [] });
                    }
                }

                window.refreshPie = (keepContext = true) => {
                    // Recarrega mantendo o nível atual (type/category/pay/etc)
                    const target = keepContext && state.current ? state.current : { level: 'type', params: {} };
                    load(target.level, target.params || {});
                };

                backBtn.addEventListener('click', () => {
                    const prev = state.stack.pop();
                    if (!prev) return;
                    load(prev.level, prev.params || {});
                });

                // Filtro de Status: só recarrega mantendo contexto atual
                statusSel?.addEventListener('change', () => window.refreshPie(true));

                // Filtro de Tipo:
                // - all  => nível "type"
                // - outro => nível "category" já com o type selecionado
                typeSel?.addEventListener('change', () => {
                    state.stack = [];
                    const t = (typeSel.value || 'all');
                    if (t === 'all') {
                        load('type', {});
                    } else {
                        load('category', { type: t });
                    }
                });

                // recarrega quando muda o mês ou tema
                monthPicker?.addEventListener('change', () => window.refreshPie(true));

                const obs = new MutationObserver(() => {
                    if (!state.lastPayload) return;
                    renderChart(state.lastPayload);
                });
                obs.observe(document.documentElement, {attributes: true, attributeFilter: ['class']});

                // inicial
                load('type', {});
            })();
        </script>
    @endpush
@endsection
