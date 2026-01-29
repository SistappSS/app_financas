<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AdditionalUser;

class ChartController extends Controller
{
    public function pie(Request $req)
    {
        $ownerId = AdditionalUser::ownerIdFor();
        $userIds = AdditionalUser::query()
            ->where('user_id', $ownerId)
            ->pluck('linked_user_id')
            ->push($ownerId)
            ->unique()
            ->values();

        [$start, $end] = $this->monthBounds($req->string('month')->toString());
        $ym = $req->string('month')->toString();
        $currentMonth = $ym && preg_match('/^\d{4}-\d{2}$/', $ym) ? $ym : now()->format('Y-m');

        $mode  = $req->string('mode', 'tx')->toString(); // tx | invoice
        $level = $req->string('level', $mode === 'invoice' ? 'invoice_cards' : 'type')->toString();

        // params comuns
        $type       = $req->string('type')->toString();
        $categoryId = $req->string('category_id')->toString();
        $pay        = $req->string('pay')->toString();
        $cardType   = $req->string('card_type')->toString();
        $invoiceId  = $req->string('invoice_id')->toString();

        // filtro de status (Todos / Pagos / Não pagos)
        $status = $req->string('status', 'all')->toString();
        if (!in_array($status, ['all','paid','unpaid'], true)) $status = 'all';

        $rangeStart = Carbon::parse($start);
        $rangeEnd   = Carbon::parse($end);

        // =======================
        // MODO: TRANSAÇÕES (sempre por "events" do mês)
        // =======================
        if ($mode === 'tx') {
            $breadcrumbs = [];

            // Carrega todos os "events" do mês alvo e aplica o filtro de status
            $events = $this->projectEventsForRange($userIds, $rangeStart, $rangeEnd, $currentMonth);
            $events = $this->filterEventsByStatus($events, $status);

            if ($level === 'type') {
                $sum = ['entrada' => 0.0, 'despesa' => 0.0, 'investimento' => 0.0];
                foreach ($events as $e) {
                    if (!in_array($e['type'], ['entrada','despesa','investimento'], true)) continue;
                    $sum[$e['type']] += abs((float)$e['amount']);
                }

                $rows = collect([
                    ['label' => 'Entrada',     'key' => 'entrada',     'color' => '#a6e3a1'],
                    ['label' => 'Despesa',     'key' => 'despesa',     'color' => '#f38ba8'],
                    ['label' => 'Investimento','key' => 'investimento','color' => '#89b4fa'],
                ])->map(fn($r) => [
                    'id'    => $r['key'],
                    'label' => $r['label'],
                    'value' => round($sum[$r['key']] ?? 0, 2),
                    'color' => $r['color'],
                    'next'  => ['level' => 'category', 'params' => ['type' => $r['key']]],
                ])->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'tx', 'level' => 'type', 'title' => 'Tipos',
                    'breadcrumbs' => [], 'items' => $rows->values(), 'total' => $rows->sum('value'),
                ]);
            }

            if ($level === 'category' && $type) {
                $breadcrumbs[] = ['label' => 'Tipos', 'level' => 'type', 'params' => []];

                // Agrupa por categoria (inclui "Faturas" como categoria sintética)
                $byCat = [];
                foreach ($events as $e) {
                    if ($e['type'] !== $type) continue;

                    $isInv = !empty($e['is_invoice']);
                    $cid   = $isInv ? '__INVOICES__' : ($e['category_id'] ?? '__none__');
                    $label = $isInv ? 'Faturas' : ($e['category_name'] ?? '—');
                    $color = $isInv ? '#8b5cf6' : ($e['color'] ?? '#94a3b8');

                    if (!isset($byCat[$cid])) $byCat[$cid] = ['label' => $label, 'color' => $color, 'value' => 0.0];
                    $byCat[$cid]['value'] += abs((float)$e['amount']);
                }

                $rows = collect($byCat)
                    ->map(fn($v, $cid) => [
                        'id'    => $cid,
                        'label' => $v['label'],
                        'value' => round($v['value'], 2),
                        'color' => $v['color'],
                        'next'  => $cid === '__INVOICES__'
                            ? ['level' => 'invoice_cards_tx', 'params' => []]
                            : ['level' => 'pay', 'params' => ['type' => $type, 'category_id' => $cid]],
                    ])
                    ->sortByDesc('value')->values()
                    ->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'tx', 'level' => 'category', 'title' => 'Categorias (' . ucfirst($type) . ')',
                    'breadcrumbs' => $breadcrumbs, 'items' => $rows, 'total' => $rows->sum('value'),
                ]);
            }

            if ($level === 'pay' && $categoryId) {
                $breadcrumbs[] = ['label' => 'Tipos', 'level' => 'type', 'params' => []];
                $breadcrumbs[] = ['label' => ucfirst($type), 'level' => 'category', 'params' => ['type' => $type]];

                $map = ['pix' => 0.0, 'money' => 0.0, 'card' => 0.0];
                foreach ($events as $e) {
                    if (($e['category_id'] ?? null) != $categoryId || !in_array($e['type'], ['entrada','despesa','investimento'], true)) continue;
                    $k = $e['pay'] ?: 'money';
                    if (!isset($map[$k])) $map[$k] = 0.0;
                    $map[$k] += abs((float)$e['amount']);
                }

                $mapLabel = ['pix' => 'PIX', 'money' => 'Dinheiro', 'card' => 'Cartão'];
                $mapColor = ['pix' => '#a6e3a1', 'money' => '#f9e2af', 'card' => '#89b4fa'];

                $rows = collect($map)
                    ->filter(fn($v) => $v > 0)
                    ->map(fn($v, $k) => [
                        'id'    => $k,
                        'label' => $mapLabel[$k] ?? strtoupper($k),
                        'value' => round($v, 2),
                        'color' => $mapColor[$k] ?? '#18dec7',
                        'next'  => ['level' => $k === 'card' ? 'card_type' : 'instrument', 'params' => ['type' => $type, 'category_id' => $categoryId, 'pay' => $k]],
                    ])->values()->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'tx', 'level' => 'pay', 'title' => 'Forma de pagamento',
                    'breadcrumbs' => $breadcrumbs, 'items' => $rows, 'total' => $rows->sum('value'),
                ]);
            }

            if ($level === 'card_type' && $categoryId) {
                $breadcrumbs[] = ['label' => 'Tipos', 'level' => 'type', 'params' => []];
                $breadcrumbs[] = ['label' => ucfirst($type), 'level' => 'category', 'params' => ['type' => $type]];
                $breadcrumbs[] = ['label' => 'Cartão', 'level' => 'pay', 'params' => ['type' => $type, 'category_id' => $categoryId]];

                $map = ['credit' => 0.0, 'debit' => 0.0, 'desconhecido' => 0.0];
                foreach ($events as $e) {
                    if (($e['category_id'] ?? null) != $categoryId || $e['pay'] !== 'card') continue;
                    $k = $e['type_card'] ?: 'desconhecido';
                    if (!isset($map[$k])) $map[$k] = 0.0;
                    $map[$k] += abs((float)$e['amount']);
                }

                $rows = collect($map)->filter(fn($v) => $v > 0)->map(function ($v, $k) {
                    $nice = ['credit' => 'Crédito', 'debit' => 'Débito']['' . $k] ?? ucfirst($k);
                    return [
                        'id'    => $k,
                        'label' => $nice,
                        'value' => round($v, 2),
                        'color' => $k === 'credit' ? '#89b4fa' : '#94e2d5',
                        'next'  => ['level' => 'instrument', 'params' => ['pay' => 'card', 'card_type' => $k]],
                    ];
                })->values()->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'tx', 'level' => 'card_type', 'title' => 'Crédito x Débito',
                    'breadcrumbs' => $breadcrumbs, 'items' => $rows, 'total' => $rows->sum('value'),
                ]);
            }

            if ($level === 'instrument') {
                $breadcrumbs[] = ['label' => 'Tipos', 'level' => 'type', 'params' => []];
                if ($type)       $breadcrumbs[] = ['label' => ucfirst($type), 'level' => 'category', 'params' => ['type' => $type]];
                if ($categoryId) $breadcrumbs[] = ['label' => 'Pagamento', 'level' => 'pay', 'params' => ['type' => $type, 'category_id' => $categoryId]];

                if ($pay === 'card') {
                    // por cartão
                    $map = [];
                    foreach ($events as $e) {
                        if ($e['pay'] !== 'card') continue;
                        if ($categoryId && ($e['category_id'] ?? null) != $categoryId) continue;
                        if ($cardType  && ($e['type_card'] ?? null)   !== $cardType) continue;
                        $cid = (string)($e['card_id'] ?? '_none');
                        if (!isset($map[$cid])) $map[$cid] = 0.0;
                        $map[$cid] += abs((float)$e['amount']);
                    }

                    $cardMeta = DB::table('cards')->whereIn('id', array_keys(array_filter($map, fn($v) => $v > 0)))
                        ->get(['id','cardholder_name','last_four_digits','color_card'])->keyBy('id');

                    $items = collect($map)->filter(fn($v) => $v > 0)->map(function ($v, $cid) use ($cardMeta) {
                        $c = $cardMeta->get($cid);
                        $label = $c ? ($c->cardholder_name . ' • ' . str_pad((string)$c->last_four_digits, 4, '0', STR_PAD_LEFT)) : '(Cartão)';
                        return ['id' => $cid, 'label' => $label, 'value' => round($v, 2), 'color' => $c->color_card ?? '#a78bfa', 'next' => null];
                    })->values()->map(fn($r) => self::paint($r));

                    return response()->json([
                        'mode' => 'tx', 'level' => 'instrument', 'title' => 'Cartões',
                        'breadcrumbs' => $breadcrumbs, 'items' => $items, 'total' => $items->sum('value'),
                    ]);
                }

                // PIX/Dinheiro → por account_id
                $map = [];
                foreach ($events as $e) {
                    if ($e['pay'] !== $pay) continue;
                    if ($categoryId && ($e['category_id'] ?? null) != $categoryId) continue;
                    $aid = (string)($e['account_id'] ?? '_none');
                    if (!isset($map[$aid])) $map[$aid] = 0.0;
                    $map[$aid] += abs((float)$e['amount']);
                }

                $accMeta = DB::table('accounts')->whereIn('id', array_keys(array_filter($map, fn($v) => $v > 0)))
                    ->get(['id','bank_name'])->keyBy('id');

                $items = collect($map)->filter(fn($v) => $v > 0)->map(function ($v, $aid) use ($accMeta) {
                    $a = $accMeta->get($aid);
                    $label = $a?->bank_name ?? '(Sem conta)';
                    return ['id' => $aid, 'label' => $label, 'value' => round($v, 2), 'color' => self::colorForKey($aid . $label), 'next' => null];
                })->values()->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'tx', 'level' => 'instrument', 'title' => 'Contas',
                    'breadcrumbs' => $breadcrumbs, 'items' => $items, 'total' => $items->sum('value'),
                ]);
            }

            // Expansão da categoria "Faturas"
            if ($level === 'invoice_cards_tx') {
                $breadcrumbs[] = ['label' => 'Tipos', 'level' => 'type', 'params' => []];
                $breadcrumbs[] = ['label' => 'Despesa', 'level' => 'category', 'params' => ['type' => 'despesa']];
                $breadcrumbs[] = ['label' => 'Faturas', 'level' => 'invoice_cards_tx', 'params' => []];

                $q = DB::table('invoices as i')
                    ->join('invoice_items as it', 'it.invoice_id', '=', 'i.id')
                    ->join('cards as k', 'k.id', '=', 'i.card_id')
                    ->whereIn('i.user_id', $userIds)
                    ->where('i.current_month', $currentMonth)
                    ->when($status !== 'all', fn($q) => $q->where('i.paid', $status === 'paid'))
                    ->selectRaw('k.id, CONCAT(k.cardholder_name," • ",LPAD(COALESCE(k.last_four_digits,0),4,"0")) as label,
                                COALESCE(k.color_card,"#a78bfa") as color, SUM(it.amount) as value')
                    ->groupBy('k.id','k.cardholder_name','k.last_four_digits','k.color_card')
                    ->orderByDesc('value')->get();

                $rows = $q->map(fn($r) => [
                    'id' => $r->id, 'label' => $r->label, 'value' => (float)$r->value, 'color' => $r->color,
                    'next' => ['level' => 'invoice_items_tx', 'params' => ['card_id' => $r->id]],
                ])->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'tx', 'level' => 'invoice_cards_tx', 'title' => 'Faturas por cartão',
                    'breadcrumbs' => $breadcrumbs, 'items' => $rows, 'total' => $rows->sum('value'),
                ]);
            }

            if ($level === 'invoice_items_tx') {
                $cardId = $req->string('card_id')->toString();

                $breadcrumbs[] = ['label' => 'Tipos', 'level' => 'type', 'params' => []];
                $breadcrumbs[] = ['label' => 'Despesa', 'level' => 'category', 'params' => ['type' => 'despesa']];
                $breadcrumbs[] = ['label' => 'Faturas', 'level' => 'invoice_cards_tx', 'params' => []];

                $q = DB::table('invoice_items as it')
                    ->join('invoices as i', 'i.id', '=', 'it.invoice_id')
                    ->join('transaction_categories as c', 'c.id', '=', 'it.transaction_category_id')
                    ->whereIn('i.user_id', $userIds)
                    ->where('i.card_id', $cardId)
                    ->where('i.current_month', $currentMonth)
                    ->when($status !== 'all', fn($q) => $q->where('i.paid', $status === 'paid'))
                    ->selectRaw('it.id, COALESCE(it.title,c.name) as label, it.amount as value, it.date,
                                 COALESCE(c.color,"#94a3b8") as color')
                    ->orderByDesc('it.date')->get();

                $rows = $q->map(fn($r) => [
                    'id' => $r->id,
                    'label' => $r->label . ' — ' . Carbon::parse($r->date)->format('d/m'),
                    'value' => (float)$r->value,
                    'color' => $r->color,
                    'next'  => null,
                ])->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'tx', 'level' => 'invoice_items_tx', 'title' => 'Itens da fatura',
                    'breadcrumbs' => $breadcrumbs, 'items' => $rows, 'total' => $rows->sum('value'),
                ]);
            }
        }

        // =======================
        // MODO: FATURAS
        // =======================
        if ($mode === 'invoice') {
            // Nível 1: faturas por cartão no mês (respeita status)
            if ($level === 'invoice_cards') {
                $rows = DB::table('invoices as i')
                    ->leftJoin('invoice_items as it', 'it.invoice_id', '=', 'i.id')
                    ->join('cards as k', 'k.id', '=', 'i.card_id')
                    ->whereIn('i.user_id', $userIds)
                    ->where('i.current_month', $currentMonth)
                    ->when($status !== 'all', fn($q) => $q->where('i.paid', $status === 'paid'))
                    ->selectRaw('i.id, k.id as card_id,
                        CONCAT(k.cardholder_name, " • ", LPAD(COALESCE(k.last_four_digits,0),4,"0")) as label,
                        COALESCE(k.color_card,"#a78bfa") as color,
                        COALESCE(SUM(it.amount),0) as items_total,
                        MAX(i.paid) as is_paid')
                    ->groupBy('i.id','k.id','k.cardholder_name','k.last_four_digits','k.color_card')
                    ->orderBy('label')->get()
                    ->map(function ($r) {
                        return [
                            'id'    => $r->id,
                            'label' => $r->label . ($r->is_paid ? ' (pago)' : ''),
                            'value' => (float)$r->items_total,
                            'color' => $r->color,
                            'next'  => ['level' => 'invoice_categories', 'params' => ['invoice_id' => $r->id]],
                        ];
                    })->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'invoice', 'level' => 'invoice_cards', 'title' => 'Faturas do mês',
                    'breadcrumbs' => [['label' => 'Faturas', 'level' => 'invoice_cards', 'params' => []]],
                    'items' => $rows, 'total' => collect($rows)->sum('value'),
                ]);
            }

            // Nível 2: categorias dentro da fatura
            if ($level === 'invoice_categories' && $invoiceId) {
                $card = DB::table('invoices as i')
                    ->join('cards as k', 'k.id', '=', 'i.card_id')
                    ->where('i.id', $invoiceId)
                    ->selectRaw('CONCAT(k.cardholder_name," • ",LPAD(COALESCE(k.last_four_digits,0),4,"0")) as label')->first();

                $rows = DB::table('invoice_items as it')
                    ->join('transaction_categories as c', 'c.id', '=', 'it.transaction_category_id')
                    ->where('it.invoice_id', $invoiceId)
                    ->selectRaw('c.id, c.name as label, COALESCE(c.color,"#18dec7") as color, SUM(it.amount) as value')
                    ->groupBy('c.id','c.name','c.color')
                    ->orderByDesc('value')->get()
                    ->map(fn($r) => [
                        'id' => $r->id, 'label' => $r->label, 'value' => (float)$r->value, 'color' => $r->color,
                        'next' => ['level' => 'invoice_items', 'params' => ['invoice_id' => $invoiceId, 'category_id' => $r->id]],
                    ])->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'invoice', 'level' => 'invoice_categories', 'title' => 'Categorias da fatura',
                    'breadcrumbs' => [
                        ['label' => 'Faturas', 'level' => 'invoice_cards', 'params' => []],
                        ['label' => $card?->label ?? 'Cartão', 'level' => 'invoice_categories', 'params' => ['invoice_id' => $invoiceId]],
                    ],
                    'items' => $rows, 'total' => collect($rows)->sum('value'),
                ]);
            }

            // Nível 3: itens da fatura (opcionalmente filtrados por categoria)
            if ($level === 'invoice_items' && $invoiceId) {
                $categoryId = $req->string('category_id')->toString();
                $catName = $categoryId ? DB::table('transaction_categories')->where('id', $categoryId)->value('name') : null;

                $q = DB::table('invoice_items as it')
                    ->join('transaction_categories as c', 'c.id', '=', 'it.transaction_category_id')
                    ->where('it.invoice_id', $invoiceId)
                    ->when($categoryId, fn($q) => $q->where('it.transaction_category_id', $categoryId))
                    ->whereBetween('it.date', [$start, $end])
                    ->selectRaw('it.id, COALESCE(it.title,c.name) as label, it.amount as value, it.date,
                                COALESCE(c.color,"#94a3b8") as color')
                    ->orderByDesc('it.date')->get();

                $rows = $q->map(fn($r) => [
                    'id' => $r->id,
                    'label' => $r->label . ' — ' . Carbon::parse($r->date)->format('d/m'),
                    'value' => (float)$r->value,
                    'color' => $r->color,
                    'next' => null,
                ])->map(fn($r) => self::paint($r));

                return response()->json([
                    'mode' => 'invoice', 'level' => 'invoice_items', 'title' => $catName ? "Itens • $catName" : 'Itens da fatura',
                    'breadcrumbs' => [
                        ['label' => 'Faturas', 'level' => 'invoice_cards', 'params' => []],
                        ['label' => 'Categorias', 'level' => 'invoice_categories', 'params' => ['invoice_id' => $invoiceId]],
                    ],
                    'items' => $rows, 'total' => $rows->sum('value'),
                ]);
            }
        }

        return response()->json(['mode' => $mode, 'level' => $level, 'items' => [], 'breadcrumbs' => [], 'total' => 0]);
    }

    private function monthBounds(?string $ym): array
    {
        $m = $ym && preg_match('/^\d{4}-\d{2}$/', $ym) ? Carbon::createFromFormat('Y-m', $ym) : now();
        $start = $m->copy()->startOfMonth()->toDateString();
        $end   = $m->copy()->endOfMonth()->toDateString();
        return [$start, $end];
    }

    private static function withAlpha(string $hex, string $alpha = '82'): string
    {
        $hex = trim($hex);
        if ($hex === '') return '#94a3b8' . $alpha;

        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        } elseif (strlen($hex) === 8) {
            $hex = substr($hex, 0, 6);
        } elseif (strlen($hex) !== 6) {
            return '#'.$hex;
        }
        return '#'.$hex.$alpha;
    }

    private static function paint(array $row): array
    {
        $base = $row['color'] ?? self::colorForKey(($row['id'] ?? $row['label'] ?? 'x'));
        $row['color']  = $base;
        $row['border'] = $base;
        $row['bg']     = self::withAlpha($base, '1a');
        return $row;
    }

    private static function colorForKey(string $key): string
    {
        $palette = [
            '#89b4fa', '#a6e3a1', '#f38ba8', '#94e2d5', '#f9e2af',
            '#fab387', '#cba6f7', '#f5c2e7', '#74c7ec', '#b4befe',
            '#8bd5ca', '#eed49f'
        ];
        $i = abs(crc32($key)) % count($palette);
        return $palette[$i];
    }

    private function paymentsIndex($userIds): array
    {
        $rows = DB::table('payment_transactions as pt')
            ->join('transactions as t','t.id','=','pt.transaction_id')
            ->whereIn('t.user_id', $userIds)
            ->get(['pt.transaction_id','pt.reference_year','pt.reference_month','pt.payment_date']);

        $idx = []; $byDate = [];
        foreach ($rows as $r) {
            if ($r->reference_year && $r->reference_month) {
                $ym = sprintf('%04d-%02d', (int)$r->reference_year, (int)$r->reference_month);
                $idx[$r->transaction_id][$ym] = true;     // por mês de referência (vencimento)
            }
            if (!empty($r->payment_date)) {
                $d = Carbon::parse($r->payment_date)->toDateString();
                $byDate[$r->transaction_id][$d] = true;   // por data exata de pagamento
            }
        }
        $idx['_byDate'] = $byDate;
        return $idx;
    }

    private function firstAlignedDays(Carbon $start, Carbon $from, int $interval): Carbon
    {
        $s = $start->copy();
        if ($s->lt($from)) {
            $diff  = $s->diffInDays($from);
            $steps = intdiv($diff + $interval - 1, $interval);
            $s->addDays($steps * $interval);
        }
        return $s;
    }

    private function normalizeW(Carbon $d, bool $sat, bool $sun): Carbon
    {
        if (!$sat && $d->isSaturday()) $d->addDays(2);
        if (!$sun && $d->isSunday())   $d->addDay();
        return $d;
    }

    /**
     * Gera eventos (pagos e não pagos) do intervalo, + faturas do mês.
     * Retorna rows canônicos:
     * ['type','amount','category_id','category_name','color','pay','type_card','account_id','card_id','is_invoice','paid']
     *
     * FIX: recorrências mensais/anuais agora entram mesmo se a âncora (t.date) já tiver sido "pulada"
     * pelo pagamento adiantado — desde que a ocorrência esteja no mês alvo OU já conste como paga.
     */
    private function projectEventsForRange($userIds, Carbon $rangeStart, Carbon $rangeEnd, string $currentMonth): array
    {
        $out  = [];
        $paid = $this->paymentsIndex($userIds);
        $monthStart = $rangeEnd->copy()->startOfMonth();

        // === ÚNICAS no intervalo (SEM excluir pagas)
        $unique = DB::table('transactions as t')
            ->join('transaction_categories as c','c.id','=','t.transaction_category_id')
            ->whereIn('t.user_id', $userIds)
            ->where('t.recurrence_type','unique')
            ->whereBetween('t.date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->where(function ($q) {
                $q->where('t.type','!=','card')
                  ->orWhereNull('t.type')
                  ->orWhere(fn($qq)=>$qq->where('t.type','card')->where('t.type_card','!=','credit'));
            })
            ->where(function ($q) {
                $q->whereNull('t.title')
                  ->orWhereRaw('LOWER(t.title) NOT IN (?, ?, ?)', ['total fatura','fatura total','total da fatura']);
            })
            ->get([
                't.id','t.amount','t.date as occ_date','t.type as pay','t.type_card','t.account_id','t.card_id',
                'c.id as category_id','c.name as category_name','c.type as cat_type','c.color'
            ]);

        foreach ($unique as $r) {
            $type = in_array($r->cat_type, ['entrada','despesa','investimento'], true) ? $r->cat_type : 'investimento';
            $ym   = Carbon::parse($r->occ_date)->format('Y-m');
            $ymd  = Carbon::parse($r->occ_date)->toDateString();
            $isPaid = !empty($paid[$r->id][$ym]) || !empty(($paid['_byDate'][$r->id] ?? [])[$ymd]);

            $out[] = [
                'type' => $type,
                'amount' => abs((float)$r->amount),
                'category_id' => (string)$r->category_id,
                'category_name' => $r->category_name,
                'color' => $r->color,
                'pay' => $r->pay,
                'type_card' => $r->type_card,
                'account_id' => $r->account_id,
                'card_id' => $r->card_id,
                'is_invoice' => false,
                'paid' => $isPaid,
            ];
        }

        // === RECORRENTES monthly/yearly (sem itens custom) — FIX âncora x pagamento adiantado
        $recMY = DB::table('recurrents as r')
            ->join('transactions as t','t.id','=','r.transaction_id')
            ->join('transaction_categories as c','c.id','=','t.transaction_category_id')
            ->whereIn('r.user_id', $userIds)
            ->whereIn('t.recurrence_type',['monthly','yearly'])
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('custom_item_recurrents as cir')
                  ->whereColumn('cir.recurrent_id','r.id');
            })
            ->where(function ($q) {
                $q->where('t.type','!=','card')
                  ->orWhereNull('t.type')
                  ->orWhere(fn($qq)=>$qq->where('t.type','card')->where('t.type_card','!=','credit'));
            })
            ->where(function ($q) {
                $q->whereNull('t.title')
                  ->orWhereRaw('LOWER(t.title) NOT IN (?, ?, ?)', ['total fatura','fatura total','total da fatura']);
            })
            ->get([
                'r.id as rid','r.payment_day','r.amount',
                't.id as tid','t.date as tdate','t.created_at as tcreated','t.type as pay','t.type_card','t.account_id','t.card_id','t.recurrence_type',
                'c.id as category_id','c.name as category_name','c.type as cat_type','c.color'
            ]);

        foreach ($recMY as $r) {
            $created  = Carbon::parse($r->tcreated)->startOfDay();
            $tStart   = Carbon::parse($r->tdate)->startOfDay();
            $anchor   = $created->lt($tStart) ? $created : $tStart; // usa a mais antiga para não perder mês pago adiantado
            $type     = in_array($r->cat_type, ['entrada','despesa','investimento'], true) ? $r->cat_type : 'investimento';

            if ($r->recurrence_type === 'monthly') {
                $occ = $monthStart->copy()->day(min((int)$r->payment_day, $monthStart->daysInMonth));
                if ($occ->betweenIncluded($rangeStart, $rangeEnd)) {
                    $ym  = $occ->format('Y-m');
                    $ymd = $occ->toDateString();
                    $isPaid = !empty($paid[$r->tid][$ym]) || !empty(($paid['_byDate'][$r->tid] ?? [])[$ymd]);

                    // inclui se (ocorrência >= âncora) OU se já constar como paga
                    if ($occ->gte($anchor) || $isPaid) {
                        $out[] = [
                            'type'=>$type,'amount'=>abs((float)$r->amount),
                            'category_id'=>(string)$r->category_id,'category_name'=>$r->category_name,'color'=>$r->color,
                            'pay'=>$r->pay,'type_card'=>$r->type_card,'account_id'=>$r->account_id,'card_id'=>$r->card_id,
                            'is_invoice'=>false,'paid'=>$isPaid,
                        ];
                    }
                }
            } else { // yearly
                $anchorMonth = (int) Carbon::parse($r->tdate)->month;
                if ((int)$monthStart->month === $anchorMonth) {
                    $daysIn = $monthStart->daysInMonth;
                    $occ = Carbon::create($monthStart->year, $anchorMonth, min((int)$r->payment_day, $daysIn));
                    if ($occ->betweenIncluded($rangeStart, $rangeEnd)) {
                        $ym  = $occ->format('Y-m');
                        $ymd = $occ->toDateString();
                        $isPaid = !empty($paid[$r->tid][$ym]) || !empty(($paid['_byDate'][$r->tid] ?? [])[$ymd]);

                        if ($occ->gte($anchor) || $isPaid) {
                            $out[] = [
                                'type'=>$type,'amount'=>abs((float)$r->amount),
                                'category_id'=>(string)$r->category_id,'category_name'=>$r->category_name,'color'=>$r->color,
                                'pay'=>$r->pay,'type_card'=>$r->type_card,'account_id'=>$r->account_id,'card_id'=>$r->card_id,
                                'is_invoice'=>false,'paid'=>$isPaid,
                            ];
                        }
                    }
                }
            }
        }

        // === RECORRENTES custom (itens explícitos)
        $recC = DB::table('recurrents as r')
            ->join('transactions as t','t.id','=','r.transaction_id')
            ->join('transaction_categories as c','c.id','=','t.transaction_category_id')
            ->join('custom_item_recurrents as ci','ci.recurrent_id','=','r.id')
            ->whereIn('r.user_id', $userIds)
            ->get([
                't.id as tid','t.type as pay','t.type_card','t.account_id','t.card_id',
                'c.id as category_id','c.name as category_name','c.type as cat_type','c.color',
                'ci.reference_year','ci.reference_month','ci.payment_day','ci.amount'
            ]);

        foreach ($recC as $r) {
            $type = in_array($r->cat_type, ['entrada','despesa','investimento'], true) ? $r->cat_type : 'investimento';
            $daysIn = Carbon::create($r->reference_year, $r->reference_month, 1)->daysInMonth;
            $occ    = Carbon::create($r->reference_year, $r->reference_month, min((int)$r->payment_day, $daysIn));
            if (!$occ->betweenIncluded($rangeStart, $rangeEnd)) continue;

            $ymd   = $occ->toDateString();
            $isPaid = !empty(($paid['_byDate'][$r->tid] ?? [])[$ymd]);

            $out[] = [
                'type'=>$type,'amount'=>abs((float)$r->amount),
                'category_id'=>(string)$r->category_id,'category_name'=>$r->category_name,'color'=>$r->color,
                'pay'=>$r->pay,'type_card'=>$r->type_card,'account_id'=>$r->account_id,'card_id'=>$r->card_id,
                'is_invoice'=>false,'paid'=>$isPaid,
            ];
        }

        // === RECORRENTES custom "a cada X dias" (sem itens)
        $recD = DB::table('recurrents as r')
            ->join('transactions as t','t.id','=','r.transaction_id')
            ->join('transaction_categories as c','c.id','=','t.transaction_category_id')
            ->whereIn('r.user_id', $userIds)
            ->where('r.interval_unit','days')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('custom_item_recurrents as cir')
                  ->whereColumn('cir.recurrent_id','r.id');
            })
            ->get(['r.*','t.id as tid','t.type as pay','t.type_card','t.account_id','t.card_id','c.id as category_id','c.name as category_name','c.type as cat_type','c.color']);

        foreach ($recD as $r) {
            $type = in_array($r->cat_type, ['entrada','despesa','investimento'], true) ? $r->cat_type : 'investimento';
            $start    = Carbon::parse($r->start_date)->startOfDay();
            $interval = max(1, (int)$r->interval_value);
            $cursor = $this->firstAlignedDays($start, $rangeStart->copy()->startOfDay(), $interval);
            $cursor = $this->normalizeW($cursor, (bool)$r->include_sat, (bool)$r->include_sun);

            while ($cursor->lte($rangeEnd)) {
                $ymd    = $cursor->toDateString();
                $isPaid = !empty(($paid['_byDate'][$r->tid] ?? [])[$ymd]);

                $out[] = [
                    'type'=>$type,'amount'=>abs((float)$r->amount),
                    'category_id'=>(string)$r->category_id,'category_name'=>$r->category_name,'color'=>$r->color,
                    'pay'=>$r->pay,'type_card'=>$r->type_card,'account_id'=>$r->account_id,'card_id'=>$r->card_id,
                    'is_invoice'=>false,'paid'=>$isPaid,
                ];

                $cursor = $this->normalizeW(
                    $cursor->copy()->addDays($interval),
                    (bool)$r->include_sat,
                    (bool)$r->include_sun
                );
            }
        }

        // === FATURAS do mês (pagas e não pagas)
        $rows = DB::table('invoices as inv')
            ->join('cards as c','c.id','=','inv.card_id')
            ->leftJoin('invoice_items as it','it.invoice_id','=','inv.id')
            ->whereIn('inv.user_id', $userIds)
            ->where('inv.current_month', $currentMonth)
            ->groupBy('inv.id','inv.card_id','inv.current_month','c.due_day','inv.paid')
            ->select('inv.id','inv.card_id','inv.current_month','c.due_day','inv.paid', DB::raw('COALESCE(SUM(it.amount),0) as total'))
            ->get();

        foreach ($rows as $r) {
            $base  = Carbon::createFromFormat('Y-m', $r->current_month)->startOfMonth();
            $due   = $base->copy()->day(min((int)($r->due_day ?: 1), $base->daysInMonth));
            $total = (float)$r->total;

            if ($total > 0 && $due->betweenIncluded($rangeStart, $rangeEnd)) {
                $out[] = [
                    'type'=>'despesa','amount'=>abs($total),
                    'category_id'=>null,'category_name'=>'Faturas','color'=>'#8b5cf6',
                    'pay'=>'card','type_card'=>'credit','account_id'=>null,'card_id'=>$r->card_id,
                    'is_invoice'=>true,'current_month'=>$r->current_month,
                    'paid'=>(bool)$r->paid,
                ];
            }
        }

        return $out;
    }

    private function filterEventsByStatus(array $events, string $status): array
    {
        if ($status === 'all') return $events;
        $want = ($status === 'paid');
        return array_values(array_filter($events, fn($e) => (bool)($e['paid'] ?? false) === $want));
    }
}