<?php

namespace App\Http\Controllers\Api;

use App\Models\AdditionalUser;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
    public function index()
    {
        $invoice = $this->invoice::with('account')->get();

        $invoice->each(function($invoice){
            $invoice->card = strtoupper($invoice->card->name);
            $invoice->current_month = strtoupper($invoice->current_month);
            $invoice->paid =  strtoupper($invoice->paid);
        });

        return response()->json($invoice);
    }

    public function store(Request $request)
    {
        $invoice = $this->invoice::with('account')->create([
            'user_id' => Auth::id(),
            'card_id' => $request->card_id,
            'current_month' => $request->month,
            'paid' => $request->paid,
        ]);

        $invoice->each(function($invoice){
            $invoice->card = strtoupper($invoice->card->name);
            $invoice->current_month = strtoupper($invoice->current_month);
            $invoice->paid =  strtoupper($invoice->paid);
        });

        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        return response()->json($invoice);
    }

    public function update(Request $request, string $cardId, string $ym)
    {
        // grupo do usuário
        $ownerId = AdditionalUser::ownerIdFor();
        $userIds = AdditionalUser::where('user_id', $ownerId)
            ->pluck('linked_user_id')->push($ownerId)->unique()->values();

        // valida ym
        try { Carbon::createFromFormat('Y-m', $ym); }
        catch (\Throwable) { return response()->json(['message' => 'YM inválido (use Y-m).'], 422); }

        // encontra a fatura
        $invoice = Invoice::whereIn('user_id', $userIds)
            ->where('card_id', $cardId)
            ->where('current_month', $ym)
            ->first();

        if (!$invoice) return response()->json(['message' => 'Fatura não encontrada.'], 404);
        if ($invoice->paid) return response()->json(['message' => 'Fatura já paga.'], 200);

        // total da fatura
        $total = (float) DB::table('invoice_items')->where('invoice_id', $invoice->id)->sum('amount');
        if ($total <= 0) return response()->json(['message' => 'Nada a pagar nesta fatura.'], 422);

        $data = $request->validate([
            'amount'    => ['nullable', 'numeric', 'min:0'],
            'paid_at'   => ['nullable', 'date'],
            'method'    => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $amount = $data['amount'] ?? $total;
        $paidAt = !empty($data['paid_at'])
            ? Carbon::parse($data['paid_at'])
            : now();

        $method    = $data['method']    ?? $request->string('method', 'other');
        $reference = $data['reference'] ?? $request->string('reference');

        try {
            DB::transaction(function () use ($invoice, $amount, $paidAt, $method, $reference, $request) {
                InvoicePayment::create([
                    'invoice_id' => $invoice->id,
                    'user_id'    => $request->user()->id,
                    'amount'     => $amount,
                    'paid_at'    => $paidAt,
                    'method'     => $method,
                    'reference'  => $reference,
                ]);

                $invoice->update(['paid' => true]);
            });
        } catch (\Throwable $e) {
            // evita duplicidade (unique invoice_id)
            return response()->json(['message' => 'Pagamento já registrado.'], 409);
        }

        return response()->json([
            'invoice_id'    => (string) $invoice->id,
            'card_id'       => (string) $invoice->card_id,
            'current_month' => $invoice->current_month,
            'paid'          => true,
            'amount'        => $total,
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();

        return response()->json(null, 204);
    }
}
