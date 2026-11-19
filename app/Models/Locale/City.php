<?php

namespace App\Models\Locale;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['name','province_id'];

    public function province()
    {
        return $this->belongsTo(Province::class,'province_id');
    }

     public function scopeSearch(Builder $query, null|string $search): void
    {
        if (empty($search)) {
            return;
        }
        
        $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%");
        });

    }
    
}
