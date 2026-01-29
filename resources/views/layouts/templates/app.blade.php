<!doctype html>
<html lang="pt-br" class="h-full antialiased">

@include('layouts.partials.head')

<body class="min-h-screen text-neutral-900 dark:text-neutral-100 bg-white dark:bg-gradient-to-b dark:from-neutral-950 dark:to-neutral-900 selection:bg-brand-200 selection:text-neutral-900">

<a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 bg-white dark:bg-neutral-800 text-sm px-3 py-2 rounded-lg shadow-soft dark:shadow-softDark">Pular para o conteúdo</a>

<div id="appLayout" class="md:grid md:grid-cols-[260px_1fr] md:min-h-screen transition-[grid-template-columns] duration-200">
    @include('layouts.partials.sidenav')

    <div class="relative flex flex-col min-h-screen md:min-h-0">
        @include('layouts.partials.navbar')
        <main id="conteudo" class="flex-1 w-full max-w-7xl mx-auto px-4 pb-[calc(5.5rem+env(safe-area-inset-bottom))] md:pt-6 md:pb-8 md:max-w-none md:mx-0 md:px-6 lg:px-8">
            @yield('new-content')
        </main>
    </div>
</div>

{{--Test reconnect--}}
<div id="connection-overlay"
     class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/70 backdrop-blur-sm">
    <div class="rounded-2xl bg-slate-900/90 px-6 py-5 text-center text-white shadow-xl max-w-sm mx-auto">
        <div class="flex items-center justify-center">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
        </div>
        <p class="mt-3 text-sm font-medium" id="connection-overlay-message">
            Tentando reconectar...
        </p>
        <p class="mt-1 text-xs text-slate-300">
            Verifique sua conexão com a internet.
        </p>
    </div>
</div>

<script>
    const connectionOverlay = document.getElementById('connection-overlay');
    const connectionOverlayMessage = document.getElementById('connection-overlay-message');

    function showConnectionOverlay(message) {
        if (message) connectionOverlayMessage.textContent = message;
        connectionOverlay.classList.remove('hidden');
        connectionOverlay.classList.add('flex');
    }

    function hideConnectionOverlay() {
        connectionOverlay.classList.add('hidden');
        connectionOverlay.classList.remove('flex');
    }

    // Eventos nativos de online/offline
    window.addEventListener('offline', () => {
        showConnectionOverlay('Sem conexão com a internet. Tentando reconectar...');
    });

    window.addEventListener('online', () => {
        hideConnectionOverlay();
    });

    // Wrapper global pra fetch/AJAX
    async function apiFetch(url, options = {}) {
        // se quiser, marca como "conexão lenta" depois de X ms
        const slowTimer = setTimeout(() => {
            if (!navigator.onLine) {
                showConnectionOverlay('Sem conexão com a internet. Tentando reconectar...');
            } else {
                showConnectionOverlay('Conexão instável. Tentando reconectar...');
            }
        }, 4000); // 4s de espera antes de mostrar

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    ...(options.headers || {}),
                },
                ...options
            });

            clearTimeout(slowTimer);
            hideConnectionOverlay();

            if (!response.ok) {
                // aqui você pode tratar erros 4xx/5xx
                throw new Error('Erro na requisição');
            }

            return response;
        } catch (error) {
            clearTimeout(slowTimer);
            showConnectionOverlay('Erro de conexão. Tentando reconectar...');
            throw error;
        }
    }
</script>

@include('layouts.partials.bottom_nav')
@include('layouts.partials.mobile_nav')
@include('layouts.partials.scripts')

</body>
</html>

