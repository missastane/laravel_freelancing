<?php

namespace App\Models\Market;

use Illuminate\Database\Eloquent\Model;

class FeatureType extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'target_type',
        'price',
        'duration_days',
        'is_active'
    ];

    protected $hidden = ['is_active'];
    protected $appends = ['is_active_value'];
    public function featurePerchased()
    {
        return $this->hasMany(FeatureType::class, 'feature_type_id');
    }

    public function getIsActiveValueAttribute()
    {
        if ($this->is_active == 1) {
            return 'فعال';
        } else {
            return 'غیرفعال';
        }
    }
}
