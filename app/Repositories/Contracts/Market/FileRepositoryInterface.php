<?php

namespace App\Repositories\Contracts\Market;

use App\Models\Market\File;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;

interface FileRepositoryInterface extends 
ShowableRepositoryInterface,
CreatableRepositoryInterface, 
DeletableRepositoryInterface
{
    public function showFile(File $file) : File;
}