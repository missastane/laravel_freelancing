<?php

namespace App\Repositories\Contracts\User;

use App\Models\User\OTP;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface OTPRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    DeletableRepositoryInterface,
    UpdatableRepositoryInterface
{
    public function findByUserToken(string $token, int $userId): OTP;
    public function findByLoginId(string $loginId): OTP;
}