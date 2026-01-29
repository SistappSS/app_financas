@extends('layouts.templates.app')
@section('new-content')
    <div class="flex items-center justify-between my-4">
        <div>
            <h2 class="text-xl font-semibold">Faturas</h2>
            <p class="text-sm text-neutral-500 dark:text-neutral-400">Acompanhe as faturas do seu
                cartão {{$card->account?->bank_name . ' ' . $card->last_four_digits}}</p>
        </div>
        <div class="hidden md:flex items-center gap-2">
            <button data-open-modal="acc"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nova conta
            </button>
        </div>
    </div>

    {{-- Carrossel de meses --}}
    <div class="icons-carousel" id="months">
        @foreach($invoices as $inv)
            <button
                class="icon-button nav-link-atalho month-btn status-{{ $inv->status }} {{ $selectedYm === $inv->ym ? 'active' : '' }}"
                data-ym="{{ $inv->ym }}"
                data-status="{{ $inv->status }}"
                data-paid="{{ $inv->paid ? 1 : 0 }}"
            >
                <span class="month-pill">{{ $inv->month }}</span>
                <b>{{ $inv->total }}</b>
            </button>
        @endforeach
    </div>

    {{-- Header da fatura selecionada --}}
    <div class="balance-box m-0 mb-3 status-{{ $header['status'] }}" id="invoiceHeader"
         data-card="{{ $card->id }}"
         data-status="{{ $header['status'] }}">
        <div class="d-flex align-items-center justify-content-between">

            @php
                $totalRaw = (float) ($header['total_raw'] ?? 0);
                // mostra botão só se NÃO estiver paga e tiver valor > 0
                $hidePay = $header['status'] === 'paid' || $totalRaw <= 0;

                $status = $header['status'];
                $badgeClasses = match ($status) {
                    'paid'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                    'overdue' => 'bg-red-50 text-red-700 ring-1 ring-red-200',
                    default   => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                };
            @endphp

            <div class="flex items-start justify-between">
                {{-- ESQUERDA: texto + badge alinhados à esquerda --}}
                <div>
        <span class="block text-sm font-semibold" id="hdr-month">
            Fatura de {{ $header['month_label'] }}
        </span>

                    <span
                        id="invoiceStatusBadge"
                        class="mt-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses }}"
                    >
            @if($status === 'paid')
                            Paga
                        @elseif($status === 'overdue')
                            Em atraso
                        @else
                            Em aberto
                        @endif
        </span>
                </div>

                {{-- DIREITA: botão "Pagar fatura" no extremo direito --}}
                <button
                    type="button"
                    id="payment-invoice-btn"
                    class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 hover:text-brand-700 pay-invoice-open {{ $hidePay ? 'hidden' : '' }}"
                    data-ym="{{ $header['ym'] }}"
                    data-total-raw="{{ $header['total_raw'] }}"
                    data-total-formatted="{{ $header['total'] }}"
                >
                    <i class="fa-solid fa-check-to-slot me-1"></i>
                    Pagar fatura
                </button>
            </div>


        </div>

        <strong id="hdr-total">{{ $header['total'] }}</strong>
        <span>Limite disponível <b id="hdr-limit">{{ $header['limit'] }}</b></span>
        <span class="closing-date" id="hdr-close">{!! $header['close_label'] !!}</span>
        <span class="due-date" id="hdr-due">{!! $header['due_label'] !!}</span>

        <button
            type="button"
            id="newInvoiceItemBtn"
            class="mt-3 inline-flex items-center gap-1 self-start text-sm font-medium text-brand-600 hover:text-brand-700"
        >
            <i class="fa-solid fa-plus"></i>
            Novo item
        </button>
    </div>

    <ul id="invoiceItems" class="swipe-list"></ul>

    <div id="confirmDeleteItem"
         class="fixed inset-0 z-[60] hidden"
         role="dialog"
         aria-modal="true"
         aria-labelledby="deleteItemTitle">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-del-overlay></div>

        {{-- Sheet --}}
        <div
            class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-[420px]">
            <div
                class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-4 md:p-6">
                <div class="flex items-start justify-between">
                    <h5 id="deleteItemTitle" class="text-lg font-semibold">Remover item</h5>
                    <button type="button"
                            id="deleteCloseBtn"
                            class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
                            aria-label="Fechar">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18"/>
                            <path d="M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <p class="mt-3 text-sm text-neutral-600 dark:text-neutral-300">
                    Deseja remover este item da fatura?
                </p>

                <div class="mt-5 flex items-center justify-end gap-2">
                    <button type="button"
                            id="deleteCancelBtn"
                            class="px-3 py-2 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                        Cancelar
                    </button>
                    <button type="button"
                            id="deleteConfirmBtn"
                            class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white shadow-soft">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="payInvoiceModal"
         class="fixed inset-0 z-[60] hidden"
         role="dialog"
         aria-modal="true"
         aria-labelledby="payInvoiceTitle">

        {{-- overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-pay-overlay></div>

        {{-- sheet --}}
        <div
            class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-[420px]">
            <div
                class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-4 md:p-6">
                <div class="flex items-start justify-between">
                    <h5 id="payInvoiceTitle" class="text-lg font-semibold">Confirmar pagamento da fatura</h5>
                    <button type="button"
                            class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
                            data-pay-close
                            aria-label="Fechar">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18"/>
                            <path d="M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="payInvoiceFormModal" method="POST" class="mt-4 space-y-4">
                    @csrf

                    {{-- Valor a pagar (editável) --}}
                    <div>
                        <label for="payAmountInput" class="text-xs text-neutral-500 dark:text-neutral-400">
                            Valor a pagar
                        </label>

                        <div
                            class="mt-1 flex rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 overflow-hidden">
                            <span class="inline-flex items-center px-3 text-sm text-neutral-500">R$</span>
                            <input type="text"
                                   class="flex-1 border-0 bg-transparent px-3 py-2 text-right font-semibold focus:outline-none focus:ring-0"
                                   id="payAmountInput"
                                   name="amount"
                                   inputmode="decimal"
                                   autocomplete="off">
                        </div>

                        <p class="mt-1 text-xs text-neutral-500">
                            Valor total da fatura: <span id="payAmountLabel">R$ 0,00</span>
                        </p>
                    </div>

                    {{-- Data do pagamento --}}
                    <div>
                        <label for="payDate" class="text-xs text-neutral-500 dark:text-neutral-400">
                            Data do pagamento
                        </label>
                        <input type="date"
                               class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                               id="payDate"
                               name="paid_at"
                               value="{{ now()->format('Y-m-d') }}">
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button type="button"
                                class="px-3 py-2 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800"
                                data-pay-close>
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                            Confirmar pagamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="invoiceItemModal"
         class="fixed inset-0 z-[95] hidden"
         role="dialog"
         aria-modal="true">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="invoiceItemOverlay"></div>

        <div
            class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-[480px]">
            <div
                class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-4 md:p-6">
                <div class="flex items-start justify-between">
                    <h3 id="invoiceItemModalTitle" class="text-lg font-semibold">Novo item</h3>
                    <button type="button"
                            id="invoiceItemModalClose"
                            class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
                            aria-label="Fechar">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6 6 18"/>
                            <path d="M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="formInvoiceItem" class="mt-4 grid gap-3" novalidate>
                    @csrf

                    {{-- sempre setado pelo Blade e atualizado via JS ao trocar o mês --}}
                    <input type="hidden"
                           name="invoice_id"
                           id="item_invoice_id"
                           value="{{ $header['invoice_id'] ?? '' }}">

                    {{-- cartão da tela --}}
                    <input type="hidden"
                           name="card_id"
                           id="item_card_id"
                           value="{{ $card->id }}">

                    {{-- usado só em edição --}}
                    <input type="hidden" name="item_id" id="item_id">

                    <div class="mb-3">
                        <label for="title"
                               class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Título</label>
                        <input type="text" name="title" id="title"
                               class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                               placeholder="Título do item" required>
                    </div>

                    <div class="mb-3">
                        <label for="amount"
                               class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Valor</label>
                        <input type="number" step="0.01" name="amount" id="amount"
                               class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                               placeholder="0,00" required>
                    </div>

                    <div class="mb-3">
                        <label for="date"
                               class="text-xs font-medium text-neutral-500 dark:text-neutral-400">Data</label>
                        <input type="date" name="date" id="date"
                               class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 px-3 py-2 text-sm"
                               required>
                    </div>

                    <label for="transaction_category_id"
                           class="text-xs font-medium text-neutral-500 dark:text-neutral-400">
                        Categoria
                    </label>

                    @php
                        $groupedCategories = $categories->groupBy('type');
                        $typeLabels = [
                            1 => 'Receitas',
                            2 => 'Despesas',
                            3 => 'Transferências',
                        ];
                    @endphp

                    <el-select name="transaction_category_id" id="transaction_category_id" value="4" class="mt-2 block">
                        <button type="button"
                                class="grid w-full cursor-default grid-cols-1 rounded-md dark:bg-neutral-900 dark:text-neutral-50 dark:outline-neutral-700 bg-white py-1.5 pl-3 pr-2 text-left text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 focus-visible:outline focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-indigo-600 sm:text-sm/6">
                            <el-selectedcontent class="col-start-1 row-start-1 flex items-center gap-3 pr-6">
                                <i class="fa-solid fa-arrow-up-short-wide fs-5"></i>
                                <span class="block truncate">Selecione uma categoria</span>
                            </el-selectedcontent>
                            <svg viewBox="0 0 16 16" fill="currentColor" data-slot="icon" aria-hidden="true"
                                 class="col-start-1 row-start-1 size-5 self-center justify-self-end text-gray-500 sm:size-4">
                                <path
                                    d="M5.22 10.22a.75.75 0 0 1 1.06 0L8 11.94l1.72-1.72a.75.75 0 1 1 1.06 1.06l-2.25 2.25a.75.75 0 0 1-1.06 0l-2.25-2.25a.75.75 0 0 1 0-1.06ZM10.78 5.78a.75.75 0 0 1-1.06 0L8 4.06 6.28 5.78a.75.75 0 0 1-1.06-1.06l2.25-2.25a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06Z"
                                    clip-rule="evenodd" fill-rule="evenodd"/>
                            </svg>
                        </button>

                        <el-options anchor="bottom start" popover
                                    class="m-0 max-h-56 w-[var(--button-width)] overflow-auto rounded-md
           bg-white text-gray-900
           dark:bg-neutral-800 dark:text-neutral-50
           p-0 py-1 text-base shadow-lg shadow-black/20
           outline outline-1 outline-black/5 dark:outline-white/10
           [--anchor-gap:theme(spacing.1)]
           data-[closed]:data-[leave]:opacity-0 data-[leave]:transition
           data-[leave]:duration-100 data-[leave]:ease-in
           data-[leave]:[transition-behavior:allow-discrete] sm:text-sm">

                            @foreach($groupedCategories as $type => $cats)
                                <div class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wide
                    text-slate-400 dark:text-slate-400">
                                    {{ $typeLabels[$type] ?? 'Outros' }}
                                </div>

                                @foreach($cats as $cat)
                                    <el-option value="{{ $cat->id }}"
                                               class="group/option relative cursor-default select-none py-2 pl-3 pr-9
                       text-gray-900 dark:text-neutral-50
                       focus:bg-blue-600 dark:focus:bg-blue-500/30
                       focus:text-white dark:focus:text-blue-100
                       focus:outline-none [&:not([hidden])]:block">
                                        <div class="flex items-center">
                                            <i class="{{ $cat->icon }} fs-5 text-slate-500 dark:text-slate-300"></i>
                                            <span class="ml-3 block truncate font-normal group-aria-selected/option:font-semibold">
                        {{ $cat->name }}
                    </span>
                                        </div>
                                    </el-option>
                                @endforeach

                                <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
                            @endforeach
                        </el-options>
                    </el-select>

                    <div class="mt-2 flex items-center justify-end gap-2">
                        <button type="button"
                                id="invoiceItemModalCancel"
                                class="px-3 py-2 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* ===== Carrossel de Meses ===== */
        .item-actions {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 6px;
        }

        .item-actions button {
            border: none;
            background: transparent;
            padding: 4px;
            border-radius: 999px;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .item-actions button:hover {
            background: rgba(15, 23, 42, .06);
            color: var(--brand);
        }

        /* bolinha do mês */
        .icons-carousel .icon-button .month-pill {
            background: #e5e7eb;
            color: var(--text-primary);
        }

        .icons-carousel .icon-button.status-paid .month-pill {
            background: var(--inv-blue);
            color: #fff;
        }

        .icons-carousel .icon-button.status-pending .month-pill {
            background: var(--inv-orange);
            color: #fff;
        }

        .icons-carousel .icon-button.status-overdue .month-pill {
            background: var(--inv-red);
            color: #fff;
        }

        /* leve destaque na borda do card do mês */
        .icons-carousel .icon-button.status-paid,
        .icons-carousel .icon-button.status-pending,
        .icons-carousel .icon-button.status-overdue {
            border: 1px solid var(--card-border);
        }

        /* box principal da fatura */
        .balance-box.status-paid {
            border: 1px solid var(--card-border);
        }

        .balance-box.status-pending {
            border: 1px solid var(--card-border);
        }

        .balance-box.status-overdue {
            border: 1px solid var(--card-border);
        }

        /* badge de status */
        .badge-status-paid {
            background: rgba(37, 99, 235, .09);
            color: #1d4ed8;
        }

        .badge-status-pending {
            background: rgba(249, 115, 22, .11);
            color: #ea580c;
        }

        .badge-status-overdue {
            background: rgba(239, 68, 68, .11);
            color: #b91c1c;
        }

        .icons-carousel {
            display: flex;
            gap: 14px;
            padding: 8px 10px;
            margin-bottom: 20px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
        }

        .icons-carousel::-webkit-scrollbar {
            display: none;
        }

        .icons-carousel .icon-button {
            flex: 0 0 auto;
            width: 80px;
            border-radius: 16px;
            padding: 14px 10px;
            text-align: center;
            transition: all .25s ease;
            scroll-snap-align: center;
            transform: translateY(0);
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            box-shadow: 0 3px 8px var(--card-shadow);
        }

        .icons-carousel .icon-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px var(--hover-shadow);
        }

        .icons-carousel .icon-button span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            margin: 0 auto 8px auto;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 500;
            background: var(--circle-bg);
            color: var(--circle-text);
            transition: all .25s ease;
        }

        .icons-carousel .icon-button b {
            font-size: 10px;
            font-weight: 500;
            color: var(--text-primary);
            display: block;
        }

        .icons-carousel .icon-button.active {
            background: var(--brand);
            border-color: var(--brand);
            transform: scale(1.08);
        }

        .icons-carousel .icon-button.active span {
            background: #fff;
            color: var(--brand);
            font-weight: 700;
        }

        .icons-carousel .icon-button.active b {
            color: #fff;
        }

        /* ===== Box da Fatura ===== */
        .balance-box {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 18px;
            border-radius: 18px;
            background: var(--card-bg);
            box-shadow: 0 4px 14px var(--card-shadow);
            margin-bottom: 20px;
            transition: all .25s ease;
        }

        .balance-box strong {
            font-size: 22px;
            font-weight: 700;
            color: var(--brand);
        }

        .balance-box span {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .balance-box span b {
            font-weight: 600;
        }

        /* ===== Lista ===== */
        #invoiceItems {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .invoice-item {
            border-radius: 14px;
            border: 1px solid var(--card-border);
            background: var(--card-bg);
            box-shadow: 0 2px 8px var(--card-shadow);
        }

        .invoice-item:hover {
            transform: translateY(-1px);
        }

        .tx-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
        }

        .tx-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .title-date {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
        }

        .tx-title {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-primary);
            line-height: 1.3;
            text-transform: capitalize;
        }

        .tx-date {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .tx-right {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .amount-box {
            text-align: right;
            min-width: 90px;
        }

        .tx-amount {
            font-weight: 700;
            font-size: 15px;
            color: var(--brand);
        }

        /* ícone redondo à esquerda */
        .icon-circle {
            display: inline-grid;
            place-items: center;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            border-radius: 50%;
            background: var(--cat-bg, var(--brand));
            color: #fff;
            font-size: 14px;
        }

        /* botões editar / excluir */
        .item-actions {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 4px;
        }

        .item-actions button {
            border: none;
            background: transparent;
            padding: 4px;
            border-radius: 999px;
            cursor: pointer;
            color: var(--text-secondary);
        }

        .item-actions button:hover {
            background: rgba(15, 23, 42, .06);
            color: var(--brand);
        }


        /* opcional: um espacinho entre o ícone e o texto */
        .d-flex.align-items-center {
            gap: 8px;
        }

        /* ===== Tema Claro ===== */
        :root {
            --brand: #2563eb;
            --card-bg: #ffffff;
            --card-border: #e5e7eb;
            --card-shadow: rgba(0, 0, 0, 0.05);
            --hover-shadow: rgba(0, 0, 0, .1);
            --circle-bg: #3b82f6;
            --circle-text: #ffffff;
            --text-primary: #111827;
            --text-secondary: #374151;

            /* Cores de status (parecidas com o print 3) */
            --inv-blue: #1dbd58; /* pagas */
            --inv-orange: #f97316; /* pendentes atuais/futuras */
            --inv-red: #ef4444; /* atrasadas */
        }

        /* opcional: no tema escuro, suaviza */
        .dark {
            --brand: #3b82f6;
            --card-bg: #1f2937;
            --card-border: #374151;
            --card-shadow: rgba(0, 0, 0, 0.3);
            --hover-shadow: rgba(0, 0, 0, .4);
            --circle-bg: #2563eb;
            --circle-text: #ffffff;
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;

            --inv-blue: #60a5fa;
            --inv-orange: #fb923c;
            --inv-red: #f87171;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const months = document.getElementById('months');
            const itemsBox = document.getElementById('invoiceItems');
            const invoiceHeader = document.getElementById('invoiceHeader');

            if (!months || !itemsBox || !invoiceHeader) return;

            const hdrMonth = document.getElementById('hdr-month');
            const hdrTotal = document.getElementById('hdr-total');
            const hdrLimit = document.getElementById('hdr-limit');
            const hdrClose = document.getElementById('hdr-close');
            const hdrDue = document.getElementById('hdr-due');

            const statusBadge = document.getElementById('invoiceStatusBadge');
            const cardId = invoiceHeader.dataset.card;

            let payOpenBtn = document.querySelector('.pay-invoice-open');

            // status atual da fatura (controla ícones de editar/excluir)
            let currentInvoiceStatus = invoiceHeader.dataset.status || 'pending';

            const CSRF = '{{ csrf_token() }}';

            function getActiveMonthBtn() {
                return months.querySelector('.month-btn.active') || months.querySelector('.month-btn');
            }

            function computeStatus(btn, serverStatus) {
                const btnStatus = btn ? btn.dataset.status : null;
                const btnPaid = btn ? btn.dataset.paid === '1' : false;

                let st = serverStatus || btnStatus || 'pending';
                if (btnPaid) st = 'paid';
                return st;
            }

            function applyStatusBadge(st) {
                if (!statusBadge) return;

                const base = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium';
                let colorClasses = '';

                if (st === 'paid') {
                    colorClasses = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
                } else if (st === 'overdue') {
                    colorClasses = 'bg-red-50 text-red-700 ring-1 ring-red-200';
                } else {
                    colorClasses = 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
                }

                statusBadge.className = base + ' ' + colorClasses;
                statusBadge.textContent =
                    st === 'paid' ? 'Paga' : (st === 'overdue' ? 'Em atraso' : 'Em aberto');
            }

            // ====== MODAL ITEM (nativo) ======
            const itemModalEl = document.getElementById('invoiceItemModal');
            const itemForm = document.getElementById('formInvoiceItem');
            const itemInvoiceInput = document.getElementById('item_invoice_id');
            const itemCardInput = document.getElementById('item_card_id');
            const newItemBtn = document.getElementById('newInvoiceItemBtn');

            let currentInvoiceId = '{{ $header['invoice_id'] ?? '' }}';

            if (itemInvoiceInput) {
                itemInvoiceInput.value = currentInvoiceId;
            }
            if (itemCardInput) {
                itemCardInput.value = '{{ $card->id }}';
            }


            const itemSave = itemForm ? itemForm.querySelector('button[type="submit"]') : null;
            const itemTitleEl = itemModalEl ? itemModalEl.querySelector('[data-crud-title]') : null;
            const itemOverlay = itemModalEl ? itemModalEl.querySelector('[data-crud-overlay]') : null;
            const itemCloseBtns = itemModalEl
                ? itemModalEl.querySelectorAll('[data-crud-close],[data-crud-cancel]')
                : [];

            let currentMode = 'create'; // create|edit|show
            let currentId = null;

            function setCrudTitle(mode) {
                if (!itemTitleEl) return;
                if (mode === 'edit') itemTitleEl.textContent = 'Editar item';
                else if (mode === 'show') itemTitleEl.textContent = 'Detalhes do item';
                else itemTitleEl.textContent = 'Novo item';
            }

            function setVal(id, val) {
                if (!itemForm) return;
                const el = itemForm.querySelector('#' + id);
                if (el) el.value = (val ?? '');
            }

            function setCheck(id, on) {
                if (!itemForm) return;
                const el = itemForm.querySelector('#' + id);
                if (el) {
                    el.checked = !!on;
                    el.dispatchEvent(new Event('change'));
                }
            }

            function getId(it) {
                return it.uuid || it.id || it._id || it.invoice_item_id;
            }

            function setFormMode(mode) {
                if (!itemForm) return;
                currentMode = mode;
                const isShow = mode === 'show';

                itemForm.querySelectorAll('input,select,textarea,button').forEach(el => {
                    if (el.type === 'submit') return;
                    el.disabled = isShow;
                });

                if (itemSave) itemSave.classList.toggle('hidden', isShow);
                setCrudTitle(mode);
            }

            function fillForm(it) {
                setVal('title', it.title);
                setVal('amount', it.raw_amount ?? it.amount);
                setVal('date', String(it.date ?? '').slice(0, 10));
                if (it.installments) setVal('installments', it.installments);
                if (it.current_installment) setVal('current_installment', it.current_installment);
                setCheck('is_projection', !!it.is_projection);

                if (it.transaction_category_id) {
                    setVal('transaction_category_id', it.transaction_category_id);
                }
            }

            function clearForm() {
                if (!itemForm) return;
                itemForm.reset();
            }

            if (newItemBtn) {
                newItemBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    currentMode = 'create';
                    currentId = null;
                    clearForm();

                    // data padrão = hoje
                    const today = new Date().toISOString().slice(0, 10);
                    setVal('date', today);

                    openItemModal('create');
                });
            }

            function openItemModal(mode, it) {
                if (!itemForm || !itemModalEl) return;

                setFormMode(mode);
                if (it) fillForm(it); else clearForm();

                itemModalEl.classList.remove('hidden');
                itemModalEl.hidden = false;
                document.body.classList.add('overflow-hidden');
            }

            function closeItemModal() {
                if (!itemModalEl) return;
                itemModalEl.classList.add('hidden');
                itemModalEl.hidden = true;
                document.body.classList.remove('overflow-hidden');
            }

// fecha pelo overlay ou botões
            itemOverlay?.addEventListener('click', (e) => {
                e.preventDefault();
                closeItemModal();
            });
            itemCloseBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    closeItemModal();
                });
            });

// fecha clicando fora do form em desktop
            function closeItemIfOutside(e) {
                if (!itemModalEl || itemModalEl.classList.contains('hidden') || !itemForm) return;

                const r = itemForm.getBoundingClientRect();
                const p = e.touches ? e.touches[0] : e;
                const x = p.clientX, y = p.clientY;
                const inside = x >= r.left && x <= r.right && y >= r.top && y <= r.bottom;

                if (!inside) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeItemModal();
                }
            }

            window.addEventListener('pointerdown', closeItemIfOutside, true);
            window.addEventListener('touchstart', closeItemIfOutside, {capture: true, passive: false});


            // ====== MODAL DELETE ======
            const delModal = document.getElementById('confirmDeleteItem');
            const delOverlay = delModal?.querySelector('[data-del-overlay]');
            const delConfirmBtn = document.getElementById('deleteConfirmBtn');
            const delCancelBtn = document.getElementById('deleteCancelBtn');
            const delCloseBtn = document.getElementById('deleteCloseBtn');
            let pendingDeleteId = null;

            function openConfirm() {
                if (!delModal) return;
                delModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeConfirm() {
                if (!delModal) return;
                delModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            delConfirmBtn?.addEventListener('click', async () => {
                await doDelete();
                closeConfirm();
            });
            delCancelBtn?.addEventListener('click', closeConfirm);
            delCloseBtn?.addEventListener('click', closeConfirm);
            delOverlay?.addEventListener('click', closeConfirm);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && delModal && !delModal.classList.contains('hidden')) {
                    closeConfirm();
                }
            });

            async function doDelete() {
                if (!pendingDeleteId) return;

                const res = await fetch(`{{ url('/invoice-items') }}/${pendingDeleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) {
                    alert('Erro ao excluir');
                    return;
                }

                pendingDeleteId = null;

                // recarrega a fatura do mês ativo (header + lista + quadradinho)
                const activeBtn = getActiveMonthBtn();
                activeBtn?.click();
            }

            // ====== MODAL PAGAR FATURA ======
            const payModal = document.getElementById('payInvoiceModal');
            const payOverlay = payModal?.querySelector('[data-pay-overlay]');
            const payFormModal = document.getElementById('payInvoiceFormModal');
            const payAmountLabel = document.getElementById('payAmountLabel');
            const payAmountInput = document.getElementById('payAmountInput');
            const payDateInput = document.getElementById('payDate');

            function openPayModal(cardId, ym, totalRaw, totalFormatted) {
                if (!payModal || !payFormModal) return;

                const action = `{{ url('/invoice/payment') }}/${cardId}/${ym}`;
                payFormModal.action = action;

                const raw = Number(totalRaw ?? 0);

                payAmountLabel.textContent = totalFormatted || 'R$ 0,00';
                payAmountInput.value = raw > 0 ? raw.toFixed(2) : '';

                if (payDateInput && !payDateInput.value) {
                    const today = new Date().toISOString().slice(0, 10);
                    payDateInput.value = today;
                }

                payModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }


            function closePayModal() {
                if (!payModal) return;
                payModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            if (payOpenBtn) {
                payOpenBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const activeBtn = getActiveMonthBtn();
                    const ym = payOpenBtn.dataset.ym || activeBtn?.dataset.ym;
                    const totalRaw = payOpenBtn.dataset.totalRaw;
                    const formatted = payOpenBtn.dataset.totalFormatted || hdrTotal.textContent;
                    if (!ym) return;
                    openPayModal(cardId, ym, totalRaw, formatted);
                });
            }

            document.querySelectorAll('[data-pay-close]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    closePayModal();
                });
            });
            payOverlay?.addEventListener('click', (e) => {
                e.preventDefault();
                closePayModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && payModal && !payModal.classList.contains('hidden')) {
                    closePayModal();
                }
            });

            // ====== RENDER ITENS ======
            function renderItem(it) {
                const id = getId(it);
                const inst = (it.installments > 1)
                    ? `${it.current_installment}/${it.installments} `
                    : '';
                const proj = it.is_projection ? '<small>(proj.)</small>' : '';
                const date = it.date ?? '';
                const amount = it.amount;

                const iconCls = it.icon && it.icon.trim() ? it.icon : 'fa-solid fa-tag';
                const bg = it.color || '#999';

                const canEdit = currentInvoiceStatus !== 'paid';

                const actionsHtml = canEdit ? `
                    <div class="item-actions">
                        <button type="button" class="item-edit-btn" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button type="button" class="item-delete-btn" title="Excluir">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                ` : '';

                return `
        <li class="invoice-item" data-id="${id}">
            <div class="tx-line">
                <div class="tx-left">
                    <span class="icon-circle" style="--cat-bg:${bg}">
                        <i class="${iconCls}"></i>
                    </span>
                    <div class="title-date">
                        <span class="tx-title">${it.title ?? 'Sem título'}</span>
                        <span class="tx-date">${date}</span>
                    </div>
                </div>

                <div class="tx-right">
                    <span class="tx-amount price-default">${inst}${amount} ${proj}</span>
                    ${actionsHtml}
                </div>
            </div>
        </li>`;
            }

            function paintList(list) {
                itemsBox.innerHTML =
                    (list && list.length)
                        ? list.map(renderItem).join('')
                        : `<li class="invoice-item"">
                        <div class="tx-line">
                    <div class="tx-left">

                <div class="title-date">
                    <span class="tx-title">Essa fatura não contém itens adicionados.</span>
                </div>
            </div>
            </div>
            </li>`;
            }

            // ====== CLIQUES NOS ITENS ======
            async function handleEdit(id) {
                currentId = id;
                const res = await fetch(`{{ url('/invoice-items') }}/${id}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) {
                    alert('Erro ao carregar');
                    return;
                }
                const it = await res.json();
                openItemModal('edit', it);
            }

            function handleAskDelete(id) {
                pendingDeleteId = id;
                openConfirm();
            }

            itemsBox.addEventListener('click', async (e) => {
                const row = e.target.closest('.invoice-item');
                if (!row) return;
                const id = row.dataset.id;

                const isPaid = currentInvoiceStatus === 'paid';

                if (e.target.closest('.item-edit-btn')) {
                    e.preventDefault();
                    if (isPaid) return;
                    await handleEdit(id);
                    return;
                }

                if (e.target.closest('.item-delete-btn')) {
                    e.preventDefault();
                    if (isPaid) return;
                    handleAskDelete(id);
                    return;
                }

                try {
                    const res = await fetch(`{{ url('/invoice-items') }}/${id}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Erro ao carregar item');
                    const it = await res.json();
                    openItemModal('show', it);
                } catch (err) {
                    alert(err.message);
                }
            });

            // submit form de item
            itemForm?.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (currentMode === 'edit') {
                    itemForm.querySelectorAll('[disabled]').forEach(el => el.disabled = false);
                }
                const fd = new FormData(itemForm);
                let url, method = 'POST';

                if (currentMode === 'edit' && currentId) {
                    url = `{{ url('/invoice-items') }}/${currentId}`;
                    fd.append('_method', 'PUT');
                } else {
                    url = `{{ route('invoice-items.store') }}`;
                }

                const res = await fetch(url, {
                    method,
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });
                if (!res.ok) {
                    alert('Erro ao salvar');
                    return;
                }
                closeItemModal();

                const activeBtn = getActiveMonthBtn();
                activeBtn?.click();
            });

            // ====== CARROSSEL DE MESES ======
            months.addEventListener('click', async (e) => {
                const btn = e.target.closest('.month-btn');
                if (!btn) return;

                const ym = btn.dataset.ym;

                months.querySelectorAll('.month-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                const url = `{{ url('/invoice') }}/${cardId}/${ym}`;
                const res = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
                if (!res.ok) return;

                const data = await res.json();

                // *** ATUALIZA O VALOR DO QUADRADINHO ***
                const valueEl = btn.querySelector('b');
                if (valueEl) {
                    valueEl.textContent = data.header.total; // ex: "R$ 1.060,01"
                }

                currentInvoiceId = data.header.invoice_id || '';

                if (itemInvoiceInput) {
                    itemInvoiceInput.value = currentInvoiceId;
                }
                if (itemCardInput) {
                    itemCardInput.value = cardId;
                }

                hdrMonth.textContent = 'Fatura de ' + data.header.month_label;
                hdrTotal.textContent = data.header.total;
                hdrLimit.innerHTML = data.header.limit;
                hdrClose.innerHTML = data.header.close_label;
                hdrDue.innerHTML = data.header.due_label;

                let st = computeStatus(btn, data.header.status);

                invoiceHeader.dataset.status = st;
                currentInvoiceStatus = st;

                invoiceHeader.classList.remove('status-paid', 'status-pending', 'status-overdue');
                invoiceHeader.classList.add('status-' + st);

                // *** ATUALIZA STATUS DO BOTÃO TAMBÉM (cor do quadradinho) ***
                btn.dataset.status = st;
                btn.dataset.paid   = (st === 'paid') ? '1' : '0';
                btn.classList.remove('status-paid', 'status-pending', 'status-overdue');
                btn.classList.add('status-' + st);

                applyStatusBadge(st);

                payOpenBtn = document.getElementById('payment-invoice-btn');

                if (payOpenBtn) {
                    const totalRaw = Number(data.header.total_raw ?? 0);

                    const mustHide = (st === 'paid') || (totalRaw <= 0);

                    if (mustHide) {
                        payOpenBtn.classList.add('hidden');
                    } else {
                        payOpenBtn.classList.remove('hidden');
                        payOpenBtn.dataset.ym = data.header.ym;
                        payOpenBtn.dataset.totalRaw = data.header.total_raw;
                        payOpenBtn.dataset.totalFormatted = data.header.total;
                    }
                }

                paintList(data.items || []);

                const offset = Math.max(0, btn.offsetLeft - 12);
                months.scrollLeft = offset;
            });

            // ====== BOOT ======
            function bootstrapFromServer() {
                const initial = [
                        @foreach($items as $it)
                    {
                        uuid: "{{ $it->uuid ?? '' }}",
                        id: "{{ $it->id ?? '' }}",
                        title: `{!! addslashes($it->title) !!}`,
                        date: "{{ $it->date }}",
                        amount: "{{ $it->amount }}",
                        installments: {{ (int)($it->installments ?? 0) }},
                        current_installment: {{ (int)($it->current_installment ?? 0) }},
                        is_projection: {{ $it->is_projection ? 'true':'false' }},
                        icon: "{{ $it->icon ?? '' }}",
                        color: "{{ $it->color ?? '#999' }}"
                    },
                    @endforeach
                ];
                paintList(initial);
            }

            function selectInitialMonth() {
                const btns = Array.from(months.querySelectorAll('.month-btn'));
                if (!btns.length) return;

                const nowYm = parseInt('{{ now()->format('Ym') }}', 10);
                let target = null;

                target = btns.find(b =>
                    parseInt(b.dataset.ym, 10) >= nowYm &&
                    b.dataset.status !== 'paid'
                );

                if (!target) {
                    target = btns.find(b => b.dataset.status !== 'paid');
                }

                if (!target) {
                    target = btns[btns.length - 1];
                }

                btns.forEach(b => b.classList.remove('active'));
                target.classList.add('active');

                const st = computeStatus(target, invoiceHeader.dataset.status || null);
                invoiceHeader.dataset.status = st;
                currentInvoiceStatus = st;

                const offset = Math.max(0, target.offsetLeft - 12);
                months.scrollLeft = offset;

                target.click();
            }

            bootstrapFromServer();
            selectInitialMonth();
        })();
    </script>
@endpush




