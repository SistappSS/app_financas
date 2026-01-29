@extends('layouts.templates.app')
@section('new-content')
    <x-card-header
        prevRoute="{{ route('dashboard') }}"
        iconRight="fa-solid fa-circle-question"
        title="Investimentos"
        description="Cadastre investimentos simples e navegue mês a mês no rendimento.">
    </x-card-header>

    <div id="investmentList" class="mt-4"></div>

    {{-- Botão flutuante: novo investimento --}}
    <button id="openInvestmentModal" class="create-btn" title="Novo investimento">
        <i class="fa fa-plus text-white"></i>
    </button>

    {{-- Atalho opcional --}}
    <a href="{{ route('transaction-view.index') }}" class="create-btn create-other" title="Transações">
        <i class="fas fa-retweet text-white"></i>
    </a>

    {{-- Modal de cadastro --}}
    <x-modal modalId="modalInvestment" formId="formInvestment" pathForm="app.investments.investment_form"></x-modal>

    <script>
        // ========= Dados vindos do controller =========
        const INVESTMENTS = @json($investments);

        // ========= Rotas Web =========
        const STORE_URL   = @json(route('investments.store'));
        const DESTROY_TPL = @json(route('investments.destroy', ['investment' => '__ID__']));

        // ========= Modal =========
        const investmentModal    = document.getElementById('modalInvestment');
        const openInvestmentBtn  = document.getElementById('openInvestmentModal');
        const closeInvestmentBtn = document.getElementById('closeModal');
        openInvestmentBtn.addEventListener('click', () => investmentModal.classList.add('show'));
        if (closeInvestmentBtn) closeInvestmentBtn.addEventListener('click', () => investmentModal.classList.remove('show'));

        // ========= Helpers =========
        const brl = v => (Number(v||0)).toLocaleString('pt-BR',{style:'currency',currency:'BRL'});
        const pct = v => `${(Number(v||0)).toFixed(2)}%`;

        function effectiveMonthlyRate(ratePercent, period){
            const r = Number(ratePercent || 0) / 100;
            return (period === 'yearly') ? Math.pow(1 + r, 1/12) - 1 : r;
        }
        function monthsSince(startDate){
            if (!startDate) return 0;
            const s = new Date(startDate);
            const n = new Date();
            let m = (n.getFullYear() - s.getFullYear()) * 12 + (n.getMonth() - s.getMonth());
            if (n.getDate() < s.getDate()) m = Math.max(0, m - 1);
            return Math.max(0, m);
        }
        function addMonths(dateStr, add){
            const d = new Date(dateStr);
            const result = new Date(d.getFullYear(), d.getMonth() + add, d.getDate());
            if (result.getDate() !== d.getDate()) result.setDate(0); // último dia do mês
            return result;
        }
        function monthLabelFrom(startDate, k){
            const d = addMonths(startDate, k);
            return d.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
        }
        function monthsBetween(aStr, bStr){
            const a = new Date(aStr);
            const b = new Date(bStr);
            let m = (b.getFullYear() - a.getFullYear()) * 12 + (b.getMonth() - a.getMonth());
            if (b.getDate() < a.getDate()) m = Math.max(0, m - 1);
            return Math.max(0, m);
        }
        const fvN = (pv, i, n) => Number(pv||0) * Math.pow(1 + i, n);

        // ========= Render geral =========
        function renderList(items){
            const container = document.getElementById('investmentList');
            container.innerHTML = '';
            (items || []).forEach(inv => renderCard(inv));
        }

        function renderCard(inv, prepend=false){
            const container   = document.getElementById('investmentList');

            const name        = (inv.name || '').toString();
            const pv          = Number(inv.purchase_value || 0);
            const ratePct     = Number(inv.interest_rate || 0);
            const ratePeriod  = inv.rate_period || 'monthly';
            const iEff        = effectiveMonthlyRate(ratePct, ratePeriod);

            const startRef    = inv.start_date || inv.created_at || null; // início real
            const createdAt   = inv.created_at ? new Date(inv.created_at).toLocaleDateString('pt-BR') : '—';
            const passed      = monthsSince(startRef); // meses já decorridos

            const currentVal  = fvN(pv, iEff, passed);
            const monthYield  = (passed > 0 ? fvN(pv, iEff, passed - 1) : pv) * iEff; // mês atual
            const rateLabel   = ratePeriod === 'yearly' ? 'a.a.' : 'a.m.';

            // Limites: 0 até 2050-12
            const END_2050 = '2050-12-01';
            const kMin = 0;
            const kMax = Math.max(monthsBetween(startRef || inv.created_at || new Date().toISOString(), END_2050), passed);
            const kCur = Math.min(Math.max(passed, kMin), kMax);

            const html = `
                <div class="balance-box" data-card="${inv.id}">
                    ${name ? `<span>${name}</span>` : ''}
                    <strong class="js-current">${brl(currentVal)}</strong>
                    <div class="text-muted" style="font-size:12px">Data do investimento: ${createdAt}</div>

                    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
                        <small>
                            <b class="text-muted">Taxa efetiva</b>
                            <div class="d-flex align-items-center">
                                <span>${pct(iEff*100)} a.m.</span>
                            </div>
                            <div class="text-muted" style="font-size:12px">
                                (${pct(ratePct)} ${rateLabel})
                            </div>
                        </small>

                        <small>
                            <b class="text-muted">Rendimento (mês atual)</b>
                            <div class="d-flex align-items-center">
                                <span class="text-success js-month-yield">${brl(monthYield)}</span>
                            </div>
                        </small>
                    </div>

                    <!-- Navegador de meses: APENAS Anterior / Próximo -->
                    <div class="p-2" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;">
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-sm btn-light js-prev" ${kCur<=kMin?'disabled':''}>&lsaquo; Anterior</button>
                            <div class="text-center">
                                <div style="font-weight:700" class="js-month-label">${monthLabelFrom(startRef || inv.created_at, kCur)}</div>
                                <small class="text-muted">Mês <span class="js-month-num">${kCur+1}</span></small>
                            </div>
                            <button class="btn btn-sm btn-light js-next" ${kCur>=kMax?'disabled':''}>Próximo &rsaquo;</button>
                        </div>

                        <div class="row mt-2">
                            <div class="col-6">
                                <small><b class="text-muted">Rendimento no mês</b></small>
                                <div><span class="js-yield-month fw-bold">${brl(fvN(pv, iEff, kCur) * iEff)}</span></div>
                            </div>
                            <div class="col-6">
                                <small><b class="text-muted">Saldo ao final do mês</b></small>
                                <div><span class="js-end-month fw-bold">${brl(fvN(pv, iEff, kCur+1))}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-end">
                        <button class="btn btn-sm btn-outline-danger js-del" data-del="${inv.id}" style="font-size:12px">
                            Excluir
                        </button>
                    </div>

                    <!-- Estado do navegador (hidden) -->
                    <input type="hidden" class="js-k" value="${kCur}">
                    <input type="hidden" class="js-kmin" value="${kMin}">
                    <input type="hidden" class="js-kmax" value="${kMax}">
                    <input type="hidden" class="js-start-ref" value="${startRef || ''}">
                    <input type="hidden" class="js-pv" value="${pv}">
                    <input type="hidden" class="js-ieff" value="${iEff}">
                </div>
            `;

            if (prepend) {
                container.insertAdjacentHTML('afterbegin', html);
                wireCard(container.firstElementChild);
            } else {
                container.insertAdjacentHTML('beforeend', html);
                wireCard(container.lastElementChild);
            }
        }

        // ========= Liga handlers (prev/next/delete) e atualiza campos =========
        function wireCard(card){
            // excluir
            const delBtn = card.querySelector('.js-del');
            if (delBtn && !delBtn.dataset.bound) {
                delBtn.dataset.bound = '1';
                delBtn.onclick = async () => {
                    if (!confirm('Excluir este investimento?')) return;
                    const id = delBtn.dataset.del;
                    const url = DESTROY_TPL.replace('__ID__', id);
                    const fd = new FormData(); fd.append('_method','DELETE');
                    try{
                        const resp = await fetch(url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept': 'application/json' },
                            body: fd,
                            credentials: 'same-origin'
                        });
                        if (!(resp.ok || resp.status === 204)) {
                            const t = await resp.text();
                            throw new Error(`Falha ao excluir (${resp.status}). ${t}`);
                        }
                        card.remove();
                    }catch(e){ alert(e.message); }
                };
            }

            // navegador
            const btnPrev  = card.querySelector('.js-prev');
            const btnNext  = card.querySelector('.js-next');

            const kEl     = card.querySelector('.js-k');
            const kminEl  = card.querySelector('.js-kmin');
            const kmaxEl  = card.querySelector('.js-kmax');
            const startEl = card.querySelector('.js-start-ref');
            const pvEl    = card.querySelector('.js-pv');
            const iEl     = card.querySelector('.js-ieff');

            const lbl     = card.querySelector('.js-month-label');
            const lblNum  = card.querySelector('.js-month-num');
            const yMonth  = card.querySelector('.js-yield-month');
            const eMonth  = card.querySelector('.js-end-month');

            const currentTop = card.querySelector('.js-current');
            const monthTop   = card.querySelector('.js-month-yield');

            function updateByK(){
                const k    = Number(kEl.value);
                const kmin = Number(kminEl.value);
                const kmax = Number(kmaxEl.value);
                const start= startEl.value;
                const pv   = Number(pvEl.value);
                const i    = Number(iEl.value);

                // labels de mês
                lbl.textContent = monthLabelFrom(start || new Date().toISOString(), k);
                lblNum.textContent = (k + 1);

                // rendimento no mês k e saldo ao final do mês k
                const baseK  = fvN(pv, i, k);
                const yieldK = baseK * i;
                const endK   = fvN(pv, i, k+1);

                yMonth.textContent = brl(yieldK);
                eMonth.textContent = brl(endK);

                // topo “agora”
                const passed = monthsSince(start || null);
                const currentVal = fvN(pv, i, passed);
                currentTop.textContent = brl(currentVal);
                monthTop.textContent   = brl((passed > 0 ? fvN(pv, i, passed - 1) : pv) * i);

                // navegação
                btnPrev.disabled = (k <= kmin);
                btnNext.disabled = (k >= kmax);
            }

            if (btnPrev && !btnPrev.dataset.bound) {
                btnPrev.dataset.bound = '1';
                btnPrev.onclick = () => { kEl.value = Math.max(Number(kEl.value)-1, Number(kminEl.value)); updateByK(); };
            }
            if (btnNext && !btnNext.dataset.bound) {
                btnNext.dataset.bound = '1';
                btnNext.onclick = () => { kEl.value = Math.min(Number(kEl.value)+1, Number(kmaxEl.value)); updateByK(); };
            }

            updateByK();
        }

        // ========= Criar =========
        document.getElementById('formInvestment').addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const fd   = new FormData(form);

            // normaliza "1.000,00" -> 1000.00 e "1,10" -> 1.10
            const rawPv   = (fd.get('purchase_value') || '').toString().replace(/\./g,'').replace(',', '.');
            const rawRate = (fd.get('interest_rate')  || '').toString().replace(',', '.');
            fd.set('purchase_value', Number(rawPv || 0));
            fd.set('interest_rate',  Number(rawRate || 0));

            try {
                const resp = await fetch(STORE_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Accept':'application/json' },
                    body: fd,
                    credentials: 'same-origin'
                });

                if (resp.status === 422) {
                    const ejson = await resp.json();
                    const first = Object.values(ejson.errors || {})[0]?.[0] || 'Dados inválidos.';
                    throw new Error(first);
                }
                if (!resp.ok) throw new Error('Erro ao salvar investimento.');

                const novo = await resp.json();
                INVESTMENTS.unshift(novo);
                renderCard(novo, true);
                investmentModal.classList.remove('show');
                form.reset();
            } catch (err) {
                alert(err.message);
            }
        });

        // ========= Init =========
        window.addEventListener('DOMContentLoaded', () => {
            renderList(INVESTMENTS);
        });
    </script>
@endsection
