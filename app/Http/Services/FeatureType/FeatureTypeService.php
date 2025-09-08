<?php

namespace App\Http\Services\FeatureType;

use App\Models\Market\FeatureType;
use App\Repositories\Contracts\Market\FeatureTypeRepositoryInterface;

class FeatureTypeService
{
    public function __construct(protected FeatureTypeRepositoryInterface $featureTypeRepository){}

    public function getFeatures()
    {
        $features = $this->featureTypeRepository->all();
        return $features;
    }

    public function show(FeatureType $featureType)
    {
        return $this->featureTypeRepository->showWithRelations($featureType);
    }

    public function store(array $data)
    {
        $feature = $this->featureTypeRepository->create($data);
        return $feature;
    }

    public function update(FeatureType $featureType, array $data)
    {
        return $this->featureTypeRepository->update($featureType,$data);
    }

    public function delete(FeatureType $featureType)
    {
        return $this->featureTypeRepository->delete($featureType);
    }
}