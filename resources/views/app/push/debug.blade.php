@extends('layouts.templates.app')
@section('content')
    <div class="container py-3">
        <h3>Debug Push</h3>
        <pre id="out" style="background:#111;color:#0f0;padding:10px;white-space:pre-wrap"></pre>

        <div class="d-flex gap-2 mb-3">
            <button class="btn btn-secondary" id="btnLocal">Notificação LOCAL</button>
            <button class="btn btn-primary" id="btnPerm">Pedir permissão</button>
            <button class="btn btn-success" id="btnSub">Assinar + Enviar ao backend</button>
            <button class="btn btn-warning" id="btnSendServer">Enviar do SERVIDOR p/ ESTE device</button>
        </div>

        <div class="alert alert-info">
            Abra esta página no **Android** que não recebe. Se a local aparecer e a do servidor não, o problema é assinatura/POST/envio.
        </div>
    </div>

    @push('scripts')
        <script>
            (async () => {
                const out = document.getElementById('out');
                const log = (...a) => { out.textContent += a.map(x => typeof x==='string'?x:JSON.stringify(x,null,2)).join(' ') + "\n"; };

                function u8(b64){ const p='='.repeat((4-b64.length%4)%4); const b=(b64+p).replace(/-/g,'+').replace(/_/g,'/'); const r=atob(b); return Uint8Array.from([...r].map(c=>c.charCodeAt(0))); }

                async function regSW() {
                    if (!('serviceWorker' in navigator)) { log('SW não suportado'); return null; }
                    try { return await navigator.serviceWorker.register("{{ asset('sw.js') }}"); }
                    catch(e){ log('Falha SW:', e.message); return null; }
                }

                async function ensure() {
                    log('Protocol:', location.protocol);
                    log('Support:', {Notification: !!window.Notification, SW: 'serviceWorker' in navigator, Push: 'PushManager' in window});
                    log('Permission:', Notification?.permission);
                    const r = await navigator.serviceWorker.ready.catch(()=>null);
                    log('SW ready?', !!r, r?.scope || '');
                    return r;
                }

                const reg = await ensure() || await regSW();

                document.getElementById('btnLocal').onclick = async () => {
                    try {
                        await (await navigator.serviceWorker.ready).showNotification('Local OK', { body:'Se apareceu, SO+SW ok.' });
                        log('Local enviada');
                    } catch(e){ log('Local erro:', e.message); }
                };

                document.getElementById('btnPerm').onclick = async () => {
                    try {
                        const p = await Notification.requestPermission();
                        log('Permissão:', p);
                    } catch(e){ log('Perm erro:', e.message); }
                };

                async function getOrCreateSub() {
                    const r = await navigator.serviceWorker.ready;
                    let sub = await r.pushManager.getSubscription();
                    if (!sub) {
                        const keyResp = await fetch("{{ url('/vapid-public-key') }}", {cache:'no-store'});
                        const vapid = (await keyResp.text()).trim();
                        sub = await r.pushManager.subscribe({ userVisibleOnly:true, applicationServerKey: u8(vapid) });
                    }
                    return sub;
                }

                document.getElementById('btnSub').onclick = async () => {
                    try {
                        const sub = await getOrCreateSub();
                        log('Subscription:', sub.toJSON());
                        // envia pro backend (usa sua rota /push/subscribe)
                        const resp = await fetch("{{ url('/push/subscribe') }}", {
                            method:'POST',
                            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':"{{ csrf_token() }}",'X-Requested-With':'XMLHttpRequest'},
                            body: JSON.stringify(sub.toJSON()),
                            credentials:'same-origin'
                        });
                        log('POST /push/subscribe status:', resp.status);
                        log('Body:', await resp.text());
                    } catch(e){ log('Sub erro:', e.message); }
                };

                document.getElementById('btnSendServer').onclick = async () => {
                    try {
                        const sub = await getOrCreateSub();
                        const j = sub.toJSON();
                        const resp = await fetch("{{ route('push.debug.send') }}", {
                            method:'POST',
                            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':"{{ csrf_token() }}"},
                            body: JSON.stringify({ endpoint:j.endpoint, p256dh:j.keys.p256dh, auth:j.keys.auth, title:'Ping', body:'Servidor → este device', url:'/' })
                        });
                        const data = await resp.json();
                        log('Server reports:', data);
                    } catch(e){ log('Server erro:', e.message); }
                };

            })();
        </script>
    @endpush
@endsection
