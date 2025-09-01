<?php

namespace App\Traits;

trait HasCRUDTrait
{
    use HasListTrait;
    use HasShowTrait;
    use HasCreateTrait;
    use HasUpdateTrait;
    use HasDeleteTrait;
}