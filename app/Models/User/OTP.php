<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
     protected $table = 'otps';
    protected $fillable = ['token', 'user_id', 'otp_code', 'login_id', 'type', 'used', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
