<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Auth\User;

class AdditionalUser extends BaseModel
{
    use HasFactory;
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'linked_user_id'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function linkedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'linked_user_id');
    }

    public function scopeForOwner($q, string $ownerId)   { return $q->where('user_id', $ownerId); }
    public function scopeForLinked($q, string $userId)   { return $q->where('linked_user_id', $userId); }

    public static function ownerIdFor(?string $uid = null): ?string
    {
        $uid = $uid ?? auth()->id();
        return static::where('linked_user_id', $uid)->value('user_id') ?: $uid;
    }
}
