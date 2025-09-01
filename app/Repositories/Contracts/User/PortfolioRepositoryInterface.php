<?php

namespace App\Repositories\Contracts\User;

use App\Models\Market\Portfolio;
use App\Repositories\Contracts\CreatableRepositoryInterface;
use App\Repositories\Contracts\DeletableRepositoryInterface;
use App\Repositories\Contracts\ShowableRepositoryInterface;
use App\Repositories\Contracts\UpdatableRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

interface PortfolioRepositoryInterface extends
    CreatableRepositoryInterface,
    ShowableRepositoryInterface,
    UpdatableRepositoryInterface,
    DeletableRepositoryInterface
{
    public function getUserPortfolios():Paginator;
    public function showPortfolio(Portfolio $portfolio);
}