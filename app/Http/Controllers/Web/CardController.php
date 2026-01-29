<?php

namespace App\Http\Controllers\Web;

use App\Models\Account;
use App\Models\Card;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        $cards = Card::all();
        return view('app.cards.card_index', compact('accounts',  'cards'));
    }
}
