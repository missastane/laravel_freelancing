<?php

namespace App\Models\Payment;

use App\Models\Market\FeatureType;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class FeaturePerchased extends Model
{
    protected $fillable = [
        'user_id',
        'feature_type_id',
        'target_type',
        'target_id',
        'perchased_at',
        'payment_id',
        'expired_at'
    ];

    protected function casts()
    {
        return [
            'perchased_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function featureType()
    {
        return $this->belongsTo(FeatureType::class, 'feature_type_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function target()
    {
        return $this->morphTo(__FUNCTION__, 'target_type', 'target_id');
    }
}
