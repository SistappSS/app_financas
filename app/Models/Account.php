<?php

namespace App\Models;

use App\Models\Auth\User;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends BaseModel
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'bank_name',
        'current_balance',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savings(): HasMany
    {
        return $this->hasMany(Saving::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }


}
