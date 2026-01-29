<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Account;
use App\Models\AdditionalUser;
use App\Models\Card;
use App\Models\InvoiceItem;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Recurrent;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use NotificationChannels\WebPush\HasPushSubscriptions;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasUuid;
    use HasRoles;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getDisplayName(): string
    {
        return $this->name ?? "Usuário #{$this->id}";
    }

    public function canAuthenticate()
    {
        return true;
    }

    public function accounts() { return $this->hasMany(Account::class); }
    public function cards() { return $this->hasMany(Card::class); }
    public function categories() { return $this->hasMany(TransactionCategory::class); }
    public function transactions() { return $this->hasMany(Transaction::class); }
    public function recurrents() { return $this->hasMany(Recurrent::class); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function cardTransactions() { return $this->hasMany(InvoiceItem::class); }
    public function savings() { return $this->hasMany(Saving::class); }
    public function notifications() { return $this->hasMany(Notification::class); }

    /**
     * Registros da tabela additional_users onde ESTE usuário é o DONO (user_id).
     */
    public function additionalUsers(): HasMany
    {
        return $this->hasMany(AdditionalUser::class, 'user_id', 'id');
    }

    /**
     * Registro da tabela additional_users que liga ESTE usuário como adicional (linked_user_id).
     * Nulo se for um usuário principal.
     */
    public function asAdditional(): HasOne
    {
        return $this->hasOne(AdditionalUser::class, 'linked_user_id', 'id');
    }

    /**
     * Acesso direto aos usuários (tabela users) que são adicionais deste dono.
     * Útil quando você quer os USERS já prontos para autenticação, sem passar pelo model AdditionalUser.
     */
    public function additionals(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'additional_users', 'user_id', 'linked_user_id')
            ->withPivot(['id','name','email','is_active','created_at'])
            ->withTimestamps();
    }

    /**
     * Dono (principal) deste usuário caso ele seja adicional.
     * Retorna uma coleção com 0/1 itens (use ->first()).
     */
    public function owner(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'additional_users', 'linked_user_id', 'user_id')
            ->withPivot(['id'])
            ->withTimestamps();
    }
}
