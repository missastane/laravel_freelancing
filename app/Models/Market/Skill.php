<?php

namespace App\Models\Market;

use App\Models\Market\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Skill extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return \Database\Factories\SkillFactory::new();
    }

    protected $fillable = ['persian_title', 'original_title'];

    public function portfolios()
    {
        return $this->belongsToMany(Skill::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function scopeSearch(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('original_title', 'LIKE', "%{$search}%")
                ->orWhere('persian_title', 'LIKE', "%{$search}%");
        });
    }
}
