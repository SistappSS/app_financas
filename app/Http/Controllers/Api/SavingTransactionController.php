<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use App\Models\Saving;
use App\Services\InvestmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SavingTransactionController extends Controller
{
    public function __construct(
        protected InvestmentService $investmentService
    ) {}

    /* -------------------------------------------------------------
     * Helper para converter valores monetários
     * ------------------------------------------------------------- */
    private function moneyToFloat($v): float
    {
        if ($v === null || $v === '') return 0.0;
        if (is_numeric($v)) return floatval($v);

        $s = preg_replace('/[^\d,.\-]/', '', (string)$v);
        $s = preg_replace('/\.(?=\d{3}(\D|$))/', '', $s);
        $s = str_replace(',', '.', $s);

        return floatval($s);
    }

    /* =======================================================
     * DEPÓSITO
     * POST /api/savings/{id}/deposit
     * ======================================================= */
    public function deposit(Request $request, $savingId)
    {
        $saving = Saving::where('user_id', Auth::id())
            ->findOrFail($savingId);

        $data = $request->validate([
            'amount'     => 'required',   // número já é convertido no helper
            'date'       => 'sometimes|date',
            'notes'      => 'nullable|string',
            'account_id' => [
                'nullable', 'uuid',
                Rule::exists('accounts', 'id')->where('user_id', Auth::id())
            ],
        ]);

        $amount  = $this->moneyToFloat($data['amount']);
        $date    = !empty($data['date']) ? Carbon::parse($data['date']) : Carbon::now();
        $account = !empty($data['account_id']) ? Account::find($data['account_id']) : null;

        if ($amount <= 0) {
            return response()->json([
                'message' => 'O valor do depósito deve ser maior que zero.'
            ], 422);
        }

        $lot = $this->investmentService->deposit(
            saving: $saving,
            amount: $amount,
            date: $date,
            account: $account,
            notes: $data['notes'] ?? 'Aporte'
        );

        return response()->json([
            'message' => 'Depósito realizado com sucesso.',
            'saving'  => $saving->fresh(['account', 'lots', 'lots.movements', 'lots.pendingYields']),
            'lot'     => $lot,
        ]);
    }

    /* =======================================================
     * SAQUE
     * POST /api/savings/{id}/withdraw
     * ======================================================= */
    public function withdraw(Request $request, $savingId)
    {
        $saving = Saving::where('user_id', Auth::id())
            ->findOrFail($savingId);

        $data = $request->validate([
            'amount'     => 'required',
            'date'       => 'sometimes|date',
            'notes'      => 'nullable|string',
            'account_id' => [
                'nullable', 'uuid',
                Rule::exists('accounts', 'id')->where('user_id', Auth::id())
            ],
        ]);

        $amount  = $this->moneyToFloat($data['amount']);
        $date    = !empty($data['date']) ? Carbon::parse($data['date']) : Carbon::now();
        $account = !empty($data['account_id']) ? Account::find($data['account_id']) : null;

        if ($amount <= 0) {
            return response()->json([
                'message' => 'O valor do saque deve ser maior que zero.'
            ], 422);
        }

        $this->investmentService->withdraw(
            saving: $saving,
            amount: $amount,
            date: $date,
            account: $account,
            notes: $data['notes'] ?? 'Saque'
        );

        return response()->json([
            'message' => 'Saque realizado com sucesso.',
            'saving'  => $saving->fresh(['account', 'lots', 'lots.movements', 'lots.pendingYields']),
        ]);
    }
}
