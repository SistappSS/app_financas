@extends('layouts.templates.guest')

@section('guest-content')
    <div class="flex min-h-[calc(100vh-7rem)] items-center">
        <div class="w-full max-w-md mx-auto">

            {{-- Alertas de erro --}}
            @if ($errors->any())
                <div
                    class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-500/40 dark:bg-red-500/10">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Card de registro --}}
            <div
                class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white/95 dark:bg-neutral-900/90 shadow-soft dark:shadow-softDark p-5 space-y-4">

                <div>
                    <h1 class="text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                        Criar conta
                    </h1>
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        Cadastre-se para começar a controlar saldos, transações e projeções.
                    </p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    {{-- Nome --}}
                    <label class="block text-sm">
                        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">
                            Nome
                        </span>
                        <input
                            id="card-name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="mt-1 block w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                   bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        />
                    </label>

                    {{-- E-mail --}}
                    <label class="block text-sm">
                        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">
                            Endereço de e-mail
                        </span>
                        <input
                            id="card-email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            class="mt-1 block w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                   bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        />
                    </label>

                    {{-- Senha --}}
                    <label class="block text-sm">
                        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">
                            Senha
                        </span>
                        <input
                            id="card-password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                   bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        />
                    </label>

                    {{-- Confirmar senha --}}
                    <label class="block text-sm">
                        <span class="text-xs font-medium text-neutral-600 dark:text-neutral-300">
                            Confirmar senha
                        </span>
                        <input
                            id="card-password-confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                   bg-white/90 dark:bg-neutral-900/70 px-3 py-2 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                        />
                    </label>

                    {{-- Botão registrar --}}
                    <button
                        type="submit"
                        class="mt-2 inline-flex w-full justify-center rounded-xl bg-brand-600 px-4 py-2.5
                               text-sm font-semibold text-white shadow-sm hover:bg-brand-700
                               focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2
                               focus-visible:outline-brand-600">
                        Registrar
                    </button>

                    {{-- Link voltar ao login --}}
                    <p class="mt-2 text-[11px] text-center text-neutral-500 dark:text-neutral-400">
                        Já tem uma conta?
                        <a href="{{ route('login') }}" class="text-brand-600 hover:text-brand-700 font-semibold">
                            Entrar
                        </a>
                    </p>
                </form>
            </div>

            {{-- Rodapé pequeno --}}
            <p class="mt-4 text-center text-[11px] text-neutral-500 dark:text-neutral-400">
                Dica: adicione o app à tela inicial para uma experiência parecida com a de um banco digital.
            </p>
        </div>
    </div>
@endsection
