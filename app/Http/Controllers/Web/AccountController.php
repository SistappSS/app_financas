<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function index()
    {
        $accounts = $this->account->all();

        return view('app.accounts.account_index', compact('accounts'));
    }


}
