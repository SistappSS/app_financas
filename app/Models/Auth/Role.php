<?php

namespace App\Models\Auth;

use Spatie\Permission\Models\Role as SpatieRole;
use App\Traits\HasUuid;

class Role extends SpatieRole
{
    use HasUuid;
}
