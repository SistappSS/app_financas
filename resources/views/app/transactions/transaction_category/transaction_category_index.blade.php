@extends('layouts.templates.app')

@section('new-content')
    @push('styles')
        <style>
            /* Skeleton / shimmer */
            .skel{position:relative;overflow:hidden;border-radius:.5rem;background:#e5e7eb}
            .dark .skel{background:#262626}
            .skel::after{content:"";position:absolute;inset:0;transform:translateX(-100%);
                background:linear-gradient(90deg,transparent,rgba(255,255,255,.55),transparent);
                animation:skel 1.1s infinite}
            @keyframes skel{100%{transform:translateX(100%)}}
            .grid-loading{position:relative}
            .grid-loading::after{content:"";position:absolute;inset:0;pointer-events:none;
                background:linear-gradient(90deg,transparent,rgba(255,255,255,.5),transparent);
                animation:skel 1.1s infinite;opacity:.35}
            .dark .grid-loading::after{background:linear-gradient(90deg,transparent,rgba(255,255,255,.08),transparent);opacity:.6}

            /* Icon picker dropdown */
            #iconPickerWrap{position:relative}
            .icon-dd{position:absolute;z-index:11000;inset:auto 0 auto 0;
                background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:12px;
                box-shadow:0 10px 24px rgba(0,0,0,.10);padding:10px;max-height:260px;overflow:auto}
            .dark .icon-dd{background:#0a0a0a;border-color:#262626}
            .icon-grid{display:grid;grid-template-columns:repeat(10,1fr);gap:8px}
            .icon-item{display:grid;place-items:center;height:34px;border-radius:8px;cursor:pointer}
            .icon-item:hover{background:rgba(0,0,0,.06)}
            .dark .icon-item:hover{background:rgba(255,255,255,.06)}

            :root{ --brand-600:#82a8fa; --brand-700:#1d4ed8; }

            .icon-item,
            .icon-item i { color: var(--brand-600) !important; }
            .icon-item:hover{ background: rgba(37,99,235,.08); }
            .dark .icon-item:hover{ background: rgba(37,99,235,.15); }
            #iconBtn i{ color: var(--brand-600) !important; }
            .tcat-icon { color: var(--brand-600) !important; }

            .ui-modal-open #tcatFab{
                opacity:0; transform: translateY(8px); pointer-events:none;
            }
        </style>
    @endpush

    <section id="tcats-page" class="mt-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">Categorias de transação</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Cadastre e gerencie suas categorias.</p>
            </div>
            <div class="hidden md:flex items-center gap-2">
                <button type="button" data-open-modal="tcat"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    Nova categoria
                </button>
            </div>
        </div>

        <!-- Lista -->
        <div id="tcatGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"></div>

        <!-- FAB (mobile) -->
        <button id="tcatFab" type="button" data-open-modal="tcat"
                class="md:hidden fixed bottom-20 right-4 z-[80] size-14 rounded-2xl grid place-items-center
                       text-white shadow-lg bg-brand-600 hover:bg-brand-700 active:scale-95 transition"
                aria-label="Nova categoria">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        </button>

        <!-- Modal Categoria -->
        <div id="tcatModal" class="fixed inset-0 z-[95] hidden" role="dialog" aria-modal="true" aria-labelledby="tcatTitle">
            <div id="tcatOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
            <div class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-[560px]">
                <div class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-4 md:p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 id="tcatTitle" class="text-lg font-semibold">Nova categoria</h3>
                            <p class="text-sm text-neutral-500 dark:text-neutral-400">Informe os detalhes da categoria.</p>
                        </div>
                        <button id="tcatClose"
                                class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
                                aria-label="Fechar">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6 6 18"/><path d="M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form id="tcatForm" class="mt-4 grid gap-3" novalidate>
                        <div id="trFormErr" class="hidden mb-2 rounded-lg bg-red-50 text-red-700 text-sm px-3 py-2"></div>

                        <input type="hidden" id="cat_id" name="id"/>
                        <input type="hidden" id="icon" name="icon" value="fa-solid fa-tags"/>
                        <input type="hidden" id="has_limit" name="has_limit" value="0"/>

                        <label class="block">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Categoria</span>
                            <input id="name" name="name" type="text" placeholder="Salário, Aluguel ..."
                                   class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2" required/>
                            <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                        </label>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Cor</span>
                                <div class="mt-1 flex items-center gap-3">
                                    <input id="color" name="color" type="color" value="#3b82f6"
                                           class="h-9 w-14 rounded-lg border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 p-1"/>
                                    <input id="color_hex" type="text" placeholder="#3b82f6"
                                           class="flex-1 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"/>
                                </div>
                                <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                            </label>

                            <label class="block">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Tipo</span>
                                <div class="mt-1 inline-flex w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-neutral-50 dark:bg-neutral-800 p-1">
                                    <input type="radio" name="type" value="entrada" id="ctEnt" class="peer/ct1 hidden" checked>
                                    <label for="ctEnt" class="flex-1 text-center px-3 py-1.5 rounded-lg bg-white dark:bg-neutral-900 shadow-sm cursor-pointer peer-checked/ct1:font-medium">Entrada</label>

                                    <input type="radio" name="type" value="despesa" id="ctDesp" class="peer/ct2 hidden">
                                    <label for="ctDesp" class="flex-1 text-center px-3 py-1.5 rounded-lg cursor-pointer hover:bg-white/70 dark:hover:bg-neutral-900/70">Despesa</label>

                                    <input type="radio" name="type" value="investimento" id="ctInv" class="peer/ct3 hidden">
                                    <label for="ctInv" class="flex-1 text-center px-3 py-1.5 rounded-lg cursor-pointer hover:bg-white/70 dark:hover:bg-neutral-900/70">Investimento</label>
                                </div>
                                <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                            </label>
                        </div>

                        <!-- Switch + campo limite -->
                        <div id="limitWrap" class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Esta categoria terá um limite?</p>
                                    <p class="text-[11px] text-neutral-400">Disponível para Despesa/Investimento</p>
                                </div>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input id="limitSwitch" type="checkbox" class="peer hidden">
                                    <span class="w-11 h-6 bg-neutral-200 rounded-full relative transition
                                        after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:size-5 after:bg-white after:rounded-full after:transition
                                        peer-checked:bg-brand-600 peer-checked:after:left-5"></span>
                                </label>
                            </div>
                            <div id="limitField" class="mt-3 hidden">
                                <label class="block">
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Limite mensal (R$)</span>
                                    <input id="monthly_limit" name="monthly_limit" inputmode="decimal" placeholder="0,00"
                                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2"/>
                                    <p class="field-error mt-1 text-xs text-red-600 hidden"></p>
                                </label>
                            </div>
                        </div>

                        <!-- Ícone -->
                        <div id="iconPickerWrap">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Ícone</span>
                            <div class="mt-1 flex items-center gap-2">
                                <input id="iconInput" type="text" readonly value="fa-solid fa-tags"
                                       class="flex-1 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                                <button type="button" id="iconBtn"
                                        class="size-10 grid place-items-center rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800">
                                    <i class="fa-solid fa-tags"></i>
                                </button>
                            </div>
                            <!-- Dropdown -->
                            <div id="iconDropdown" class="icon-dd hidden">
                                <input id="iconSearch" class="w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2" placeholder="Buscar ícone...">
                                <div id="iconGrid" class="icon-grid"></div>
                            </div>
                        </div>

                        <div class="mt-2 flex items-center justify-end gap-2">
                            <button type="button" id="tcatCancel"
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

        <!-- Menu flutuante (ancorado ao botão "mais") -->
        <div id="tcatMenu" class="hidden fixed z-[75] min-w-40 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 shadow-soft dark:shadow-softDark p-1">
            <button data-menu-action="edit" class="w-full text-left px-4 py-2 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800">Editar</button>
            <button data-menu-action="show" class="w-full text-left px-4 py-2 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-800">Ver detalhes</button>
            <button data-menu-action="delete" class="w-full text-left px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">Excluir</button>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const CSRF = '{{ csrf_token() }}';
            const ROUTES = {
                index:  "{{ route('transaction-categories.index') }}",
                store:  "{{ route('transaction-categories.store') }}",
                show:   "{{ url('/transaction-categories') }}/:id",
                update: "{{ url('/transaction-categories') }}/:id",
                destroy:"{{ url('/transaction-categories') }}/:id"
            };
            const u = (t, id) => t.replace(':id', id);

            const grid = document.getElementById('tcatGrid');
            const fab = document.getElementById('tcatFab');
            const modal = document.getElementById('tcatModal');
            const overlay = document.getElementById('tcatOverlay');
            const btnOpeners = document.querySelectorAll('[data-open-modal="tcat"]');
            const btnClose = document.getElementById('tcatClose');
            const btnCancel = document.getElementById('tcatCancel');
            const form = document.getElementById('tcatForm');
            const title = document.getElementById('tcatTitle');

            const catIdInp   = document.getElementById('cat_id');
            const nameInput  = document.getElementById('name');
            const color      = document.getElementById('color');
            const colorHex   = document.getElementById('color_hex');

            // limite
            const limitSwitch   = document.getElementById('limitSwitch');
            const limitField    = document.getElementById('limitField');
            const hasLimitInp   = document.getElementById('has_limit');
            const monthlyLimit  = document.getElementById('monthly_limit');

            // ícone
            const iconHidden = document.getElementById('icon');
            const iconInput  = document.getElementById('iconInput');
            const iconBtn    = document.getElementById('iconBtn');
            const iconDD     = document.getElementById('iconDropdown');
            const iconGrid   = document.getElementById('iconGrid');
            const iconSearch = document.getElementById('iconSearch');
            const pickerWrap = document.getElementById('iconPickerWrap');

            // Erro global do form
            const formErr = document.getElementById('trFormErr');

            let mode = 'create';          // 'create' | 'edit' | 'show'
            let currentId = null;
            let suppressUntil = 0;

            // Menu
            const menu = document.getElementById('tcatMenu');
            let menuForId = null;

            // radios de tipo
            const typeRadios = form.querySelectorAll('input[name="type"]');
            typeRadios.forEach(r=>{
                r.addEventListener('change', ()=>{
                    const t = getTypeVal();
                    setLimitUIByType(t);
                    limitField.classList.toggle('hidden', !limitSwitch.checked);
                });
            });

            // Cache
            const CAT_CACHE_KEY = 'tcat_cache_v1';

            // ===== Utils
            function unwrap(obj){ return (obj && typeof obj==='object' && 'data' in obj) ? obj.data : obj; }
            function ensureArray(d){ return Array.isArray(d) ? d : (d?.data ?? (typeof d==='object' ? Object.values(d) : [])); }
            const brl = n => (isNaN(n) ? 'R$ 0,00' : Number(n).toLocaleString('pt-BR',{style:'currency',currency:'BRL'}));
            const moneyToNumber = (v) => {
                if (v == null) return 0;
                if (typeof v === 'number') return v;
                const s = String(v).trim().replace(/[^\d,.-]/g,'');
                if (s.includes(',') && s.includes('.')) return parseFloat(s.replace(/\./g,'').replace(',','.')) || 0;
                if (s.includes(',')) return parseFloat(s.replace(',','.')) || 0;
                return parseFloat(s) || 0;
            };
            function readCache(){ try{ return JSON.parse(localStorage.getItem(CAT_CACHE_KEY)) || null; }catch{ return null; } }
            function writeCache(categories){ try{ localStorage.setItem(CAT_CACHE_KEY, JSON.stringify({categories, t: Date.now()})); }catch{} }
            function showGridOverlay(){ grid.classList.add('grid-loading'); }
            function hideGridOverlay(){ grid.classList.remove('grid-loading'); }
            function toHexOrDefault(v){ const s=String(v||'').trim(); return /^#[0-9a-fA-F]{6}$/.test(s)?s.toLowerCase():'#3b82f6'; }

            // ===== Skeletons
            function renderSkeletons(n=6){
                const item = `
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
  <div class="mt-4 grid grid-cols-2 gap-3">
    <div class="h-16 rounded-xl skel"></div>
    <div class="h-16 rounded-xl skel"></div>
  </div>
</article>`;
                grid.innerHTML = Array.from({length:n}).map(()=>item).join('');
            }

            // ===== Modal
            function setMode(m){
                mode = m;
                const isShow = (m === 'show');
                title.textContent = m === 'edit' ? 'Editar categoria' : (isShow ? 'Detalhes da categoria' : 'Nova categoria');
                form.querySelectorAll('input, [type="radio"]').forEach(el => el.disabled = isShow);
                form.querySelector('button[type="submit"]').classList.toggle('hidden', isShow);
                iconBtn.disabled = isShow;
                if (isShow) iconDD.classList.add('hidden');
                // limpa erro global ao mudar de modo
                formErr.classList.add('hidden'); formErr.textContent='';
            }

            function getTypeVal(){
                const el = form.querySelector('input[name="type"]:checked');
                return el ? el.value : 'entrada';
            }

            function refreshLimitUI(){
                const t = getTypeVal();
                const allow = (t === 'despesa' || t === 'investimento');
                limitSwitch.disabled = !allow;
                if (!allow){
                    limitSwitch.checked = false;
                    hasLimitInp.value   = '0';
                    monthlyLimit.value  = '';
                }
                limitField.classList.toggle('hidden', !limitSwitch.checked);
            }

            function setLimitUIByType(typeVal){
                const allow = (typeVal === 'despesa' || typeVal === 'investimento');
                limitSwitch.disabled = !allow;
                if (!allow){
                    limitSwitch.checked = false;
                    hasLimitInp.value   = '0';
                    limitField.classList.add('hidden');
                    monthlyLimit.value  = '';
                }
            }

            function detectFaClass(val){
                const s = String(val||'').trim();
                if (!s) return 'fa-solid fa-tags';
                const parts = s.split(/\s+/);
                if (parts.length >= 2 && parts[0].startsWith('fa')) return parts.slice(0,2).join(' ');
                if (parts.length >= 1 && parts[0].startsWith('fa-')) return 'fa-solid ' + parts[0];
                return 'fa-solid fa-tags';
            }

            function fillForm(cat){
                const id     = cat.id ?? cat.uuid ?? cat.category_id ?? '';
                const name   = cat.name ?? cat.title ?? '';
                const colorV = cat.color ?? cat.color_hex ?? '#3b82f6';
                const iconV  = cat.icon ?? cat.icon_class ?? 'fa-solid fa-tags';
                const mlRaw  = cat.monthly_limit ?? cat.limit ?? cat.monthlyLimit ?? '';

                // nome
                nameInput.value = name;

                // tipo
                const tIn = (cat.type ?? cat.type_id ?? cat.category_type ?? '').toString().toLowerCase();
                let t = 'despesa';
                if (tIn === '1' || tIn === 'entrada') t = 'entrada';
                else if (tIn === '3' || tIn === 'investimento') t = 'investimento';
                form.querySelectorAll('input[name="type"]').forEach(i => i.checked = (i.value === t));

                // cor
                const cc = toHexOrDefault(colorV);
                color.value = cc;
                colorHex.value = cc;

                // limite
                if (typeof mlRaw === 'number') monthlyLimit.value = String(mlRaw).replace('.',',');
                else if (typeof mlRaw === 'string') monthlyLimit.value = mlRaw;
                else monthlyLimit.value = '';

                const hasLim = !!moneyToNumber(mlRaw) && (t !== 'entrada');
                limitSwitch.checked = hasLim;
                hasLimitInp.value   = hasLim ? '1' : '0';
                refreshLimitUI();

                // ícone
                const ic = detectFaClass(iconV);
                iconHidden.value = ic;
                iconInput.value  = ic;
                iconBtn.querySelector('i').className = ic;

                // id
                catIdInp.value = id;
            }

            function openModal(m='create', data=null){
                setMode(m);
                if (data) {
                    fillForm(data);
                } else {
                    form.reset();
                    catIdInp.value='';
                    color.value='#3b82f6'; colorHex.value='#3b82f6';

                    const def = normalizeIconForRuntime('fa-solid fa-tags');
                    iconHidden.value = def; iconInput.value = def; iconBtn.querySelector('i').className = def;

                    // Tipo default = entrada
                    form.querySelector('#ctEnt').checked = true;

                    // Regras iniciais
                    setLimitUIByType(getTypeVal());
                    limitSwitch.checked = false;
                    hasLimitInp.value = '0';
                    limitField.classList.add('hidden');
                }
                if ((m==='edit' || m==='show') && !catIdInp.value) catIdInp.value = currentId ?? '';
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden','ui-modal-open');
            }
            function closeModal(){
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden','ui-modal-open');
            }
            function openCreate(e){
                e?.preventDefault(); e?.stopPropagation();
                currentId = null;
                closeMenu();
                openModal('create');
            }
            btnOpeners.forEach(b => b.addEventListener('click', openCreate));
            fab?.addEventListener('click', openCreate, {passive:false});
            btnClose.addEventListener('click', closeModal);
            btnCancel.addEventListener('click', closeModal);
            overlay.addEventListener('click', closeModal);
            document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && !modal.classList.contains('hidden')) closeModal(); });

            // sync color inputs
            color.addEventListener('input', ()=>{ colorHex.value = color.value; });
            colorHex.addEventListener('input', ()=>{ if (/^#[0-9a-fA-F]{6}$/.test(colorHex.value)) color.value = colorHex.value; });

            // switch limite
            limitSwitch.addEventListener('change', ()=>{
                hasLimitInp.value = limitSwitch.checked ? '1' : '0';
                limitField.classList.toggle('hidden', !limitSwitch.checked);
                if (!limitSwitch.checked) monthlyLimit.value = '';
            });

            // ===== Ícones FA5/FA6
            function detectFA(){
                const t6 = document.createElement('i');
                t6.className = 'fa-solid fa-tags'; t6.style.position='absolute'; t6.style.opacity=0;
                document.body.appendChild(t6);
                const has6 = /Font Awesome/i.test(getComputedStyle(t6).fontFamily||'');
                t6.remove();
                if (has6) return {v:6, prefix:'fa-solid'};

                const t5 = document.createElement('i');
                t5.className = 'fas fa-tags'; t5.style.position='absolute'; t5.style.opacity=0;
                document.body.appendChild(t5);
                const has5 = /Font Awesome/i.test(getComputedStyle(t5).fontFamily||'');
                t5.remove();
                return {v: has5?5:0, prefix: has5 ? 'fas' : 'fa'};
            }
            const FA = detectFA();

            const MAP_V6_TO_V5 = {
                'cart-shopping':'shopping-cart',
                'bag-shopping':'shopping-bag',
                'basket-shopping':'shopping-basket',
                'mobile-screen':'mobile-alt',
                'location-dot':'map-marker-alt'
            };

            function normalizeIconForRuntime(cls){
                const parts = String(cls||'').trim().split(/\s+/);
                const base = parts.find(p=>p.startsWith('fa-') && p!=='fa-solid' && p!=='fas') || 'fa-tags';
                const name = base.replace(/^fa-/,'');
                if (FA.v === 5){
                    const mapped = MAP_V6_TO_V5[name] || name;
                    return `fas fa-${mapped}`;
                }
                return `fa-solid fa-${name}`;
            }

            const ICONS_V6 = [
                'tags','wallet','cart-shopping','bag-shopping','basket-shopping','money-bill','sack-dollar',
                'arrow-trend-up','arrow-trend-down','chart-line','piggy-bank','utensils','house','wifi','bolt',
                'car','gas-pump','ticket','hospital','stethoscope','gift','plane','hotel','music','film','book',
                'graduation-cap','shirt','paw','leaf','bicycle','broom','trash','screwdriver-wrench','briefcase',
                'clipboard-list','file-invoice-dollar','heart','calendar','calendar-days','calendar-check','bell',
                'clock','envelope','paper-plane','phone','mobile-screen','desktop','laptop','camera','image','images',
                'map','location-dot','clipboard','check','xmark','circle-info','star','trophy','medal','store',
                'warehouse','box','boxes','truck','user','users','id-card','id-badge','lock','key','shield-check',
                'cloud','download','upload','share-nodes','comment','comments','message','globe','flag','thermometer',
                'fire','water','sun','moon','lightbulb','battery-full','microphone','volume-up','file','folder',
                'folder-open','bars','filter','search','sign-in-alt','sign-out-alt','home','compass','road','kit-medical',
                'pills','dna','server','database','handshake','coins','credit-card','landmark','calculator','receipt',
                'chart-bar','chart-pie','barcode','qrcode','cart-plus','tag','at','hashtag','bullhorn','code','terminal',
                'bug','cube','cubes','memory','hdd','tablet-alt','mobile-alt','unlock-alt','heartbeat','running',
                'campground','recycle','mountain','map-pin','route','truck-moving','tractor','bed','bath','shower','mug-hot',
                'wine-glass','beer','umbrella','fan','pizza-slice','hamburger','coffee','seedling','tree','user-cog',
                'user-graduate','child','smile','meh','frown','magnet','spray-can','wrench','flask'
            ];
            const ICONS_V5 = [
                'tags','wallet','shopping-cart','shopping-bag','shopping-basket','money-bill','sack-dollar',
                'chart-line','piggy-bank','utensils','home','wifi','bolt','car','gas-pump','ticket-alt','hospital',
                'stethoscope','gift','plane','hotel','music','film','book','graduation-cap','tshirt','paw','leaf',
                'bicycle','broom','trash','tools','briefcase','clipboard-list','file-invoice-dollar','heart','calendar',
                'calendar-alt','calendar-check','bell','clock','envelope','paper-plane','phone','mobile-alt','desktop',
                'laptop','camera','image','images','map','map-marker-alt','clipboard','check','times','info-circle','star',
                'trophy','medal','store','warehouse','box','boxes','truck','user','users','id-card','id-badge','lock','key',
                'shield-alt','cloud','download','upload','share-alt','comment','comments','comment-dots','globe','flag',
                'thermometer-half','fire','tint','sun','moon','lightbulb','battery-full','microphone','volume-up','file',
                'folder','folder-open','bars','filter','search','sign-in-alt','sign-out-alt','home','compass','road',
                'first-aid','pills','dna','server','database','handshake','coins','credit-card','landmark','calculator',
                'receipt','chart-bar','chart-pie','barcode','qrcode','cart-plus','tag','at','hashtag','bullhorn','code',
                'terminal','bug','cube','cubes','memory','hdd','tablet-alt','mobile-alt','unlock-alt','heartbeat','running',
                'campground','recycle','mountain','map-pin','route','truck-moving','tractor','bed','bath','shower','mug-hot',
                'wine-glass','beer','umbrella','fan','pizza-slice','hamburger','coffee','seedling','tree','user-cog',
                'user-graduate','child','smile','meh','frown','magnet','spray-can','wrench','flask'
            ];
            const ICONS = (FA.v===5) ? ICONS_V5 : ICONS_V6;

            function renderIcons(list){
                iconGrid.innerHTML = '';
                list.forEach(n=>{
                    const div = document.createElement('div');
                    div.className = 'icon-item';
                    const i = document.createElement('i');
                    i.className = `${FA.prefix} fa-${n}`;
                    div.appendChild(i);
                    div.dataset.value = i.className;
                    div.onclick = ()=>{
                        const val = div.dataset.value;
                        iconHidden.value = val;
                        iconInput.value  = val;
                        iconBtn.querySelector('i').className = val;
                        closeIconDD();
                    };
                    iconGrid.appendChild(div);
                });
            }
            function openIconDD(){
                iconDD.classList.remove('hidden');
                renderIcons(ICONS);
                iconSearch.value=''; iconSearch.focus();
                const r = pickerWrap.getBoundingClientRect();
                const spaceBelow = window.innerHeight - r.bottom;
                iconDD.style.top = ''; iconDD.style.bottom = '';
                iconDD.style.marginTop = ''; iconDD.style.marginBottom='';
                if (spaceBelow < 280){ iconDD.style.bottom = '100%'; iconDD.style.marginBottom = '6px'; }
                else { iconDD.style.top = '100%'; iconDD.style.marginTop = '6px'; }
            }
            function closeIconDD(){ iconDD.classList.add('hidden'); }

            iconBtn.addEventListener('click', (e)=>{ e.preventDefault(); iconDD.classList.toggle('hidden'); if(!iconDD.classList.contains('hidden')) openIconDD(); });
            iconInput.addEventListener('click', (e)=>{ e.preventDefault(); if(iconDD.classList.contains('hidden')) openIconDD(); });
            iconSearch.addEventListener('input', ()=>{ const q = iconSearch.value.trim().toLowerCase(); renderIcons(ICONS.filter(n=>n.includes(q))); });
            document.addEventListener('click', (e)=>{ if (!pickerWrap.contains(e.target)) closeIconDD(); });
            document.addEventListener('keydown', (e)=>{ if (e.key==='Escape') closeIconDD(); });

            (function syncDefaultIcon(){
                const ic = normalizeIconForRuntime(iconHidden.value || 'fa-solid fa-tags');
                iconHidden.value = ic; iconInput.value = ic; iconBtn.querySelector('i').className = ic;
            })();

            // ===== Card
            function typeBadgeLabel(t){
                t = String(t||'').toLowerCase();
                if (t==='entrada' || t==='1') return 'Entrada';
                if (t==='investimento' || t==='3') return 'Investimento';
                return 'Despesa';
            }
            function cardTemplate(cat){
                const id = cat.id ?? cat.uuid ?? cat.category_id;
                const name = cat.name ?? 'Sem nome';
                const colorHex = toHexOrDefault(cat.color);
                const typeLabel = typeBadgeLabel(cat.type);
                const limitNum = moneyToNumber(cat.monthly_limit);
                const limitTxt = limitNum ? (typeof cat.monthly_limit==='string' ? cat.monthly_limit : brl(limitNum)) : '—';
                const fa = normalizeIconForRuntime(cat.icon || 'fa-solid fa-tags');

                let colorType, bgType;
                if(typeLabel === 'Entrada') {
                    colorType = '#00d679'; bgType = '#00d6791a';
                } else if(typeLabel === 'Despesa') {
                    colorType = '#e46c6c'; bgType = '#e46c6c1a';
                } else {
                    colorType = '#d6c400'; bgType = '#d6c4001a';
                }

                return `
<article data-id="${id}" class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-5 shadow-soft dark:shadow-softDark group">
  <div class="flex items-start justify-between gap-3">
    <div class="flex items-center gap-3">
      <span class="size-12 grid place-items-center rounded-xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">
        <i class="${fa} fa-fw" style="color:${colorHex};"></i>
      </span>
      <div>
        <p class="font-semibold">${name}</p>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">${typeLabel}</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="inline-flex items-center h-8 px-2 rounded-lg text-[11px] font-medium" style="color:${colorType};border:1px solid ${colorType};background:${bgType}">${typeLabel}</span>
      <button data-action="more" class="inline-grid size-10 place-items-center rounded-lg border border-neutral-200/70 dark:border-neutral-800/70 hover:bg-neutral-50 dark:hover:bg-neutral-800" aria-label="Mais ações">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/></svg>
      </button>
    </div>
  </div>

  <div class="mt-4 grid grid-cols-2 gap-3">
    <div class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 p-3">
      <p class="text-xs text-neutral-500 dark:text-neutral-400">Limite mensal</p>
      <p class="text-lg font-medium">${limitNum ? limitTxt : '—'}</p>
    </div>
    <div class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 p-3">
      <p class="text-xs text-neutral-500 dark:text-neutral-400">Cor</p>
      <div class="mt-1 flex items-center gap-2">
        <span class="inline-block size-4 rounded" style="background:${colorHex};border:1px solid #00000014;"></span>
        <span class="text-sm">${colorHex}</span>
      </div>
    </div>
  </div>
</article>`;
            }

            // ===== Prime from cache
            (function primeFromCache(){
                const cached = readCache();
                if (cached?.categories?.length){
                    grid.innerHTML = cached.categories.map(cardTemplate).join('');
                    showGridOverlay();
                } else {
                    renderSkeletons();
                }
            })();

            // ===== Load
            async function loadCategories(){
                try{
                    const res = await fetch(ROUTES.index, { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
                    if (!res.ok) throw new Error('Falha ao carregar categorias');
                    const cats = ensureArray(await res.json());
                    grid.innerHTML = cats.length ? cats.map(cardTemplate).join('') :
                        `<div class="text-sm text-neutral-500">Nenhuma categoria cadastrada.</div>`;
                    writeCache(cats);
                } catch(e){ console.error(e); }
                finally{ hideGridOverlay(); }
            }

            // ===== Menu flutuante
            function closeMenu(){ menu.classList.add('hidden'); menuForId = null; }
            function openMenuFor(cardEl, anchorBtn){
                menuForId = cardEl.dataset.id;
                const r = anchorBtn.getBoundingClientRect();
                const top = r.bottom + window.scrollY + 6;
                const left = Math.min(window.scrollX + r.left, window.scrollX + window.innerWidth - 200);
                menu.style.top = `${top}px`;
                menu.style.left = `${left}px`;
                menu.classList.remove('hidden');
            }
            window.addEventListener('scroll', closeMenu, {passive:true});
            window.addEventListener('resize', closeMenu, {passive:true});
            document.addEventListener('click', (e)=>{
                if (!menu.classList.contains('hidden') && !e.target.closest('#tcatMenu') && !e.target.closest('[data-action="more"]')) closeMenu();
            });

            menu.addEventListener('click', async (e)=>{
                const b = e.target.closest('[data-menu-action]');
                if (!b || !menuForId) return;
                const act = b.dataset.menuAction;
                const id = menuForId;
                closeMenu();

                if (act === 'edit' || act === 'show'){
                    try{
                        const res = await fetch(u(ROUTES.show, id), { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
                        if (!res.ok) throw 0;
                        const cat = unwrap(await res.json());
                        currentId = id;
                        openModal(act === 'edit' ? 'edit' : 'show', cat);
                    } catch { alert('Erro ao carregar categoria'); }
                    return;
                }
                if (act === 'delete'){
                    if (!confirm('Excluir esta categoria?')) return;
                    try{
                        await doDeleteCategory(id);
                        const el = [...grid.querySelectorAll('article[data-id]')].find(n=>n.dataset.id==id);
                        el?.remove();
                    } catch { alert('Erro ao excluir'); }
                    return;
                }
            });

            // ===== Delegação grid
            grid.addEventListener('click', async (e)=>{
                const card = e.target.closest('article[data-id]');
                if (!card) return;
                const id = card.dataset.id;

                const more = e.target.closest('[data-action="more"]');
                if (more){
                    e.preventDefault();
                    suppressUntil = Date.now() + 400;
                    openMenuFor(card, more);
                    return;
                }

                if (Date.now() < suppressUntil) return;
                try{
                    const res = await fetch(u(ROUTES.show, id), { headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'} });
                    if (!res.ok) throw 0;
                    const cat = unwrap(await res.json());
                    currentId = id;
                    openModal('show', cat);
                } catch { alert('Erro ao carregar detalhes'); }
            });

            // ===== Submit
            form.addEventListener('submit', async (e)=>{
                e.preventDefault();

                // limpa erros anteriores
                formErr.classList.add('hidden'); formErr.textContent='';
                form.querySelectorAll('.field-error').forEach(el=>{ el.textContent=''; el.classList.add('hidden'); });
                form.querySelectorAll('input,select,textarea').forEach(el=> el.classList.remove('ring-2','ring-red-500/40','border-red-500'));

                const fd = new FormData(form);

                // normaliza limite
                const lim = fd.get('monthly_limit');
                const hasLim = hasLimitInp.value === '1';
                if (hasLim && lim != null){
                    const cleaned = String(lim).replace(/[^\d,.,-]/g,'').replace(/\.(?=\d{3}(?:\D|$))/g,'').replace(',', '.');
                    fd.set('monthly_limit', cleaned);
                } else {
                    fd.set('monthly_limit', '');
                }

                // garante cor
                const cHex = (colorHex.value || color.value || '').trim();
                if (cHex) fd.set('color', cHex);

                const id = catIdInp.value?.trim();
                const isEdit = !!id;
                let url = isEdit ? u(ROUTES.update, id) : ROUTES.store;
                if (isEdit) fd.append('_method', 'PUT');

                try{
                    showGridOverlay();
                    const res = await fetch(url, {
                        method:'POST',
                        headers:{'X-CSRF-TOKEN': CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                        body: fd
                    });

                    if (!res.ok){
                        let data=null; try{ data = await res.json(); }catch{}
                        if (res.status===422 && data?.errors){
                            for (const [field,msgs] of Object.entries(data.errors)){
                                const input = form.querySelector(`[name="${field}"]`);
                                const errEl = input ? input.closest('label,div,fieldset')?.querySelector('.field-error') : null;
                                if (errEl){ errEl.textContent = msgs?.[0] || 'Campo inválido'; errEl.classList.remove('hidden'); }
                                input?.classList.add('ring-2','ring-red-500/40','border-red-500');
                            }
                            if (data?.message){ formErr.textContent = data.message; formErr.classList.remove('hidden'); }
                            return;
                        }
                        if (data?.message){ formErr.textContent = data.message; formErr.classList.remove('hidden'); }
                        throw new Error(data?.message || 'Erro ao salvar');
                    }

                    closeModal();
                    catIdInp.value = '';
                    await loadCategories();
                } catch(err){
                    alert(err.message || 'Falha ao salvar');
                } finally{
                    hideGridOverlay();
                }
            });

            // limpar erro inline ao digitar
            form.addEventListener('input', (e)=>{
                const el = e.target.closest('input,select,textarea');
                if (!el) return;
                const wrapErr = el.closest('label,div,fieldset')?.querySelector('.field-error');
                wrapErr?.classList.add('hidden'); if (wrapErr) wrapErr.textContent='';
                el.classList.remove('ring-2','ring-red-500/40','border-red-500');
            });

            // ===== Delete
            async function doDeleteCategory(rawId){
                const id = (rawId ?? '').toString().trim() || catIdInp?.value?.trim() || currentId;
                if (!id) throw new Error('ID inválido');
                const url = u(ROUTES.destroy, encodeURIComponent(id));
                const fd = new FormData();
                fd.append('_method', 'DELETE'); fd.append('id', id);
                const res = await fetch(url, {
                    method:'POST',
                    headers:{'X-CSRF-TOKEN': CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
                    body: fd
                });
                if (!res.ok) throw new Error('Falha ao excluir');
            }

            // Boot
            window.addEventListener('DOMContentLoaded', ()=>{
                fab?.classList.remove('hidden');
                loadCategories().catch(()=>{});
            });
        })();
    </script>
@endpush
