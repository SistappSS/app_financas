<?php

namespace App\Models\Auth;

use Spatie\Permission\Models\Permission as SpatiePermission;
use App\Traits\HasUuid;

class Permission extends SpatiePermission
{
    use HasUuid;
}
