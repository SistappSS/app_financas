<?php

// app/Http/Controllers/Api/InvestmentController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvestmentController extends Controller
{
    // GET /api/investments
    public function index()
    {
        $items = Investment::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($items);
    }

    // POST /api/investments
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'purchase_value' => 'required|numeric|min:0',
            'interest_rate'  => 'required|numeric|min:0', // em %
            'rate_period'    => 'nullable|in:monthly,yearly',
            'start_date'     => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();
        $data['rate_period'] = $data['rate_period'] ?? 'monthly';

        $inv = Investment::create($data);

        return response()->json($inv, 201);
    }

    // GET /api/investments/{investment}
    public function show(Investment $investment)
    {
        abort_if($investment->user_id !== Auth::id(), 403);
        return response()->json($investment);
    }

    // PUT/PATCH /api/investments/{investment}
    public function update(Request $request, Investment $investment)
    {
        abort_if($investment->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'purchase_value' => 'sometimes|numeric|min:0',
            'interest_rate'  => 'sometimes|numeric|min:0', // em %
            'rate_period'    => 'sometimes|in:monthly,yearly',
            'start_date'     => 'sometimes|date|nullable',
            'notes'          => 'sometimes|string|nullable',
        ]);

        $investment->update($data);

        return response()->json($investment);
    }

    // DELETE /api/investments/{investment}
    public function destroy(Investment $investment)
    {
        abort_if($investment->user_id !== Auth::id(), 403);

        $investment->delete();

        return response()->json(null, 204);
    }
}

