<?php

namespace App\Repositories\Eloquent\User;

use App\Models\Market\Portfolio;
use App\Repositories\Contracts\User\PortfolioRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;
use Illuminate\Contracts\Pagination\Paginator;

class PortfolioRepository extends BaseRepository implements PortfolioRepositoryInterface
{
    use HasCreateTrait;
    use HasUpdateTrait;
    use HasShowTrait;
    use HasDeleteTrait;
    public function __construct(Portfolio $model)
    {
        parent::__construct($model);
    }
    public function getUserPortfolios(): Paginator
    {
        $portfolios = $this->model->where('user_id', auth()->id())
            ->with('files', 'freelancer:id,username')
            ->orderBy('created_at', 'desc')->simplePaginate(15);
            return $portfolios;
    }
    public function showPortfolio(Portfolio $portfolio)
    {
        return $this->showWithRelations($portfolio,['files', 'freelancer:id,username']);
    }
}