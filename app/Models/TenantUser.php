<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

class TenantUser extends Model
{
    use HasPermissions, HasRoles;

    protected $connection = 'tenant';

    protected $table = 'tenant_user';

    public $incrementing = false;

    protected $guarded = [];

    protected $guard_name = 'web';
}
