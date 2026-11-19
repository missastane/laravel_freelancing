<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;

class Role extends Model
{
    use HasFactory, HasPermissions;
    protected $fillable = ['name', 'guard_name'];

    public function users()
    {
        return $this->morphedByMany(User::class, 'model', 'model_has_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Config('permission.models.permission'),
            config('permission.table_names.role_has_permissions'),
        );
    }

    public function scopeSearch(Builder $query, null|string $search): void
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }
        return;
    }
}
