<?php

namespace App\Http\Controllers\Web;

use App\Models\InvoicePayment;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Card;
use App\Models\Invoice;

use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index($cardId)
    {
        Carbon::setLocale('pt_BR');
        $card  = Card::with('invoices.items')->findOrFail($cardId);

        // garante 12 meses a partir de hoje
        $today = Carbon::today();
        $startMonth = Carbon::create($today->year, $today->month, 1);
        for ($i = 0; $i < 12; $i++) {
            $m = $startMonth->copy()->addMonths($i)->format('Y-m');
            Invoice::firstOrCreate(
                ['user_id' => auth()->id(), 'card_id' => $card->id, 'current_month' => $m],
                ['paid' => false]
            );
        }

        $card->load('invoices.items.category');

        // ordena faturas por competência
        $today  = Carbon::today();
        $sorted = $card->invoices->sortBy('current_month')->values();

        // === 1) escolhe o mês padrão ===
        // 1º: primeira fatura em aberto com vencimento >= hoje (pendente atual / futura)
        $selectedYm = null;
        foreach ($sorted as $inv) {
            $dt  = Carbon::createFromFormat('Y-m', $inv->current_month);
            $due = Carbon::create($dt->year, $dt->month, $card->due_day);
            if (!$inv->paid && $due->greaterThanOrEqualTo($today)) {
                $selectedYm = $inv->current_month;
                break;
            }
        }
        // 2º: se não tiver, pega a primeira em aberto (atrasada)
        if (!$selectedYm) {
            foreach ($sorted as $inv) {
                if (!$inv->paid) {
                    $selectedYm = $inv->current_month;
                    break;
                }
            }
        }
        // 3º: fallback = competência atual
        if (!$selectedYm) {
            $selectedYm = $today->format('Y-m');
        }

        // header para o mês selecionado
        [$header, $items] = $this->buildInvoicePayload($card, $selectedYm);

        $categories = TransactionCategory::orderBy('name')->get();

        // carrossel com status (paid | pending | overdue)
        $invoices = $sorted->map(function ($inv) use ($card, $today) {
            $dt  = Carbon::createFromFormat('Y-m', $inv->current_month)->locale('pt_BR');
            $due = Carbon::create($dt->year, $dt->month, $card->due_day);

            if ($inv->paid) {
                $status = 'paid';
            } elseif ($due->lt($today)) {
                $status = 'overdue'; // vermelho
            } else {
                $status = 'pending'; // laranja
            }

            return (object)[
                'ym'     => $inv->current_month,
                'month'  => strtoupper($dt->isoFormat('MMM')),
                'paid'   => (bool) $inv->paid,
                'status' => $status,
                'total'  => brlPrice($inv->items->sum('amount')),
            ];
        })->values();

        return view('app.invoices.invoice.invoice_index', compact('card', 'invoices', 'header', 'items', 'selectedYm', 'categories'));
    }

    public function show($cardId, $ym)
    {
        $card = Card::with('invoices.items.category')->findOrFail($cardId);

        [$header, $items] = $this->buildInvoicePayload($card, $ym);

        return response()->json(compact('header','items'));
    }

    public function update(Request $request, $cardId, $ym)
    {
        $card = Card::with('invoices.items')->findOrFail($cardId);

        /** @var \App\Models\Invoice|null $invoice */
        $invoice = $card->invoices()->where('current_month', $ym)->first();

        if (!$invoice) {
            return back()->with('error', 'Fatura não encontrada.');
        }

        if ($invoice->paid) {
            return back()->with('info', 'Fatura já estava paga.');
        }

        // valor calculado padrão (soma dos itens)
        $calculatedAmount = (float) DB::table('invoice_items')
            ->where('invoice_id', $invoice->id)
            ->sum('amount');

        $data = $request->validate([
            'amount'    => ['nullable', 'numeric', 'min:0'],
            'paid_at'   => ['nullable', 'date'],
            'method'    => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $amount = $data['amount'] ?? $calculatedAmount;
        $paidAt = !empty($data['paid_at'])
            ? Carbon::parse($data['paid_at'])->endOfDay()
            : now();

        $method    = $data['method']    ?? 'manual';
        $reference = $data['reference'] ?? $ym;

        DB::transaction(function () use ($invoice, $amount, $paidAt, $method, $reference) {
            InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'user_id'    => auth()->id(),
                'amount'     => $amount,
                'paid_at'    => $paidAt,
                'method'     => $method,
                'reference'  => $reference,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $invoice->update(['paid' => true]);
        });

        return back()->with('success', 'Fatura marcada como paga.');
    }

    private function buildInvoicePayload(Card $card, string $ym): array
    {
        $today = Carbon::today();

        $dt   = Carbon::createFromFormat('Y-m', $ym);
        $close= Carbon::create($dt->year, $dt->month, $card->closing_day);
        $due  = Carbon::create($dt->year, $dt->month, $card->due_day);

        $inv = $card->invoices->firstWhere('current_month', $ym);
        $items = collect();
        $monthTotal = 0;

        if ($inv) {
            // garante que vem número
            $monthTotal = (float) $inv->items->sum('amount');
            $items = $inv->items()
                ->with('category')
                ->orderBy('date')
                ->get()
                ->map(function (\App\Models\InvoiceItem $it){
                    return (object)[
                        'id' => $it->id,
                        'title' => $it->title,
                        'date' => \Carbon\Carbon::parse($it->date)->format('d/m/Y'),
                        'amount_raw' => (float) $it->amount,
                        'amount' => brlPrice($it->amount),
                        'installments' => (int)$it->installments,
                        'current_installment' => (int)$it->current_installment,
                        'is_projection' => (bool)$it->is_projection,
                        'icon' => optional($it->category)->icon,
                        'color' => optional($it->category)->color,
                    ];
                });
        }

        $selectedYm = $ym;

        $blocked = \App\Models\InvoiceItem::query()
            ->whereHas('invoice', function ($q) use ($card, $selectedYm) {
                $q->where('card_id', $card->id)
                    ->where('current_month', '<=', $selectedYm)
                    ->where('paid', false);
            })
            ->get()
            ->sum(function ($item) {
                if ($item->installments > 1) {
                    $restantes = $item->installments - $item->current_installment + 1;
                    return ($item->amount) * $restantes;
                }
                return $item->amount;
            });

        $limitAvail = max(0, $card->credit_limit - $blocked);

        // status da fatura
        $status = 'pending';
        if ($inv && $inv->paid) {
            $status = 'paid';
        } elseif ($due->lt($today)) {
            $status = 'overdue';
        }

        $header = [
            'ym'           => $ym,
            'invoice_id'   => optional($inv)->id,   // <--- NOVO
            'month_label'  => ucfirst($dt->locale('pt_BR')->isoFormat('MMMM')),
            'paid'         => (bool) optional($inv)->paid,
            'status'       => $status,
            'total'        => brlPrice($monthTotal),
            'total_raw'    => $monthTotal,
            'limit'        => brlPrice($limitAvail),
            'close_label'  => 'Fecha em <b>'.strtoupper($close->isoFormat('DD MMM')).'</b>',
            'due_label'    => 'Vence em <b>'.strtoupper($due->isoFormat('DD MMM')).'</b>',
        ];

        return [$header, $items];
    }
}
