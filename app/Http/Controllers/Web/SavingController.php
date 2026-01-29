<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

class SavingController extends Controller
{
    public function index()
    {
        $accounts = Account::where('user_id', Auth::id())
            ->orderBy('bank_name')
            ->get(['id','bank_name','current_balance']);

        return view('app.savings.saving_index', compact('accounts'));
    }
}
