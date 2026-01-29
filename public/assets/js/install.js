(() => {
    function onReady(fn){
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn, {once:true});
        else fn();
    }

    onReady(() => {
        const installBtn = document.querySelector('[data-install]'); // começa com d-none no HTML
        const iosHint    = document.getElementById('ios-a2hs');

        // Esconde tudo de cara
        installBtn?.classList.add('d-none');
        iosHint?.classList.add('d-none');

        const ua = navigator.userAgent || '';
        const platform = navigator.platform || '';
        const maxTP = navigator.maxTouchPoints || 0;

        const isStandalone =
            (typeof window.matchMedia === 'function' && matchMedia('(display-mode: standalone)').matches) ||
            (navigator.standalone === true);

        // iOS/iPadOS robusto: cobre iPhone/iPad clássicos + iPadOS 13+ (MacIntel + touch)
        const isIOSLike = (/(iPad|iPhone|iPod)/.test(ua) && !window.MSStream) || (platform === 'MacIntel' && maxTP > 1);

        // Android simples
        const isAndroid = /Android/i.test(ua);

        // DEBUG leve (comente se não quiser)
        // console.log({isStandalone, isIOSLike, isAndroid, ua, platform, maxTP});

        // Se já está instalado em modo standalone: nada a exibir
        if (isStandalone) {
            document.getElementById('ios-a2hs')?.classList.add('d-none');
            document.querySelector('[data-install]')?.classList.add('d-none');
            return;
        }

        // iOS/iPadOS não tem beforeinstallprompt → mostra hint
        if (isIOSLike) {
            iosHint?.classList.remove('d-none');
            installBtn?.classList.add('d-none');
            return;
        }

        // Android/Chromium: só mostra botão quando o navegador sinaliza que pode instalar
        let deferred = null;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferred = e;
            installBtn?.classList.remove('d-none');
            iosHint?.classList.add('d-none');
        });

        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-install]');
            if (!btn || !deferred) return;
            try {
                await deferred.prompt();
                await deferred.userChoice;
            } finally {
                deferred = null;
                btn.classList.add('d-none');
            }
        });

        window.addEventListener('appinstalled', () => {
            installBtn?.classList.add('d-none');
            iosHint?.classList.add('d-none');
        });

        // Fallback extra: se não é iOSLike nem Android e também não veio beforeinstallprompt,
        // não mostra nada (desktop normal). Mas se estiver num navegador iOS “diferentão”
        // que não bateu no isIOSLike, tenta detectar Safari iOS por pistas do UA e
        // mostra o hint mesmo assim.
        setTimeout(() => {
            const ua = navigator.userAgent || '';
            const isStandalone =
                (typeof matchMedia==='function' && matchMedia('(display-mode: standalone)').matches) ||
                (navigator.standalone === true);
            const isAndroid = /Android/i.test(ua);

            const isPossiblyIOS =
                /Version\/\d+.*Mobile\/\w+.*Safari/i.test(ua) || // Safari iOS
                /CriOS\/.*Mobile/i.test(ua);                     // Chrome em iOS

            // só pega o elemento se existir
            const iosHint = document.getElementById('ios-a2hs');
            const installBtn = document.querySelector('[data-install]');

            // se já está instalado ou Android (onde teremos o beforeinstallprompt), sai
            if (isStandalone || isAndroid) return;

            // se o botão não apareceu (sem BIP) e “parece iOS”, mostra o hint
            const bipSeen = window.__BIP_FIRED__ === true;
            if (!bipSeen && isPossiblyIOS && iosHint) {
                iosHint.classList.remove('d-none');
                installBtn?.classList.add('d-none');
            }
        }, 1200);

        window.addEventListener('beforeinstallprompt', () => { window.__BIP_FIRED__ = true; });
    });

    window.addEventListener('DOMContentLoaded', () => {
        const iosBtn = document.getElementById('ios-enable-push');

        const isStandalone =
            (typeof matchMedia === 'function' && matchMedia('(display-mode: standalone)').matches) ||
            (navigator.standalone === true);

        // iOS/iPadOS robusto: iPhone/iPad clássicos + iPadOS 13+ (MacIntel + touch)
        const ua = navigator.userAgent || '';
        const isIOSLike =
            (/(iPad|iPhone|iPod)/.test(ua) && !window.MSStream) ||
            (navigator.platform === 'MacIntel' && (navigator.maxTouchPoints || 0) > 1);

        if (isIOSLike && isStandalone) {
            if ('Notification' in window && Notification.permission !== 'granted') {
                iosBtn?.classList.remove('d-none');
                iosBtn?.addEventListener('click', () => window.initPush && window.initPush(), { once: true });
            } else {
                iosBtn?.classList.add('d-none');
            }
        } else {
            // Não é iOS standalone → esconde o botão
            iosBtn?.classList.add('d-none');
        }
    });
})();
