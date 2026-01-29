@extends('layouts.templates.app')

@section('new-content')
    @push('styles')
        <style>
            /* Skeleton */
            .skel{position:relative;overflow:hidden;border-radius:.5rem;background:#e5e7eb}
            .dark .skel{background:#262626}
            .skel::after{content:"";position:absolute;inset:0;transform:translateX(-100%);background:linear-gradient(90deg,transparent,rgba(255,255,255,.55),transparent);animation:skel 1.1s infinite}
            @keyframes skel{100%{transform:translateX(100%)}}

            /* Overlay shimmer por cima de cards reais (quando há cache) */
            .grid-loading{position:relative}
            .grid-loading::after{content:"";position:absolute;inset:0;pointer-events:none;background:linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);animation:skel 1.1s infinite;opacity:.35}
            .dark .grid-loading::after{background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent);opacity:.6}

            #cardFab{z-index:80}
            body.ui-modal-open #cardFab, body.ui-sheet-open #cardFab{z-index:40;pointer-events:none}

            /* Hover/Active efeito flutuante */
            .card-floating{transition:transform .2s ease, box-shadow .2s ease;cursor:pointer;}
            .card-floating:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(0,0,0,.25)}
            .card-floating:active{transform:translateY(0);box-shadow:0 4px 10px rgba(0,0,0,.2)}
        </style>
    @endpush

    <section id="cards-page" class="mt-6">
        <x-card-header
            prevRoute="{{ route('dashboard') }}"
            iconRight="credit-card"
            title="Cartões"
            description="Gerencie seus cartões vinculados às contas.">

            <div class="hidden md:flex items-center gap-2">
                <button data-open-modal="card" class="inline-flex items-center gap-2 p-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                    <i class="fa-solid fa-plus-minus fs-3"></i>
                </button>
            </div>
        </x-card-header>

        <!-- Lista -->
        <div id="cardGrid" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4"></div>

        <!-- Modal Cartão -->
        <div id="cardModal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true" aria-labelledby="cardModalTitle">
            <div id="cardOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-[560px]">
                <div class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-4 md:p-6 max-h-[92vh] overflow-y-auto">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 id="cardModalTitle" class="text-lg font-semibold">Novo cartão</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Informe os dados do cartão.</p>
                        </div>
                        <button id="cardClose" class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800" aria-label="Fechar">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div id="cardFormErr" class="hidden mb-2 rounded-lg bg-red-50 text-red-700 text-sm px-3 py-2"></div>

                    <form id="cardForm" class="mt-4 grid gap-3" novalidate>
                        <input type="hidden" id="card_id" name="id"/>

                        <label class="block">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Banco vinculado</span>
                            <select id="account_id" name="account_id" class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2" required>
                                <option value="">Nenhum banco vinculado</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->bank_name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Titular</span>
                            <input id="cardholder_name" name="cardholder_name" type="text" placeholder="John Doe" class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2" required/>
                        </label>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Últimos 4 dígitos</span>
                                <input id="last_four_digits" name="last_four_digits" inputmode="numeric" maxlength="4" placeholder="0766" class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2" required/>
                            </label>
                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Bandeira</span>
                                <select id="brand" name="brand" class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2" required>
                                    <option value="1">Visa</option>
                                    <option value="2">Mastercard</option>
                                    <option value="3">American Express</option>
                                    <option value="4">Discover</option>
                                    <option value="5">Diners Club</option>
                                    <option value="6">JCB</option>
                                    <option value="7">Elo</option>
                                </select>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Cor do cartão</span>
                                <input id="color_card" name="color_card" type="color" class="mt-1 w-full h-10 rounded-lg border border-neutral-200/70 dark:border-neutral-800/70 cursor-pointer"/>
                            </label>
                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Limite (R$)</span>
                                <input id="credit_limit" name="credit_limit" inputmode="decimal" placeholder="0,00" class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"/>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Dia de fechamento</span>
                                <input id="closing_day" name="closing_day" type="number" min="1" max="31" placeholder="15" class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"/>
                            </label>
                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Dia de vencimento</span>
                                <input id="due_day" name="due_day" type="number" min="1" max="31" placeholder="25" class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"/>
                            </label>
                        </div>

                        <div class="mt-2 flex items-center justify-end gap-2">
                            <button type="button" id="cardCancel" class="px-3 py-2 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800">Cancelar</button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bottom Sheet (mobile) -->
        <div id="cardSheet" class="fixed inset-0 z-[70] hidden" aria-modal="true" role="dialog">
            <div id="cardSheetOv" class="absolute inset-0 bg-black/40 backdrop-blur-[2px]"></div>
            <div class="absolute inset-x-0 bottom-0 rounded-t-2xl border border-neutral-200/60 dark:border-neutral-800/60 bg-white dark:bg-neutral-900 shadow-soft p-2">
                <div class="mx-auto h-1 w-10 rounded-full bg-neutral-300/70 dark:bg-neutral-700/70 mb-2"></div>
                <div class="grid gap-1 p-1">
                    <button data-sheet-action="invoices" class="w-full text-left px-4 py-3 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800">Visualizar faturas</button>
                    <button data-sheet-action="edit" class="w-full text-left px-4 py-3 rounded-xl hover:bg-neutral-50 dark:hover:bg-neutral-800">Editar</button>
                    <button data-sheet-action="delete" class="w-full text-left px-4 py-3 rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Excluir</button>
                </div>
            </div>
        </div>
    </section>

    <!-- FAB (mobile) -->
    <button id="cardFab" type="button" data-open-modal="card" class="md:hidden fixed bottom-20 right-4 z-[80] size-14 rounded-2xl grid place-items-center text-white shadow-lg bg-brand-600 hover:bg-brand-700 active:scale-95 transition" aria-label="Novo cartão">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
    </button>

    @push('scripts')
        <script src="{{ asset('assets/js/common/crud-model.js') }}"></script>
        <script>
            (()=>{

                // ---------- Constantes/rotas
                const CSRF='{{ csrf_token() }}';
                const ROUTES={
                    index:  "{{ route('cards.index') }}",
                    store:  "{{ route('cards.store') }}",
                    show:   "{{ url('/cards') }}/:id",
                    update: "{{ url('/cards') }}/:id",
                    destroy:"{{ url('/cards') }}/:id",
                    invoicesBase: "{{ url('/invoice') }}"
                };
                const u=(t,id)=>t.replace(':id', id);

                // ---------- DOM
                const grid   = document.getElementById('cardGrid');
                const modal  = document.getElementById('cardModal');
                const form   = document.getElementById('cardForm');
                const titleEl= document.getElementById('cardModalTitle');
                const overlay= document.getElementById('cardOverlay');
                const cardFab= document.getElementById('cardFab');
                const btnClose=document.getElementById('cardClose');
                const btnCancel=document.getElementById('cardCancel');
                const errBox = document.getElementById('cardFormErr');

                // Bottom sheet
                const sheet   = document.getElementById('cardSheet');
                const sheetOv = document.getElementById('cardSheetOv');

                // ---------- Utils
                const brandMap={1:'Visa',2:'Mastercard',3:'American Express',4:'Discover',5:'Diners Club',6:'JCB',7:'Elo'};

                // Sempre retorna array (aceita {data:[...]}, {data:{...}}, {...})
                const ensureArray = (d) => {
                    if (!d) return [];
                    const base = (typeof d === 'object' && 'data' in d) ? d.data : d;
                    if (Array.isArray(base)) return base;
                    if (typeof base === 'object') return Object.values(base);
                    return [];
                };

                // helpers de moeda robustos
                const moneyToNumber = (v) => {
                    if (v == null) return 0;
                    if (typeof v === 'number') return v;
                    const s = String(v).trim().replace(/[^\d,.-]/g, '');
                    if (s.includes(',') && s.includes('.')) return parseFloat(s.replace(/\./g, '').replace(',', '.')) || 0;
                    if (s.includes(',')) return parseFloat(s.replace(',', '.')) || 0;
                    return parseFloat(s) || 0;
                };
                const brlSmart = (n) => Number(moneyToNumber(n)).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                const last4=(n)=> String(n||'').slice(-4).padStart(4,'0');

                function updateFabVisibility(has){
                    if(!cardFab) return;
                    const isDesktop=window.matchMedia('(min-width:768px)').matches;
                    cardFab.style.display=(isDesktop && has)?'none':'grid';
                }

                // ---------- Skeleton
                function cardSkeleton(){
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
    <div class="h-16 rounded-xl skel"></div>
  </div>
</article>`}

                // ---------- Template de card
                function cardTemplate(card){
                    const cor=card.color_card || '#444';
                    const brandName=brandMap[card.brand] || card.brand_name || '';
                    return `
<article data-id="${card.id ?? card.uuid}" class="card-floating rounded-2xl shadow-soft overflow-hidden">
  <div class="aspect-[16/10] rounded-2xl p-4 text-white flex flex-col justify-between shadow-inner relative" style="background:${cor}">
    <div class="flex items-start justify-between">
      <div>
        <p class="font-medium">${card.cardholder_name || ''}</p>
        <p class="text-xs opacity-80">${card.account?.bank_name || ''} • ${brandName}</p>
      </div>
      <button type="button" data-sheet-open class="inline-grid size-8 place-items-center rounded-lg hover:bg-black/20" aria-label="Mais ações">
        <svg class="size-4 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>
        </svg>
      </button>
    </div>

    <div class="mt-6">
      <p class="text-2xl tracking-widest">•••• ${last4(card.last_four_digits)}</p>
      <p class="text-xs mt-1">Fechamento ${card.closing_day||'--'} • Vencimento ${card.due_day||'--'}</p>
    </div>

    <div class="mt-4 flex justify-between text-sm">
      <span>Limite</span>
      <span class="font-semibold">${brlSmart(card.credit_limit ?? card.limit ?? card.creditLimit)}</span>
    </div>
  </div>
</article>`;
                }

                // ---------- Helpers de form
                function resetFormCard(formEl) {
                    if (!formEl) return;
                    formEl.reset();
                    const hid = formEl.querySelector('#card_id');
                    if (hid) hid.value = ''; // zera o id para “create”
                    const errBox = document.getElementById('cardFormErr');
                    if (errBox) { errBox.classList.add('hidden'); errBox.textContent=''; }
                }

                // ---------- Hooks CrudLite
                function onModeChange(mode, formEl, title){
                    const isShow=(mode==='show');
                    if(title) title.textContent = mode==='edit' ? 'Editar cartão' : (isShow ? 'Detalhes do cartão' : 'Novo cartão');

                    // limpa tudo ao abrir em modo create (evita id fantasma após delete)
                    if (mode === 'create') {
                        resetFormCard(formEl);
                    }

                    if(formEl){
                        formEl.querySelectorAll('input,select').forEach(el=> el.disabled = isShow);
                        const btn=formEl.querySelector('button[type="submit"]');
                        if(btn) btn.classList.toggle('hidden', isShow);
                    }
                }

                function fillFormCard(formEl, card){
                    if(!formEl||!card) return;
                    (formEl.querySelector('#card_id')||{}).value = card.id ?? card.uuid ?? '';
                    formEl.account_id.value = card.account_id ?? card.account?.id ?? '';
                    formEl.cardholder_name.value = card.cardholder_name ?? '';
                    formEl.last_four_digits.value = (card.last_four_digits ?? '').toString().slice(-4);
                    formEl.brand.value = card.brand ?? '';
                    formEl.color_card.value = card.color_card || '#444444';
                    const rawLimit = card.credit_limit;
                    formEl.credit_limit.value = typeof rawLimit==='number' ? String(rawLimit).replace('.', ',') : (rawLimit ?? '');
                    formEl.closing_day.value = card.closing_day ?? '';
                    formEl.due_day.value = card.due_day ?? '';
                }

                function beforeSubmit(fd){
                    // Se id vazio, não envia (garante POST em create)
                    const idHidden = (fd.get('id') ?? '').toString().trim();
                    if (!idHidden) fd.delete('id');

                    // ✅ account_id (UUID): se vazio remove; se tiver valor, envia string “como veio”
                    const acc = fd.get('account_id');
                    if (acc == null || String(acc).trim() === '') {
                        fd.delete('account_id');          // => vira NULL (passa em nullable|uuid)
                    } else {
                        fd.set('account_id', String(acc).trim());  // <- nada de parseInt aqui!
                    }

                    // Limite (aceita 1.234,56 e 1234,56)
                    const raw = fd.get('credit_limit');
                    if (raw != null) {
                        const cleaned = String(raw)
                            .replace(/[^\d,.,-]/g,'')
                            .replace(/\.(?=\d{3}(?:\D|$))/g,'')
                            .replace(',', '.');
                        fd.set('credit_limit', cleaned);
                    }

                    // últimos 4 dígitos (mantém só os 4 finais)
                    const last = fd.get('last_four_digits');
                    if (last != null) fd.set('last_four_digits', String(last).slice(-4));

                    return fd;
                }


                // ---------- Inicializa CrudLite
                const crud = CrudLite({
                    key: 'cards',
                    routes: {
                        index: ROUTES.index,
                        store: ROUTES.store,
                        show:  ROUTES.show,
                        update:ROUTES.update,
                        destroy:ROUTES.destroy
                    },
                    selectors: {
                        grid: '#cardGrid',
                        modal: '#cardModal',
                        form:  '#cardForm',
                        title: '#cardModalTitle',
                        overlay:'#cardOverlay',
                        openers:'[data-open-modal="card"]',
                        btnClose: '#cardClose',
                        btnCancel:'#cardCancel'
                    },
                    template: cardTemplate,
                    skeleton: cardSkeleton,
                    skeletonCount: 6,
                    parseIndex: (json)=> ensureArray(json?.data ?? json),
                    parseShow:  (json)=> (json && typeof json==='object' && 'data' in json) ? json.data : json,
                    onModeChange,
                    fillForm: fillFormCard,
                    onBeforeSubmit: beforeSubmit,
                    confirmDelete: (id)=> confirm('Excluir este cartão?'),
                    onAction: (act, id)=>{}
                });

                // ---------- Bottom Sheet
                let sheetId=null;
                function openSheet(id){ sheetId=id; sheet.classList.remove('hidden'); document.body.classList.add('overflow-hidden','ui-sheet-open'); }
                function closeSheet(){ sheet.classList.add('hidden'); document.body.classList.remove('overflow-hidden','ui-sheet-open'); }
                sheetOv.addEventListener('click', closeSheet);
                document.addEventListener('keydown', e=>{ if(e.key==='Escape' && !sheet.classList.contains('hidden')) closeSheet(); });

                // Intercepta cliques no botão de 3 pontinhos ANTES de qualquer outro listener (capture = true)
                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-sheet-open]');
                    if (!btn) return;
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    const cardEl = btn.closest('article[data-id]');
                    const id = cardEl?.dataset.id;
                    if (id) openSheet(id);
                }, true);

                // Ações do grid (editar/excluir inline se existirem botões)
                grid.addEventListener('click', async (e)=>{
                    const cardEl=e.target.closest('article[data-id]');
                    if(!cardEl) return;
                    const id=cardEl.dataset.id;

                    if(e.target.closest('[data-action="edit"]')){
                        e.preventDefault(); e.stopPropagation();
                        try{
                            const res=await fetch(u(ROUTES.show,id),{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                            if(!res.ok) throw 0; const rec=await res.json();
                            crud.openModal('edit', rec);
                        }catch{ alert('Erro ao carregar cartão'); }
                        return;
                    }
                    if(e.target.closest('[data-action="delete"]')){
                        e.preventDefault(); e.stopPropagation();
                        if(!confirm('Excluir este cartão?')) return;
                        try{ await doDeleteCard(id); await crud.reload(); }catch{ alert('Erro ao excluir'); }
                        return;
                    }
                });

                document.getElementById('cardSheet').addEventListener('click', async (e)=>{
                    const b=e.target.closest('[data-sheet-action]');
                    if(!b||!sheetId) return;
                    const act=b.dataset.sheetAction;
                    if(act==='edit'){
                        closeSheet();
                        try{
                            const res=await fetch(u(ROUTES.show,sheetId),{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
                            if(!res.ok) throw 0; const rec=await res.json();
                            crud.openModal('edit', rec);
                        }catch{ alert('Erro ao carregar cartão'); }
                        return;
                    }
                    if(act==='invoices'){
                        closeSheet();
                        window.location.href = ROUTES.invoicesBase + '/' + encodeURIComponent(sheetId);
                        return;
                    }
                    if(act==='delete'){
                        closeSheet();
                        if(!confirm('Excluir este cartão?')) return;
                        try{ await doDeleteCard(sheetId); await crud.reload(); }catch{ alert('Erro ao excluir'); }
                        return;
                    }
                });

                async function doDeleteCard(id){
                    const fd=new FormData(); fd.append('_method','DELETE'); fd.append('id', id);
                    const res=await fetch(u(ROUTES.destroy, encodeURIComponent(id)),{
                        method:'POST', headers:{'X-CSRF-TOKEN': CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body: fd
                    });
                    if(!res.ok) throw new Error('Falha ao excluir');
                }

                // ---------- Modal
                function closeModal(){
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden','ui-modal-open');
                    // evita herdar estado antigo caso feche sem salvar
                    resetFormCard(form);
                }
                document.querySelectorAll('[data-open-modal="card"]').forEach(b=> b.addEventListener('click', ()=> crud.openModal('create')));
                btnClose?.addEventListener('click', closeModal);
                overlay?.addEventListener('click', closeModal);
                btnCancel?.addEventListener('click', closeModal);
                document.addEventListener('keydown', e=>{ if(e.key==='Escape' && !modal.classList.contains('hidden')) closeModal(); });

                // ---------- Boot
                window.addEventListener('DOMContentLoaded', ()=>{ updateFabVisibility(false); });
                window.addEventListener('resize', ()=>{ const has=!!document.querySelector('#cardGrid article[data-id]'); updateFabVisibility(has); });
            })();
        </script>
    @endpush
@endsection
