<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\FinalFile;
use App\Repositories\Contracts\Market\FinalFileRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasShowTrait;
use App\Traits\HasUpdateTrait;

class FinalFileRepository extends BaseRepository implements FinalFileRepositoryInterface
{
    use HasCreateTrait;
    use HasShowTrait;
    use HasUpdateTrait;
    public function __construct(FinalFile $model)
    {
        parent::__construct($model);
    }
}