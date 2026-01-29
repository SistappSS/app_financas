<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Services\ProjectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectionController extends Controller
{
    public function index(Request $req)
    {
        $uid = $req->user()->id;

        // Mesmo critério de dono + adicionais usado no service
        $ownerId = DB::table('additional_users')
            ->where('linked_user_id', $uid)
            ->value('user_id') ?? $uid;

        $userIds = DB::table('additional_users')
            ->where('user_id', $ownerId)
            ->pluck('linked_user_id')
            ->all();

        $userIds[] = $ownerId;
        $userIds = array_values(array_unique($userIds));

        $savings = Saving::withoutGlobalScopes()
            ->whereIn('user_id', $userIds)
            ->orderBy('name')
            ->get(['id', 'name', 'current_amount']);

        return view('app.projection.projection_index', compact('savings'));
    }

    public function data(Request $req, ProjectionService $svc)
    {
        $userId = $req->user()->id;
        $start  = $req->query('start', now('America/Sao_Paulo')->startOfMonth()->toDateString());
        $end    = $req->query('end',   now('America/Sao_Paulo')->endOfMonth()->toDateString());

        // IDs dos cofrinhos selecionados (via query param savings[])
        $savingIds = (array) $req->query('savings', []);

        return response()->json(
            $svc->build($userId, $start, $end, $savingIds)
        );
    }
}
