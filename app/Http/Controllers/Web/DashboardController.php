<?php

namespace App\Http\Controllers\Web;

use App\Helpers\CardCycle;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AdditionalUser;
use App\Models\Card;
use App\Models\CustomItemRecurrents;
use App\Models\InvoicePayment;
use App\Models\PaymentTransaction;
use App\Models\Recurrent;
use App\Models\Saving;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $ownerId = AdditionalUser::ownerIdFor();
        $userIds = AdditionalUser::query()
            ->where('user_id', $ownerId)
            ->pluck('linked_user_id')
            ->push($ownerId)
            ->unique()
            ->values();

        $monthParam = $request->query('month');
        $startOfMonth = $monthParam
            ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : Carbon::now()->startOfMonth();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();
        $today = Carbon::today();

        $recentTransactions = Transaction::with(['transactionCategory:id,name,type,color,icon', 'card'])
            ->whereIn('transactions.user_id', $userIds)
            ->orderByDesc('transactions.date')
            ->limit(5)
            ->get(['transactions.id', 'transactions.type', 'transactions.title', 'transactions.amount', 'transactions.date', 'transactions.transaction_category_id']);

        $upcomingPayments = Transaction::with(['transactionCategory:id,name,type,color,icon'])
            ->leftJoin('payment_transactions as pt', 'pt.transaction_id', '=', 'transactions.id') // novo
            ->whereIn('transactions.user_id', $userIds)
            ->whereHas('transactionCategory', fn($q) => $q->where('type', 'despesa'))
            ->whereNotNull('transactions.date')
            ->whereDate('transactions.date', '>=', $today)
            ->whereNull('pt.id') // novo: só as que ainda não foram pagas
            ->where('type', '!=', 'card')
            ->orderBy('transactions.date')
            ->limit(5)
            ->get(['transactions.id', 'transactions.title', 'transactions.amount', 'transactions.date', 'transactions.transaction_category_id']);

        $upcomingInvoiceCards = $this->buildUpcomingInvoicesForList($userIds, $today, 50);

        $tx = $upcomingPayments->map(function ($t) {
            return [
                'kind' => 'tx',
                'id' => (string)$t->id,
                'date' => (string)$t->date,
                'title' => $t->title ?? optional($t->transactionCategory)->name,
                'amount' => (float)$t->amount,
                'color' => optional($t->transactionCategory)->color,
                'icon' => optional($t->transactionCategory)->icon,
                'modal_id' => (string)$t->id,
                'modal_amount' => (float)$t->amount,
                'modal_date' => (string)$t->date,
            ];
        });

        $invs = collect($upcomingInvoiceCards)
            ->filter(fn($r) => \Carbon\Carbon::parse($r['due_date'])->gte($today))
            ->map(function ($r) {
                return [
                    'kind' => 'inv',
                    'id' => (string)$r['invoice_id'] ?? ($r['card_id'] . '-' . $r['current_month']),
                    'date' => (string)$r['due_date'],
                    'title' => (string)$r['title'],
                    'amount' => (float)$r['total'],
                    'color' => '#be123c',
                    'icon' => 'fa-solid fa-credit-card',
                    'card_id' => (string)$r['card_id'],
                    'current_month' => (string)$r['current_month'],
                ];
            });

        $upcomingRec = $this->buildUpcomingRecurrences($userIds, $today, 50);

        $upcomingAny = $tx
            ->toBase()
            ->concat($invs)
            ->concat($upcomingRec)
            ->filter(fn($x) => !empty($x['date']))
            ->sortBy('date')
            ->take(5)
            ->values();

        $winStart = (clone $startOfMonth)->startOfMonth();
        $winEnd = (clone $winStart)->addMonthsNoOverflow(11)->endOfMonth();

        $calendarEvents = $this->buildWindowEvents($userIds, $winStart, $winEnd)->values();

        [$currentInvoices, $cardTip] = $this->buildInvoicesWidget($userIds, $today);

        $kpis = $this->kpisForMonth($userIds, $startOfMonth, $endOfMonth);
        $accountsBalance = $kpis['accountsBalance'];
        $savingsBalance = Saving::whereIn('savings.user_id', $userIds)->sum('current_amount');
        $total = $kpis['saldoPrevisto']; // ou $kpis['saldoReal']

        return view('app.dashboard', compact(
            'accountsBalance', 'savingsBalance', 'total',
            'recentTransactions', 'calendarEvents',
            'startOfMonth', 'endOfMonth',
            'currentInvoices', 'cardTip',
            'upcomingAny'
        ));
    }

    private function percentChange(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            if ($current == 0.0) return 0.0;
            return null; // evita “infinito”; na view mostramos "—"
        }

        return (($current - $previous) / $previous) * 100.0;
    }

    public function calendarEvents(Request $request)
    {
        $ownerId = AdditionalUser::ownerIdFor();
        $userIds = AdditionalUser::where('user_id', $ownerId)
            ->pluck('linked_user_id')->push($ownerId)->unique()->values();

        $start = $request->query('start', now()->format('Y-m'));
        $months = max(1, min((int)$request->query('months', 2), 24));

        $winStart = Carbon::createFromFormat('Y-m', $start)->startOfMonth();
        $winEnd = (clone $winStart)->addMonthsNoOverflow($months - 1)->endOfMonth();

        return response()->json($this->buildWindowEvents($userIds, $winStart, $winEnd)->values());
    }

    private function buildWindowEvents(Collection $userIds, Carbon $winStart, Carbon $winEnd): Collection
    {
        $seenTxDate = [];
        $events = collect();

        $paidIdx = $this->paymentsIndex($userIds);
        $paidByDate = $paidIdx['_byDate'] ?? [];

        $paidRows = PaymentTransaction::query()
            ->join('transactions as t', 't.id', '=', 'payment_transactions.transaction_id')
            ->whereIn('t.user_id', $userIds)
            ->whereBetween('payment_transactions.payment_date', [$winStart, $winEnd])
            ->get([
                'payment_transactions.id',
                'payment_transactions.title',
                'payment_transactions.amount',
                'payment_transactions.payment_date',
            ]);

        foreach ($paidRows as $p) {
            $events->push([
                'id' => "pay_{$p->id}",
                'title' => $p->title ?: 'Pagamento',
                'start' => Carbon::parse($p->payment_date)->toDateString(),
                'bg' => '#0ea5e9',
                'icon' => 'fa-regular fa-circle-check',
                'color' => '#0ea5e9',
                'extendedProps' => [
                    'amount' => (float)$p->amount,
                    'amount_brl' => brlPrice($p->amount),
                    'category_name' => 'Pagamento',
                    'type' => 'payment',
                ],
            ]);
        }

        $ipRows = DB::table('invoice_payments as ip')
            ->join('invoices as inv', 'inv.id', '=', 'ip.invoice_id')
            ->join('cards as c', 'c.id', '=', 'inv.card_id')
            ->whereIn('inv.user_id', $userIds)
            ->whereBetween('ip.paid_at', [$winStart, $winEnd])
            ->get([
                'ip.id', 'ip.amount', 'ip.paid_at',
                'inv.card_id', 'inv.current_month',
                'c.cardholder_name', 'c.last_four_digits'
            ]);

        foreach ($ipRows as $p) {
            $firstName = explode(' ', trim((string)$p->cardholder_name))[0];

            $events->push([
                'id' => "invpay_{$p->id}",
                'title' => "Fatura {$firstName} {$p->last_four_digits}", // mantém o nome da fatura
                'start' => Carbon::parse($p->paid_at)->toDateString(),
                'bg' => '#0ea5e9',
                'icon' => 'fa-regular fa-circle-check',
                'color' => '#0ea5e9',
                'extendedProps' => [
                    'amount' => abs((float)$p->amount), // positivo
                    'amount_brl' => function_exists('brlPrice') ? brlPrice(abs((float)$p->amount)) : number_format(abs((float)$p->amount), 2, ',', '.'),
                    'category_name' => 'Pagamento fatura',
                    'type' => 'payment',
                    'is_invoice' => true,
                    'paid' => true,
                    'card_id' => (string)$p->card_id,
                    'current_month' => $p->current_month,
                ],
            ]);
        }

        $uniqueTx = Transaction::withoutGlobalScopes()
            ->with(['transactionCategory:id,name,type,color,icon'])
            ->leftJoin('payment_transactions as pt', 'pt.transaction_id', '=', 'transactions.id')
            ->whereIn('transactions.user_id', $userIds)
            ->where('recurrence_type', 'unique')
            ->whereBetween('transactions.date', [$winStart, $winEnd])
            ->whereNull('pt.id')
            ->where(function ($q) {
                $q->where('transactions.type', '!=', 'card')
                    ->orWhereNull('transactions.type')
                    ->orWhere(function ($qq) {
                        $qq->where('transactions.type', 'card')
                            ->where('transactions.type_card', '!=', 'credit');
                    });
            })
            ->where(function ($q) {
                $q->whereNull('transactions.title')
                    ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', [
                        'total fatura', 'fatura total', 'total da fatura'
                    ]);
            })
            ->get([
                'transactions.id', 'transactions.title', 'transactions.amount',
                'transactions.date', 'transactions.transaction_category_id'
            ]);

        foreach ($uniqueTx as $t) {
            $cat = $t->transactionCategory;
            $type = in_array($cat?->type, ['entrada', 'despesa', 'investimento'], true) ? $cat->type : 'investimento';

            $day = \Carbon\Carbon::parse($t->date)->toDateString();
            $key = $t->id . '|' . $day;
            if (!empty($seenTxDate[$key])) continue;
            $seenTxDate[$key] = true;

            $events->push([
                'id' => $t->id,
                'title' => $t->title ?? $cat?->name,
                'start' => $t->date,
                'bg' => $cat?->color,
                'icon' => $cat?->icon,
                'color' => $type === 'despesa' ? '#ef4444' : ($type === 'entrada' ? '#22c55e' : '#0ea5e9'),
                'extendedProps' => [
                    'amount' => (float)$t->amount,
                    'amount_brl' => brlPrice($t->amount),
                    'category_name' => $cat?->name,
                    'type' => $type,
                    'transaction_id' => (string)$t->id,
                ],
            ]);
        }

        $recMY = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->whereIn('recurrents.user_id', $userIds)
            ->whereHas('transaction', function ($q) {
                $q->whereIn('recurrence_type', ['monthly', 'yearly'])
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')->where('transactions.type_card', '!=', 'credit'));
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', ['total fatura', 'fatura total', 'total da fatura']);
                    });
            })
            // ⬇️ troque por whereNotExists
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('custom_item_recurrents as cir')
                    ->whereColumn('cir.recurrent_id', 'recurrents.id');
            })
            ->get(['recurrents.id', 'recurrents.user_id', 'recurrents.transaction_id', 'recurrents.payment_day', 'recurrents.amount']);

        foreach ($recMY as $r) {
            $t = $r->transaction;
            if (!$t) continue;

            // proteção extra
            if ($this->isInvoiceControlTitle($t->title) || $t->type === 'card') continue;

            $cat = $t->transactionCategory;
            $type = in_array($cat?->type, ['entrada', 'despesa', 'investimento'], true) ? $cat->type : 'investimento';
            $startBase = Carbon::parse($t->date)->startOfDay();
            $paymentDay = max(1, (int)$r->payment_day);
            $amount = (float)$r->amount;


            if ($t->recurrence_type === 'monthly') {
                $m = $winStart->copy()->startOfMonth();
                while ($m->lte($winEnd)) {
                    $occ = $m->copy()->day(min($paymentDay, $m->daysInMonth));
                    $ym = $occ->format('Y-m');

                    if (empty($paidIdx[$t->id][$ym]) && $occ->gte($startBase)) {
                        $day = $occ->toDateString();
                        $key = $t->id . '|' . $day;
                        if (empty($seenTxDate[$key])) {
                            $seenTxDate[$key] = true;
                            $events->push($this->ev($r->id, 'm', $t, $cat, $type, $occ, $amount));
                        }
                    }

                    // sempre avança o mês
                    $m->addMonth();
                }
            } else { // yearly
                $anchorMonth = (int)$startBase->month;
                for ($y = $winStart->year; $y <= $winEnd->year; $y++) {
                    $daysIn = Carbon::create($y, $anchorMonth, 1)->daysInMonth;
                    $occ = Carbon::create($y, $anchorMonth, min($paymentDay, $daysIn));

                    $ym = $occ->format('Y-m');
                    if (!empty($paidIdx[$t->id][$ym])) continue;

                    if ($occ->betweenIncluded($winStart, $winEnd) && $occ->gte($startBase)) {
                        $day = $occ->toDateString();
                        $key = $t->id . '|' . $day;
                        if (!empty($seenTxDate[$key])) continue; // <- aqui NÃO use $m
                        $seenTxDate[$key] = true;

                        $events->push($this->ev($r->id, 'y', $t, $cat, $type, $occ, $amount));
                    }
                }
            }
        }

        $recC = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->whereIn('recurrents.user_id', $userIds)
            ->whereHas('transaction', function ($q) {
                $q->whereIn('recurrence_type', ['custom', 'monthly', 'yearly'])
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')->where('transactions.type_card', '!=', 'credit'));
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', ['total fatura', 'fatura total', 'total da fatura']);
                    });
            })
            // ⬇️ só quem realmente TEM itens custom
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('custom_item_recurrents as cir')
                    ->whereColumn('cir.recurrent_id', 'recurrents.id');
            })
            ->get(['recurrents.id', 'recurrents.user_id', 'recurrents.transaction_id']);

        foreach ($recC as $r) {
            $t = $r->transaction;
            if (!$t) continue;
            // só emite se houver itens custom
            $items = CustomItemRecurrents::where('recurrent_id', $r->id)
                ->get(['payment_day', 'reference_month', 'reference_year', 'amount', 'custom_occurrence_number']);

            if ($items->isEmpty()) continue; // evita colisão com o bloco recMY (sem término)

            $cat = $t->transactionCategory;
            $type = in_array($cat?->type, ['entrada', 'despesa', 'investimento'], true) ? $cat->type : 'investimento';

            foreach ($items as $ci) {
                $daysIn = \Carbon\Carbon::create($ci->reference_year, $ci->reference_month, 1)->daysInMonth;
                $occ = \Carbon\Carbon::create($ci->reference_year, $ci->reference_month, min((int)$ci->payment_day, $daysIn));

                $ymd = $occ->toDateString();
                if (!empty($paidByDate[$t->id][$ymd])) continue;

                if (!$occ->betweenIncluded($winStart, $winEnd)) continue;

                $day = $occ->toDateString();
                $key = $t->id . '|' . $day;
                if (!empty($seenTxDate[$key])) continue;
                $seenTxDate[$key] = true;

                $events->push($this->ev($r->id, 'c', $t, $cat, $type, $occ, (float)$ci->amount, $ci->custom_occurrence_number));
            }
        }

        // ===== 3b) RECORRENTES CUSTOM (X dias) SEM TÉRMINO (não têm itens em custom_item_recurrents)
        $recD = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->whereIn('recurrents.user_id', $userIds)
            ->where('interval_unit', 'days')
            ->whereHas('transaction', function ($q) {
                $q->where('recurrence_type', 'custom')
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(function ($qq) {
                                $qq->where('transactions.type', 'card')
                                    ->where('transactions.type_card', '!=', 'credit');
                            });
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', [
                                'total fatura', 'fatura total', 'total da fatura'
                            ]);
                    });
            })
            ->get(['recurrents.*']); // pega start_date, interval_value, include_sat/sun, amount, etc.

        foreach ($recD as $r) {
            $t = $r->transaction;
            if (!$t) continue;

            // se já existem itens explícitos p/ esse recurrent → pula (evita duplicar com bloco $recC)
            $hasItems = DB::table('custom_item_recurrents')->where('recurrent_id', $r->id)->exists();
            if ($hasItems) continue;

            $cat = $t->transactionCategory;
            $type = in_array($cat?->type, ['entrada', 'despesa', 'investimento'], true) ? $cat->type : 'investimento';

            $start = Carbon::parse($r->start_date)->startOfDay();
            $interval = max(1, (int)$r->interval_value);

            // primeira >= janela e alinhada ao step
            $cursor = $this->firstAlignedDays($start, $winStart, $interval);
            $cursor = $this->normalizeW($cursor, (bool)$r->include_sat, (bool)$r->include_sun);

            while ($cursor->lte($winEnd)) {
                if ($cursor->gte($start)) {
                    $ymd = $cursor->toDateString();
                    $key = $t->id . '|' . $ymd;
                    if (empty(($paidByDate[$t->id] ?? [])[$ymd]) && empty($seenTxDate[$key])) {
                        $seenTxDate[$key] = true;
                        $events->push($this->ev($r->id, 'd', $t, $cat, $type, $cursor, (float)$r->amount));
                    }
                }
                $cursor = $this->normalizeW(
                    $cursor->copy()->addDays($interval),
                    (bool)$r->include_sat,
                    (bool)$r->include_sun
                );
            }
        }

        // ===== 4) FATURAS DE CARTÃO (um evento por invoice, na data de vencimento)
        $rows = DB::table('invoices as inv')
            ->join('cards as c', 'c.id', '=', 'inv.card_id')
            ->leftJoin('invoice_items as it', 'it.invoice_id', '=', 'inv.id')
            ->whereIn('inv.user_id', $userIds)
            ->groupBy(
                'inv.id', 'inv.card_id', 'inv.current_month', 'inv.paid',
                'c.cardholder_name', 'c.last_four_digits', 'c.due_day'
            )
            ->get([
                'inv.id',
                'inv.card_id',
                'inv.current_month',
                'inv.paid',
                'c.cardholder_name',
                'c.last_four_digits',
                'c.due_day',
                DB::raw('COALESCE(SUM(it.amount),0) as total'),
            ]);

        foreach ($rows as $r) {
            $base = Carbon::createFromFormat('Y-m', $r->current_month)->startOfMonth();
            $due = $base->copy()->day(min((int)($r->due_day ?: 1), $base->daysInMonth));
            $total = (float)$r->total;

            // pula faturas pagas: não aparecem no calendário
            if ((bool)$r->paid) continue;

            $firstName = explode(' ', trim((string)$r->cardholder_name))[0];

            if ($total > 0 && $due->betweenIncluded($winStart, $winEnd)) {
                $events->push([
                    'id' => (string)$r->id,
                    'title' => "Fatura {$firstName} {$r->last_four_digits}",
                    'start' => $due->toDateString(),
                    'bg' => '#be123c',
                    'icon' => 'fa-solid fa-credit-card',
                    'color' => '#ef4444',
                    'extendedProps' => [
                        'amount' => -abs($total), // despesa (negativo)
                        'amount_brl' => function_exists('brlPrice') ? brlPrice(abs($total)) : number_format(abs($total), 2, ',', '.'),
                        'category_name' => 'Fatura Cartão',
                        'type' => 'despesa',
                        'is_invoice' => true,
                        'paid' => false,
                        'card_id' => (string)$r->card_id,
                        'current_month' => $r->current_month,
                    ],
                ]);
            }
        }
        return $events;
    }

    private function ev($rid, $kind, $t, $cat, $type, Carbon $occ, float $amount, ?int $n = null): array
    {
        return [
            'id' => "rec_{$kind}_{$rid}_" . $occ->format('Ymd') . ($n ? "_$n" : ''),
            'title' => $t->title ?? $cat?->name,
            'start' => $occ->toDateString(),
            'bg' => $cat?->color,
            'icon' => $cat?->icon,
            'color' => $type === 'despesa' ? '#ef4444' : ($type === 'entrada' ? '#22c55e' : '#0ea5e9'),
            'extendedProps' => [
                'amount' => $type === 'entrada' ? abs($amount) : -abs($amount),
                'amount_brl' => brlPrice($amount),
                'category_name' => $cat?->name,
                'type' => $type,
                'transaction_id' => (string)$t->id, // <- importante
            ],
        ];
    }

    /** Títulos que representam “controle de fatura” (não devem aparecer no calendário) */
    private function isInvoiceControlTitle(?string $title): bool
    {
        $t = mb_strtolower(trim((string)$title));
        return in_array($t, ['total fatura', 'fatura total', 'total da fatura'], true);
    }

    private function buildInvoicesWidget(Collection $userIds, Carbon $today): array
    {
        // Carrega cartões do “grupo”
        $cards = Card::withoutGlobalScopes()
            ->whereIn('cards.user_id', $userIds)
            ->get(['id', 'cardholder_name', 'last_four_digits', 'closing_day', 'due_day', 'credit_limit', 'color_card']); // troque o nome se necessário

        $result = [];
        foreach ($cards as $card) {
            // ciclo do mês corrente
            $cycleMonth = CardCycle::cycleMonthFor($today, (int)$card->closing_day);

            $row = $this->invoiceRow($userIds, $card->id, $cycleMonth);

            // se está pago ou não existe → pega o próximo ciclo
            if (!$row || $row->paid) {
                $nextMonth = Carbon::createFromFormat('Y-m', $cycleMonth)->addMonth()->format('Y-m');
                $row = $this->invoiceRow($userIds, $card->id, $nextMonth);
                $cycleMonth = $nextMonth;
            }

            if (!$row) {
                // sem fatura e sem itens → ainda assim mostramos “zerada” para UX
                $due = Carbon::createFromFormat('Y-m', $cycleMonth)->startOfMonth()
                    ->day(min((int)($card->due_day ?: 1), Carbon::createFromFormat('Y-m', $cycleMonth)->daysInMonth));
                $result[] = [
                    'card_id' => (string)$card->id,
                    'title' => trim($card->cardholder_name) . ' ' . $card->last_four_digits,
                    'total' => 0.00,
                    'total_brl' => function_exists('brlPrice') ? brlPrice(0) : 'R$ 0,00',
                    'due_date' => $due->toDateString(),
                    'due_label' => $due->locale('pt_BR')->isoFormat('DD/MMM'),
                    'paid' => false,
                    'current_month' => $cycleMonth,
                    'color_card' => $card->color_card,
                    'available_limit' => $this->availableLimit($card, 0),
                ];
                continue;
            }

            $base = Carbon::createFromFormat('Y-m', $cycleMonth)->startOfMonth();
            $due = $base->copy()->day(min((int)($card->due_day ?: 1), $base->daysInMonth));
            $total = (float)$row->total;

            $result[] = [
                'card_id' => (string)$card->id,
                'title' => trim($card->cardholder_name) . ' ' . $card->last_four_digits,
                'total' => round($total, 2),
                'total_brl' => function_exists('brlPrice') ? brlPrice($total) : number_format($total, 2, ',', '.'),
                'due_date' => $due->toDateString(),
                'due_label' => $due->locale('pt_BR')->isoFormat('DD/MMM'),
                'paid' => (bool)$row->paid,
                'current_month' => $cycleMonth,
                'color_card' => $card->color_card,
                'available_limit' => $this->availableLimit($card, $total),
            ];
        }

        // sugestão de qual cartão usar:
        $tip = $this->suggestCardToUse($cards, $today);

        return [$result, $tip];
    }

    private function invoiceRow(Collection $userIds, string $cardId, string $cycleMonth): ?object
    {
        return DB::table('invoices as inv')
            ->leftJoin('invoice_items as it', 'it.invoice_id', '=', 'inv.id')
            ->whereIn('inv.user_id', $userIds)
            ->where('inv.card_id', $cardId)
            ->where('inv.current_month', $cycleMonth)
            ->groupBy('inv.id', 'inv.paid')
            ->select('inv.id', 'inv.paid', DB::raw('COALESCE(SUM(it.amount),0) as total'))
            ->first();
    }

    private function availableLimit(Card $card, float $openTotal): ?float
    {
        // ajuste o campo se necessário (ex.: $card->limit_total)
        if (!isset($card->credit_limit)) return null;
        return round((float)$card->credit_limit - max(0, $openTotal), 2);
    }

    private function suggestCardToUse(Collection $cards, Carbon $today): ?array
    {
        if ($cards->isEmpty()) return null;

        // Calcula último e próximo fechamento para cada cartão
        $data = $cards->map(function ($c) use ($today) {
            // Se tiver helpers CardCycle::lastClose/nextClose use-os; senão a lógica abaixo funciona igual
            $cm = Carbon::create($today->year, $today->month, 1);
            $closeThisMonth = $cm->copy()->day(min((int)$c->closing_day, $cm->daysInMonth));

            $lastClose = $today->gte($closeThisMonth)
                ? $closeThisMonth
                : $cm->copy()->subMonth()->day(min((int)$c->closing_day, $cm->copy()->subMonth()->daysInMonth));

            $nextClose = $today->lt($closeThisMonth)
                ? $closeThisMonth
                : $cm->copy()->addMonth()->day(min((int)$c->closing_day, $cm->copy()->addMonth()->daysInMonth));

            return [
                'card' => $c,
                'id' => (string)$c->id,
                'last4' => $c->last_four_digits,
                'color_card' => $c->color_card,
                'last_close' => $lastClose,
                'next_close' => $nextClose,
                'last_ts' => $lastClose->timestamp,
                'next_ts' => $nextClose->timestamp,
            ];
        });

        // Cartão atual = o que teve o último fechamento mais recente (<= hoje)
        $current = $data->sortByDesc('last_ts')->first();
        if (!$current) return null;

        // Entre os OUTROS cartões, qual tem o próximo fechamento mais cedo?
        $others = $data->filter(fn($x) => $x['id'] !== $current['id']);
        $switch = $others->sortBy('next_ts')->first(); // pode ser null se só existir 1 cartão

        // "Usar até" = próximo fechamento mais cedo entre os OUTROS; se não houver outros, usa o do próprio
        $useUntil = $switch['next_close'] ?? $current['next_close'];

        $useUntilLabel = strtoupper($useUntil->locale('pt_BR')->isoFormat('DD/MMM'));

        // Monta label: “Use o 2068 até 02/SET. Em seguida, use o 6277.”
        $label = "Utilize o cartão {$current['last4']} até {$useUntilLabel}.";
        if ($switch) {
            $label .= " Em seguida, use o {$switch['last4']}.";
        }

        return [
            'label' => $label,
            'color' => $current['color_card'] ?? '#000', // para colorir o ícone na view
            // extras úteis se quiser exibir/depurar
            'current' => [
                'id' => $current['id'],
                'last4' => $current['last4'],
                'last_close' => $current['last_close']->toDateString(),
                'next_close' => $current['next_close']->toDateString(),
                'use_until' => $useUntil->toDateString(),
            ],
            'next' => $switch ? [
                'id' => $switch['id'],
                'last4' => $switch['last4'],
                'next_close' => $switch['next_close']->toDateString(),
            ] : null,
        ];
    }

    private function buildUpcomingRecurrences(Collection $userIds, Carbon $today, int $limit = 5): \Illuminate\Support\Collection
    {
        $paid = $this->paymentsIndex($userIds);

        $rows = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->whereIn('recurrents.user_id', $userIds)
            ->whereHas('transaction', function ($q) {
                $q->whereIn('recurrence_type', ['monthly', 'yearly'])
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')->where('transactions.type_card', '!=', 'credit'));
                    });
            })
            ->get(['id', 'transaction_id', 'payment_day', 'amount']);

        $list = collect();

        foreach ($rows as $r) {
            $t = $r->transaction;
            if (!$t) continue;
            $cat = $t->transactionCategory;

            $start = \Carbon\Carbon::parse($t->date)->startOfDay(); // ÂNCORA REAL
            $payday = max(1, (int)$r->payment_day);

            if ($t->recurrence_type === 'monthly') {
                // primeira ocorrência no mês atual >= âncora
                $occ = $today->copy()->day(min($payday, $today->daysInMonth));
                while ($occ->lt($start)) {
                    $occ->addMonthNoOverflow()->day(min($payday, $occ->daysInMonth));
                }
            } else { // yearly
                $occ = \Carbon\Carbon::create($today->year, $start->month, 1)
                    ->day(min($payday, \Carbon\Carbon::create($today->year, $start->month, 1)->daysInMonth));
                if ($occ->lt($start)) $occ->addYear();
            }

            $ym = $occ->format('Y-m');
            if (!empty($paid[$t->id][$ym])) continue;

            $list->push([
                'kind' => 'tx',
                'id' => (string)$t->id,
                'date' => $occ->toDateString(),
                'title' => $t->title ?? $cat?->name,
                'amount' => (float)$r->amount,
                'color' => $cat?->color,
                'icon' => $cat?->icon,
                'modal_id' => (string)$t->id,
                'modal_amount' => (float)$r->amount,
                'modal_date' => $occ->toDateString(),
            ]);
        }

        return $list->sortBy('date')->take($limit)->values();
    }

    private function buildUpcomingInvoicesForList(Collection $userIds, Carbon $today, int $limit = 5): \Illuminate\Support\Collection
    {
        $rows = DB::table('invoices as inv')
            ->join('cards as c', 'c.id', '=', 'inv.card_id')
            ->leftJoin('invoice_items as it', 'it.invoice_id', '=', 'inv.id')
            ->whereIn('inv.user_id', $userIds)
            ->where('inv.paid', false)
            ->groupBy('inv.id', 'inv.card_id', 'inv.current_month', 'c.cardholder_name', 'c.last_four_digits', 'c.due_day')
            ->select(
                'inv.id', 'inv.card_id', 'inv.current_month',
                'c.cardholder_name', 'c.last_four_digits', 'c.due_day',
                DB::raw('COALESCE(SUM(it.amount),0) as total')
            )
            ->get();

        $list = collect();
        foreach ($rows as $r) {
            $base = Carbon::createFromFormat('Y-m', $r->current_month)->startOfMonth();
            $dueDay = (int)($r->due_day ?: 1);
            $due = $base->copy()->day(min($dueDay, $base->daysInMonth));
            $total = (float)$r->total;
            if ($total <= 0) continue; // ignora zeradas

            $list->push([
                'invoice_id' => (string)$r->id,
                'card_id' => (string)$r->card_id,
                'current_month' => $r->current_month,
                'title' => 'Fatura ' . trim($r->cardholder_name) . ' ' . $r->last_four_digits,
                'due_date' => $due->toDateString(),
                'total' => round($total, 2),
                'total_brl' => function_exists('brlPrice') ? brlPrice($total) : number_format($total, 2, ',', '.'),
                'overdue' => $due->lt($today),
            ]);
        }

        // Ordena: vencidas primeiro (mais urgentes), depois próximas por data
        return $list
            ->sortBy([
                ['overdue', 'desc'],
                ['due_date', 'asc'],
            ])
            ->take($limit)
            ->values();
    }

    private function kpisForMonth(Collection $userIds, Carbon $startOfMonth, Carbon $endOfMonth): array
    {
        return $this->kpisForRange($userIds, $startOfMonth, $endOfMonth);
    }

    public function kpis(Request $request)
    {
        $ownerId = AdditionalUser::ownerIdFor();
        $userIds = AdditionalUser::where('user_id', $ownerId)
            ->pluck('linked_user_id')->push($ownerId)->unique()->values();

        $monthParam = $request->query('month', now()->format('Y-m'));
        $cumulative = (int)$request->query('cumulative', 1); // 1 = projeção (padrão), 0 = apenas o mês

        $startOfMonth = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        $endOfMonth = (clone $startOfMonth)->endOfMonth();

        // Se cumulative=1: de HOJE até fim do mês alvo (projeção)
        // Se cumulative=0: do início ao fim do mês alvo (visão mensal)
        $rangeStart = $cumulative ? Carbon::today()->startOfDay() : $startOfMonth;

        $kpis = $this->kpisForRange($userIds, $rangeStart, $endOfMonth);

        // resposta padronizada para o JS
        return response()->json([
            'accountsBalance' => $kpis['accountsBalance'],
            'savingsBalance' => $kpis['savingsBalance'],        // novo
            'aReceber' => $kpis['aReceber'],
            'aPagar' => $kpis['aPagar'],
            'aReceber_mes_brl' => brlPrice($kpis['aReceber_mes'] ?? 0),
            'aReceber_atrasados_brl' => brlPrice($kpis['aReceber_atrasados'] ?? 0),
            'aPagar_mes_brl' => brlPrice($kpis['aPagar_mes'] ?? 0),
            'aPagar_atrasados_brl' => brlPrice($kpis['aPagar_atrasados'] ?? 0),
            'saldoPrevisto' => $kpis['saldoPrevisto'],
            'accountsBalance_brl' => brlPrice($kpis['accountsBalance']),
            'savingsBalance_brl' => brlPrice($kpis['savingsBalance']), // novo
            'aReceber_brl' => brlPrice($kpis['aReceber']),
            'aPagar_brl' => brlPrice($kpis['aPagar']),
            'saldoPrevisto_brl' => brlPrice($kpis['saldoPrevisto']),
        ]);

    }

    public function paymentTransaction(Request $request, Transaction $transaction)
{
    // ==== NORMALIZAÇÃO DO VALOR (pt-BR -> padrão numérico) ====
    $rawAmount = $request->input('amount');

    if (is_string($rawAmount)) {
        $rawAmount = trim($rawAmount);

        // Só converte se tiver vírgula (formato pt-BR)
        if ($rawAmount !== '' && str_contains($rawAmount, ',')) {
            // remove separador de milhar
            $normalized = str_replace('.', '', $rawAmount);
            // troca vírgula decimal por ponto
            $normalized = str_replace(',', '.', $normalized);

            // sobrescreve no request
            $request->merge([
                'amount' => $normalized,
            ]);
        }
    }

    // ==== AUTH por grupo ====
    $ownerId = AdditionalUser::ownerIdFor();
    $userIds = AdditionalUser::where('user_id', $ownerId)
        ->pluck('linked_user_id')->push($ownerId)->unique()->values();

    abort_unless(in_array($transaction->user_id, $userIds->all()), 403);

    $data = $request->validate([
        'amount'       => ['required', 'numeric'],
        'payment_date' => ['required', 'date'],
        'month'        => ['nullable', 'date_format:Y-m'], // mês selecionado na UI (para recalcular KPIs)
        'due_date'     => ['nullable', 'date'],            // NOVO: data da ocorrência/vencimento
        'account_id'   => ['nullable', 'integer'],
    ]);

    // UNIQUE: se já foi paga, 409 e sai antes da transação
    if (
        $transaction->recurrence_type === 'unique' &&
        PaymentTransaction::where('transaction_id', $transaction->id)->exists()
    ) {
        return response()->json(['ok' => false, 'message' => 'Transação única já foi paga.'], 409);
    }

    // ===== define $refDate (data de referência da ocorrência) =====
    if (!empty($data['due_date'])) {
        $refDate = Carbon::parse($data['due_date']);
    } elseif (
        in_array($transaction->recurrence_type, ['monthly', 'yearly'], true)
        && !empty($transaction->date)
    ) {
        $refDate = Carbon::parse($transaction->date);
    } elseif (!empty($data['month'])) {
        $refDate = Carbon::createFromFormat('Y-m', $data['month'])->startOfDay();
    } else {
        $refDate = Carbon::parse($data['payment_date']);
    }
$existsSameRef = PaymentTransaction::where('transaction_id', $transaction->id)
    ->whereDate('reference_date', $refDate->toDateString())
    ->exists();

if ($existsSameRef) {
    return response()->json(['ok' => false, 'message' => 'Essa ocorrência já foi paga.'], 409);
}
    DB::transaction(function () use ($transaction, $data, $userIds, $refDate) {
        // Escolha da conta
        $account = null;

        if (!empty($data['account_id'])) {
            $account = Account::whereIn('user_id', $userIds)
                ->where('id', $data['account_id'])
                ->first();
        }

        if (!$account && !empty($transaction->account_id)) {
            $account = Account::whereIn('user_id', $userIds)
                ->where('id', $transaction->account_id)
                ->first();
        }

        if (!$account) {
            $account = Account::whereIn('user_id', $userIds)->orderBy('id')->first();
        }

        // Cria o pagamento
        $pt = PaymentTransaction::create([
            'transaction_id'  => $transaction->id,
            'title'           => $transaction->title,
            'amount'          => (float) $data['amount'],
            'payment_date'    => $data['payment_date'],
            'reference_date'  => $refDate->toDateString(),
            'reference_month' => $refDate->format('m'),
            'reference_year'  => $refDate->format('Y'),
            'account_id'      => $account?->id,
        ]);

        // Movimenta saldo da conta (entrada incrementa, despesa decrementa)
        if ($account) {
            $type  = strtolower((string) optional($transaction->transactionCategory)->type);
            $value = abs((float) $pt->amount);

            if ($value == 0.0) {
                // pagamento “zerado” só serve pra marcar como quitado
                // não mexe em saldo
                return;
            }

            if ($type === 'entrada') {
                // aumenta saldo
                $account->increment('current_balance', $value);
            } else {
                // qualquer coisa que NÃO é entrada → trata como saída
                $account->decrement('current_balance', $value);
            }
        }
    });

    // Bump da âncora só para monthly/yearly SEM itens custom
    $shouldBump = false;
    if (in_array($transaction->recurrence_type, ['monthly', 'yearly'], true)) {
        $rec = Recurrent::where('transaction_id', $transaction->id)->first();
        $hasCustomItems = $rec
            ? DB::table('custom_item_recurrents')->where('recurrent_id', $rec->id)->exists()
            : false;
        $shouldBump = !$hasCustomItems;
    }

    if ($shouldBump) {
        $base = Carbon::parse($transaction->date);
        $transaction->update([
            'date' => $transaction->recurrence_type === 'monthly'
                ? $base->addMonthNoOverflow()
                : $base->addYear(),
        ]);
    }

    // Recalcula KPIs do mês atual da UI
    $month      = $data['month'] ?? now()->format('Y-m');
    $startMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
    $endMonth   = (clone $startMonth)->endOfMonth();

    $kpis = $this->kpisForMonth($userIds, $startMonth, $endMonth);

    return response()->json($this->kpisJson($kpis));
}

    private function kpisJson(array $k): array
    {
        $saldo = $k['saldoMes'] ?? ($k['saldoPrevisto'] ?? ($k['saldoReal'] ?? 0));

        return [
            'ok' => true,
            'accountsBalance' => (float)($k['accountsBalance'] ?? 0),
            'aReceber' => (float)($k['aReceber'] ?? 0),
            'aPagar' => (float)($k['aPagar'] ?? 0),
            'saldoMes' => (float)$saldo,
            'accountsBalance_brl' => brlPrice($k['accountsBalance'] ?? 0),
            'aReceber_brl' => brlPrice($k['aReceber'] ?? 0),
            'aPagar_brl' => brlPrice($k['aPagar'] ?? 0),
            'saldoMes_brl' => brlPrice($saldo),
        ];
    }

    private function kpisForRange(Collection $userIds, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $accountsBalance = (float)Account::whereIn('accounts.user_id', $userIds)->sum('current_balance');

        // totais projetados (transações/recorrentes)
        $proj = $this->sumProjectedEntriesAndExpenses($userIds, $rangeStart, $rangeEnd);
        $receivable = (float)($proj['aReceber'] ?? 0);

        // === NOVO: somar faturas no breakdown (mês vs atrasados)
        $monthStart = $rangeEnd->copy()->startOfMonth();

        $rows = DB::table('invoices as inv')
            ->leftJoin('invoice_items as it', 'it.invoice_id', '=', 'inv.id')
            ->join('cards as c', 'c.id', '=', 'inv.card_id')
            ->whereIn('inv.user_id', $userIds)
            ->where('inv.paid', false)
            ->groupBy('inv.id', 'inv.current_month', 'c.due_day')
            ->select('inv.current_month', 'c.due_day', DB::raw('COALESCE(SUM(it.amount),0) as total'))
            ->get();

        $invMes = 0.0;
        $invAtras = 0.0;

        foreach ($rows as $r) {
            $base = Carbon::createFromFormat('Y-m', $r->current_month)->startOfMonth();
            $due = $base->copy()->day(min((int)($r->due_day ?: 1), $base->daysInMonth));
            $total = (float)$r->total;
            if ($total <= 0) continue;

            if ($due->lt($monthStart)) {
                $invAtras += $total;                           // faturas vencidas antes do mês-alvo
            } elseif ($due->betweenIncluded($monthStart, $rangeEnd)) {
                $invMes += $total;                             // faturas que vencem no mês-alvo
            }
            // faturas com vencimento > $rangeEnd ficam fora desta visão
        }

        // soma final do a pagar: transações + faturas (mês + atrasados)
        $aPagar_mes = round(($proj['aPagar_mes'] ?? 0) + $invMes, 2);
        $aPagar_atrasados = round(($proj['aPagar_atrasados'] ?? 0) + $invAtras, 2);
        $aPagar = $aPagar_mes + $aPagar_atrasados;

        $saldoPrevisto = round($accountsBalance + $receivable - $aPagar, 2);
        $savingsBalance = (float)Saving::whereIn('savings.user_id', $userIds)->sum('current_amount');

        return [
            'accountsBalance' => round($accountsBalance, 2),
            'savingsBalance' => round($savingsBalance, 2),

            'aReceber' => round($receivable, 2),
            'aPagar' => round($aPagar, 2),
            'saldoPrevisto' => $saldoPrevisto,

            // breakdown (agora COM faturas)
            'aReceber_mes' => (float)($proj['aReceber_mes'] ?? 0),
            'aReceber_atrasados' => (float)($proj['aReceber_atrasados'] ?? 0),
            'aPagar_mes' => $aPagar_mes,
            'aPagar_atrasados' => $aPagar_atrasados,
        ];
    }

    private function paymentsIndex(Collection $userIds): array
{
    $rows = DB::table('payment_transactions as pt')
        ->join('transactions as t', 't.id', '=', 'pt.transaction_id')
        ->whereIn('t.user_id', $userIds)
        ->get([
            'pt.transaction_id',
            'pt.reference_year',
            'pt.reference_month',
            'pt.payment_date',
            'pt.reference_date', // nova coluna
        ]);

    $idx    = [];
    $byDate = [];

    foreach ($rows as $r) {
        if ($r->reference_year && $r->reference_month) {
            $ym = sprintf('%04d-%02d', (int) $r->reference_year, (int) $r->reference_month);
            $idx[$r->transaction_id][$ym] = true;
        }

        // PRIORIDADE: reference_date (data da ocorrência/vencimento)
        if (!empty($r->reference_date)) {
            $d = \Carbon\Carbon::parse($r->reference_date)->toDateString();
            $byDate[$r->transaction_id][$d] = true;
        } elseif (!empty($r->payment_date)) {
            // fallback para registros antigos sem reference_date
            $d = \Carbon\Carbon::parse($r->payment_date)->toDateString();
            $byDate[$r->transaction_id][$d] = true;
        }
    }

    $idx['_byDate'] = $byDate;

    return $idx;
}

    private function normType(?string $t): string
    {
        return trim(mb_strtolower((string)$t));
    }

    private function sumProjectedEntriesAndExpenses(Collection $userIds, Carbon $start, Carbon $end): array
    {
        $paid = $this->paymentsIndex($userIds);

        $monthStart = $end->copy()->startOfMonth();

        // breakdown
        $sumReceberMes = 0.0;
        $sumPagarMes = 0.0;
        $sumReceberOver = 0.0;
        $sumPagarOver = 0.0;

        /* ===== ATRASADOS (antes do mês alvo) ===== */

        // únicas atrasadas
        $uniqueOver = Transaction::withoutGlobalScopes()
            ->with(['transactionCategory:id,type'])
            ->whereIn('transactions.user_id', $userIds)
            ->where('recurrence_type', 'unique')
            ->whereDate('transactions.date', '<', $monthStart)
            ->where(function ($q) {
                $q->where('transactions.type', '!=', 'card')
                    ->orWhereNull('transactions.type')
                    ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                        ->where('transactions.type_card', '!=', 'credit'));
            })
            ->where(function ($q) {
                $q->whereNull('transactions.title')
                    ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', [
                        'total fatura', 'fatura total', 'total da fatura'
                    ]);
            })
            ->get(['id', 'amount', 'date', 'transaction_category_id']);

        foreach ($uniqueOver as $t) {
            $type = strtolower($t->transactionCategory?->type ?? '');
            $ym = Carbon::parse($t->date)->format('Y-m');
            if (!empty($paid[$t->id][$ym])) continue;
            $v = abs((float)$t->amount);
            if ($type === 'entrada') $sumReceberOver += $v;
            elseif ($type === 'despesa') $sumPagarOver += $v;
        }

        // monthly/yearly atrasados
        $recMYOver = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,type'])
            ->whereIn('recurrents.user_id', $userIds)
            ->whereHas('transaction', function ($q) {
                $q->whereIn('recurrence_type', ['monthly', 'yearly'])
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                                ->where('transactions.type_card', '!=', 'credit'));
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', [
                                'total fatura', 'fatura total', 'total da fatura'
                            ]);
                    });
            })
            ->get(['id', 'transaction_id', 'payment_day', 'amount']);

        $limit = $monthStart->copy()->subDay();

        foreach ($recMYOver as $r) {
            $t = $r->transaction;
            if (!$t) continue;
            $type = strtolower($t->transactionCategory?->type ?? '');
            $amount = abs((float)$r->amount);

            $anchor = Carbon::parse($t->date)->startOfDay();
            $payDay = max(1, (int)$r->payment_day);

            if ($t->recurrence_type === 'monthly') {
                $cur = $anchor->copy()->day(min($payDay, $anchor->daysInMonth));
                while ($cur->lte($limit)) {
                    $ym = $cur->format('Y-m');
                    if (empty($paid[$t->id][$ym])) {
                        if ($type === 'entrada') $sumReceberOver += $amount; else $sumPagarOver += $amount;
                    }
                    $cur->addMonthNoOverflow()->day(min($payDay, $cur->daysInMonth));
                }
            } else { // yearly
                $anchorMonth = (int)$anchor->month;
                $cur = Carbon::create($anchor->year, $anchorMonth, 1)
                    ->day(min($payDay, Carbon::create($anchor->year, $anchorMonth, 1)->daysInMonth));
                while ($cur->lt($anchor)) $cur->addYear();
                while ($cur->lte($limit)) {
                    $ym = $cur->format('Y-m');
                    if (empty($paid[$t->id][$ym])) {
                        if ($type === 'entrada') $sumReceberOver += $amount; else $sumPagarOver += $amount;
                    }
                    $cur->addYear();
                }
            }
        }

        // custom (itens) atrasados
        $recCOver = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,type'])
            ->whereIn('recurrents.user_id', $userIds)
            ->whereHas('transaction', fn($q) => $q->where('recurrence_type', 'custom'))
            ->get(['id', 'transaction_id']);

        foreach ($recCOver as $r) {
            $t = $r->transaction;
            if (!$t) continue;
            $type = strtolower($t->transactionCategory?->type ?? '');

            $items = CustomItemRecurrents::where('recurrent_id', $r->id)->get([
                'payment_day', 'reference_month', 'reference_year', 'amount'
            ]);

            foreach ($items as $ci) {
                $daysIn = Carbon::create($ci->reference_year, $ci->reference_month, 1)->daysInMonth;
                $occ = Carbon::create($ci->reference_year, $ci->reference_month, min((int)$ci->payment_day, $daysIn));
                if ($occ->gte($monthStart)) continue;

                $ym = $occ->format('Y-m');
                if (!empty($paid[$t->id][$ym])) continue;

                $v = abs((float)$ci->amount);
                if ($type === 'entrada') $sumReceberOver += $v; else $sumPagarOver += $v;
            }
        }

        // custom (a cada X dias) ATRASADOS (sem itens)
        $recDOver = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,type'])
            ->whereIn('recurrents.user_id', $userIds)
            ->where('interval_unit', 'days')
            ->whereHas('transaction', function ($q) {
                $q->where('recurrence_type', 'custom')
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                                ->where('transactions.type_card', '!=', 'credit'));
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', [
                                'total fatura', 'fatura total', 'total da fatura'
                            ]);
                    });
            })
            ->get(['recurrents.*']);

        foreach ($recDOver as $r) {
            // se tiver itens explicitados, já contamos acima
            if (DB::table('custom_item_recurrents')->where('recurrent_id', $r->id)->exists()) continue;

            $t = $r->transaction;
            if (!$t) continue;
            $type = strtolower(optional($t->transactionCategory)->type ?? '');
            if (!in_array($type, ['entrada', 'despesa', 'investimento'], true)) $type = 'investimento';

            $start = \Carbon\Carbon::parse($r->start_date)->startOfDay();
            $interval = max(1, (int)$r->interval_value);

            // primeira ocorrência alinhada >= start
            $cursor = $this->firstAlignedDays($start, $start, $interval);
            $cursor = $this->normalizeW($cursor, (bool)$r->include_sat, (bool)$r->include_sun);

            // conta todas as ocorrências antes do início do mês alvo
            while ($cursor->lt($monthStart)) {
                $ymd = $cursor->toDateString();
                if (empty(($paid['_byDate'][$t->id] ?? [])[$ymd])) {
                    $v = abs((float)$r->amount);
                    if ($type === 'entrada') $sumReceberOver += $v;
                    else                      $sumPagarOver += $v;
                }
                $cursor = $this->normalizeW(
                    $cursor->copy()->addDays($interval),
                    (bool)$r->include_sat,
                    (bool)$r->include_sun
                );
            }
        }

        /* ===== MÊS ALVO (monthStart..$end) ===== */

        // únicas no mês
        $uniqueTx = Transaction::withoutGlobalScopes()
            ->with(['transactionCategory:id,type'])
            ->whereIn('transactions.user_id', $userIds)
            ->where('recurrence_type', 'unique')
            ->whereBetween('transactions.date', [$monthStart, $end])
            ->where(function ($q) {
                $q->where('transactions.type', '!=', 'card')
                    ->orWhereNull('transactions.type')
                    ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                        ->where('transactions.type_card', '!=', 'credit'));
            })
            ->where(function ($q) {
                $q->whereNull('transactions.title')
                    ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', [
                        'total fatura', 'fatura total', 'total da fatura'
                    ]);
            })
            ->get(['id', 'amount', 'date', 'transaction_category_id']);

        foreach ($uniqueTx as $t) {
            $type = strtolower($t->transactionCategory?->type ?? '');
            $ym = Carbon::parse($t->date)->format('Y-m');
            if (!empty($paid[$t->id][$ym])) continue;

            $v = abs((float)$t->amount);
            if ($type === 'entrada') $sumReceberMes += $v; else if ($type === 'despesa') $sumPagarMes += $v;
        }

        // monthly/yearly no mês
        $recMY = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->whereIn('recurrents.user_id', $userIds)
            ->whereHas('transaction', function ($q) {
                $q->whereIn('recurrence_type', ['monthly', 'yearly'])
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')->where('transactions.type_card', '!=', 'credit'));
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', ['total fatura', 'fatura total', 'total da fatura']);
                    });
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('custom_item_recurrents as cir')
                    ->whereColumn('cir.recurrent_id', 'recurrents.id');
            })
            ->get(['recurrents.id', 'recurrents.user_id', 'recurrents.transaction_id', 'recurrents.payment_day', 'recurrents.amount']);

        foreach ($recMY as $r) {
            $t = $r->transaction;
            if (!$t) continue;
            $type = strtolower($t->transactionCategory?->type ?? '');
            $amount = abs((float)$r->amount);

            if ($t->recurrence_type === 'monthly') {
                $occ = $monthStart->copy()->day(min((int)$r->payment_day, $monthStart->daysInMonth));
                if ($occ->betweenIncluded($monthStart, $end) && $occ->gte(Carbon::parse($t->date))) {
                    $ym = $occ->format('Y-m');
                    if (empty($paid[$t->id][$ym])) {
                        if ($type === 'entrada') $sumReceberMes += $amount; else $sumPagarMes += $amount;
                    }
                }
            } else { // yearly
                $anchorMonth = (int)Carbon::parse($t->date)->month;
                if ($monthStart->month === $anchorMonth) {
                    $daysIn = $monthStart->daysInMonth;
                    $occ = Carbon::create($monthStart->year, $anchorMonth, min((int)$r->payment_day, $daysIn));
                    if ($occ->betweenIncluded($monthStart, $end) && $occ->gte(Carbon::parse($t->date))) {
                        $ym = $occ->format('Y-m');
                        if (empty($paid[$t->id][$ym])) {
                            if ($type === 'entrada') $sumReceberMes += $amount; else $sumPagarMes += $amount;
                        }
                    }
                }
            }
        }

        // custom (itens) no mês
        $recC = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,name,type,color,icon'])
            ->whereIn('recurrents.user_id', $userIds)
            ->whereHas('transaction', function ($q) {
                $q->whereIn('recurrence_type', ['custom', 'monthly', 'yearly'])
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')->where('transactions.type_card', '!=', 'credit'));
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', ['total fatura', 'fatura total', 'total da fatura']);
                    });
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('custom_item_recurrents as cir')
                    ->whereColumn('cir.recurrent_id', 'recurrents.id');
            })
            ->get(['recurrents.id', 'recurrents.user_id', 'recurrents.transaction_id']);

        foreach ($recC as $r) {
            $t = $r->transaction;
            if (!$t) continue;
            $type = strtolower($t->transactionCategory?->type ?? '');

            $items = CustomItemRecurrents::where('recurrent_id', $r->id)
                ->get(['payment_day', 'reference_month', 'reference_year', 'amount']);

            foreach ($items as $ci) {
                $daysIn = Carbon::create($ci->reference_year, $ci->reference_month, 1)->daysInMonth;
                $occ = Carbon::create($ci->reference_year, $ci->reference_month, min((int)$ci->payment_day, $daysIn));
                if (!$occ->betweenIncluded($monthStart, $end)) continue;

                $ymd = $occ->toDateString();
                if (!empty(($paid['_byDate'][$t->id] ?? [])[$ymd])) continue;

                $v = abs((float)$ci->amount);
                if ($type === 'entrada') $sumReceberMes += $v; else $sumPagarMes += $v;
            }
        }

        // custom “a cada X dias” no mês (sem itens) — FIX no continue
        $recD = Recurrent::withoutGlobalScopes()
            ->with(['transaction.transactionCategory:id,type'])
            ->whereIn('recurrents.user_id', $userIds)
            ->where('interval_unit', 'days')
            ->whereHas('transaction', function ($q) {
                $q->where('recurrence_type', 'custom')
                    ->where(function ($q2) {
                        $q2->where('transactions.type', '!=', 'card')
                            ->orWhereNull('transactions.type')
                            ->orWhere(fn($qq) => $qq->where('transactions.type', 'card')
                                ->where('transactions.type_card', '!=', 'credit'));
                    })
                    ->where(function ($q3) {
                        $q3->whereNull('transactions.title')
                            ->orWhereRaw('LOWER(transactions.title) NOT IN (?, ?, ?)', [
                                'total fatura', 'fatura total', 'total da fatura'
                            ]);
                    });
            })
            ->get(['recurrents.*']);

        foreach ($recD as $r) {
            if (DB::table('custom_item_recurrents')->where('recurrent_id', $r->id)->exists()) continue;

            $t = $r->transaction;
            if (!$t) continue;
            $type = strtolower($t->transactionCategory?->type ?? '');
            if (!in_array($type, ['entrada', 'despesa', 'investimento'], true)) $type = 'investimento';

            $start = Carbon::parse($r->start_date)->startOfDay();
            $interval = max(1, (int)$r->interval_value);

            $cursor = $this->firstAlignedDays($start, $monthStart, $interval);
            $cursor = $this->normalizeW($cursor, (bool)$r->include_sat, (bool)$r->include_sun);

            while ($cursor->lte($end)) {
                if ($cursor->lt($monthStart)) {
                    $cursor = $this->normalizeW(
                        $cursor->copy()->addDays($interval),
                        (bool)$r->include_sat,
                        (bool)$r->include_sun
                    );
                    continue;
                }

                $ymd = $cursor->toDateString();
                if (empty(($paid['_byDate'][$t->id] ?? [])[$ymd])) {
                    $v = abs((float)$r->amount);
                    if ($type === 'entrada') $sumReceberMes += $v;
                    else                      $sumPagarMes += $v;
                }

                $cursor = $this->normalizeW(
                    $cursor->copy()->addDays($interval),
                    (bool)$r->include_sat,
                    (bool)$r->include_sun
                );
            }
        }

        return [
            'aReceber' => $sumReceberMes + $sumReceberOver,
            'aPagar' => $sumPagarMes + $sumPagarOver,
            'aReceber_mes' => $sumReceberMes,
            'aReceber_atrasados' => $sumReceberOver,
            'aPagar_mes' => $sumPagarMes,
            'aPagar_atrasados' => $sumPagarOver,
        ];
    }

    private function normalizeW(Carbon $d, bool $sat, bool $sun): Carbon
    {
        if (!$sat && $d->isSaturday()) $d->addDays(2);
        if (!$sun && $d->isSunday()) $d->addDay();
        return $d;
    }

    /** primeira ocorrência alinhada ao intervalo >= $from  */
    private function firstAlignedDays(Carbon $start, Carbon $from, int $interval): Carbon
    {
        $s = $start->copy();
        if ($s->lt($from)) {
            $diff = $s->diffInDays($from);
            $steps = intdiv($diff + $interval - 1, $interval); // ceil
            $s->addDays($steps * $interval);
        }
        return $s;
    }
}
