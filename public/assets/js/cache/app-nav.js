(() => {
    if (!window.__SPA_LITE__) return;

    const main = document.getElementById('app-main');
    if (!main) return;

    let currentCtrl = null;
    const prefetchCache = new Map(); // href -> html
    const netBanner = document.getElementById('net-banner');

    // conexão lenta?
    const nc = navigator.connection;
    const isSlow = () => nc && ['slow-2g','2g','3g'].includes(nc.effectiveType || '');
    const showBanner = () => { if (isSlow()) netBanner.style.display = 'block'; };
    const hideBanner = () => { netBanner.style.display = 'none'; };

    // skeleton por tipo
    function showSkeleton() {
        main.classList.add('is-loading');
        // se quiser, personalize por data-skeleton
    }
    function hideSkeleton() {
        main.classList.remove('is-loading');
    }

    function mount(html) {
        if (document.startViewTransition) {
            document.startViewTransition(() => { main.innerHTML = html; });
        } else {
            main.innerHTML = html;
        }
    }

    async function fetchPartial(href, {useCache=true} = {}) {
        if (useCache && prefetchCache.has(href)) {
            return prefetchCache.get(href);
        }
        if (currentCtrl) currentCtrl.abort();
        currentCtrl = new AbortController();

        const t = setTimeout(showBanner, 300);

        try {
            const res = await fetch(href, {
                headers: { 'X-Partial': '1', 'X-Requested-With': 'XMLHttpRequest' },
                signal: currentCtrl.signal
            });
            clearTimeout(t);
            hideBanner();
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const html = await res.text();
            if (useCache) prefetchCache.set(href, html);
            return html;
        } finally {
            // nada
        }
    }

    // navegação
    async function goto(href, {push=true} = {}) {
        showSkeleton();
        try {
            const html = await fetchPartial(href, {useCache:true});
            mount(html);
            if (push) history.pushState({ href }, '', href);
        } catch (e) {
            console.error(e);
            // fallback: navegação normal
            location.href = href;
        } finally {
            hideSkeleton();
        }
    }

    // intercepta cliques
    document.addEventListener('click', (e) => {
        const a = e.target.closest('a[data-nav]');
        if (!a || a.target === '_blank' || e.metaKey || e.ctrlKey) return;
        e.preventDefault();
        goto(a.href);
    });

    // back/forward
    window.addEventListener('popstate', (e) => {
        const href = (e.state && e.state.href) || location.href;
        goto(href, {push:false});
    });

    // prefetch on-hover
    let hoverTO = null;
    document.addEventListener('mouseover', (e) => {
        const a = e.target.closest('a[data-nav]');
        if (!a) return;
        if (prefetchCache.has(a.href)) return;
        clearTimeout(hoverTO);
        hoverTO = setTimeout(() => {
            fetchPartial(a.href).catch(()=>{});
        }, isSlow() ? 800 : 150);
    });

    // primeira navegação guarda estado
    history.replaceState({ href: location.href }, '', location.href);
})();
