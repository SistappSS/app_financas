<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionCategoryController extends Controller
{
    public function index()
    {
        return view('app.transactions.transaction_category.transaction_category_index');
    }
}
