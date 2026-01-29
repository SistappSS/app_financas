<!doctype html>
<html lang="pt-br" class="h-full antialiased">

<!-- Include:head -->
@include('layouts.partials.head')

<body
    class="min-h-screen text-neutral-900 dark:text-neutral-100 bg-white dark:bg-gradient-to-b dark:from-neutral-950 dark:to-neutral-900 selection:bg-brand-200 selection:text-neutral-900 overflow-hidden">

<a href="#conteudo"
   class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 bg-white dark:bg-neutral-800 text-sm px-3 py-2 rounded-lg shadow-soft dark:shadow-softDark">Pular
    para o conteúdo</a>

@include('layouts.partials.navbar')
<div class="flex md:min-h-screen">

    <main id="conteudo" class="flex-1 flex items-start md:items-center justify-center
               max-w-7xl mx-auto w-full
               px-4
               pb-[calc(5.5rem+env(safe-area-inset-bottom))] md:pb-10 md:pt-8"
    >
        @yield('guest-content')
    </main>
</div>


<!-- Include:scripts -->
@include('layouts.partials.scripts')
</body>
</html>

