<?php

namespace App\Repositories\Eloquent\Content;

use App\Models\Content\Faq;
use App\Repositories\Contracts\Content\FaqRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCRUDTrait;

class FaqRepository extends BaseRepository implements FaqRepositoryInterface
{
    use HasCRUDTrait;

    public function __construct(Faq $model)
    {
        parent::__construct($model);
    }

}