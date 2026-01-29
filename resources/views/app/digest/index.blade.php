@extends('layouts.templates.app')

@section('new-content')
    <x-card-header
            prevRoute="{{ route('dashboard') }}"
            iconRight="calendar"
            title="Lançamentos"
            description="Veja os seus lançamentos de hoje e dos próximos dias!">
    </x-card-header>

    @php
        $fmtBrl = fn($v) => brlPrice(abs($v ?? 0));
        $signed = fn($v) => ($v ?? 0) < 0 ? '- ' : '+ ';
        $fmt = fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y');

        // TRANSAÇÃO -> card normalizado
        $txToCard = function($t) use ($today, $fmt, $paidByKey){
           $cat    = optional($t->transactionCategory);
           $type   = $cat->type; // 'entrada'|'despesa'|'investimento'
           $amt    = (float)$t->amount;
           $signedAmt = $type === 'entrada' ? abs($amt) : -abs($amt);

           $date = $t->date
               ? \Carbon\Carbon::parse($t->date)->toDateString()
               : $today->toDateString();

           $key  = (string)$t->id . '#' . $date;
           $info = $paidByKey[$key] ?? ['paid' => false, 'paid_at' => null];

           return [
               'bg'          => $cat->color ?: '#6b7280',
               'icon'        => $cat->icon  ?: 'fa-solid fa-receipt',
               'title'       => $t->title ?? $cat->name ?? 'Lançamento',
               'date'        => $date,
               'amt'         => $signedAmt,
               'is_invoice'  => false,
               'paid'        => (bool)($info['paid'] ?? false),
               'paid_at'     => $info['paid_at'] ?? null,
               'tx_id'       => (string)$t->id,
           ];
       };

        // FATURA -> card normalizado
        $invToCard = fn($inv) => [
            'bg'            => '#be123c',
            'icon'          => 'fa-solid fa-credit-card',
            'title'         => $inv['title'],
            'date'          => $inv['due_date'],
            'amt'           => -abs((float)$inv['total']),
            'is_invoice'    => true,
            'paid'          => false,
            'card_id'       => $inv['card_id'],
            'current_month' => $inv['current_month'],
        ];

        // HOJE
        $cardsToday = collect();
        if (isset($invoicesToday)) {
            $cardsToday = $cardsToday->merge($invoicesToday->map($invToCard));
        }

        $cardsToday = $cardsToday
            ->merge($todayIn->map($txToCard))
            ->merge($todayOut->map($txToCard))
            ->merge($todayInv->map($txToCard))
            ->sortBy('date')
            ->values();

        // AMANHÃ
        $cardsTomorrow = collect();
        if (isset($invoicesTomorrow)) {
            $cardsTomorrow = $cardsTomorrow->merge($invoicesTomorrow->map($invToCard));
        }

        $cardsTomorrow = $cardsTomorrow
            ->merge($tomIn->map($txToCard))
            ->merge($tomOut->map($txToCard))
            ->merge($tomInv->map($txToCard))
            ->sortBy('date')
            ->values();

        $overdueTop  = isset($overdueCards) ? $overdueCards->take(2) : collect();
        $overdueRest = isset($overdueCards) ? $overdueCards->slice(2)->values() : collect();
    @endphp

    <section class="mt-4 space-y-4">
        <div class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            <p class="text-xs font-semibold tracking-[0.12em] uppercase text-neutral-500 dark:text-neutral-400">
                Total de hoje
            </p>
            <p class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-neutral-50">
                {{ $signed($kpiToday['net'] ?? 0) }}{{ $fmtBrl($kpiToday['net'] ?? 0) }}
            </p>

            <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    <i class="fa-solid fa-arrow-trend-up text-[11px]"></i>
                    <span>{{ $fmtBrl($kpiToday['in'] ?? 0) }}</span>
                </span>

                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                    <i class="fa-solid fa-arrow-trend-down text-[11px]"></i>
                    <span>{{ $fmtBrl($kpiToday['out'] ?? 0) }}</span>
                </span>
            </div>

            <div class="h-px bg-neutral-100 dark:bg-neutral-800 my-5"></div>

            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-100">
                    Hoje ({{ $today->format('d/m/Y') }})
                </h2>
            </div>

            <ul class="mt-3 divide-y divide-neutral-200/70 dark:divide-neutral-800/70">
                @forelse($cardsToday as $c)
                    <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 transaction-card">
                        <span class="size-10 grid place-items-center rounded-xl text-white"
                              style="background: {{ $c['bg'] }}">
                            <i class="{{ $c['icon'] }}"></i>
                        </span>

                        <div>
                            <p class="text-sm font-medium">
                                {{ $c['title'] }}
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $fmt($c['date']) }}
                            </p>

                            @if(!empty($c['paid']))
                                <p class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium
                                           bg-emerald-50 text-emerald-700
                                           dark:bg-emerald-950/40 dark:text-emerald-300">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                    @php
                                        $paidAt = $c['paid_at'] ?? null;
                                    @endphp
                                    @if($paidAt)
                                        Realizado em {{ \Carbon\Carbon::parse($paidAt)->format('d/m/Y') }}
                                    @else
                                        Realizado
                                    @endif
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 text-right">
                            <p class="text-sm font-semibold price-default">
                                {{ ($c['amt'] ?? 0) < 0 ? '-' : '+' }} {{ brlPrice(abs($c['amt'] ?? 0)) }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3">
                        <span class="size-10 grid place-items-center rounded-xl bg-neutral-100 dark:bg-neutral-800">
                            <i class="fa-solid fa-calendar-day"></i>
                        </span>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">
                            Sem lançamentos hoje.
                        </div>
                        <div></div>
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
            <p class="text-xs font-semibold tracking-[0.12em] uppercase text-neutral-500 dark:text-neutral-400">
                Próximos 7 dias
            </p>
            <p class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-neutral-50">
                {{ $signed($kpiNext7['net'] ?? 0) }}{{ $fmtBrl($kpiNext7['net'] ?? 0) }}
            </p>

            <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                <span
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 bg-emerald-50 text-emerald-700
                           dark:bg-emerald-950/40 dark:text-emerald-300">
                    <i class="fa-solid fa-arrow-trend-up text-[11px]"></i>
                    <span>{{ $fmtBrl($kpiNext7['in'] ?? 0) }}</span>
                </span>

                <span
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 bg-rose-50 text-rose-700
                           dark:bg-rose-950/40 dark:text-rose-300">
                    <i class="fa-solid fa-arrow-trend-down text-[11px]"></i>
                    <span>{{ $fmtBrl($kpiNext7['out'] ?? 0) }}</span>
                </span>
            </div>

            @if(isset($kpiNext7Paid) && (($kpiNext7Paid['in'] ?? 0) != 0 || ($kpiNext7Paid['out'] ?? 0) != 0))
                <p class="mt-2 text-[11px] text-neutral-500 dark:text-neutral-400">
                    Já realizado nesse período:
                    <span class="font-semibold">
                        {{ $signed($kpiNext7Paid['net'] ?? 0) }}{{ $fmtBrl($kpiNext7Paid['net'] ?? 0) }}
                    </span>
                </p>
            @endif

            <div class="h-px bg-neutral-100 dark:bg-neutral-800 my-5"></div>

            <div class="flex items-center justify-between gap-2">
                <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-100">
                    Amanhã ({{ $tomorrow->format('d/m/Y') }})
                </h2>
            </div>

            <ul class="mt-3 divide-y divide-neutral-200/70 dark:divide-neutral-800/70">
                @forelse($cardsTomorrow as $c)
                    <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 transaction-card">
                        <span class="size-10 grid place-items-center rounded-xl text-white"
                              style="background: {{ $c['bg'] }}">
                            <i class="{{ $c['icon'] }}"></i>
                        </span>

                        <div>
                            <p class="text-sm font-medium">
                                {{ $c['title'] }}
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $fmt($c['date']) }}
                            </p>

                            @if(!empty($c['paid']))
                                <p class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium
                                           bg-emerald-50 text-emerald-700
                                           dark:bg-emerald-950/40 dark:text-emerald-300">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                    @php
                                        $paidAt = $c['paid_at'] ?? null;
                                    @endphp
                                    @if($paidAt)
                                        Realizado em {{ \Carbon\Carbon::parse($paidAt)->format('d/m/Y') }}
                                    @else
                                        Realizado
                                    @endif
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 text-right">
                            <p class="text-sm font-semibold price-default">
                                {{ ($c['amt'] ?? 0) < 0 ? '-' : '+' }} {{ brlPrice(abs($c['amt'] ?? 0)) }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3">
                        <span class="size-10 grid place-items-center rounded-xl bg-neutral-100 dark:bg-neutral-800">
                            <i class="fa-solid fa-calendar-day"></i>
                        </span>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">
                            Sem lançamentos amanhã.
                        </div>
                        <div></div>
                    </li>
                @endforelse
            </ul>

            <div class="flex items-center justify-between gap-2 my-5">
                <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-100">
                    Próximos lançamentos
                </h2>
            </div>

            <ul class="mt-3 divide-y divide-neutral-200/70 dark:divide-neutral-800/70">
                @forelse($nextFive as $item)
                    @php
                        $amt    = $item['extendedProps']['amount'] ?? 0;
                        $paid   = (bool)($item['extendedProps']['paid'] ?? false);
                        $paidAt = $item['extendedProps']['paid_at'] ?? null;
                    @endphp
                    <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 transaction-card">
                        <span class="size-10 grid place-items-center rounded-xl text-white"
                              style="background: {{ $item['bg'] ?? '#6b7280' }}">
                            <i class="{{ $item['icon'] ?? 'fa-solid fa-calendar-day' }}"></i>
                        </span>

                        <div>
                            <p class="text-sm font-medium">
                                {{ $item['title'] ?? ($item['extendedProps']['category_name'] ?? 'Sem descrição') }}
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ \Carbon\Carbon::parse($item['start'])->format('d/m/Y') }}
                            </p>

                            @if($paid)
                                <p class="mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium
                                           bg-emerald-50 text-emerald-700
                                           dark:bg-emerald-950/40 dark:text-emerald-300">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                    @if($paidAt)
                                        Realizado em {{ \Carbon\Carbon::parse($paidAt)->format('d/m/Y') }}
                                    @else
                                        Realizado
                                    @endif
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-3 text-right">
                            <p class="text-sm font-semibold price-default">
                                {{ $amt < 0 ? '-' : '+' }} {{ brlPrice(abs($amt)) }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3">
                        <span class="size-10 grid place-items-center rounded-xl bg-neutral-100 dark:bg-neutral-800">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </span>
                        <div class="text-sm text-neutral-500 dark:text-neutral-400">
                            Não há próximos lançamentos.
                        </div>
                        <div></div>
                    </li>
                @endforelse
            </ul>
        </div>

        @if(isset($overdueCards) && $overdueCards->count())
            {{-- TOTAL ATRASADO --}}
            <div class="rounded-2xl border border-neutral-200/70 dark:border-neutral-800/70 bg-white dark:bg-neutral-900 p-4">
                <p class="text-xs font-semibold tracking-[0.12em] uppercase text-neutral-500 dark:text-neutral-400">
                    Total atrasado
                </p>
                <p class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-neutral-50">
                    {{ $signed($kpiOverdue['net'] ?? 0) }}{{ $fmtBrl($kpiOverdue['net'] ?? 0) }}
                </p>

                <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                    {{-- Entradas --}}
                    <span
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 bg-emerald-50 text-emerald-700
                           dark:bg-emerald-950/40 dark:text-emerald-300">
                    <i class="fa-solid fa-arrow-trend-up text-[11px]"></i>
                    <span>{{ $fmtBrl($kpiOverdue['in'] ?? 0) }}</span>
                </span>

                    {{-- Saídas --}}
                    <span
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 bg-rose-50 text-rose-700
                           dark:bg-rose-950/40 dark:text-rose-300">
                    <i class="fa-solid fa-arrow-trend-down text-[11px]"></i>
                    <span>{{ $fmtBrl($kpiOverdue['out'] ?? 0) }}</span>
                </span>
                </div>

                <div class="h-px bg-neutral-100 dark:bg-neutral-800 my-5"></div>

                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold text-neutral-700 dark:text-neutral-100">
                        Lançamentos atrasados
                    </h2>

                    @if($overdueRest->count())
                        <button
                                type="button"
                                id="btn-overdue-toggle"
                                class="text-xs font-medium text-red-700 hover:text-red-800 dark:text-red-300 dark:hover:text-red-200"
                                data-label-more="Ver mais ({{ $overdueRest->count() }})"
                                data-label-less="Ver menos"
                        >
                            Ver mais ({{ $overdueRest->count() }})
                        </button>
                    @endif
                </div>

                {{-- 2 primeiros --}}
                <ul class="mt-3 divide-y divide-red-100/70 dark:divide-red-900/50">
                    @foreach($overdueTop as $c)
                        <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 transaction-card">
                            <span class="size-10 grid place-items-center rounded-xl text-white"
                                  style="background: {{ $c['bg'] }}">
                                <i class="{{ $c['icon'] }}"></i>
                            </span>

                            <div>
                                <p class="text-sm font-medium">
                                    {{ $c['title'] }}
                                </p>
                                <p class="text-xs text-red-700/80 dark:text-red-200/80">
                                    @if(!empty($c['parcel_of']) && !empty($c['parcel_total']) && $c['parcel_total'] > 1)
                                        <span class="font-semibold">
                                            Parcela {{ $c['parcel_of'] }}/{{ $c['parcel_total'] }}
                                        </span>
                                        <span class="mx-1">•</span>
                                    @endif
                                    Venceu em {{ $fmt($c['date']) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-3 text-right">
                                <p class="text-sm font-semibold price-default">
                                    {{ ($c['amt'] ?? 0) < 0 ? '-' : '+' }} {{ brlPrice(abs($c['amt'] ?? 0)) }}
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                {{-- resto (accordion) --}}
                @if($overdueRest->count())
                    <ul id="overdue-more" class="mt-1 divide-y divide-red-100/70 dark:divide-red-900/50 hidden">
                        @foreach($overdueRest as $c)
                            <li class="grid grid-cols-[auto_1fr_auto] items-center gap-3 py-3 transaction-card">
                                <span class="size-10 grid place-items-center rounded-xl text-white"
                                      style="background: {{ $c['bg'] }}">
                                    <i class="{{ $c['icon'] }}"></i>
                                </span>

                                <div>
                                    <p class="text-sm font-medium">
                                        {{ $c['title'] }}
                                    </p>
                                    <p class="text-xs text-red-700/80 dark:text-red-200/80">
                                        @if(!empty($c['parcel_of']) && !empty($c['parcel_total']) && $c['parcel_total'] > 1)
                                            <span class="font-semibold">
                                                Parcela {{ $c['parcel_of'] }}/{{ $c['parcel_total'] }}
                                            </span>
                                            <span class="mx-1">•</span>
                                        @endif
                                        Venceu em {{ $fmt($c['date']) }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-3 text-right">
                                    <p class="text-sm font-semibold price-default">
                                        {{ ($c['amt'] ?? 0) < 0 ? '-' : '+' }} {{ brlPrice(abs($c['amt'] ?? 0)) }}
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btn = document.getElementById('btn-overdue-toggle');
                const more = document.getElementById('overdue-more');

                if (!btn || !more) return;

                const labelMore = btn.dataset.labelMore || btn.textContent;
                const labelLess = btn.dataset.labelLess || 'Ver menos';

                btn.addEventListener('click', () => {
                    const isOpen = !more.classList.contains('hidden');

                    if (isOpen) {
                        more.classList.add('hidden');
                        btn.textContent = labelMore;
                    } else {
                        more.classList.remove('hidden');
                        btn.textContent = labelLess;
                        more.scrollIntoView({behavior: 'smooth', block: 'start'});
                    }
                });
            });
        </script>
    @endpush
@endsection
