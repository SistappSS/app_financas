<!-- Menu de abas - mobile -->
<div
    id="mobile-app-menu"
    class="fixed inset-0 z-[999] hidden md:hidden"
    role="dialog"
    aria-modal="true"
>
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-menu-close></div>

    <!-- Painel -->
    <div
        data-menu-panel
        class="absolute inset-x-0 bottom-0 origin-bottom translate-y-full opacity-0 transition-all duration-200 ease-out"
    >
        <div
            class="mx-auto max-w-md rounded-t-3xl bg-white dark:bg-neutral-950 border-t border-neutral-200/80 dark:border-neutral-800/80 shadow-xl shadow-black/20">
            <div class="flex items-center justify-between px-5 pt-4 pb-2">
                <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                    Acessar áreas do app
                </p>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-full p-1.5 text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-950"
                    data-menu-close
                >
                    <span class="sr-only">Fechar menu</span>
                    <i class="fa-solid fa-xmark text-[15px]"></i>
                </button>
            </div>

            <div class="px-5 pb-10 space-y-5">
                {{-- ÁREAS PRINCIPAIS --}}
                <section>
                    <p class="text-[11px] font-medium tracking-wide text-neutral-400 uppercase">
                        Áreas principais
                    </p>

                    <div class="mt-3 grid grid-cols-4 gap-4 text-center text-xs font-medium text-neutral-700 dark:text-neutral-100">
                        {{-- Home --}}
                        <a href="{{ route('dashboard') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-house text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200">Home</span>
                        </a>

                        {{-- Transações --}}
                        <a href="{{ route('transaction-view.index') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-receipt text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200">Transações</span>
                        </a>

                        {{-- Projeções --}}
                        <a href="{{ route('projection-view.index') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-chart-line text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200">Projeções</span>
                        </a>

                        {{-- Meu perfil --}}
                        <a href="{{ route('user-view.index') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-user text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200">Perfil</span>
                        </a>
                    </div>
                </section>

                {{-- DIVISÓRIA --}}
                <div class="h-px bg-neutral-100 dark:bg-neutral-800"></div>

                {{-- FINANÇAS --}}
                <section>
                    <p class="text-[11px] font-medium tracking-wide text-neutral-400 uppercase">
                        Finanças
                    </p>

                    <div class="mt-3 grid grid-cols-4 gap-4 text-center text-xs font-medium text-neutral-700 dark:text-neutral-100">
                        {{-- Contas --}}
                        <a href="{{ route('account-view.index') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-landmark text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200">Contas</span>
                        </a>

                        {{-- Cartões --}}
                        <a href="{{ route('card-view.index') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-credit-card text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200">Cartões</span>
                        </a>

                        {{-- Categorias --}}
                        <a href="{{ route('transactionCategory-view.index') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-tags text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200">Categorias</span>
                        </a>

                        {{-- Cofrinhos / Savings --}}
                        <a href="{{ route('saving-view.index') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-piggy-bank text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200">Cofrinhos</span>
                        </a>
                    </div>
                </section>

                {{-- DIVISÓRIA --}}
                <div class="h-px bg-neutral-100 dark:bg-neutral-800"></div>

                {{-- ATALHOS --}}
                <section>
                    <p class="text-[11px] font-medium tracking-wide text-neutral-400 uppercase">
                        Atalhos
                    </p>

                    <div class="mt-3 grid grid-cols-4 gap-4 text-center text-xs font-medium text-neutral-700 dark:text-neutral-100">
                        {{-- Lançamentos do dia --}}
                        <a href="{{ route('digest.index') }}" class="group flex flex-col items-center gap-1">
                            <span class="grid place-items-center size-11 rounded-2xl bg-brand-50 text-brand-700 dark:bg-neutral-900 dark:text-brand-200 shadow-soft">
                                <i class="fa-solid fa-calendar-day text-[18px]"></i>
                            </span>
                            <span class="group-hover:text-brand-700 dark:group-hover:text-brand-200 text-[11px] leading-tight">
                                Lanç. do dia
                            </span>
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
