<script>
    (function () {
        const ua = navigator.userAgent || '';
        const isIOSLike =
            (/(iPad|iPhone|iPod)/.test(ua) && !window.MSStream) ||
            (navigator.platform === 'MacIntel' && (navigator.maxTouchPoints || 0) > 1);

        window.AUTH = @json(auth()->check());
        window.PUSH_CFG = {
            vapidKeyUrl: "{{ url('/vapid-public-key') }}",
            subscribeUrl: "{{ url('/push/subscribe') }}",
            swUrl: "{{ asset('sw.js') }}?v={{ filemtime(public_path('sw.js')) }}",
            loginPath: "{{ route('login') }}",
            isIOS: isIOSLike
        };
    })();
</script>

@php
    $pushRegisterPath = public_path('assets/js/push-register.js');
@endphp

<script
    src="{{ asset('assets/js/push-register.js') }}?v={{ file_exists($pushRegisterPath) ? filemtime($pushRegisterPath) : time() }}"
    defer></script>

<script>
    const btnTheme = document.getElementById('btnTheme');
    const iconSun = document.getElementById('iconSun');
    const iconMoon = document.getElementById('iconMoon');

    function syncThemeUI() {
        const isDark = document.documentElement.classList.contains('dark');
        if (iconSun) iconSun.classList.toggle('hidden', !isDark);
        if (iconMoon) iconMoon.classList.toggle('hidden', isDark);
        if (btnTheme) btnTheme.setAttribute('aria-pressed', String(isDark));
    }

    if (btnTheme) {
        btnTheme.addEventListener('click', () => {
            const nowDark = document.documentElement.classList.toggle('dark');
            try {
                localStorage.setItem('theme', nowDark ? 'dark' : 'light');
            } catch (e) {
            }
            syncThemeUI();
        });
    }

    document.addEventListener('DOMContentLoaded', syncThemeUI);

    const tabs = document.querySelectorAll('[data-tab]');
    const sections = Array.from(tabs)
        .map(a => a.getAttribute('href'))
        .filter(href => href && href.startsWith('#'))
        .map(sel => document.querySelector(sel))
        .filter(Boolean);

    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            const i = sections.indexOf(e.target);
            if (i >= 0 && e.isIntersecting) {
                tabs.forEach(t => t.removeAttribute('aria-current'));
                tabs[i].setAttribute('aria-current', 'page');
            }
        });
    }, {rootMargin: '-40% 0px -55% 0px', threshold: 0});
    sections.forEach(s => s && io.observe(s));

    // Animação simples do saldo (contagem)
    const saldoEl = document.getElementById('saldoValor');
    if (saldoEl) {
        const target = 12450.27; // valor de exemplo
        let start = null;
        const fmt = v => v.toLocaleString('pt-BR', {style: 'currency', currency: 'BRL'});

        function step(ts) {
            if (!start) start = ts;
            const p = Math.min(1, (ts - start) / 900);
            const val = target * (0.7 + 0.3 * p); // começa em 70% e vai até 100%
            saldoEl.textContent = fmt(val);
            if (p < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    }

    // Ação de menu (apenas demo)
    document.getElementById('btnMenu')?.addEventListener('click', () => {
        alert('Menu lateral disponível no desktop.');
    });

    // Speed Dial
    const sd = document.getElementById('speedDial');
    const sdMain = document.getElementById('speedDialMain');
    const sdActions = document.getElementById('speedDialActions');

    function toggleDial(force) {
        const open = force ?? sdMain.getAttribute('aria-expanded') === 'false';
        sdMain.setAttribute('aria-expanded', String(open));
        sdActions.style.pointerEvents = open ? 'auto' : 'none';
        sdActions.style.opacity = open ? '1' : '0';
        sdActions.style.transform = open ? 'translateY(0)' : 'translateY(12px)';
    }

    sdMain?.addEventListener('click', () => toggleDial());
    document.addEventListener('click', (e) => {
        if (!sd) return;
        if (!sd.contains(e.target)) toggleDial(false);
    });

    // Modal de transação (já existente)
    const txModal = document.getElementById('txModal');
    const txOverlay = document.getElementById('txModalOverlay');
    const txClose = document.getElementById('txClose');
    const txCancel = document.getElementById('txCancel');
    const txForm = document.getElementById('txForm');

    function openTxModal() {
        txModal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        const first = txForm?.querySelector('input, select, textarea, button');
        first?.focus();
    }

    function closeTxModal() {
        txModal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        sdMain?.focus();
    }

    document.querySelectorAll('[data-open-modal="tx"]').forEach(btn =>
        btn.addEventListener('click', openTxModal)
    );
    txOverlay?.addEventListener('click', closeTxModal);
    txClose?.addEventListener('click', closeTxModal);
    txCancel?.addEventListener('click', closeTxModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !txModal?.classList.contains('hidden')) closeTxModal();
    });
    txForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Transação salva! (exemplo)');
        closeTxModal();
    });

    // Página de categorias: mostrar FAB próprio e esconder speed dial quando visível
    const catPage = document.getElementById('categorias-page');
    const catFab = document.getElementById('catFab');
    if (catPage && catFab) {
        const ioCat = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                const vis = e.isIntersecting;
                catFab.classList.toggle('hidden', !vis);
                sd?.classList.toggle('hidden', vis);
            });
        }, {rootMargin: '-40% 0px -55% 0px', threshold: 0});
        ioCat.observe(catPage);
    }

    // Modal de categoria
    const catModal = document.getElementById('catModal');
    const catOverlay = document.getElementById('catOverlay');
    const catClose = document.getElementById('catClose');
    const catForm = document.getElementById('catForm');

    function openCatModal() {
        catModal?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        const first = catForm?.querySelector('input, select, textarea, button');
        first?.focus();
    }

    function closeCatModal() {
        catModal?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        catFab?.focus();
    }

    document.querySelectorAll('[data-open-modal="cat"]').forEach(btn =>
        btn.addEventListener('click', openCatModal)
    );
    catOverlay?.addEventListener('click', closeCatModal);
    catClose?.addEventListener('click', closeCatModal);
    catForm?.querySelector('[data-cancel]')?.addEventListener('click', closeCatModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !catModal?.classList.contains('hidden')) closeCatModal();
    });
    catForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Categoria salva! (exemplo)');
        closeCatModal();
    });

    // Cor rápida: aplica no input color e mostra aneis de seleção
    (function () {
        const wrap = catForm;
        if (!wrap) return;
        const colorInput = wrap.querySelector('input[type="color"]');
        wrap.querySelectorAll('[data-color]').forEach(btn => {
            btn.addEventListener('click', () => {
                wrap.querySelectorAll('[data-color]').forEach(b => b.classList.remove('ring-white', 'ring-2'));
                btn.classList.add('ring-2', 'ring-white');
                if (colorInput) colorInput.value = btn.getAttribute('data-color');
            });
        });
    })();
    // Página de contas: mostrar FAB próprio e esconder speed dial quando visível
    const accPage = document.getElementById('contas-page');
    const accFab = document.getElementById('accFab');
    if (accPage && accFab) {
        const ioAcc = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                const vis = e.isIntersecting;
                accFab.classList.toggle('hidden', !vis);
                sd?.classList.toggle('hidden', vis);
            });
        }, {rootMargin: '-40% 0px -55% 0px', threshold: 0});
        ioAcc.observe(accPage);
    }

    // // Modal de conta bancária
    // const accModal = document.getElementById('accModal');
    // const accOverlay = document.getElementById('accOverlay');
    // const accClose = document.getElementById('accClose');
    // const accCancel = document.getElementById('accCancel');
    // const accForm = document.getElementById('accForm');
    //
    // function openAccModal() {
    //     accModal?.classList.remove('hidden');
    //     document.body.classList.add('overflow-hidden');
    //     accForm?.querySelector('input,select,button')?.focus();
    // }
    //
    // function closeAccModal() {
    //     accModal?.classList.add('hidden');
    //     document.body.classList.remove('overflow-hidden');
    //     accFab?.focus();
    // }
    //
    // document.querySelectorAll('[data-open-modal=\"acc\"]').forEach(btn => btn.addEventListener('click', openAccModal));
    // accOverlay?.addEventListener('click', closeAccModal);
    // accClose?.addEventListener('click', closeAccModal);
    // accCancel?.addEventListener('click', closeAccModal);
    // document.addEventListener('keydown', (e) => {
    //     if (e.key === 'Escape' && !accModal?.classList.contains('hidden')) closeAccModal();
    // });
    // accForm?.addEventListener('submit', (e) => {
    //     e.preventDefault();
    //     alert('Conta salva! (exemplo)');
    //     closeAccModal();
    // });
</script>

<script>
    window.__SPA_LITE__ = true;
</script>

<script src="{{asset('assets/js/cache/app-nav.js')}}"></script>
<script src="{{asset('assets/js/cache/http.js')}}"></script>
<script src="{{asset('assets/js/cache/storage.js')}}"></script>

@php
    $installPath = public_path('assets/js/install.js');
@endphp

<script src="{{ asset('assets/js/install.js') }}?v={{ file_exists($installPath) ? filemtime($installPath) : time() }}"
        defer></script>

<div id="net-banner"
     style="display:none;position:fixed;left:50%;transform:translateX(-50%);bottom:85px;z-index:1200;background:#222;color:#fff;padding:6px 10px;border-radius:8px;font-size:12px;">
    Conexão lenta — exibindo dados em cache…
</div>

{{--Desktop Toggle--}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const layout = document.getElementById('appLayout');
        const sidebarToggle = document.getElementById('sidebarToggleDesktop');
        const sidebarIcon = document.getElementById('sidebarToggleIcon');

        if (!layout || !sidebarToggle) return;

        const updateIcon = () => {
            const collapsed = layout.classList.contains('sidebar-collapsed');

            // Ícone duplo << >> simples: se quiser, troca o desenho aqui.
            if (collapsed) {
                // Mostrar chevrons apontando para a direita (abrir)
                sidebarIcon.innerHTML = `
                <path d="M9 6l4 6-4 6" />
                <path d="M5 6l4 6-4 6" />
            `;
            } else {
                // Mostrar chevrons apontando para a esquerda (fechar)
                sidebarIcon.innerHTML = `
                <path d="M10 6 6 12l4 6" />
                <path d="M14 6l-4 6 4-6-4-6" />
            `;
            }
        };

        sidebarToggle.addEventListener('click', () => {
            layout.classList.toggle('sidebar-collapsed');
            updateIcon();
        });

        // estado inicial
        updateIcon();
    });
</script>
{{--Mobile Menu Script--}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('appMenuToggle');
        const menu = document.getElementById('mobile-app-menu');
        if (!toggle || !menu) return;

        const panel = menu.querySelector('[data-menu-panel]');
        const closeEls = menu.querySelectorAll('[data-menu-close]');

        const openMenu = () => {
            menu.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');

            // animação
            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-full', 'opacity-0');
            });
        };

        const closeMenu = () => {
            toggle.setAttribute('aria-expanded', 'false');
            panel.classList.add('translate-y-full', 'opacity-0');

            const onEnd = (e) => {
                if (e.target !== panel) return;
                menu.classList.add('hidden');
                panel.removeEventListener('transitionend', onEnd);
            };
            panel.addEventListener('transitionend', onEnd);
        };

        toggle.addEventListener('click', () => {
            const isOpen = !menu.classList.contains('hidden');
            isOpen ? closeMenu() : openMenu();
        });

        closeEls.forEach(el => {
            el.addEventListener('click', closeMenu);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !menu.classList.contains('hidden')) {
                closeMenu();
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
@stack('scripts')
