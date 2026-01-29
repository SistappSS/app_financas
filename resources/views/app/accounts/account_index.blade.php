@extends('layouts.templates.app')

@section('new-content')
    @push('styles')
        <style>
            /* Skeleton */
            .skel {
                position: relative;
                overflow: hidden;
                border-radius: .5rem;
                background: #e5e7eb
            }

            .dark .skel {
                background: #262626
            }

            .skel::after {
                content: "";
                position: absolute;
                inset: 0;
                transform: translateX(-100%);
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .55), transparent);
                animation: skel 1.1s infinite
            }

            @keyframes skel {
                100% {
                    transform: translateX(100%)
                }
            }

            /* Overlay shimmer por cima de cards reais (quando há cache) */
            .grid-loading {
                position: relative
            }

            .grid-loading::after {
                content: "";
                position: absolute;
                inset: 0;
                pointer-events: none;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .5), transparent);
                animation: skel 1.1s infinite;
                opacity: .35
            }

            .dark .grid-loading::after {
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .08), transparent);
                opacity: .6
            }

            #accFab {
                z-index: 80;
            }

            body.ui-modal-open #accFab,
            body.ui-sheet-open #accFab {
                z-index: 40;
                pointer-events: none;
            }
        </style>
    @endpush

    <section id="contas-page" class="mt-6">
        <x-card-header
            prevRoute="{{ route('dashboard') }}"
            iconRight="piggy-bank"
            title="Contas bancárias"
            description="Acompanhe saldos por banco e acesse o extrato de cada conta.">

            <div class="hidden md:flex items-center gap-2">
                <button data-open-modal="acc" class="inline-flex items-center gap-2 p-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                    <i class="fa-solid fa-plus-minus fs-3"></i>
                </button>
            </div>
        </x-card-header>

        <!-- Lista -->
        <div id="accGrid" class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>

        <!-- Modal Conta -->
        <div id="accModal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true"
             aria-labelledby="accModalTitle">
            <div id="accOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-crud-overlay></div>
            <div class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-[560px]">
                <div class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-4 md:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 id="accModalTitle" class="text-lg font-semibold" data-crud-title>Nova conta
                                bancária</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Informe os detalhes da conta.</p>
                        </div>
                        <button id="accClose" data-crud-close
                                class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                aria-label="Fechar">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18"/>
                                <path d="M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- erro global opcional -->
                    <div id="accFormErr" class="hidden mb-2 rounded-lg bg-red-50 text-red-700 text-sm px-3 py-2"
                         data-form-error></div>

                    <form id="accForm" class="mt-4 grid gap-3" novalidate>
                        <input type="hidden" id="acc_id" name="id"/>

                        <label class="block">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Nome do banco</span>
                            <input id="bank_name" name="bank_name" type="text" placeholder="Ex: Banco do Norte"
                                   class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                                   required/>
                            <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Valor em conta (R$)</span>
                                <input id="current_balance" name="current_balance" inputmode="decimal"
                                       placeholder="0,00"
                                       value="0.00"
                                       class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                                       required/>
                                <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                            </label>

{{--                            <label class="block">--}}
{{--                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Tipo de conta</span>--}}
{{--                                <div class="mt-1 inline-flex w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-neutral-50 dark:bg-neutral-800 p-1">--}}
{{--                                    <input type="radio" name="type" value="1" id="accCorr" class="peer/acc1 hidden"--}}
{{--                                           checked>--}}
{{--                                    <label for="accCorr"--}}
{{--                                           class="flex-1 text-center px-3 py-1.5 rounded-lg bg-white dark:bg-neutral-900 shadow-sm cursor-pointer peer-checked/acc1:font-medium">Corrente</label>--}}

{{--                                    <input type="radio" name="type" value="2" id="accPoup" class="peer/acc2 hidden">--}}
{{--                                    <label for="accPoup"--}}
{{--                                           class="flex-1 text-center px-3 py-1.5 rounded-lg cursor-pointer hover:bg-white/70 dark:hover:bg-neutral-900/70">Poupança</label>--}}

{{--                                    <input type="radio" name="type" value="3" id="accInv" class="peer/acc3 hidden">--}}
{{--                                    <label for="accInv"--}}
{{--                                           class="flex-1 text-center px-3 py-1.5 rounded-lg cursor-pointer hover:bg-white/70 dark:hover:bg-neutral-900/70">Investimento</label>--}}
{{--                                </div>--}}
{{--                                <p class="field-error mt-1 text-xs text-red-600 hidden"></p>--}}
{{--                            </label>--}}
                        </div>

                        <div class="mt-2 flex items-center justify-end gap-2">
                            <button type="button" id="accCancel" data-crud-cancel
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

        <!-- Modal Transferência -->
        <div id="trModal" class="fixed inset-0 z-[65] hidden" role="dialog" aria-modal="true" aria-labelledby="trTitle">
            <div id="trOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-[520px]">
                <div class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-4 md:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 id="trTitle" class="text-lg font-semibold">Transferência entre contas</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Selecione a conta de destino e o
                                valor.</p>
                        </div>
                        <button id="trClose"
                                class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                aria-label="Fechar">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18"/>
                                <path d="M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form id="trForm" class="mt-4 grid gap-3" novalidate>
                        <div id="formError"
                             class="hidden mb-2 rounded-lg bg-red-50 text-red-700 text-sm px-3 py-2"></div>

                        <input type="hidden" id="trFrom" name="from_id"/>
                        <label class="block">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Conta destino</span>
                            <select id="trTo" name="to_id"
                                    class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                                    required></select>
                            <p id="trToErr" class="mt-1 text-xs text-red-600 hidden"></p>
                        </label>

                        <label class="block">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Valor (R$)</span>
                            <input id="trAmount" name="amount" inputmode="decimal" placeholder="0,00"
                                   class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                                   required/>
                            <p id="trAmountErr" class="mt-1 text-xs text-red-600 hidden"></p>
                        </label>

                        <div class="mt-2 flex items-center justify-end gap-2">
                            <button type="button" id="trCancel"
                                    class="px-3 py-2 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                                Transferir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bottom Sheet (mobile) -->
        <div id="accSheet" class="fixed inset-0 z-[70] hidden" aria-modal="true" role="dialog">
            <div id="accSheetOv" class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
            <div class="absolute inset-x-0 bottom-0 rounded-t-2xl border border-neutral-200/60 dark:border-neutral-800/60 bg-white dark:bg-neutral-900 shadow-soft p-2">
                <div class="mx-auto h-1 w-10 rounded-full bg-neutral-300/70 dark:bg-neutral-700/70 mb-2"></div>
                <div class="grid gap-1 p-1">
                    <button data-sheet-action="edit"
                            class="w-full text-left px-4 py-3 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800">
                        Editar
                    </button>
                    <button data-sheet-action="transfer"
                            class="w-full text-left px-4 py-3 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800">
                        Transferir
                    </button>
                    <button data-sheet-action="statement"
                            class="w-full text-left px-4 py-3 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800">
                        Ver extrato
                    </button>
                    <button data-sheet-action="delete"
                            class="w-full text-left px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- FAB (mobile) -->
    <button id="accFab" type="button" data-open-modal="acc"
            class="md:hidden fixed bottom-20 right-4 z-[80] size-14 rounded-2xl grid place-items-center text-white shadow-lg bg-brand-600 hover:bg-brand-700 active:scale-95 transition"
            aria-label="Nova conta">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14M5 12h14"/>
        </svg>
    </button>

    @push('scripts')
        <script src="{{ asset('assets/js/common/crud-model.js') }}"></script>
        <script>
            (() => {
                // ---------- Constantes/rotas
                const CSRF = '{{ csrf_token() }}';
                const ROUTES = {
                    index: "{{ route('accounts.index') }}",
                    store: "{{ route('accounts.store') }}",
                    show:  "{{ url('/accounts') }}/:id",
                    update:"{{ url('/accounts') }}/:id",
                    destroy:"{{ url('/accounts') }}/:id",
                    savings:"{{ route('savings.index') }}",
                    tx: "{{ route('transaction-view.index') }}",
                    transfer:"{{ route('accounts.transfer') }}"
                };
                const u = (t,id)=>t.replace(':id', id);

                // ---------- DOM
                const grid   = document.getElementById('accGrid');
                const modal  = document.getElementById('accModal');
                const form   = document.getElementById('accForm');
                const titleEl= document.getElementById('accModalTitle');
                const overlay= document.getElementById('accOverlay');
                const accFab = document.getElementById('accFab');

                // Bottom sheet
                const sheet   = document.getElementById('accSheet');
                const sheetOv = document.getElementById('accSheetOv');

                // Transfer
                const trModal  = document.getElementById('trModal');
                const trOverlay= document.getElementById('trOverlay');
                const trClose  = document.getElementById('trClose');
                const trCancel = document.getElementById('trCancel');
                const trForm   = document.getElementById('trForm');
                const trFrom   = document.getElementById('trFrom');
                const trTo     = document.getElementById('trTo');
                const trAmount = document.getElementById('trAmount');

                // ---------- Utils
                const ensureArray = (d)=>Array.isArray(d)?d:(d?.data ?? (typeof d==='object'?Object.values(d):[]));
                const moneyToNumber = (v)=>{
                    if (v==null) return 0;
                    if (typeof v==='number') return v;
                    const s = String(v).trim().replace(/[^\d,.-]/g,'');
                    if (s.includes(',') && s.includes('.')) return parseFloat(s.replace(/\./g,'').replace(',', '.'))||0;
                    if (s.includes(',')) return parseFloat(s.replace(',', '.'))||0;
                    return parseFloat(s)||0;
                };
                const brl = (n)=> (isNaN(n)?'R$ 0,00': Number(n).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}));
                const typeGradient = (t)=> (String(t)==='2'||String(t).toLowerCase()==='poupanca')?'from-emerald-400 to-emerald-600'
                    : (String(t)==='3'||String(t).toLowerCase()==='investimento')?'from-violet-400 to-violet-600'
                        : 'from-brand-400 to-brand-600';
                // const typeLabel = (t)=> (String(t)==='2'||String(t).toLowerCase()==='poupanca')?'Poupança'
                //     : (String(t)==='3'||String(t).toLowerCase()==='investimento')?'Investimento'
                //         : 'Conta corrente';

                // FAB toggle
                function updateFabVisibility(has) {
                    if (!accFab) return;
                    const isDesktop = window.matchMedia('(min-width:768px)').matches;
                    // mobile: SEMPRE visível
                    // desktop: esconde se houver contas
                    accFab.style.display = (isDesktop && has) ? 'none' : 'grid';
                }

                // Skeleton
                function cardSkeleton() {
                    return `
<article class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-5 shadow-soft dark:shadow-softDark">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-center gap-3">
      <span class="size-12 rounded-xl skel"></span>
      <div class="w-40 space-y-2">
        <div class="h-4 skel"></div>
        <div class="h-3 w-24 skel"></div>
      </div>
    </div>
    <div class="h-8 w-24 rounded-lg skel"></div>
  </div>
  <div class="mt-4 space-y-3">
    <div class="h-7 w-36 skel"></div>
    <div class="grid grid-cols-2 gap-3">
      <div class="h-16 rounded-xl skel"></div>
      <div class="h-16 rounded-xl skel"></div>
    </div>
  </div>
</article>`;
                }

                function cardTemplate(acc) {
                    const id = acc.id ?? acc.uuid ?? acc.account_id;
                    const t = acc.type ?? '1';
                    //const label = typeLabel(t);
                    const grad = typeGradient(t);
                    const inAcc = moneyToNumber(acc.current_balance);
                    const cofr = moneyToNumber(acc.saving_amount);
                    const total = inAcc + cofr;
                    return `
<article data-id="${id}" class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-5 shadow-soft">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-center gap-3">
      <span class="size-12 grid place-items-center rounded-xl bg-gradient-to-br ${grad} text-white shadow-soft">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 3h18v6H3z"/><path d="M5 9v11h14V9"/>
        </svg>
      </span>
      <div>
        <p class="font-semibold">${acc.bank_name ?? 'Sem título'}</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <button data-action="edit" class="hidden md:inline-flex text-xs px-2 py-1.5 rounded-lg border hover:bg-neutral-50 dark:hover:bg-neutral-800">Editar</button>
      <button data-action="transfer" class="hidden md:inline-flex text-xs px-2 py-1.5 rounded-lg border hover:bg-neutral-50 dark:hover:bg-neutral-800">Transferir</button>
      <button data-action="delete" class="hidden md:inline-flex text-xs px-2 py-1.5 rounded-lg border border-red-200/70 text-red-600 hover:bg-red-50 dark:border-red-900/50 dark:text-red-400 dark:hover:bg-red-900/20">Excluir</button>
<button type="button" data-action="sheet"
        class="inline-grid size-10 place-items-center rounded-lg border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800"
        aria-label="Mais ações">
    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>
    </svg>
</button>

    </div>
  </div>

  <div class="mt-4">
    <p class="text-xs text-neutral-500 dark:text-neutral-400">Saldo total</p>
    <p class="text-3xl font-semibold tracking-tight" data-total>${brl(total)}</p>
  </div>

  <div class="mt-3 grid grid-cols-2 gap-3">
    <div class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 p-3">
      <p class="text-xs text-neutral-500 dark:text-neutral-400">Em conta</p>
      <p class="text-lg font-medium" data-inacc>${typeof acc.current_balance === 'string' ? acc.current_balance : brl(inAcc)}</p>
    </div>
    <div class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 p-3">
      <p class="text-xs text-neutral-500 dark:text-neutral-400">Cofrinhos</p>
      <p class="text-lg font-medium" data-cofr>${brl(cofr)}</p>
    </div>
  </div>
</article>`;
                }

                // ---------- Hooks personalizados do CrudLite
                function onModeChange(m, formEl, title, data) {
                    const isShow = (m==='show');
                    if (title) title.textContent = m==='edit' ? 'Editar conta' : (isShow ? 'Detalhes da conta' : 'Nova conta bancária');
                    if (formEl) {
                        formEl.querySelectorAll('input,[type="radio"]').forEach(el => el.disabled = isShow);
                        const btn = formEl.querySelector('button[type="submit"]');
                        if (btn) btn.classList.toggle('hidden', isShow);
                    }
                }

                function mapTypeIn(acc) {
                    const v = acc.type ?? acc.account_type ?? acc.account_type_id;
                    if (v===1||v==='1'||v==='corrente') return '1';
                    if (v===2||v==='2'||v==='poupanca') return '2';
                    if (v===3||v==='3'||v==='investimento') return '3';
                    return '1';
                }

                function fillFormAcc(formEl, acc) {
                    if (!formEl || !acc) return;
                    formEl.bank_name.value = acc.bank_name ?? '';
                    const raw = acc.current_balance;
                    formEl.current_balance.value = typeof raw==='number' ? String(raw).replace('.', ',') : String(raw ?? '');
                    // const t = mapTypeIn(acc);
                    // formEl.querySelectorAll('input[name="type"]').forEach(i => i.checked = (i.value === t));
                    // id hidden
                    const hid = formEl.querySelector('#acc_id');
                    if (hid) hid.value = acc.id ?? acc.uuid ?? '';
                }

                function beforeSubmit(fd, mode) {
                    // normaliza valor
                    const val = fd.get('current_balance');
                    if (val != null) {
                        const cleaned = String(val).replace(/[^\d,.,-]/g,'').replace(/\.(?=\d{3}(?:\D|$))/g,'').replace(',', '.');
                        fd.set('current_balance', cleaned);
                    }
                    const t = fd.get('type') || '1';
                    fd.set('type', t);
                    fd.set('account_type', t==='2' ? 'poupanca' : (t==='3' ? 'investimento' : 'corrente'));
                    return fd;
                }

                // patch dos valores de cofrinho/total após render (busca savings)
                let lastList = [];
                async function patchSavingsAndFab(arr) {
                    try {
                        lastList = Array.isArray(arr) ? arr : [];
                        const resSav = await fetch(ROUTES.savings, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                        const savings = resSav.ok ? ensureArray(await resSav.json()) : [];
                        const map = new Map();
                        for (const s of savings) {
                            const id = s.account_id || s.account?.id;
                            if (!id) continue;
                            map.set(id, (map.get(id)||0) + moneyToNumber(s.current_amount));
                        }
                        // atualiza DOM
                        grid.querySelectorAll('article[data-id]').forEach(card => {
                            const id = card.dataset.id;
                            const rec = lastList.find(a => String(a.id ?? a.uuid ?? a.account_id) === String(id));
                            const inAcc = moneyToNumber(rec?.current_balance);
                            const cofr = map.get(id) ?? moneyToNumber(rec?.saving_amount);
                            const total = inAcc + cofr;
                            const cofrEl = card.querySelector('[data-cofr]');
                            const totEl  = card.querySelector('[data-total]');
                            if (cofrEl) cofrEl.textContent = brl(cofr);
                            if (totEl)  totEl.textContent  = brl(total);
                        });
                    } finally {
                        updateFabVisibility(!!grid.querySelector('article[data-id]'));
                    }
                }

                // ---------- Inicializa CrudLite
                const crud = CrudLite({
                    key: 'accounts',
                    routes: {
                        index: ROUTES.index,
                        store: ROUTES.store,
                        show:  ROUTES.show,
                        update:ROUTES.update,
                        destroy:ROUTES.destroy
                    },
                    selectors: {
                        grid: '#accGrid',
                        modal: '#accModal',
                        form:  '#accForm',
                        title: '#accModalTitle',
                        overlay:'#accOverlay',
                        openers:'[data-open-modal="acc"]',
                        btnClose: '#accClose',
                        btnCancel:'#accCancel',
                        fab: '#accFab' // só pra termos referência; CrudLite não usa sozinho
                    },
                    template: cardTemplate,
                    skeleton: cardSkeleton,
                    skeletonCount: 4,
                    parseIndex: (json)=> ensureArray(json),
                    parseShow:  (json)=> (json && typeof json==='object' && 'data' in json) ? json.data : json,
                    onModeChange,
                    fillForm: fillFormAcc,
                    onBeforeSubmit: beforeSubmit,
                    onAfterRender: (arr)=> { patchSavingsAndFab(arr); },
                    confirmDelete: (id)=> confirm('Excluir esta conta?'),
                    onAction: (act, id, api)=> {
                        if (act === 'transfer') {
                            openTransfer(id);
                            return;
                        }

                        if (act === 'sheet') {
                            openSheet(id);
                            return;
                        }

                        // outros “act” custom aqui, se precisar
                    }
                });

                // ---------- Bottom Sheet (abre com botão [data-sheet-open])
                let sheetId = null;
                function openSheet(id) {
                    sheetId = id;
                    sheet.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden','ui-sheet-open');
                }
                function closeSheet() {
                    sheet.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden','ui-sheet-open');
                }
                sheetOv.addEventListener('click', closeSheet);
                document.addEventListener('keydown', e => { if (e.key==='Escape' && !sheet.classList.contains('hidden')) closeSheet(); });

                // Ações do sheet
                sheet.addEventListener('click', async (e)=>{
                    const b = e.target.closest('[data-sheet-action]');
                    if (!b || !sheetId) return;
                    const act = b.dataset.sheetAction;
                    if (act==='transfer') {
                        closeSheet();
                        openTransfer(sheetId);
                        return;
                    }
                    if (act==='statement') {
                        closeSheet();
                        window.location.href = ROUTES.tx + '?account=' + encodeURIComponent(sheetId);
                        return;
                    }
                    if (act==='edit') {
                        closeSheet();
                        // abre via CrudLite (show->edit)
                        try {
                            const res = await fetch(u(ROUTES.show, sheetId), {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                            if (!res.ok) throw 0;
                            const rec = await res.json();
                            crud.openModal('edit', rec);
                        } catch { alert('Erro ao carregar conta'); }
                        return;
                    }
                    if (act==='delete') {
                        closeSheet();
                        if (!confirm('Excluir esta conta?')) return;
                        try {
                            await doDeleteAccount(sheetId);
                            // recarrega lista pra garantir FAB + savings patch
                            await crud.reload();
                        } catch { alert('Erro ao excluir'); }
                        return;
                    }
                });

                // Pequena “rede de segurança” para o FAB após delete pela ação inline do CrudLite
                grid.addEventListener('click', (e)=>{
                    if (e.target.closest('[data-action="delete"]')) {
                        setTimeout(()=> updateFabVisibility(!!grid.querySelector('article[data-id]')), 120);
                    }
                });

                // ---------- Delete manual (sheet/externo)
                async function doDeleteAccount(id) {
                    const fd = new FormData();
                    fd.append('_method','DELETE');
                    fd.append('id', id);
                    const res = await fetch(u(ROUTES.destroy, encodeURIComponent(id)), {
                        method:'POST',
                        headers:{'X-CSRF-TOKEN': CSRF, 'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                        body: fd
                    });
                    if (!res.ok) throw new Error('Falha ao excluir');
                }

                // ---------- Transferência
                function clearFieldError(inputEl, id) {
                    const el = document.getElementById(id);
                    el?.classList.add('hidden'); if (el) el.textContent='';
                    inputEl?.classList.remove('ring-2','ring-red-500/40','border-red-500');
                }
                function showFieldError(inputEl, id, msg) {
                    const el = document.getElementById(id);
                    if (el) { el.textContent = msg||'Campo inválido'; el.classList.remove('hidden'); }
                    inputEl?.classList.add('ring-2','ring-red-500/40','border-red-500');
                }
                function clearTransferErrors() {
                    clearFieldError(trTo,'trToErr');
                    clearFieldError(trAmount,'trAmountErr');
                    const g = document.getElementById('formError');
                    if (g) { g.classList.add('hidden'); g.textContent=''; }
                }
                function showTransferError(msg) {
                    const g = document.getElementById('formError');
                    if (g) { g.textContent = msg||'Erro ao enviar'; g.classList.remove('hidden'); }
                }

                function fillToSelect(accounts, fromId) {
                    const opts = accounts
                        .filter(a => String(a.id ?? a.uuid) !== String(fromId))
                        .map(a => `<option value="${a.id ?? a.uuid}">${String(a.bank_name ?? 'Sem título').toUpperCase()}</option>`);
                    trTo.innerHTML = opts.length ? opts.join('') : '<option value="">Nenhuma conta disponível</option>';
                }

                function openTransfer(fromId) {
                    clearTransferErrors();
                    trForm.reset();
                    trFrom.value = fromId;
                    const list = lastList || [];
                    if (list.length) {
                        fillToSelect(list, fromId);
                    } else {
                        fetch(ROUTES.index, {headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}})
                            .then(r=>r.json()).then(arr=>fillToSelect(ensureArray(arr), fromId))
                            .catch(()=> trTo.innerHTML = '<option value="">Falha ao carregar</option>');
                    }
                    trModal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden','ui-modal-open');
                }
                function closeTransfer() {
                    trModal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden','ui-modal-open');
                }
                trTo.addEventListener('change', ()=> clearFieldError(trTo,'trToErr'));
                trAmount.addEventListener('input', ()=> clearFieldError(trAmount,'trAmountErr'));
                trClose.addEventListener('click', ()=> { clearTransferErrors(); closeTransfer(); });
                trCancel.addEventListener('click', ()=> { clearTransferErrors(); closeTransfer(); });
                trOverlay.addEventListener('click', ()=> { clearTransferErrors(); closeTransfer(); });
                document.addEventListener('keydown', (e)=>{ if (e.key==='Escape' && !trModal.classList.contains('hidden')) closeTransfer(); });

                trForm.addEventListener('submit', async (e)=>{
                    e.preventDefault();
                    clearTransferErrors();
                    const fd = new FormData(trForm);
                    const raw = fd.get('amount');
                    const cleaned = String(raw ?? '').replace(/[^\d,.-]/g,'').replace(/\.(?=\d{3}(?:\D|$))/g,'').replace(',', '.');
                    fd.set('amount', cleaned);
                    try {
                        grid.classList.add('grid-loading');
                        const res = await fetch(ROUTES.transfer, {
                            method:'POST',
                            headers:{'X-CSRF-TOKEN': CSRF, 'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                            body: fd
                        });
                        if (!res.ok) {
                            let data=null; try { data = await res.json(); } catch {}
                            if (res.status===422 && data?.errors) {
                                if (data.errors.to_id?.[0]) showFieldError(trTo,'trToErr',data.errors.to_id[0]);
                                if (data.errors.amount?.[0]) showFieldError(trAmount,'trAmountErr',data.errors.amount[0]);
                                if (data.errors.from_id?.[0]) showTransferError(data.errors.from_id[0]);
                            } else {
                                showTransferError(data?.message || 'Falha na transferência');
                            }
                            return;
                        }
                        closeTransfer();
                        await crud.reload();
                    } catch (err) {
                        showTransferError('Erro ao realizar transferência');
                        console.error(err);
                    } finally {
                        grid.classList.remove('grid-loading');
                    }
                });

                // ---------- Boot
                window.addEventListener('DOMContentLoaded', () => {
                    updateFabVisibility(false); // mostra já no first paint
                });

                window.addEventListener('resize', () => {
                    const has = !!document.querySelector('#accGrid article[data-id]');
                    updateFabVisibility(has);
                });
            })();
        </script>
    @endpush
@endsection
