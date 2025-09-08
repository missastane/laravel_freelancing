<?php

namespace App\Repositories\Eloquent\Market;

use App\Models\Market\FeatureType;
use App\Repositories\Contracts\Market\FeatureTypeRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;

class FeatureTypeRepository extends BaseRepository implements FeatureTypeRepositoryInterface
{
    use HasCRUDTrait;

    public function __construct(FeatureType $model)
    {
        parent::__construct($model);
    }

    
}
