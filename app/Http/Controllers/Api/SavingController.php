<?php

namespace App\Http\Controllers\Api;

use App\Models\Saving;
use App\Models\Account;
use App\Services\InvestmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SavingController extends Controller
{
    public function __construct(
        protected InvestmentService $investmentService
    ) {}

    /* ============================================================
     *  Helper — converter entrada monetária para float
     * ============================================================ */
    private function moneyToFloat($v): float
    {
        if ($v === null || $v === '') return 0.0;
        if (is_numeric($v)) return (float)$v;

        $s = preg_replace('/[^\d,.\-]/', '', (string)$v);
        $s = preg_replace('/\.(?=\d{3}(\D|$))/', '', $s); // remover milhar
        $s = str_replace(',', '.', $s);
        return (float)$s;
    }

    /* ============================================================
     *  LISTAR INVESTIMENTOS
     * ============================================================ */
    public function index()
    {
        $savings = Saving::with(['account', 'lots', 'lots.pendingYields'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($savings);
    }

    /* ============================================================
     *  CRIAR INVESTIMENTO
     * ============================================================ */
    public function store(Request $request)
    {
        $userId = Auth::id();

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'current_amount' => 'nullable', // aceita null, vamos converter depois
            'cdi_percent'    => 'required|numeric|min:0.50|max:5.00',
            'start_date'     => 'nullable|date',
            'notes'          => 'nullable|string',
            // conta passa a ser obrigatória
            'account_id'     => [
                'required',
                'uuid',
                Rule::exists('accounts', 'id')->where('user_id', $userId),
            ],
            'color_card'     => 'nullable|string|max:9',
        ]);

        $data['current_amount'] = $this->moneyToFloat($data['current_amount'] ?? 0);
        $data['user_id']        = $userId;

        $startDate = !empty($data['start_date'])
            ? Carbon::parse($data['start_date'])
            : Carbon::now();

        // Conta vinculada (sempre do usuário)
        $account = Account::where('user_id', $userId)->findOrFail($data['account_id']);

        // Trava: não deixa criar cofrinho com valor inicial maior que o saldo da conta
        if ($data['current_amount'] > 0 && $account->current_balance < $data['current_amount']) {
            throw ValidationException::withMessages([
                'current_amount' => 'Saldo insuficiente na conta para realizar esta operação.',
            ]);
        }

        // Criar o investimento inicialmente SEM saldo
        $saving = Saving::create([
            'user_id'        => $data['user_id'],
            'account_id'     => $account->id,
            'name'           => strtoupper($data['name']),
            'current_amount' => 0, // saldo real vem das cotas / movimentos
            'start_date'     => $startDate,
            'notes'          => $data['notes'] ?? null,
            'cdi_percent'    => (float) $data['cdi_percent'],
            'color_card'     => $data['color_card'] ?? '#00BFA6',
        ]);

        // Aporte inicial: tira da conta e manda para o cofrinho via InvestmentService
        if ($data['current_amount'] > 0) {
            $this->investmentService->deposit(
                saving:  $saving,
                amount:  $data['current_amount'],
                date:    $startDate,
                account: $account,
                notes:   'Aporte Inicial'
            );
        }

        $saving->load(['account', 'lots']);

        return response()->json($saving, 201);
    }

    /* ============================================================
     *  DETALHES DO INVESTIMENTO
     * ============================================================ */
    public function show($id)
    {
        $saving = Saving::with([
            'account',
            'lots',
            'lots.pendingYields',
            'lots.movements',
        ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($saving);
    }

    /* ============================================================
     *  ATUALIZAR INVESTIMENTO
     * ============================================================ */
    public function update(Request $request, $id)
    {
        $userId = Auth::id();

        $saving = Saving::where('user_id', $userId)->findOrFail($id);
        $originalAccountId = $saving->account_id;

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'notes'       => 'sometimes|string|nullable',
            'cdi_percent' => 'sometimes|numeric|min:0.50|max:5.00',
            'account_id'  => [
                'sometimes',
                'required',
                'uuid',
                Rule::exists('accounts', 'id')->where('user_id', $userId),
            ],
            'color_card'  => 'sometimes|string|max:9|nullable',
        ]);

        // Trava: não deixa trocar a conta se já tiver saldo ou lotes
        if (array_key_exists('account_id', $data) && $data['account_id'] !== $originalAccountId) {
            if ($saving->current_amount > 0 || $saving->lots()->exists()) {
                throw ValidationException::withMessages([
                    'account_id' => 'Não é possível alterar a conta de um cofrinho que já possui saldo ou movimentações.',
                ]);
            }
        }

        if (!empty($data['name'])) {
            $data['name'] = strtoupper($data['name']);
        }

        $saving->update($data);

        $saving->load(['account', 'lots']);

        return response()->json($saving);
    }

    /* ============================================================
     *  EXCLUIR INVESTIMENTO
     * ============================================================ */
    /* ============================================================
 *  EXCLUIR INVESTIMENTO
 * ============================================================ */
    public function destroy($id)
    {
        $userId = Auth::id();

        $saving = Saving::where('user_id', $userId)->findOrFail($id);

        // Se houver conta vinculada e saldo no cofrinho, faz o resgate automático
        if ($saving->account_id && $saving->current_amount > 0) {
            $account = Account::where('user_id', $userId)->findOrFail($saving->account_id);

            // Resgata TODO o saldo do cofrinho de volta para a conta
            $this->investmentService->withdraw(
                saving:  $saving,
                amount:  $saving->current_amount,
                date:    Carbon::now(),
                account: $account,
                notes:   'Resgate automático por exclusão do cofrinho'
            );

            // após o withdraw, o current_amount deve estar zerado
            $saving->refresh();
        }

        // Agora pode excluir o cofrinho normalmente
        $saving->delete();

        return response()->json(null, 204);
    }


    /* ============================================================
     *  DEPOSITAR NO COFRINHO (debita conta, credita saving)
     *  Rota: POST /savings/{id}/deposit
     * ============================================================ */
    public function deposit(Request $request, string $id)
    {
        $userId = Auth::id();

        $saving = Saving::where('user_id', $userId)->findOrFail($id);

        if (!$saving->account_id) {
            throw ValidationException::withMessages([
                'account_id' => 'Este cofrinho não possui uma conta vinculada.',
            ]);
        }

        $data = $request->validate([
            'amount' => 'required',
            'date'   => 'nullable|date',
            'notes'  => 'nullable|string',
        ]);

        $amount = $this->moneyToFloat($data['amount']);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'O valor deve ser maior que zero.',
            ]);
        }

        $account = Account::where('user_id', $userId)->findOrFail($saving->account_id);

        // Trava: saldo suficiente na conta
        if ($account->current_balance < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo insuficiente na conta para realizar esta operação.',
            ]);
        }

        $date  = !empty($data['date']) ? Carbon::parse($data['date']) : Carbon::now();
        $notes = $data['notes'] ?? 'Depósito no cofrinho';

        // Delega a lógica de lotes / movimentos para o InvestmentService
        $this->investmentService->deposit(
            saving:  $saving,
            amount:  $amount,
            date:    $date,
            account: $account,
            notes:   $notes
        );

        $saving->refresh()->load(['account', 'lots', 'lots.pendingYields']);

        return response()->json([
            'message' => 'Depósito realizado com sucesso.',
            'data'    => $saving,
        ]);
    }

    /* ============================================================
     *  RESGATAR DO COFRINHO (debita saving, credita conta)
     *  Rota: POST /savings/{id}/withdraw
     * ============================================================ */
    public function withdraw(Request $request, string $id)
    {
        $userId = Auth::id();

        $saving = Saving::where('user_id', $userId)->findOrFail($id);

        if (!$saving->account_id) {
            throw ValidationException::withMessages([
                'account_id' => 'Este cofrinho não possui uma conta vinculada.',
            ]);
        }

        $data = $request->validate([
            'amount' => 'required',
            'date'   => 'nullable|date',
            'notes'  => 'nullable|string',
        ]);

        $amount = $this->moneyToFloat($data['amount']);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'O valor deve ser maior que zero.',
            ]);
        }

        // Trava: saldo suficiente no cofrinho
        if ($saving->current_amount < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo insuficiente no cofrinho para realizar esta operação.',
            ]);
        }

        $account = Account::where('user_id', $userId)->findOrFail($saving->account_id);

        $date  = !empty($data['date']) ? Carbon::parse($data['date']) : Carbon::now();
        $notes = $data['notes'] ?? 'Resgate do cofrinho';

        $this->investmentService->withdraw(
            saving:  $saving,
            amount:  $amount,
            date:    $date,
            account: $account,
            notes:   $notes
        );

        $saving->refresh()->load(['account', 'lots', 'lots.pendingYields']);

        return response()->json([
            'message' => 'Resgate realizado com sucesso.',
            'data'    => $saving,
        ]);
    }
}
