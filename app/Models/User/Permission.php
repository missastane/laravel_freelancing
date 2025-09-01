<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Permission extends Model
{
     use HasFactory, HasRoles;

     protected $fillable = ['name','guard_name'];
    public function users()
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_permissions');
    }

    public function roles()
    {
        return $this->belongsToMany(
            Config('permission.models.role'),
            config('permission.table_names.role_has_permissions'),
        );
    }
}
