<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
//    public function paymentTransaction(Request $request, Transaction $transaction)
//    {
//        abort_unless($transaction->user_id === auth()->id(), 403);
//
//        $data = $request->validate([
//            'amount'       => ['required','numeric'],
//            'payment_date' => ['required','date'],
//        ]);
//
//        DB::transaction(function () use ($transaction, $data) {
//            PaymentTransaction::create([
//                'transaction_id'  => $transaction->id,
//                'title'           => $transaction->title,
//                'amount'          => $data['amount'],
//                'payment_date'    => $data['payment_date'],
//                'reference_month' => Carbon::parse($transaction->date)->format('m'),
//                'reference_year'  => Carbon::parse($transaction->date)->format('Y'),
//            ]);
//
//            $next = Carbon::parse($transaction->date)->addMonthNoOverflow();
//            $transaction->update(['date' => $next]);
//        });
//
//        return back()->with('success', 'Pagamento registrado e próxima data atualizada.');
//    }
}
