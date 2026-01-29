<!-- MODAL DE CADASTRO/EDIÇÃO -->
<div id="savModal" class="fixed inset-0 z-[60] hidden" role="dialog" aria-modal="true">
    <div id="savOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <div class="absolute inset-x-0 bottom-0 md:inset-auto md:top-1/2 md:left-1/2
                md:-translate-x-1/2 md:-translate-y-1/2 md:w-[560px]">

        <div class="rounded-t-3xl md:rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70
                    bg-white dark:bg-neutral-900 shadow-soft p-4 md:p-6 max-h-[92vh] overflow-y-auto">

            <div class="flex items-start justify-between mb-2">
                <div>
                    <h3 id="savModalTitle" class="text-lg font-semibold">Novo cofrinho</h3>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">Informe os dados do cofrinho.</p>
                </div>

                <button id="savClose"
                        class="size-10 grid place-items-center rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800"
                        aria-label="Fechar">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18"/><path d="M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="savFormErr"
                 class="hidden mb-2 rounded-lg bg-red-50 text-red-700 text-sm px-3 py-2"></div>

            <form id="savForm" class="grid gap-3" novalidate>

                <input type="hidden" id="sav_id" name="id"/>

                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Nome</span>
                    <input id="name" name="name" type="text" placeholder="Ex: Viagem"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                  bg-white/90 dark:bg-neutral-900/70 px-3 py-2" required>
                </label>

                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Conta debitada</span>
                    <select id="account_id" name="account_id"
                            class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                   bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                        <option value="">Nenhuma</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">
                                {{ strtoupper($acc->bank_name) }} — Saldo: R$ {{ brlPrice($acc->current_balance) }}
                            </option>
                        @endforeach
                    </select>
                </label>

{{--                <label class="block">--}}
{{--                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Aporte inicial (R$)</span>--}}
{{--                    <input id="current_amount" name="current_amount" inputmode="decimal"--}}
{{--                           placeholder="0,00"--}}
{{--                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70--}}
{{--                                  bg-white/90 dark:bg-neutral-900/70 px-3 py-2">--}}
{{--                </label>--}}

                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">% do CDI</span>
                    <input id="cdi_percent" name="cdi_percent" inputmode="decimal" placeholder="Ex: 105"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                  bg-white/90 dark:bg-neutral-900/70 px-3 py-2"
                           value="">
                </label>

                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Início</span>
                    <input id="start_date" name="start_date" type="date"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                  bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                </label>

                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Cor do cartão</span>
                    <input id="color_card" name="color_card" type="color" value="#00BFA6"
                           class="mt-1 w-28 h-10 rounded-lg border border-neutral-200/70 dark:border-neutral-800/70
                                  cursor-pointer">
                </label>

                <label class="block">
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Observações</span>
                    <input id="notes" name="notes" type="text" placeholder="Opcional"
                           class="mt-1 w-full rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                  bg-white/90 dark:bg-neutral-900/70 px-3 py-2">
                </label>

                <div class="mt-2 flex items-center justify-end gap-2">
                    <button type="button" id="savCancel"
                            class="px-3 py-2 rounded-xl border border-neutral-200/70 dark:border-neutral-800/70
                                   hover:bg-neutral-50 dark:hover:bg-neutral-800">
                        Cancelar
                    </button>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700
                                               text-white shadow-soft">
                        Salvar
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
