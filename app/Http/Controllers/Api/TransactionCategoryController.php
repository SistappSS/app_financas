<?php

namespace App\Http\Controllers\Api;

use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TransactionCategoryController extends Controller
{
    public function index()
    {
        // Se quiser escopar por usuário, troque por: ->where('user_id', Auth::id())->get();
        $transactionCategories = TransactionCategory::all();

        $transactionCategories->each(function ($c) {
            $c->monthly_limit = brlPrice($c->monthly_limit);
            // manter compatível com o front (ele faz strtolower):
            $c->type = ucfirst($c->type);
        });

        return response()->json($transactionCategories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => ['required', Rule::in(['entrada','despesa','investimento'])],
            'has_limit'      => ['nullable', Rule::in(['0','1'])],
            'monthly_limit'  => 'nullable|numeric',
            'color'          => 'nullable|string|max:50',
            'icon'           => 'nullable|string|max:100',
        ]);

        $limit = ($data['has_limit'] ?? '0') === '1'
            ? ($data['monthly_limit'] ?? 0)
            : 0;

        $category = TransactionCategory::create([
            'user_id'       => Auth::id(),
            'name'          => $data['name'],
            'type'          => $data['type'],
            'monthly_limit' => $limit,
            'color'         => $data['color'] ?? '#3b82f6',
            'icon'          => $data['icon'] ?? 'fa-solid fa-tags',
        ]);

        // Formata para o front (mesmo do index)
        $category->monthly_limit = brlPrice($category->monthly_limit);
        $category->type = ucfirst($category->type);

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = TransactionCategory::findOrFail($id);

        // formatação uniforme como no index
        $category->monthly_limit = brlPrice($category->monthly_limit);
        $category->type = ucfirst($category->type);

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = TransactionCategory::findOrFail($id);

        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'type'           => ['sometimes', Rule::in(['entrada','despesa','investimento'])],
            'has_limit'      => ['nullable', Rule::in(['0','1'])],
            'monthly_limit'  => 'nullable|numeric',
            'color'          => 'nullable|string|max:50',
            'icon'           => 'nullable|string|max:100',
        ]);

        // recalcula limite se enviado "has_limit"
        if (array_key_exists('has_limit', $data)) {
            $limit = ($data['has_limit'] ?? '0') === '1'
                ? ($data['monthly_limit'] ?? 0)
                : 0;
            $data['monthly_limit'] = $limit;
        }

        $category->update($data);

        // responde formatado de forma consistente
        $category->refresh();
        $category->monthly_limit = brlPrice($category->monthly_limit);
        $category->type = ucfirst($category->type);

        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = TransactionCategory::findOrFail($id);
        $category->delete();

        return response()->json(null, 204);
    }
}
