<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;
    protected static function newFactory()
    {
        return \Database\Factories\OTPFactory::new();
    }
    protected $table = 'otps';
    protected $fillable = ['token', 'user_id', 'otp_code', 'login_id', 'type', 'used', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
