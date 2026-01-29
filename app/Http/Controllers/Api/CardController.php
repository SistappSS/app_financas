<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use App\Models\Card;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public $card;

    public function __construct(Card $card)
    {
        $this->card = $card;
    }

    public function index()
    {
        $cards = $this->card::with('account')->get();

        $cards->each(function ($card) {
            $card->cardholder_name = strtoupper($card->cardholder_name);
            $card->credit_limit = brlPrice($card->credit_limit);

            if($card->account && $card->account->bank_name) {
                $card->account->bank_name = strtoupper($card->account->bank_name);
            }

            $currentMonth = Carbon::now()->format('Y-m');
            $invoice = $card->invoices()->where('current_month', $currentMonth)->first();

            if($invoice) {
                $card->invoice_total = $invoice->items()->sum('amount');
            }
        });


        return response()->json($cards);
    }

    public function store(Request $request)
    {
        $card = $this->card->with('account')->create([
            'user_id' => Auth::id(),
            'account_id' => $request->account_id,
            'cardholder_name' => $request->cardholder_name,
            'last_four_digits' => $request->last_four_digits,
            'brand' => $request->brand,
            'color_card' => $request->color_card,
            'credit_limit' => $request->credit_limit,
            'closing_day' => $request->closing_day,
            'due_day' => $request->due_day,
        ]);

        $card->cardholder_name = strtoupper($card->cardholder_name);
        $card->credit_limit = brlPrice($card->credit_limit);

        if($card->account && $card->account->bank_name) {
            $card->account->bank_name = strtoupper($card->account->bank_name);
        }

        return response()->json($card);
    }

    public function show($id)
    {
        $card = $this->card->with('account')->find($id);

        if($card) {
            return response()->json($card);
        } else {
            return response()->json('Nenhum card foi encontrado');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'credit_limit' => 'sometimes|numeric',
            'closing_day' => 'sometimes|integer|min:1|max:31',
            'due_day' => 'sometimes|integer|min:1|max:31',
            'account_id' => 'nullable|uuid|exists:accounts,id',
        ]);

        $card = Card::where('id', $id)->first();

        $card->update([
            'account_id' => $request->account_id,
            'cardholder_name' => $request->cardholder_name,
            'last_four_digits' => $request->last_four_digits,
            'brand' => $request->brand,
            'color_card' => $request->color_card,
            'credit_limit' => $request->credit_limit,
            'closing_day' => $request->closing_day,
            'due_day' => $request->due_day,
        ]);

        return response()->json($card);
    }

    public function destroy($id)
    {
        $card = $this->card->where('id', $id)->first();

        $card->delete();

        return response()->noContent();
    }
}
