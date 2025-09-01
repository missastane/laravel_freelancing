<?php

namespace App\Repositories\Eloquent\Content;

use App\Models\Market\File;
use App\Repositories\Contracts\Market\FileRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Traits\HasCreateTrait;
use App\Traits\HasDeleteTrait;
use App\Traits\HasShowTrait;

class FileRepository extends BaseRepository implements FileRepositoryInterface
{
    use HasShowTrait;
    use HasCreateTrait;
    use HasDeleteTrait;
    public function __construct(File $model)
    {
        parent::__construct($model);
    }

    public function showFile(File $file): File
    {
        return $this->showWithRelations($file);
    }
}