<?php

namespace App\Repositories\Eloquent\User;

use App\Http\Resources\ResourceCollections\BaseCollection;
use App\Http\Resources\User\PortfolioResource;
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
    public function getUserPortfolios()
    {
        $portfolios = $this->model->where('user_id', auth()->id())
            ->with('files', 'skills')
            ->orderBy('created_at', 'desc')->paginate(15);
        return new BaseCollection($portfolios, PortfolioResource::class, null);
    }
    public function showPortfolio(Portfolio $portfolio)
    {
        $result = $this->showWithRelations($portfolio, ['files', 'skills']);
        return new PortfolioResource($result);
    }

    public function syncSkills(Portfolio $portfolio, array $skills)
    {
        return $portfolio->skills()->sync($skills);
    }
}