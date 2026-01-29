<?php

namespace App\Http\Controllers\Web;


use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class InvoiceItemController extends Controller
{
    public function index()
    {
        $invoices = Invoice::all();

        return view('app.invoices.invoice.invoice_index', compact('invoices'));
    }
}
