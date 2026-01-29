{{-- resources/views/projection/statement.blade.php --}}
@extends('layouts.templates.app')

@section('new-content')
    @push('styles')
        <style>
            .skel{position:relative;overflow:hidden;border-radius:.5rem;background:#e5e7eb}
            .dark .skel{background:#262626}
            .skel::after{content:"";position:absolute;inset:0;transform:translateX(-100%);background:linear-gradient(90deg,transparent,rgba(255,255,255,.55),transparent);animation:skel 1.1s infinite}
            @keyframes skel{100%{transform:translateX(100%)}}

            .chip{display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .7rem;border:1px solid rgba(0,0,0,.08);border-radius:.6rem;background:#fff;color:#334155;font-weight:600;font-size:.8rem}
            .dark .chip{border-color:rgba(255,255,255,.10);background:rgba(255,255,255,.02)}
            .chip.active{background:#2563eb;color:#fff;border-color:transparent;box-shadow:0 6px 16px rgba(37,99,235,.25)}
            .badge-soft{font-size:.7rem;border:1px solid rgba(0,0,0,.08);border-radius:999px;padding:1px 6px;background:#fff;color:#64748B;font-weight:500}
            .dark .badge-soft{border-color:rgba(255,255,255,.10);background:rgba(255,255,255,.04)}
            .col-amt.pos{color:#1e00cf}
            .col-amt.neg{color:#980000}
        </style>
    @endpush

    <section class="mt-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">Projeção financeira — Extrato</h2>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Entradas, saídas e saldo acumulado no período selecionado.</p>
            </div>
        </div>

        <div class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Início</span>
                    <input type="date" id="dtStart"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                </label>
                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Fim</span>
                    <input type="date" id="dtEnd"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                </label>
            </div>

            @if(isset($savings) && $savings->count())
                <div class="mt-3 border-t border-neutral-200/70 dark:border-neutral-800/70 pt-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                Incluir valores dos cofrinhos?
                            </div>
                            <p class="text-[11px] text-neutral-400 dark:text-neutral-500">
                                Esses valores serão somados ao saldo inicial da projeção.
                            </p>
                        </div>
                        <button type="button" id="toggleSavings"
                                class="relative inline-flex h-6 w-11 items-center rounded-full bg-neutral-300 dark:bg-neutral-700 transition-colors">
                            <span class="sr-only">Incluir cofrinhos</span>
                            <span data-knob
                                  class="inline-block h-5 w-5 rounded-full bg-white shadow transform transition-transform translate-x-0"></span>
                        </button>
                    </div>

                    <!-- Lista de cofrinhos com toggle individual -->
                    <div id="savingsList" class="mt-3 hidden space-y-2">
                        @foreach($savings as $saving)
                            <label class="flex items-center justify-between gap-3 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-neutral-50/70 dark:bg-neutral-900/60 px-3 py-2">
                                <div>
                                    <div class="text-xs font-medium text-neutral-800 dark:text-neutral-100">
                                        {{ $saving->name ?? 'Cofrinho' }}
                                    </div>
                                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                        Saldo:
                                        R$ {{ number_format($saving->current_amount ?? 0, 2, ',', '.') }}
                                    </div>
                                </div>
                                <button type="button"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full text-[10px] font-semibold uppercase tracking-wide saving-toggle"
                                        data-saving-id="{{ $saving->id }}">
                                    <span class="sr-only">Incluir {{ $saving->name ?? 'cofrinho' }}</span>
                                    <span class="absolute inset-y-0 left-0 m-[2px] inline-flex h-4 w-4 items-center justify-center rounded-full bg-white shadow"></span>
                                </button>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-3 flex flex-wrap gap-2">
                <button class="chip" data-range="1">+1 mês</button>
                <button class="chip" data-range="3">+3 meses</button>
                <button class="chip" data-range="6">+6 meses</button>
                <button class="chip" data-range="12">+12 meses</button>
                <button class="chip" data-range="15">+15 meses</button>

                <button id="btnApply"
                        class="ml-auto inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white shadow-soft">
                    <i class="fa fa-magnifying-glass text-[12px]"></i>
                    <span class="text-[14px] tracking-wide">Aplicar</span>
                </button>
            </div>
        </div>

        <div class="mt-3 rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="flex items-center justify-between">
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Saldo em conta</div>
                    <div id="sumOpening" class="font-semibold text-[13px]">—</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Entradas</div>
                    <div id="sumIn" class="font-semibold text-[13px] text-indigo-700">—</div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Saídas</div>
                    <div id="sumOut" class="font-semibold text-[13px] text-red-700">—</div>
                </div>
                <div class="col-span-2 md:col-span-1 md:col-start-4 flex items-center justify-between">
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Saldo final</div>
                    <div id="sumEnd" class="font-semibold text-[13px]">—</div>
                </div>
            </div>
        </div>

        <div id="statement" class="mt-3">Carregando…</div>
    </section>

    @push('scripts')
        <script>
            (() => {
                const fmtBRL = v => (Number(v) || 0).toLocaleString('pt-BR', {style:'currency',currency:'BRL'});
                const pad2 = n => String(n).padStart(2,'0');

                const $start = document.getElementById('dtStart');
                const $end   = document.getElementById('dtEnd');
                const $apply = document.getElementById('btnApply');
                const $statement = document.getElementById('statement');

                const today = new Date();
                const defStart = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                const defEnd   = new Date(today.getFullYear(), today.getMonth() + 3, today.getDate());

                $start.value = toISO(defStart);
                $end.value   = toISO(defEnd);

                // ===== COFRINHOS =====
                const selectedSavings = new Set();
                let includeSavings = false;

                const $toggleSavings = document.getElementById('toggleSavings');
                const $savingsList   = document.getElementById('savingsList');

                if ($toggleSavings) {
                    const knob = $toggleSavings.querySelector('[data-knob]');
                    $toggleSavings.addEventListener('click', () => {
                        includeSavings = !includeSavings;

                        // cor do toggle
                        $toggleSavings.classList.toggle('bg-brand-600', includeSavings);
                        $toggleSavings.classList.toggle('bg-neutral-300', !includeSavings);
                        $toggleSavings.classList.toggle('dark:bg-neutral-700', !includeSavings);

                        // posição do “knob”
                        if (knob) {
                            knob.classList.toggle('translate-x-5', includeSavings);
                            knob.classList.toggle('translate-x-0', !includeSavings);
                        }

                        if ($savingsList) {
                            $savingsList.classList.toggle('hidden', !includeSavings);
                        }
                    });
                }

                document.querySelectorAll('.saving-toggle').forEach(btn => {
                    const id = btn.dataset.savingId;

                    // estado inicial OFF
                    btn.classList.add('bg-neutral-300','text-neutral-600','dark:bg-neutral-700','dark:text-neutral-200');

                    btn.addEventListener('click', () => {
                        const isOn = btn.classList.contains('bg-brand-600');

                        if (isOn) {
                            // desligar
                            btn.classList.remove('bg-brand-600','text-white');
                            btn.classList.add('bg-neutral-300','text-neutral-600','dark:bg-neutral-700','dark:text-neutral-200');
                            selectedSavings.delete(id);
                        } else {
                            // ligar
                            btn.classList.add('bg-brand-600','text-white');
                            btn.classList.remove('bg-neutral-300','text-neutral-600','dark:bg-neutral-700','dark:text-neutral-200');
                            selectedSavings.add(id);
                        }
                    });
                });
                // ======================

                document.querySelectorAll('.chip[data-range]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        document.querySelectorAll('.chip[data-range]').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        const months = parseInt(btn.dataset.range, 10);
                        const s = new Date($start.value || today.toISOString().slice(0,10));
                        const e = new Date(s);
                        e.setMonth(e.getMonth() + months);
                        $end.value = toISO(e);
                    });
                });

                $apply.addEventListener('click', () => load($start.value, $end.value));
                load($start.value, $end.value);

                function toISO(d){ return d.getFullYear()+'-'+pad2(d.getMonth()+1)+'-'+pad2(d.getDate()); }
                function formatDate(iso){ const [y,m,d]=iso.split('-'); return `${d}/${m}/${y}`; }
                function esc(s){ return String(s||'').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

                async function load(start, end){
                    try{
                        $statement.innerHTML = `
        <article class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-5">
          <div class="h-6 skel w-40"></div>
          <div class="mt-3 space-y-2">
            <div class="h-12 skel rounded-xl"></div>
            <div class="h-12 skel rounded-xl"></div>
            <div class="h-12 skel rounded-xl"></div>
          </div>
        </article>`;

                        const url = new URL("{{ route('projection.data') }}", window.location.origin);
                        url.searchParams.set('start', start);
                        url.searchParams.set('end', end);

                        // envia ids dos cofrinhos selecionados
                        if (includeSavings && selectedSavings.size > 0) {
                            selectedSavings.forEach(id => url.searchParams.append('savings[]', id));
                        }

                        const r = await fetch(url, { headers:{ 'Accept':'application/json' } });
                        if(!r.ok) throw new Error('Falha ao carregar projeção');
                        const data = await r.json();

                        renderSummary(data);
                        renderStatement(data.days, data.opening_balance, start, end);
                    }catch(e){
                        console.error('[projection load error]', e);
                        $statement.innerHTML = `
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-700 px-3 py-2">
          ${esc(e.message)} — verifique se a rota <strong>projection.data</strong> está retornando JSON.
        </div>`;
                    }
                }

                function renderSummary(data){
                    const savings = Number(data.savings_balance || 0);

                    let openingToShow;

                    if (data.align_to_current && data.has_today && typeof data.current_balance === 'number') {
                        openingToShow = Number(data.current_balance) + savings;
                    } else {
                        openingToShow = Number(data.opening_balance || 0);
                    }

                    document.getElementById('sumOpening').textContent = fmtBRL(openingToShow);
                    document.getElementById('sumIn').textContent      = fmtBRL(data.total_in);
                    document.getElementById('sumOut').textContent     = fmtBRL(data.total_out);
                    document.getElementById('sumEnd').textContent     = fmtBRL(data.ending_balance);
                }

                function renderStatement(days, openingBalance, startISO, endISO){
                    const list = Array.isArray(days) ? days.slice() : [];

                    const monthKey = iso => iso.slice(0,7);
                    const parseISO = iso => { const [y,m,d]=iso.split('-').map(Number); return new Date(y, m-1, d); };
                    const enumMonths = (startISO, endISO) => {
                        if(!startISO || !endISO) return [];
                        const s = parseISO(startISO);
                        const e = parseISO(endISO);
                        const out=[];
                        const cur = new Date(s.getFullYear(), s.getMonth(), 1);
                        const last= new Date(e.getFullYear(), e.getMonth(), 1);
                        while(cur <= last){
                            out.push(cur.getFullYear()+'-'+pad2(cur.getMonth()+1));
                            cur.setMonth(cur.getMonth()+1);
                        }
                        return out;
                    };

                    list.sort((a,b)=> String(a.date).localeCompare(String(b.date)));

                    const byMonth = {};
                    for(const d of list){
                        const ym = monthKey(d.date);
                        (byMonth[ym] ||= []).push(d);
                    }

                    const months = enumMonths(startISO, endISO);
                    if(!months.length){
                        $statement.innerHTML = `
                  <div class="rounded-xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-5">
                    <div class="text-neutral-500">Sem lançamentos no período.</div>
                  </div>`;
                        return;
                    }

                    let runningBalance = Number(openingBalance) || 0;
                    const htmlParts = [];

                    months.forEach((ym, idx) => {
                        const ds = (byMonth[ym] || []).sort((a,b)=> String(a.date).localeCompare(String(b.date)));

                        const [Y, M] = ym.split('-');
                        const label = `${M}/${Y}`;

                        const monthOpening = runningBalance;

                        const monthIn  = ds.reduce((s,x)=> s + Number(x.in  || 0), 0);
                        const monthOut = ds.reduce((s,x)=> s + Number(x.out || 0), 0);
                        const monthNet = monthIn - monthOut;

                        let monthEnding = monthOpening + monthNet;

                        let html = `
<article class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 overflow-hidden shadow-soft mb-4">
  <div class="px-4 pt-4">
    <div class="text-sm text-neutral-500 dark:text-neutral-400 font-medium">${label}</div>

    <div class="mt-2 space-y-1 text-sm">
      <div class="flex items-center justify-between">
        <span class="text-neutral-500 dark:text-neutral-400">Saldo inicial do mês</span>
        <span class="font-semibold">${fmtBRL(monthOpening)}</span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-neutral-500 dark:text-neutral-400">Entradas</span>
        <span class="font-semibold text-indigo-700">${fmtBRL(monthIn)}</span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-neutral-500 dark:text-neutral-400">Saídas</span>
        <span class="font-semibold text-red-700">${fmtBRL(monthOut)}</span>
      </div>
      <hr class="my-1 border-neutral-200/70 dark:border-neutral-800/70">
      <div class="flex items-center justify-between">
        <span class="text-neutral-500 dark:text-neutral-400">Saldo do mês</span>
        <span class="font-semibold">${fmtBRL(monthNet)}</span>
      </div>
      <div class="flex items-center justify-between">
        <span class="text-neutral-500 dark:text-neutral-400">Saldo final do mês (projetado)</span>
        <span class="font-semibold">${fmtBRL(monthEnding)}</span>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <div class="grid grid-cols-[130px_1fr_130px] max-sm:grid-cols-[1fr_110px] items-center bg-brand-600 text-white text-[13.5px] font-semibold px-4 py-2">
      <div class="max-sm:hidden">Data</div>
      <div>Transação</div>
      <div class="justify-self-end">Valor</div>
    </div>
`;

                        if (ds.length === 0) {
                            html += `
    <div class="grid grid-cols-[130px_1fr_130px] max-sm:grid-cols-[1fr_110px] items-center px-4 py-3 border-b border-neutral-200/70 dark:border-neutral-800/70 bg-neutral-50/40 dark:bg-neutral-900/40">
      <div class="max-sm:hidden"></div>
      <div class="text-sm text-neutral-500">Sem lançamentos neste mês</div>
      <div></div>
    </div>
`;
                        } else {
                            ds.forEach(d => {
                                const dateLabel = formatDate(d.date);
                                const items = Array.isArray(d.items) ? d.items : [];

                                items.forEach(it => {
                                    const amt = Number(it.amount || 0);
                                    const isPos = amt >= 0;
                                    const badges = [];
                                    if (it.is_invoice) badges.push('<span class="badge-soft">Fatura</span>');
                                    if (it.type === 'pix') badges.push('<span class="badge-soft">PIX</span>');
                                    if (it.type === 'money') badges.push('<span class="badge-soft">Dinheiro</span>');
                                    if (it.type === 'card' && it.type_card) badges.push(`<span class="badge-soft">Cartão ${esc(it.type_card)}</span>`);
                                    if (it.category) badges.push(`<span class="badge-soft">${esc(it.category)}</span>`);
                                    if (it.account_name)         badges.push(`<span class="badge-soft">Conta: ${esc(it.account_name)}</span>`);
                                    if (it.counter_account_name) badges.push(`<span class="badge-soft">↔ ${esc(it.counter_account_name)}</span>`);

                                    html += `
    <div class="grid grid-cols-[130px_1fr_130px] max-sm:grid-cols-[1fr_110px] items-center border-b border-neutral-200/70 dark:border-neutral-800/70 bg-neutral-50/40 dark:bg-neutral-900 px-4 py-3">
      <div class="text-[11.5px] text-neutral-500 max-sm:col-span-2 max-sm:mb-1">${dateLabel}</div>
      <div>
        <div class="text-[.92rem] font-semibold">${esc(it.title || '—')}</div>
        ${badges.length ? `<div class="mt-1 flex flex-wrap gap-1">${badges.join('')}</div>` : ''}
      </div>
      <div class="justify-self-end text-[12.5px] font-semibold ${isPos ? 'col-amt pos' : 'col-amt neg'}">${fmtBRL(amt)} ${isPos ? 'C' : 'D'}</div>
    </div>
`;
                                });

                                const saldoDia = (typeof d.balance === 'number')
                                    ? Number(d.balance)
                                    : (monthOpening + (Number(d.in||0) - Number(d.out||0)));

                                html += `
    <div class="grid grid-cols-[130px_1fr_130px] max-sm:grid-cols-[1fr_110px] items-center border-b border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 px-4 py-3">
      <div class="text-[11.5px] text-neutral-500 max-sm:col-span-2 max-sm:mb-1">${dateLabel}</div>
      <div class="text-[10px] tracking-wide uppercase text-neutral-500">Saldo</div>
      <div class="justify-self-end font-semibold ${saldoDia >= 0 ? 'col-amt pos' : 'col-amt neg'}">${fmtBRL(saldoDia)}</div>
    </div>
`;
                            });
                        }

                        html += `
    <div class="grid grid-cols-[130px_1fr_130px] max-sm:grid-cols-[1fr_110px] items-center bg-neutral-50 dark:bg-neutral-900/60 px-4 py-3">
      <div class="max-sm:hidden"></div>
      <div class="text-[10px] tracking-wide uppercase text-neutral-600 dark:text-neutral-400">Fechamento do mês</div>
      <div class="justify-self-end font-semibold ${monthEnding >= 0 ? 'col-amt pos' : 'col-amt neg'}">${fmtBRL(monthEnding)}</div>
    </div>
  </div>
</article>
`;

                        htmlParts.push(html);
                        runningBalance = monthEnding;
                    });

                    $statement.innerHTML = htmlParts.join('');
                }
            })();
        </script>
    @endpush
@endsection
