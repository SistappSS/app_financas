<?php

namespace App\Http\Controllers\Api;

use App\Models\Recurrent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class RecurrentController extends Controller
{
    public function index()
    {
        return response()->json(
            Auth::user()->recurrents()->orderBy('next_date')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'type' => 'required|in:income,expense',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'next_date' => 'required|date',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'account_id' => 'nullable|uuid|exists:accounts,id'
        ]);

        $recurrent = Auth::user()->recurrents()->create($data);

        return response()->json($recurrent, 201);
    }

    public function show(Recurrent $recurrent)
    {
        $this->authorize('view', $recurrent);
        return response()->json($recurrent);
    }

    public function update(Request $request, Recurrent $recurrent)
    {
        $this->authorize('update', $recurrent);

        $data = $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric',
            'type' => 'sometimes|in:income,expense',
            'frequency' => 'sometimes|in:daily,weekly,monthly,yearly',
            'next_date' => 'sometimes|date',
            'category_id' => 'nullable|uuid|exists:categories,id',
            'account_id' => 'nullable|uuid|exists:accounts,id'
        ]);

        $recurrent->update($data);

        return response()->json($recurrent);
    }

    public function destroy(Recurrent $recurrent)
    {
        $this->authorize('delete', $recurrent);
        $recurrent->delete();

        return response()->json(null, 204);
    }
}
