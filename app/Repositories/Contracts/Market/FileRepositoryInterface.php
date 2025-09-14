<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\File;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;

interface FileRepositoryInterface extends 
ShowableRepositoryInterface,
CreatableRepositoryInterface,
UpdatableRepositoryInterface, 
DeletableRepositoryInterface
{
    public function showFile(File $file) : File;
}