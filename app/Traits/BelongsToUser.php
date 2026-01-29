<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait BelongsToUser
{
    protected static function bootBelongsToUser()
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (!Auth::check()) return;

            $table = $builder->getModel()->getTable();
            $ids   = self::resolveOwnerAndAdditionals(Auth::id());

            // Prefixado para evitar ambiguidade em JOINs
            $builder->whereIn("$table.user_id", $ids);
        });
    }

    /** Resolve dono + adicionais via DB::table (sem Eloquent) e com cache por request */
    protected static function resolveOwnerAndAdditionals(string $uid): array
    {
        static $cache = [];

        if (isset($cache[$uid])) {
            return $cache[$uid];
        }

        // Se o logado é adicional, pega o dono; senão, é o próprio
        $ownerId = DB::table('additional_users')->where('linked_user_id', $uid)->value('user_id') ?? $uid;

        // IDs dos adicionais + o dono
        $ids = DB::table('additional_users')
            ->where('user_id', $ownerId)
            ->pluck('linked_user_id')
            ->all();

        $ids[] = $ownerId;

        return $cache[$uid] = array_values(array_unique($ids));
    }

    // Escopos opcionais já prefixando tabela
    public function scopeByUser($query, $userId = null)
    {
        return $query->where($this->getTable().'.user_id', $userId ?? Auth::id());
    }

    public function scopeForOwnerAndAdditionals($query, string $ownerId)
    {
        $ids = DB::table('additional_users')->where('user_id', $ownerId)
            ->pluck('linked_user_id')->all();
        $ids[] = $ownerId;

        return $query->whereIn($this->getTable().'.user_id', array_values(array_unique($ids)));
    }
}
