window.http = (() => {
    let ctrl = null;
    function abortActive(){ if (ctrl) ctrl.abort(); }
    async function get(url, {timeout=10000, headers={}, retry=1}={}) {
        abortActive();
        ctrl = new AbortController();
        const t = setTimeout(()=>ctrl.abort('timeout'), timeout);
        try {
            const res = await fetch(url, {headers, signal: ctrl.signal});
            if (!res.ok) throw new Error('HTTP '+res.status);
            const ct = res.headers.get('content-type')||'';
            return ct.includes('json') ? res.json() : res.text();
        } catch(e){
            if (retry>0) return get(url,{timeout,headers,retry:retry-1});
            throw e;
        } finally { clearTimeout(t); }
    }
    async function post(url, body, {timeout=12000, headers={}}={}){
        const idk = crypto.randomUUID?.() || String(Date.now());
        headers['X-CSRF-TOKEN'] = document.querySelector('meta[name=csrf-token]')?.content || '';
        headers['Idempotency-Key'] = idk;
        const ctrl = new AbortController();
        const t = setTimeout(()=>ctrl.abort('timeout'), timeout);
        try{
            const res = await fetch(url, {method:'POST', body, headers, signal:ctrl.signal});
            if (!res.ok) throw new Error('HTTP '+res.status);
            const ct = res.headers.get('content-type')||'';
            return ct.includes('json') ? res.json() : res.text();
        } finally { clearTimeout(t); }
    }
    return { get, post, abortActive };
})();
