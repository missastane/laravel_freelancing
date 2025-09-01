<?php

namespace App\Repositories\Eloquent\User;

use App\Models\User\OTP;
use App\Repositories\Contracts\User\OTPRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Support\Carbon;

class OTPRepository extends BaseRepository implements OTPRepositoryInterface
{
    use HasCreateTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;
    use HasShowTrait;
    public function findByUserToken(string $token, int $userId): OTP
    {
        $otp = $this->model->where('token', $token)
            ->where('user_id', $userId)->where('used', 0)
            ->where(
                'created_at',
                '>=',
                Carbon::now()->subMinutes(2)->toDateTimeString()
            )
            ->first();
        return $otp;
    }

    public function findByLoginId(string $loginId): OTP
    {
        $otp = $this->model->where('login_id', $loginId)
        ->where('used', 0)->orderBy('created_at', 'desc')
        ->first();
        return $otp;
    }

}