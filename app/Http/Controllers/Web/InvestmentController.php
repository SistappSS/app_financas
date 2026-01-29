<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    public function index()
    {
        // Busca todos os investimentos do usuário
        $investments = Investment::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get(); // Não limitamos colunas para trazer created_at

        return view('app.investments.investment_index', compact('investments'));
    }
}
