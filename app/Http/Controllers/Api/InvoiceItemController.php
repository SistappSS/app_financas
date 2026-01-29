<?php

namespace App\Http\Controllers\Api;

use App\Helpers\CardCycle;
use App\Models\Card;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceItemController extends Controller
{
    public function index()
    {
        $items = InvoiceItem::with('category')
            ->where('user_id', Auth::id())
            ->get();

        $items->each(function ($item) {
            $item->amount = brlPrice($item->amount);
            $item->date   = Carbon::parse($item->date)->format('d/m/Y');
            $item->current_installment = $item->current_installment.'x';
        });

        return response()->json($items);
    }

    public function show(InvoiceItem $invoiceItem)
    {
        // formata para o formulário do modal
        return response()->json([
            'id'                  => $invoiceItem->id,
            'title'               => $invoiceItem->title,
            'amount'              => (float) $invoiceItem->amount,
            'date'                => optional($invoiceItem->date)->format('Y-m-d'),
            'installments'        => (int) $invoiceItem->installments,
            'current_installment' => (int) $invoiceItem->current_installment,
            'is_projection'       => (bool) $invoiceItem->is_projection,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_id'             => ['required', 'uuid', 'exists:invoices,id'],
            'title'                  => ['required', 'string', 'max:255'],
            'amount'                 => ['required', 'numeric', 'min:0'],
            'date'                   => ['required', 'date'],
            'installments'           => ['nullable', 'integer', 'min:1'],
            'current_installment'    => ['nullable', 'integer', 'min:1'],
            'transaction_category_id'=> ['nullable','uuid','exists:transaction_categories,id'],
        ]);

        $userId = Auth::id();

        // fatura atual (a que está selecionada na tela)
        /** @var \App\Models\Invoice $currentInvoice */
        $currentInvoice = Invoice::with('card')
            ->where('user_id', $userId)
            ->findOrFail($data['invoice_id']);

        $card = $currentInvoice->card;
        $date = \Carbon\Carbon::parse($data['date'])->startOfDay();

        // calcula em qual competência essa compra cai (regra de fechamento)
        $cycleYm = CardCycle::cycleMonthFor($date, (int) $card->closing_day); // "Y-m"

        // fatura correta para a data informada
        $targetInvoice = Invoice::firstOrCreate(
            [
                'user_id'       => $userId,
                'card_id'       => $card->id,
                'current_month' => $cycleYm,
            ],
            ['paid' => false]
        );

        $item = InvoiceItem::create([
            'user_id'                 => $userId,
            'invoice_id'              => $targetInvoice->id,
            'card_id'                 => $card->id,
            'title'                   => $data['title'],
            'amount'                  => $data['amount'],
            'date'                    => $date,
            'installments'            => $data['installments']        ?? 1,
            'current_installment'     => $data['current_installment'] ?? 1,
            'transaction_category_id' => $data['transaction_category_id'] ?? null,
            'is_projection'           => false,
        ]);

        return response()->json($item->fresh(), 201);
    }

    public function update(Request $request, InvoiceItem $invoiceItem)
    {
        $data = $request->validate([
            'title'   => ['sometimes','string','max:255'],
            'amount'  => ['sometimes','numeric','min:0'],
            'date'    => ['sometimes','date'],
        ]);

        DB::transaction(function () use ($invoiceItem, $data) {
            $invoiceItem->load('invoice.card');

            $oldInvoice = $invoiceItem->invoice;
            $card       = $oldInvoice->card;

            $newDate   = isset($data['date'])
                ? Carbon::parse($data['date'])->startOfDay()
                : Carbon::parse($invoiceItem->date)->startOfDay();

            $newTitle  = $data['title']  ?? $invoiceItem->title;
            $newAmount = $data['amount'] ?? $invoiceItem->amount;

            // <<< recalcula competência pela NOVA data >>>
            $newYm = CardCycle::cycleMonthFor($newDate, (int) $card->closing_day);

            if ($oldInvoice->current_month !== $newYm) {
                $newInvoice = Invoice::firstOrCreate(
                    [
                        'user_id'       => $oldInvoice->user_id,
                        'card_id'       => $card->id,
                        'current_month' => $newYm,
                    ],
                    ['paid' => false]
                );
                $invoiceItem->invoice_id = $newInvoice->id;
            }

            $invoiceItem->title  = $newTitle;
            $invoiceItem->amount = $newAmount;
            $invoiceItem->date   = $newDate;
            $invoiceItem->save();

            if ($invoiceItem->transaction_id) {
                Transaction::where('id', $invoiceItem->transaction_id)->update([
                    'title'       => $newTitle,
                    'amount'      => $newAmount,
                    'date'        => $newDate,
                    'create_date' => $newDate,
                ]);
            }
        });

        return response()->json($invoiceItem->fresh());
    }

    public function destroy(InvoiceItem $invoiceItem)
    {
        $invoiceItem->delete();
        return response()->json(null, 204);
    }
}
