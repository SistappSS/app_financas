@extends('layouts.templates.app')

@section('new-content')
    @push('styles')
        <style>
            .skel{position:relative;overflow:hidden;border-radius:.5rem;background:#e5e7eb}
            .dark .skel{background:#262626}
            .skel::after{content:"";position:absolute;inset:0;transform:translateX(-100%);background:linear-gradient(90deg,transparent,rgba(255,255,255,.55),transparent);animation:skel 1.1s infinite}
            @keyframes skel{100%{transform:translateX(100%)}}

            .grid-loading{position:relative}
            .grid-loading::after{content:"";position:absolute;inset:0;pointer-events:none;background:linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);animation:skel 1.1s infinite;opacity:.35}
            .dark .grid-loading::after{background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent);opacity:.6}

            :root{--ink:#1F2937;--muted:#6B7280;--line:rgba(0,0,0,.08);--accent:#00BFA6}

            .tx-tab{font-size:.78rem;letter-spacing:.02em;font-weight:600;color:var(--muted);padding:.5rem .75rem;border-radius:.5rem}
            .tx-tab.active{background:#2563eb;color:#fff}

            #stSubcats{display:flex;gap:.5rem;flex-wrap:wrap}
            #stSubcats .chip{font-size:.75rem;letter-spacing:.02em;font-weight:600;display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .6rem;border:1px solid var(--line);border-radius:.45rem;background:#fff;color:#334155}
            #stSubcats .chip.active{background:#2563eb1a;color:#2563eb;border-color:#2563eb}
            #stSubcats .dot{width:.5rem;height:.5rem;border-radius:999px;background:currentColor;display:inline-block}

            #txModal [data-crud-body]{max-height:70vh;overflow:auto}

            .tx-chip{--chip-border:rgba(0,0,0,.10);display:inline-flex;align-items:center;gap:.5rem;padding:.55rem .8rem;border:1px solid var(--chip-border);border-radius:.9rem;background:transparent;color:inherit;transition:background .18s ease,color .18s ease,border-color .18s ease,box-shadow .18s ease}
            .tx-chip .size-4{width:1rem;height:1rem;border-radius:999px;border:2px solid currentColor}
            .tx-chip:has(input:checked){background:#2563eb;color:#fff;border-color:transparent;box-shadow:0 6px 16px rgba(37,99,235,.25)}
            .tx-chip:has(input:checked) .size-4{border-color:#fff;background:#fff}
            .tx-chip:has(input:focus-visible){outline:2px solid #2563eb;outline-offset:2px}
            .tx-chip.tx-chip--thin{--chip-border:rgba(0,0,0,.06)}
            .dark .tx-chip.tx-chip--thin{--chip-border:rgba(255,255,255,.10)}
            .tx-chip.tx-chip--noborder{border-color:transparent}
        </style>
    @endpush

    <section class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">Transações</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Acompanhe suas transações por categoria e tipo.</p>
            </div>
            <div class="hidden md:flex items-center gap-2">
                {{-- CATEGORIAS (mesmo tamanho/cor) --}}
                <a
                    href="{{ \Illuminate\Support\Facades\Route::has('transactionCategory.index') ? route('transactionCategory.index') : url('/transaction-category') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                    <i class="fa-solid fa-list-ul text-[12px]"></i>
                    Categorias
                </a>

                {{-- NOVA TRANSAÇÃO --}}
                <button data-open-modal="tx"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    Nova transação
                </button>
            </div>
        </div>

        <div id="stFilters"
             class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            <div class="grid grid-cols-2 gap-6">
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Início</span>
                    <input type="date" id="stStart"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                </label>
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Fim</span>
                    <input type="date" id="stEnd"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                </label>
            </div>

            <div id="stTabs" class="mt-3 flex gap-2 overflow-x-auto">
                <button type="button" class="tx-tab active" data-type="all">Todos</button>
                <button type="button" class="tx-tab" data-type="entrada">Entradas</button>
                <button type="button" class="tx-tab" data-type="despesa">Despesas</button>
                <button type="button" class="tx-tab" data-type="investimento">Investimentos</button>
            </div>

            <div id="stSubcats" class="mt-2"></div>

            <div class="mt-3 flex justify-end">
                <button id="stApply"
                        class="inline-flex items-center p-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                    <i class="fa fa-magnifying-glass text-[12px]"></i>
                    <span class="text-[14px] tracking-wide"></span>
                </button>
            </div>
        </div>

        <div id="txGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4"></div>

        {{-- FAB Nova transação --}}
        <x-fab id="txFab" target="tx"/>

        {{-- FAB Categorias (mesma base visual do FAB acima) --}}
        <a
            href="{{ \Illuminate\Support\Facades\Route::has('transactionCategory.index') ? route('transactionCategory.index') : url('/transaction-category') }}"
            class="md:hidden fixed bottom-36 right-4 z-[40] size-14 rounded-2xl grid place-items-center text-white shadow-lg
                 bg-brand-600 hover:bg-brand-700 active:scale-95 transition"
            aria-label="Categorias">
            <i class="fa-solid fa-list-ul text-base"></i>
        </a>

        <div id="txMenu"
             class="hidden fixed z-[40] min-w-40 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft p-1">
            <button data-menu-action="edit"  class="w-full text-left px-4 py-2 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800">Editar</button>
            <button data-menu-action="show"  class="w-full text-left px-4 py-2 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800">Ver detalhes</button>
            <button data-menu-action="delete" class="w-full text-left px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Excluir</button>
        </div>

        {{-- MODAL (mantido igual) --}}
        <x-modal id="txModal" titleCreate="Nova transação" titleEdit="Editar transação" titleShow="Detalhes da transação" submitLabel="Salvar">
            <input type="hidden" name="id"/>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Título</span>
                    <input name="title" type="text" placeholder="Ex: Pagamento aluguel"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2" required/>
                    <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                </label>

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
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Valor</span>
                    <input name="amount" id="tx_amount" inputmode="decimal" placeholder="0,00"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"/>
                    <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                </label>
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Data (início)</span>
                    <input name="date" type="date"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"/>
                    <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                </label>
            </div>

            <div id="tx_saving_wrap" class="mt-2 hidden">
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Cofrinho</span>
                    <select name="saving_id"
                            class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                        @foreach($savings as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="mt-3">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Forma de pagamento</span>
                <div class="mt-1 grid grid-cols-3 gap-2">
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="pix" type="radio" name="type" value="pix" class="hidden"><span class="size-4 rounded-full border"></span><span>Pix</span></label>
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="card" type="radio" name="type" value="card" class="hidden"><span class="size-4 rounded-full border"></span><span>Cartão</span></label>
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="money" type="radio" name="type" value="money" class="hidden"><span class="size-4 rounded-full border"></span><span>Dinheiro</span></label>
                </div>
            </div>

            <div id="tx_card_type" class="mt-3 hidden">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Tipo de cartão</span>
                <div class="mt-1 grid grid-cols-2 gap-2">
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="credit" type="radio" name="type_card" value="credit" class="hidden"><span class="size-4 rounded-full border"></span><span>Crédito</span></label>
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="debit" type="radio" name="type_card" value="debit" class="hidden"><span class="size-4 rounded-full border"></span><span>Débito</span></label>
                </div>
            </div>

            <div id="tx_alt_row" class="mt-3 hidden">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Alternar entre cartões? (crédito + recorrente)</div>
                    <label class="inline-flex items-center cursor-pointer">
                        <input id="alternate_cards" name="alternate_cards" type="checkbox" value="1" class="peer hidden">
                        <span class="w-11 h-6 bg-neutral-200 rounded-full relative transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:size-5 after:bg-white after:rounded-full after:transition peer-checked:bg-brand-600 peer-checked:after:left-5"></span>
                    </label>
                </div>
            </div>

            <div id="tx_alt_select" class="mt-2 hidden">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Selecione os cartões</span>
                <div class="mt-1 grid grid-cols-1 md:grid-cols-2 gap-y-2">
                    @foreach($cards as $card)
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" name="alternate_card_ids[]" value="{{ $card->id }}">
                            <span>{{ $card->account ? $card->account->bank_name : '' }} {{ $card->last_four_digits }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div id="tx_pix_acc" class="mt-3">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Conta (Pix/Dinheiro)</span>
                <select name="account_id"
                        class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->bank_name }}</option>
                    @endforeach
                </select>
            </div>

            <div id="tx_card_select" class="mt-3 hidden">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Cartão vinculado</span>
                <select name="card_id"
                        class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                    <option value="">Selecione um cartão</option>
                    @foreach($cards as $card)
                        <option value="{{ $card->id }}">{{ $card->account ? $card->account->bank_name : '' }} {{ $card->last_four_digits }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-3">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Recorrência</span>
                <div class="mt-1 grid grid-cols-2 gap-2">
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="unique" type="radio" name="recurrence_type" value="unique" class="hidden"><span class="size-4 rounded-full border"></span><span>Única</span></label>
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="monthly" type="radio" name="recurrence_type" value="monthly" class="hidden"><span class="size-4 rounded-full border"></span><span>Mensal</span></label>
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="yearly" type="radio" name="recurrence_type" value="yearly" class="hidden"><span class="size-4 rounded-full border"></span><span>Anual</span></label>
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="custom" type="radio" name="recurrence_type" value="custom" class="hidden"><span class="size-4 rounded-full border"></span><span>Personalizado</span></label>
                </div>
            </div>

            {{-- Parcelar valor? (PIX ou cartão de crédito + Única) --}}
            <div id="tx_can_install_row" class="mt-3 hidden">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Parcelar valor?</span>
                <div class="mt-1 grid grid-cols-2 gap-2">
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl">
                        <input id="can_install_no" type="radio" name="can_install" value="0" class="hidden" checked>
                        <span class="size-4 rounded-full border"></span>
                        <span>Não</span>
                    </label>
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl">
                        <input id="can_install_yes" type="radio" name="can_install" value="1" class="hidden">
                        <span class="size-4 rounded-full border"></span>
                        <span>Sim</span>
                    </label>
                </div>
            </div>

            {{-- Nº de parcelas --}}
            <div id="tx_installments_wrap" class="mt-3 hidden">
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Parcelas</span>
                    <input id="installments" name="installments" type="number" min="1"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                           placeholder="Ex: 3 parcelas">
                </label>
            </div>

            <div id="tx_custom_rec" class="mt-3 hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Intervalo (dias)</span>
                        <input type="number" min="1" id="interval_value" name="interval_value"
                               class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                               placeholder="Ex: 7" value="7">
                    </label>
                    <div class="block">
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Contar fim de semana?</span>
                        <div class="mt-2 flex items-center gap-4">
                            <label class="inline-flex items-center gap-2"><input type="checkbox" name="include_sat" id="include_sat" value="1" checked><span>Sábado</span></label>
                            <label class="inline-flex items-center gap-2"><input type="checkbox" name="include_sun" id="include_sun" value="1" checked><span>Domingo</span></label>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tx_term_row" class="mt-3 hidden">
                <span class="text-xs text-neutral-500 dark:text-neutral-400">Término</span>
                <div class="mt-1 grid grid-cols-2 gap-2">
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="no_end" type="radio" name="termination" value="no_end" class="hidden" checked><span class="size-4 rounded-full border"></span><span>Sem término</span></label>
                    <label class="tx-chip inline-flex items-center gap-2 px-3 py-2 rounded-xl"><input id="has_end" type="radio" name="termination" value="has_end" class="hidden"><span class="size-4 rounded-full border"></span><span>Com término</span></label>
                </div>
            </div>

            <div id="tx_occ" class="mt-3 hidden">
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Nº de ocorrências</span>
                    <input type="number" min="1" id="custom_occurrences" name="custom_occurrences"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                           placeholder="Ex: 12">
                </label>
            </div>
        </x-modal>

        <div id="txSheet" class="fixed inset-0 z-[70] hidden" aria-modal="true" role="dialog">
            <div id="txSheetOv" class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
            <div class="absolute inset-x-0 bottom-0 rounded-t-2xl border border-neutral-200/60 dark:border-neutral-800/60 bg-white dark:bg-neutral-900 shadow-soft p-2">
                <div class="mx-auto h-1 w-10 rounded-full bg-neutral-300/70 dark:bg-neutral-700/70 mb-2"></div>
                <div class="grid gap-1 p-1">
                    <button data-sheet-action="edit"  class="w-full text-left px-4 py-3 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800">Editar</button>
                    <button data-sheet-action="show"  class="w-full text-left px-4 py-3 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800">Ver detalhes</button>
                    <button data-sheet-action="delete" class="w-full text-left px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Excluir</button>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script src="{{ asset('assets/js/common/crud-model.js') }}"></script>
        <script>
            (() => {
                const ROUTES = {
                    index: "{{ route('transactions.index') }}",
                    store: "{{ route('transactions.store') }}",
                    show: "{{ url('/transactions') }}/:id",
                    update: "{{ url('/transactions') }}/:id",
                    destroy: "{{ url('/transactions') }}/:id"
                };

                const brl = (n) => (isNaN(+n) ? 'R$ 0,00' : Number(n).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}));
                const normType = (t) => {
                    const s = String(t ?? '').toLowerCase();
                    if (s === '1' || s.includes('entrada')) return 'entrada';
                    if (s === '3' || s.includes('invest')) return 'investimento';
                    return (s === '2' || s.includes('desp')) ? 'despesa' : 'despesa';
                };

                const RAW_CATS = @json($categories->map(fn($c)=>['id'=>$c->id,'name'=>$c->name,'type'=>$c->type])->values());
                const ALL_CATS = (RAW_CATS || []).map(c => ({id: c.id, name: c.name, type: normType(c.type)}));

                const state = {type:'all', catIds:new Set(), start:'', end:''};
                const fx = document.getElementById('stFilters');
                const tabs = fx.querySelector('#stTabs');
                const sub  = fx.querySelector('#stSubcats');
                const inS  = fx.querySelector('#stStart');
                const inE  = fx.querySelector('#stEnd');

                const BASE_INDEX = "{{ route('transactions.index') }}";

                function buildQS(){
                    const p = new URLSearchParams();
                    if (state.type!=='all') p.set('type', state.type);
                    if (state.start) p.set('start', state.start);
                    if (state.end)   p.set('end', state.end);
                    if (state.catIds.size) [...state.catIds].forEach(id => p.append('category_ids[]', id));
                    return p.toString();
                }
                function reloadWithFilters(){
                    const q = buildQS();
                    ROUTES.index = q ? `${BASE_INDEX}?${q}` : BASE_INDEX;
                    txCrud.reload();
                }
                function renderSubcats(){
                    sub.innerHTML=''; if(state.type==='all') return;
                    const cats = ALL_CATS.filter(c=>c.type===state.type);
                    if (!cats.length){ sub.innerHTML='<small class="text-neutral-500">Sem categorias</small>'; return; }
                    const all=document.createElement('button'); all.className='chip '+(state.catIds.size?'':'active'); all.textContent='Todas';
                    all.onclick=()=>{ state.catIds.clear(); renderSubcats(); };
                    sub.appendChild(all);
                    for(const c of cats){
                        const b=document.createElement('button');
                        b.className='chip '+(state.catIds.has(c.id)?'active':'');
                        b.innerHTML=`<span class="dot"></span>${c.name}`;
                        b.onclick=()=>{ state.catIds.has(c.id)?state.catIds.delete(c.id):state.catIds.add(c.id); renderSubcats(); };
                        sub.appendChild(b);
                    }
                }
                tabs.addEventListener('click',(e)=>{
                    const btn=e.target.closest('.tx-tab'); if(!btn) return;
                    tabs.querySelectorAll('.tx-tab').forEach(b=>b.classList.remove('active'));
                    btn.classList.add('active');
                    state.type=btn.dataset.type||'all'; state.catIds.clear(); renderSubcats(); reloadWithFilters();
                });
                fx.querySelector('#stApply').addEventListener('click',()=>{
                    state.start=inS.value||''; state.end=inE.value||''; reloadWithFilters();
                });
                renderSubcats();

                const TYPE_COLOR={pix:'#2ecc71',card:'#3498db',money:'#f39c12'};
                const TYPE_LABEL={pix:'Pix',card:'Cartão',money:'Dinheiro'};

                function cardTemplate(tx){
                    const id=tx.id??tx.uuid??tx._id??tx.transaction_id;
                    const src=tx.create_date??tx.date;
                    const d=src?new Date(src):null;
                    const meses=['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
                    const date=(d&&!isNaN(d))?(d.toLocaleString('pt-BR',{day:'2-digit',timeZone:'America/Sao_Paulo'})+' '+meses[d.getMonth()]):'';
                    const type=tx.type??'money';
                    const color=TYPE_COLOR[type]||'#777';
                    const label=TYPE_LABEL[type]||type;
                    const amount=(typeof tx.amount==='string')?tx.amount:brl(tx.amount??tx.value??0);
                    const catName=tx.category?.name??tx.transaction_category?.name??tx.category_name??'';

                    return `
<article data-id="${id}" class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-5 shadow-soft">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-center gap-3">
      <span class="size-12 grid place-items-center rounded-xl bg-neutral-100 dark:bg-neutral-800">
        <i class="fa-solid fa-money-bill-transfer fa-fw" style="color:${color}"></i>
      </span>
      <div>
        <p class="font-semibold">${tx.title ?? 'Sem título'}</p>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">${catName ? catName + ' • ' : ''}${date}</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center h-8 px-2 rounded-lg text-[11px] font-medium" style="color:${color};border:1px solid ${color};background:${color}1a">${label}</span>
      <button data-action="more" class="inline-grid size-10 place-items-center rounded-lg border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800" aria-label="Mais ações">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
      </button>
    </div>
  </div>

  <div class="mt-4 grid grid-cols-2 gap-3">
    <div class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 p-3">
      <p class="text-xs text-neutral-500 dark:text-neutral-400">Valor</p>
      <p class="text-lg font-medium">${amount}</p>
    </div>
    <div class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 p-3">
      <p class="text-xs text-neutral-500 dark:text-neutral-400">Data</p>
      <p class="text-lg font-medium">${date || '—'}</p>
    </div>
  </div>
</article>`;
                }

                const txCrud = CrudLite({
                    key:'tx', csrf:'{{ csrf_token() }}', routes:ROUTES,
                    selectors:{
                        grid:'#txGrid', modal:'#txModal', form:'#txModal form', title:'#txModal [data-crud-title]',
                        overlay:'#txModal [data-crud-overlay]', openers:'[data-open-modal="tx"]',
                        btnClose:'#txModal [data-crud-close]', btnCancel:'#txModal [data-crud-cancel]', menu:'#txMenu'
                    },
                    template:cardTemplate,
                    parseIndex:(json)=>{
                        if(Array.isArray(json)) return json;
                        if(Array.isArray(json?.data)) return json.data;
                        if(Array.isArray(json?.transactions?.data)) return json.transactions.data;
                        if(Array.isArray(json?.transactions)) return json.transactions;
                        if(Array.isArray(json?.items)) return json.items;
                        if(Array.isArray(json?.results)) return json.results;
                        if(Array.isArray(json?.data?.data)) return json.data.data;
                        return [];
                    },
                    parseShow:(json)=> json?.data ?? json?.transaction ?? json,
                    onModeChange:(m,form,titleEl)=>{
                        if(titleEl) titleEl.textContent = m==='edit' ? 'Editar transação' : (m==='show' ? 'Detalhes da transação' : 'Nova transação');
                        form.querySelectorAll('input,select,textarea,[type="radio"]').forEach(el=> el.disabled=(m==='show'));
                        const submit=form.querySelector('button[type="submit"]');
                        if(submit) submit.classList.toggle('hidden', m==='show');
                        requestAnimationFrame(()=> toggleUI(form));
                    },
                    fillForm:(form,tx)=>{
                        const set=(name,val)=>{ const el=form.querySelector(`[name="${name}"]`); if(el) el.value=(val??''); };
                        const setC=(id,on)=>{ const el=form.querySelector('#'+id); if(el){ el.checked=!!on; el.dispatchEvent(new Event('change')); } };

                        form.querySelector('[name="id"]')?.setAttribute('value', tx.id ?? tx.uuid ?? '');

                        set('title', tx.title);
                        set('description', tx.description);
                        set('amount', tx.amount);
                        set('date', String(tx.date ?? '').slice(0,10));
                        set('transaction_category_id', tx.transaction_category_id ?? tx.category_id);

                        if(tx.account_id) set('account_id', tx.account_id);
                        if(tx.card_id)    set('card_id', tx.card_id);

                        const type = tx.type || 'pix';
                        setC('pix',   type==='pix');
                        setC('card',  type==='card');
                        setC('money', type==='money');

                        if(tx.type_card){
                            setC('credit', tx.type_card==='credit');
                            setC('debit',  tx.type_card==='debit');
                        }

                        const rec = tx.recurrence_type || 'unique';
                        setC('unique',  rec==='unique');
                        setC('monthly', rec==='monthly');
                        setC('yearly',  rec==='yearly');
                        setC('custom',  rec==='custom');

                        if(tx.custom_occurrences) set('custom_occurrences', tx.custom_occurrences);
                        if(tx.interval_value)     set('interval_value', tx.interval_value);

                        if(tx.include_sat != null) form.querySelector('#include_sat').checked = !!+tx.include_sat;
                        if(tx.include_sun != null) form.querySelector('#include_sun').checked = !!+tx.include_sun;

                        // Heurística: se é ÚNICA + tem custom_occurrences > 1 e (PIX ou crédito),
                        // assume que é compra parcelada e preenche campos.
                        const occ        = parseInt(tx.custom_occurrences ?? tx.installments ?? 0, 10);
                        const isPix      = type === 'pix';
                        const isCred     = type === 'card' && tx.type_card === 'credit';
                        const isUnique   = rec === 'unique';
                        const parcelado  = isUnique && occ > 1 && (isPix || isCred);

                        if (parcelado) {
                            setC('can_install_yes', true);
                            const instInput = form.querySelector('#installments');
                            if (instInput) instInput.value = occ || '';
                        } else {
                            setC('can_install_no', true);
                        }

                        toggleUI(form);
                    },
                    clearForm:(form)=>{
                        form.reset();
                        form.querySelector('[name="id"]')?.setAttribute('value','');

                        // dispara change nos grupos principais
                        ['pix','card','money','credit','debit','unique','monthly','yearly','custom','no_end','has_end','can_install_no','can_install_yes'].forEach(id=>{
                            const el=form.querySelector('#'+id);
                            if(el) el.dispatchEvent(new Event('change'));
                        });

                        // garante defaults básicos
                        const setC=(id,on)=>{ const el=form.querySelector('#'+id); if(el){ el.checked=!!on; } };
                        setC('unique', true);
                        setC('no_end', true);
                        setC('can_install_no', true);

                        toggleUI(form);
                    },
                    onBeforeSubmit:(fd)=>{
                        const raw=fd.get('amount');
                        if(raw!=null){
                            const cleaned=String(raw).replace(/[^\d,.,-]/g,'').replace(/\.(?=\d{3}(?:\D|$))/g,'').replace(',', '.');
                            fd.set('amount', cleaned);
                        }
                        return fd;
                    },
                });

                (function bindModalUI(){
                    const modal=document.getElementById('txModal'); if(!modal) return;

                    const groups = {
                        pay:        ['pix','card','money'],
                        card:       ['credit','debit'],
                        rec:        ['unique','monthly','yearly','custom'],
                        term:       ['no_end','has_end'],
                        canInstall: ['can_install_no','can_install_yes'],
                    };

                    modal.addEventListener('change',(e)=>{
                        const id=e.target?.id; if(!id) return;

                        Object.values(groups).forEach(g=>{
                            if(g.includes(id) && modal.querySelector('#'+id)?.checked){
                                g.forEach(other=>{
                                    if(other!==id){
                                        const el=modal.querySelector('#'+other);
                                        if(el) el.checked=false;
                                    }
                                });
                            }
                        });

                        toggleUI(modal);
                    });

                    modal.querySelector('#tx_cat')?.addEventListener('change',()=>toggleUI(modal));
                    modal.querySelector('#alternate_cards')?.addEventListener('change',()=>toggleUI(modal));
                })();

                function toggleUI(scope){
                    const $=(sel)=>scope.querySelector(sel);

                    const pay = $('#pix')?.checked ? 'pix'
                        : ($('#card')?.checked ? 'card'
                            : ($('#money')?.checked ? 'money' : null));

                    const rec = $('#custom')?.checked ? 'custom'
                        : ($('#monthly')?.checked ? 'monthly'
                            : ($('#yearly')?.checked ? 'yearly' : 'unique'));

                    const catSel = document.getElementById('tx_cat');
                    const catType = (catSel?.selectedOptions?.[0]?.dataset?.type) || '';

                    const typeNorm = (t)=>{
                        t=String(t).toLowerCase();
                        if(t==='1'||t.includes('entrada')) return 'entrada';
                        if(t==='3'||t.includes('invest'))  return 'investimento';
                        return 'despesa';
                    };
                    const invest = (typeNorm(catType)==='investimento');

                    const cardType  = document.getElementById('tx_card_type');
                    const pixAcc    = document.getElementById('tx_pix_acc');
                    const cardSel   = document.getElementById('tx_card_select');
                    const altRow    = document.getElementById('tx_alt_row');
                    const altSel    = document.getElementById('tx_alt_select');
                    const savingW   = document.getElementById('tx_saving_wrap');
                    const termRow   = document.getElementById('tx_term_row');
                    const occWrap   = document.getElementById('tx_occ');
                    const customRec = document.getElementById('tx_custom_rec');

                    const canRow    = document.getElementById('tx_can_install_row');
                    const instWrap  = document.getElementById('tx_installments_wrap');
                    const canYes    = document.getElementById('can_install_yes');
                    const canNo     = document.getElementById('can_install_no');

                    const credit = document.getElementById('credit');
                    const debit  = document.getElementById('debit');

                    // investimento não pode ser crédito
                    if(credit){
                        credit.disabled = invest;
                        if(invest && credit.checked){
                            credit.checked = false;
                            if(debit) debit.checked = true;
                        }
                    }

                    const isPix  = (pay === 'pix');
                    const isCard = (pay === 'card');
                    const isCred = isCard && !!(credit && credit.checked);
                    const isRec  = (rec !== 'unique');

                    // tipo de cartão / conta pix
                    cardType?.classList.toggle('hidden', !isCard);
                    pixAcc?.classList.toggle('hidden', !(isPix || pay==='money'));

                    // alternância de cartões (crédito recorrente)
                    const showAlt  = isCred && isRec;
                    const altOn    = document.getElementById('alternate_cards')?.checked;
                    altRow?.classList.toggle('hidden', !showAlt);
                    altSel?.classList.toggle('hidden', !(showAlt && altOn));
                    cardSel?.classList.toggle('hidden', !(isCard && !(showAlt && altOn)));

                    // término / nº ocorrências
                    termRow?.classList.toggle('hidden', rec === 'unique');
                    const hasEnd = document.getElementById('has_end')?.checked;
                    occWrap?.classList.toggle('hidden', !(hasEnd && rec!=='unique'));

                    // recorrência custom
                    customRec?.classList.toggle('hidden', rec !== 'custom');

                    // cofrinho para investimento
                    savingW?.classList.toggle('hidden', !invest);

                    // ===== Parcelamento (PIX ou cartão de CRÉDITO + Única) =====
                    const showCan = !isRec && (isPix || isCred);

                    if (canRow) {
                        canRow.classList.toggle('hidden', !showCan);
                        if (!showCan) {
                            if (canNo)  canNo.checked  = true;
                            if (canYes) canYes.checked = false;
                        }
                    }

                    const canInstall = !!(canYes && canYes.checked);
                    const showInst   = showCan && canInstall;

                    if (instWrap) {
                        instWrap.classList.toggle('hidden', !showInst);
                        if (!showInst) {
                            const inp = document.getElementById('installments');
                            if (inp) inp.value = '';
                        }
                    }
                }

                const CSRF='{{ csrf_token() }}';
                const u=(t,id)=>t.replace(':id', id);
                const txGrid=document.getElementById('txGrid');
                const isMobile=()=>window.matchMedia('(max-width: 767px)').matches;

                txGrid.addEventListener('click', async (e)=>{
                    const btn=e.target.closest('[data-action="more"]'); if(!btn) return;
                    if(isMobile()){
                        e.preventDefault(); e.stopPropagation(); if(e.stopImmediatePropagation) e.stopImmediatePropagation();
                        const card=btn.closest('article[data-id]'); if(!card) return; openTxSheet(card.dataset.id);
                    }
                }, true);

                const txSheet=document.getElementById('txSheet');
                const txSheetOv=document.getElementById('txSheetOv');
                let txSheetId=null;

                function openTxSheet(id){ txSheetId=id; txSheet.classList.remove('hidden'); document.body.classList.add('overflow-hidden','ui-sheet-open'); }
                function closeTxSheet(){ txSheet.classList.add('hidden'); document.body.classList.remove('overflow-hidden','ui-sheet-open'); }

                async function fetchTx(id){
                    const res=await fetch(u(ROUTES.show,id),{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                    if(!res.ok) throw new Error('Erro ao carregar');
                    const json=await res.json(); return (json?.data ?? json?.transaction ?? json);
                }
                async function deleteTx(id){
                    const fd=new FormData(); fd.append('_method','DELETE'); fd.append('id', id);
                    const res=await fetch(u(ROUTES.destroy, encodeURIComponent(id)),{method:'POST',headers:{'X-CSRF-TOKEN': CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:fd});
                    if(!res.ok) throw new Error('Falha ao excluir');
                }

                txSheet.addEventListener('click', async (e)=>{
                    const b=e.target.closest('[data-sheet-action]'); if(!b||!txSheetId) return;
                    const act=b.dataset.sheetAction;
                    if(act==='edit'){ try{ const data=await fetchTx(txSheetId); closeTxSheet(); openTxModal('edit', data); }catch{ alert('Erro ao carregar transação'); } return; }
                    if(act==='show'){ try{ const data=await fetchTx(txSheetId); closeTxSheet(); openTxModal('show', data); }catch{ alert('Erro ao carregar transação'); } return; }
                    if(act==='delete'){ closeTxSheet(); if(!confirm('Excluir esta transação?')) return; try{ await deleteTx(txSheetId); txCrud.reload(); }catch{ alert('Erro ao excluir'); } return; }
                });

                function openTxModal(mode, tx){
                    const modal=document.getElementById('txModal');
                    const form=modal.querySelector('form');
                    const title=modal.querySelector('[data-crud-title]');
                    const submit=form.querySelector('button[type="submit"]');
                    const isShow=(mode==='show');
                    if(title) title.textContent = mode==='edit' ? 'Editar transação' : 'Detalhes da transação';
                    form.querySelectorAll('input,select,textarea,[type="radio"]').forEach(el=> el.disabled=isShow);
                    if(submit) submit.classList.toggle('hidden', isShow);
                    fillTxForm(form, tx);
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden','ui-modal-open');
                }
                function fillTxForm(form, tx){
                    const set=(name,val)=>{ const el=form.querySelector(`[name="${name}"]`); if(el) el.value=(val??''); };
                    const setC=(id,on)=>{ const el=form.querySelector('#'+id); if(el){ el.checked=!!on; el.dispatchEvent(new Event('change')); } };

                    form.querySelector('[name="id"]')?.setAttribute('value', tx.id ?? tx.uuid ?? '');
                    set('title', tx.title); set('description', tx.description); set('amount', tx.amount);
                    set('date', String(tx.date ?? '').slice(0,10)); set('transaction_category_id', tx.transaction_category_id ?? tx.category_id);
                    if(tx.account_id) set('account_id', tx.account_id); if(tx.card_id) set('card_id', tx.card_id);

                    const type=tx.type||'pix'; setC('pix',type==='pix'); setC('card',type==='card'); setC('money',type==='money');
                    if(tx.type_card){ setC('credit',tx.type_card==='credit'); setC('debit',tx.type_card==='debit'); }

                    const rec=tx.recurrence_type||'unique';
                    setC('unique',rec==='unique'); setC('monthly',rec==='monthly'); setC('yearly',rec==='yearly'); setC('custom',rec==='custom');

                    if(tx.custom_occurrences) set('custom_occurrences', tx.custom_occurrences);
                    if(tx.interval_value) set('interval_value', tx.interval_value);
                    if(tx.include_sat!=null) form.querySelector('#include_sat').checked=!!+tx.include_sat;
                    if(tx.include_sun!=null) form.querySelector('#include_sun').checked=!!+tx.include_sun;

                    toggleUI(form);
                }

                txSheetOv.addEventListener('click', closeTxSheet);
                document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !txSheet.classList.contains('hidden')) closeTxSheet(); });

                (function boot(){
                    const g=document.getElementById('txGrid');
                    const sk=`<article class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-5 shadow-soft">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-center gap-3">
      <span class="size-12 rounded-xl skel"></span>
      <div class="w-40 space-y-2"><div class="h-4 skel"></div><div class="h-3 w-24 skel"></div></div>
    </div>
    <div class="h-8 w-24 rounded-lg skel"></div>
  </div>
  <div class="mt-4 grid grid-cols-2 gap-3"><div class="h-16 rounded-xl skel"></div><div class="h-16 rounded-xl skel"></div></div>
</article>`;
                    g.innerHTML=sk+sk+sk+sk;
                    reloadWithFilters();
                })();
            })();
        </script>
    @endpush
@endsection
