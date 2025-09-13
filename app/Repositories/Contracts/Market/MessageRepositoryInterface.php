<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\Message;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;

interface MessageRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function showMessage(Message $message);
}