<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyDigestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tz = 'America/Sao_Paulo';

        $today = now($tz)->startOfDay();
        $tomorrow = $today->copy()->addDay();
        $afterTomorrow = $tomorrow->copy()->addDay();

        // ========= TRANSAÇÕES (sem cartão/credit e sem “total fatura”)
        $txBase = $user->transactions()->with('transactionCategory')->where(function ($q) {
            $q->where('transactions.type', '!=', 'card')
                ->orWhereNull('transactions.type')
                ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                    ->where('transactions.type_card', '!=', 'credit')
                );
        })
            ->where(function ($q) {
                $q->whereNull('transactions.title')
                    ->orWhereRaw('LOWER(transactions.title) NOT IN (?,?,?)', [
                        'total fatura', 'fatura total', 'total da fatura'
                    ]);
            });

        // ========= HOJE
        $todayBase = (clone $txBase)->whereDate('date', $today->toDateString());
        $todayIn = (clone $todayBase)->whereHas('transactionCategory', fn($q) => $q->where('type', 'entrada'))->get();
        $todayOut = (clone $todayBase)->whereHas('transactionCategory', fn($q) => $q->where('type', 'despesa'))->get();
        $todayInv = (clone $todayBase)->whereHas('transactionCategory', fn($q) => $q->where('type', 'investimento'))->get();

        // ========= AMANHÃ
        $tomBase = (clone $txBase)->whereDate('date', $tomorrow->toDateString());
        $tomIn = (clone $tomBase)->whereHas('transactionCategory', fn($q) => $q->where('type', 'entrada'))->get();
        $tomOut = (clone $tomBase)->whereHas('transactionCategory', fn($q) => $q->where('type', 'despesa'))->get();
        $tomInv = (clone $tomBase)->whereHas('transactionCategory', fn($q) => $q->where('type', 'investimento'))->get();

        // ========= FATURAS (para hoje/amanhã e para KPIs)
        $invoicesAgg = DB::table('invoices as inv')->join('cards as c', 'c.id', '=', 'inv.card_id')->leftJoin('invoice_items as it', 'it.invoice_id', '=', 'inv.id')->where('inv.user_id', $user->id)->where('inv.paid', false)->groupBy('inv.id', 'inv.card_id', 'inv.current_month',
            'c.cardholder_name', 'c.last_four_digits', 'c.due_day')
            ->select(
                'inv.id', 'inv.card_id', 'inv.current_month',
                'c.cardholder_name', 'c.last_four_digits', 'c.due_day',
                DB::raw('COALESCE(SUM(it.amount),0) as total')
            )
            ->get()
            ->map(function ($r) {
                $base = Carbon::createFromFormat('Y-m', $r->current_month)->startOfMonth();
                $due = $base->copy()->day(min((int)($r->due_day ?: 1), $base->daysInMonth));
                $first = explode(' ', trim((string)$r->cardholder_name))[0];

                return [
                    'invoice_id' => (string)$r->id,
                    'card_id' => (string)$r->card_id,
                    'current_month' => $r->current_month,
                    'title' => "Fatura {$first} {$r->last_four_digits}",
                    'due_date' => $due->toDateString(),
                    'total' => (float)$r->total,
                ];
            })
            ->filter(fn($x) => $x['total'] > 0)
            ->values();

        $invoicesToday = $invoicesAgg->where('due_date', $today->toDateString())->values();
        $invoicesTomorrow = $invoicesAgg->where('due_date', $tomorrow->toDateString())->values();

        // ========= EVENTOS (únicas + recorrentes + custom + faturas)
        // janela: 6 meses para trás até 12 meses para frente
        $winStart = $today->copy()->subMonthsNoOverflow(6);
        $winEnd = $today->copy()->addMonthsNoOverflow(12);

        $events = $this->buildWindowEventsLite($user->id, $winStart, $winEnd);
        $eventsCol = collect($events);

        // helper de KPI a partir dos eventos
        // $onlyUnpaid = true -> ignora eventos já pagos (caso dos atrasados)
        $buildKpi = function ($minDate, $maxDate, bool $onlyUnpaid = false) use ($eventsCol) {
            $min = $minDate ? Carbon::parse($minDate)->toDateString() : null;
            $max = $maxDate ? Carbon::parse($maxDate)->toDateString() : null;

            $in = 0.0;
            $out = 0.0;

            foreach ($eventsCol as $e) {
                $d = Carbon::parse($e['start'])->toDateString();
                if ($min && $d < $min) continue;
                if ($max && $d > $max) continue;

                $props = $e['extendedProps'] ?? [];
                $type = $props['type'] ?? null;
                if ($type === 'payment') continue; // se um dia tiver evento de pagamento, ignora

                $paid = (bool)($props['paid'] ?? false);
                if ($onlyUnpaid && $paid) {
                    // só queremos o que AINDA não foi pago
                    continue;
                }

                $amount = (float)($props['amount'] ?? 0);

                if ($type === 'entrada') {
                    $in += abs($amount);
                } else {
                    $out += abs($amount);
                }
            }

            return [
                'in' => $in,
                'out' => $out,
                'net' => $in - $out,
            ];
        };

        $buildKpiPaid = function ($minDate, $maxDate) use ($eventsCol) {
            $min = $minDate ? Carbon::parse($minDate)->toDateString() : null;
            $max = $maxDate ? Carbon::parse($maxDate)->toDateString() : null;

            $in = 0.0;
            $out = 0.0;

            foreach ($eventsCol as $e) {
                $d = Carbon::parse($e['start'])->toDateString();
                if ($min && $d < $min) continue;
                if ($max && $d > $max) continue;

                $props = $e['extendedProps'] ?? [];
                $type = $props['type'] ?? null;
                if ($type === 'payment') continue;

                $paid = (bool)($props['paid'] ?? false);
                if (!$paid) {
                    // aqui queremos somente o que JÁ foi lançado/pago
                    continue;
                }

                $amount = (float)($props['amount'] ?? 0);

                if ($type === 'entrada') {
                    $in += abs($amount);
                } else {
                    $out += abs($amount);
                }
            }

            return [
                'in' => $in,
                'out' => $out,
                'net' => $in - $out,
            ];
        };

        // ========= KPIs
        // Atrasados: só o que ainda NÃO foi pago
        $kpiOverdue = $buildKpi(null, $today->copy()->subDay(), true);

        // Hoje: considera pagos + não pagos
        $kpiToday = $buildKpi($today, $today, false);

        // Próximos 7 dias:
        // - kpiNext7: apenas o que ainda NÃO foi pago (projeção futura)
        // - kpiNext7Paid: o que já foi lançado dentro desse período
        $kpiNext7 = $buildKpi($tomorrow, $today->copy()->addDays(7), true);
        $kpiNext7Paid = $buildKpiPaid($tomorrow, $today->copy()->addDays(7));

        // ========= CARDS ATRASADOS (lista detalhada) — somente NÃO pagos
        $overdueEvents = $eventsCol
            ->filter(function ($e) use ($today) {
                $props = $e['extendedProps'] ?? [];
                $d = Carbon::parse($e['start']);
                $type = $props['type'] ?? null;
                $paid = (bool)($props['paid'] ?? false);

                return $d->lt($today) && $type !== 'payment' && !$paid;
            })
            ->sortByDesc('start');

        $overdueCards = $overdueEvents->map(function ($e) {
            $props = $e['extendedProps'] ?? [];
            $type = $props['type'] ?? null;
            $amountRaw = (float)($props['amount'] ?? 0);
            $signed = $type === 'entrada' ? abs($amountRaw) : -abs($amountRaw);

            return [
                'bg' => $e['bg'] ?? '#6b7280',
                'icon' => $e['icon'] ?? 'fa-solid fa-calendar-day',
                'title' => $e['title'] ?? ($props['category_name'] ?? 'Lançamento'),
                'date' => Carbon::parse($e['start'])->toDateString(),
                'amt' => $signed,
                'is_invoice' => !empty($props['is_invoice']),
                'paid' => (bool)($props['paid'] ?? false),
                'tx_id' => $props['transaction_id'] ?? null,
                'card_id' => $props['card_id'] ?? null,
                'current_month' => $props['current_month'] ?? null,
                'parcel_of' => $props['parcel_of'] ?? null,
                'parcel_total' => $props['parcel_total'] ?? null,
            ];
        })->values();

        // ========= PRÓXIMOS 5 (mantendo a lógica antiga)
        $nextFive = $eventsCol
            ->filter(function ($e) use ($afterTomorrow) {
                $props = $e['extendedProps'] ?? [];
                $type = $props['type'] ?? null;
                if ($type === 'payment') return false;

                $d = Carbon::parse($e['start']);
                return $d->gte($afterTomorrow);
            })
            ->sortBy('start')
            ->take(5)
            ->values();

        // mapa [transaction_id#data] => true/false (pago?)
        $paidByKey = $eventsCol
            ->map(function ($e) {
                $props = $e['extendedProps'] ?? [];
                if (empty($props['transaction_id'])) {
                    return null;
                }

                $date = Carbon::parse($e['start'])->toDateString();

                return [
                    'key'     => $props['transaction_id'] . '#' . $date,
                    'paid'    => (bool)($props['paid'] ?? false),
                    'paid_at' => $props['paid_at'] ?? null,
                ];
            })
            ->filter()
            ->groupBy('key')
            ->map(function ($rows) {
                $anyPaid = $rows->contains(fn ($r) => $r['paid']);

                $paidAt = null;
                foreach ($rows as $r) {
                    if ($r['paid'] && !empty($r['paid_at'])) {
                        if ($paidAt === null || $r['paid_at'] > $paidAt) {
                            $paidAt = $r['paid_at'];
                        }
                    }
                }

                return [
                    'paid'    => $anyPaid,
                    'paid_at' => $paidAt,
                ];
            })
            ->toArray();

        return view('app.digest.index', compact(
            'today', 'tomorrow',
            'todayIn', 'todayOut', 'todayInv',
            'tomIn', 'tomOut', 'tomInv',
            'invoicesToday', 'invoicesTomorrow',
            'nextFive',
            'overdueCards',
            'kpiOverdue', 'kpiToday', 'kpiNext7', 'kpiNext7Paid',
            'paidByKey'
        ));
    }

    private function buildWindowEventsLite(string $userId, Carbon $winStart, Carbon $winEnd): \Illuminate\Support\Collection
    {
        $events = collect();

        $payments = DB::table('payment_transactions as pt')
            ->join('transactions as t', 't.id', '=', 'pt.transaction_id')
            ->where('t.user_id', $userId)
            ->select(
                'pt.transaction_id',
                'pt.reference_month',
                'pt.reference_year',
                'pt.payment_date'
            )
            ->get();

        $paidInfo  = []; // key: txId#YYYY-MM => ['paid' => true, 'paid_at' => 'YYYY-MM-DD']
        $anchorMap = []; // txId => menor YYYY-MM pago

        foreach ($payments as $p) {
            $monthPadded = str_pad((string) $p->reference_month, 2, '0', STR_PAD_LEFT);
            $ym          = $p->reference_year . '-' . $monthPadded;

            $txId = (string) $p->transaction_id;
            $key  = $txId . '#' . $ym;

            $paymentDate = $p->payment_date
                ? Carbon::parse($p->payment_date)->toDateString()
                : null;

            if (!isset($paidInfo[$key])) {
                $paidInfo[$key] = [
                    'paid'    => true,
                    'paid_at' => $paymentDate,
                ];
            } else {
                // guarda a data MAIS RECENTE de pagamento pra esse mês
                $current = $paidInfo[$key]['paid_at'] ?? null;
                if ($paymentDate && (!$current || $paymentDate > $current)) {
                    $paidInfo[$key]['paid_at'] = $paymentDate;
                }
            }

            // âncora mínima por transação
            if (!isset($anchorMap[$txId]) || $ym < $anchorMap[$txId]) {
                $anchorMap[$txId] = $ym;
            }
        }

        $isPaid = function (string $transactionId, Carbon $occDate) use ($paidInfo): bool {
            $key = $transactionId . '#' . $occDate->format('Y-m');
            return !empty($paidInfo[$key]);
        };

        $getPaidAt = function (string $transactionId, Carbon $occDate) use ($paidInfo) {
            $key = $transactionId . '#' . $occDate->format('Y-m');
            return $paidInfo[$key]['paid_at'] ?? null;
        };

        // ===== ÚNICAS =====
        $uniqueTx = \App\Models\Transaction::withoutGlobalScopes()
            ->with(['transactionCategory:id,name,type,color,icon'])
            ->where('transactions.user_id', $userId)
            ->where('recurrence_type', 'unique')
            ->whereBetween('transactions.date', [$winStart, $winEnd])
            ->where(function ($q) {
                $q->where('transactions.type', '!=', 'card')
                    ->orWhereNull('transactions.type')
                    ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                        ->where('transactions.type_card', '!=', 'credit')
                    );
            })
            ->where(function ($q) {
                $q->whereNull('transactions.title')
                    ->orWhereRaw('LOWER(transactions.title) NOT IN (?,?,?)', [
                        'total fatura', 'fatura total', 'total da fatura'
                    ]);
            })
            ->get([
                'transactions.id',
                'transactions.title',
                'transactions.amount',
                'transactions.date',
                'transactions.transaction_category_id',
            ]);

        foreach ($uniqueTx as $t) {
            $cat = $t->transactionCategory;
            $type = in_array($cat?->type, ['entrada', 'despesa', 'investimento'], true)
                ? $cat->type
                : 'investimento';

            $occ = Carbon::parse($t->date)->startOfDay();
            $paid = $isPaid((string)$t->id, $occ);
            $paidAt = $getPaidAt((string) $t->id, $occ);

            $events->push([
                'id' => (string)$t->id,
                'title' => $t->title ?? $cat?->name,
                'start' => $occ->toDateString(),
                'bg' => $cat?->color,
                'icon' => $cat?->icon,
                'color' => $type === 'despesa' ? '#ef4444'
                    : ($type === 'entrada' ? '#22c55e' : '#0ea5e9'),
                'extendedProps' => [
                    'amount' => (float)$t->amount,
                    'amount_brl' => function_exists('brlPrice')
                        ? brlPrice($t->amount)
                        : number_format($t->amount, 2, ',', '.'),
                    'category_name' => $cat?->name,
                    'type' => $type,
                    'transaction_id' => (string)$t->id,
                    'paid'    => $paid,
                    'paid_at' => $paidAt,
                ],
            ]);
        }

        // ===== RECORRENTES MONTHLY / YEARLY (sem custom items) =====
        $recMY = \App\Models\Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->where('recurrents.user_id', $userId)
            ->whereHas('transaction', function ($q) {
                $q->whereIn('recurrence_type', ['monthly', 'yearly'])
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                                ->where('transactions.type_card', '!=', 'credit')
                            );
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?,?,?)', [
                                'total fatura', 'fatura total', 'total da fatura'
                            ]);
                    });
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('custom_item_recurrents as cir')
                    ->whereColumn('cir.recurrent_id', 'recurrents.id');
            })
            ->get([
                'recurrents.id',
                'recurrents.transaction_id',
                'recurrents.payment_day',
                'recurrents.amount',
            ]);

        foreach ($recMY as $r) {
            $t = $r->transaction;
            if (!$t) continue;

            $cat = $t->transactionCategory;
            $type = in_array($cat?->type, ['entrada', 'despesa', 'investimento'], true)
                ? $cat->type
                : 'investimento';

            $amount = (float)$r->amount;

            // âncora base: transaction->date (pode estar "andando" por causa do bump)
            $anchor = Carbon::parse($t->date)->startOfDay();

            // se houver pagamentos registrados, usa o MENOR ano-mês como limite inferior
            $txKey = (string)$t->id;
            if (!empty($anchorMap[$txKey])) {
                // yyyy-mm -> começo do mês
                $anchorRef = Carbon::createFromFormat('Y-m', $anchorMap[$txKey])->startOfDay();
                if ($anchorRef->lt($anchor)) {
                    $anchor = $anchorRef;
                }
            }

            $pd = max(1, (int)$r->payment_day);

            if ($t->recurrence_type === 'monthly') {
                $m = $winStart->copy()->startOfMonth();

                while ($m->lte($winEnd)) {
                    $occ = $m->copy()->day(min($pd, $m->daysInMonth));

                    if ($occ->betweenIncluded($winStart, $winEnd) && $occ->gte($anchor)) {
                        $paid = $isPaid((string)$t->id, $occ);
                        $paidAt = $getPaidAt((string) $t->id, $occ);

                        $events->push([
                            'id' => "rec_m_{$r->id}_" . $occ->format('Ymd'),
                            'title' => $t->title ?? $cat?->name,
                            'start' => $occ->toDateString(),
                            'bg' => $cat?->color,
                            'icon' => $cat?->icon,
                            'color' => $type === 'despesa' ? '#ef4444'
                                : ($type === 'entrada' ? '#22c55e' : '#0ea5e9'),
                            'extendedProps' => [
                                'amount' => $type === 'entrada'
                                    ? abs($amount)
                                    : -abs($amount),
                                'amount_brl' => function_exists('brlPrice')
                                    ? brlPrice($amount)
                                    : number_format($amount, 2, ',', '.'),
                                'category_name' => $cat?->name,
                                'type' => $type,
                                'transaction_id' => (string)$t->id,
                                'paid' => $paid,
                                'paid_at' => $paidAt,
                            ],
                        ]);
                    }

                    $m->addMonthNoOverflow();
                }
            } else {
                // YEARLY
                $anchorMonth = (int)$anchor->month;

                for ($y = $winStart->year; $y <= $winEnd->year; $y++) {
                    $daysIn = Carbon::create($y, $anchorMonth, 1)->daysInMonth;
                    $occ = Carbon::create($y, $anchorMonth, min($pd, $daysIn));

                    if ($occ->betweenIncluded($winStart, $winEnd) && $occ->gte($anchor)) {
                        $paid = $isPaid((string)$t->id, $occ);
                        $paidAt = $getPaidAt((string)$t->id, $occ);

                        $events->push([
                            'id' => "rec_y_{$r->id}_" . $occ->format('Ymd'),
                            'title' => $t->title ?? $cat?->name,
                            'start' => $occ->toDateString(),
                            'bg' => $cat?->color,
                            'icon' => $cat?->icon,
                            'color' => $type === 'despesa' ? '#ef4444'
                                : ($type === 'entrada' ? '#22c55e' : '#0ea5e9'),
                            'extendedProps' => [
                                'amount' => $type === 'entrada'
                                    ? abs($amount)
                                    : -abs($amount),
                                'amount_brl' => function_exists('brlPrice')
                                    ? brlPrice($amount)
                                    : number_format($amount, 2, ',', '.'),
                                'category_name' => $cat?->name,
                                'type' => $type,
                                'transaction_id' => (string)$t->id,
                                'paid' => $paid,
                                'paid_at' => $paidAt,
                            ],
                        ]);
                    }
                }
            }
        }

        // ===== CUSTOM COM ITENS (tabela custom_item_recurrents) =====
        $recCustom = \App\Models\Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->where('recurrents.user_id', $userId)
            ->whereHas('transaction', fn($q) => $q->whereIn('recurrence_type', ['custom', 'monthly', 'yearly'])
            )
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('custom_item_recurrents as cir')
                    ->whereColumn('cir.recurrent_id', 'recurrents.id');
            })
            ->get(['recurrents.id', 'recurrents.transaction_id']);

        foreach ($recCustom as $r) {
            $t = $r->transaction;
            if (!$t) continue;

            $cat = $t->transactionCategory;
            $type = in_array($cat?->type, ['entrada', 'despesa', 'investimento'], true)
                ? $cat->type
                : 'investimento';

            $items = \App\Models\CustomItemRecurrents::where('recurrent_id', $r->id)
                ->get([
                    'payment_day',
                    'reference_month',
                    'reference_year',
                    'amount',
                    'custom_occurrence_number',
                ]);

            $totalOccurrences = max($items->max('custom_occurrence_number') ?? 0, $items->count());

            foreach ($items as $ci) {
                $days = Carbon::create($ci->reference_year, $ci->reference_month, 1)->daysInMonth;
                $occ = Carbon::create(
                    $ci->reference_year,
                    $ci->reference_month,
                    min((int)$ci->payment_day, $days)
                );

                if (!$occ->betweenIncluded($winStart, $winEnd)) {
                    continue;
                }

                $amount = (float)$ci->amount;
                $occurrence = (int)$ci->custom_occurrence_number;

                $parcelOf = $totalOccurrences > 1 ? $occurrence : null;
                $parcelTotal = $totalOccurrences > 1 ? $totalOccurrences : null;

                $paid = $isPaid((string)$t->id, $occ);
                $paidAt = $getPaidAt((string) $t->id, $occ);

                $events->push([
                    'id' => "rec_c_{$r->id}_" . $occ->format('Ymd') . "_" . $ci->custom_occurrence_number,
                    'title' => $t->title ?? $cat?->name,
                    'start' => $occ->toDateString(),
                    'bg' => $cat?->color,
                    'icon' => $cat?->icon,
                    'color' => $type === 'despesa' ? '#ef4444'
                        : ($type === 'entrada' ? '#22c55e' : '#0ea5e9'),
                    'extendedProps' => [
                        'amount' => $type === 'entrada'
                            ? abs($amount)
                            : -abs($amount),
                        'amount_brl' => function_exists('brlPrice')
                            ? brlPrice($amount)
                            : number_format($amount, 2, ',', '.'),
                        'category_name' => $cat?->name,
                        'type' => $type,
                        'transaction_id' => (string)$t->id,
                        'parcel_of' => $parcelOf,
                        'parcel_total' => $parcelTotal,
                        'paid' => $paid,
                        'paid_at' => $paidAt,
                    ],
                ]);
            }
        }

        // ===== CUSTOM “A CADA X DIAS” (sem itens) =====
        $recDays = \App\Models\Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->where('recurrents.user_id', $userId)
            ->where('interval_unit', 'days')
            ->whereHas('transaction', function ($q) {
                $q->where('recurrence_type', 'custom')
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                                ->where('transactions.type_card', '!=', 'credit')
                            );
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?,?,?)', [
                                'total fatura', 'fatura total', 'total da fatura'
                            ]);
                    });
            })
            ->get(['recurrents.*']);

        foreach ($recDays as $r) {
            if (DB::table('custom_item_recurrents')->where('recurrent_id', $r->id)->exists()) {
                continue; // já tratado em $recCustom
            }

            $t = $r->transaction;
            if (!$t) continue;

            $cat = $t->transactionCategory;
            $type = in_array($cat?->type, ['entrada', 'despesa', 'investimento'], true)
                ? $cat->type
                : 'investimento';

            $startBase = Carbon::parse($t->date)->startOfDay();
            $start = Carbon::parse($r->start_date ?: $startBase)->startOfDay();
            if ($start->lt($startBase)) {
                $start = $startBase;
            }

            $interval = max(1, (int)$r->interval_value);
            $cursor = $this->firstAlignedDays($start, $winStart, $interval);
            $cursor = $this->normalizeW($cursor, (bool)$r->include_sat, (bool)$r->include_sun);

            $amount = (float)($r->amount ?: $t->amount);

            while ($cursor->lte($winEnd)) {
                $paid = $isPaid((string)$t->id, $cursor);

                $events->push([
                    'id' => "rec_d_{$r->id}_" . $cursor->format('Ymd'),
                    'title' => $t->title ?? $cat?->name,
                    'start' => $cursor->toDateString(),
                    'bg' => $cat?->color,
                    'icon' => $cat?->icon,
                    'color' => $type === 'despesa' ? '#ef4444'
                        : ($type === 'entrada' ? '#22c55e' : '#0ea5e9'),
                    'extendedProps' => [
                        'amount' => $type === 'entrada'
                            ? abs($amount)
                            : -abs($amount),
                        'amount_brl' => function_exists('brlPrice')
                            ? brlPrice($amount)
                            : number_format($amount, 2, ',', '.'),
                        'category_name' => $cat?->name,
                        'type' => $type,
                        'transaction_id' => (string)$t->id,
                        'paid' => $paid,
                    ],
                ]);

                $cursor = $this->normalizeW(
                    $cursor->copy()->addDays($interval),
                    (bool)$r->include_sat,
                    (bool)$r->include_sun
                );
            }
        }

        // ===== FATURAS (invoice) – um por invoice no vencimento =====
        $rows = DB::table('invoices as inv')
            ->join('cards as c', 'c.id', '=', 'inv.card_id')
            ->leftJoin('invoice_items as it', 'it.invoice_id', '=', 'inv.id')
            ->where('inv.user_id', $userId)
            ->where('inv.paid', false)
            ->groupBy(
                'inv.id', 'inv.card_id', 'inv.current_month',
                'c.cardholder_name', 'c.last_four_digits', 'c.due_day'
            )
            ->select(
                'inv.id', 'inv.card_id', 'inv.current_month',
                'c.cardholder_name', 'c.last_four_digits', 'c.due_day',
                DB::raw('COALESCE(SUM(it.amount),0) as total')
            )
            ->get();

        foreach ($rows as $r) {
            $base = Carbon::createFromFormat('Y-m', $r->current_month)->startOfMonth();
            $due = $base->copy()->day(min((int)($r->due_day ?: 1), $base->daysInMonth));
            $total = (float)$r->total;

            if ($total <= 0 || !$due->betweenIncluded($winStart, $winEnd)) {
                continue;
            }

            $first = explode(' ', trim((string)$r->cardholder_name))[0];

            $events->push([
                'id' => (string)$r->id,
                'title' => "Fatura {$first} {$r->last_four_digits}",
                'start' => $due->toDateString(),
                'bg' => '#be123c',
                'icon' => 'fa-solid fa-credit-card',
                'color' => '#ef4444',
                'extendedProps' => [
                    'amount' => -abs($total),
                    'amount_brl' => function_exists('brlPrice')
                        ? brlPrice($total)
                        : number_format($total, 2, ',', '.'),
                    'category_name' => 'Fatura Cartão',
                    'type' => 'despesa',
                    'is_invoice' => true,
                    'paid' => false, // invoice inteira ainda em aberto
                    'card_id' => (string)$r->card_id,
                    'current_month' => $r->current_month,
                ],
            ]);
        }

        return $events->values();
    }

    private function normalizeW(Carbon $d, bool $sat, bool $sun): Carbon
    {
        if (!$sat && $d->isSaturday()) $d->addDays(2);
        if (!$sun && $d->isSunday()) $d->addDay();
        return $d;
    }

    private function firstAlignedDays(Carbon $start, Carbon $from, int $interval): Carbon
    {
        $s = $start->copy();
        if ($s->lt($from)) {
            $diff = $s->diffInDays($from);
            $steps = intdiv($diff + $interval - 1, $interval);
            $s->addDays($steps * $interval);
        }
        return $s;
    }
}
