<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface extends
ListableRepositoryInterface,
ShowableRepositoryInterface,
CreatableRepositoryInterface,
UpdatableRepositoryInterface,
DeletableRepositoryInterface
{}
