<?php

namespace App\Http\Services\User;

use App\Http\Services\FileManagemant\FileManagementService;
use App\Http\Services\Public\MediaStorageService;
use App\Models\Market\Portfolio;
use App\Repositories\Contracts\User\PortfolioRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PortFolioService
{
    public function __construct(
        protected PortfolioRepositoryInterface $portfolioRepository,
        protected FileManagementService $fileManagementService,
        protected MediaStorageService $mediaStorageService
    ) {
    }

    public function getUserPortfolios()
    {
        return $this->portfolioRepository->getUserPortfolios();
    }

    public function showPortfolio(Portfolio $portfolio)
    {
        return $this->portfolioRepository->showPortfolio($portfolio);
    }

    public function storePortfolio(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['user_id'] = auth()->id();
            $data['banner'] = $this->mediaStorageService->storeSingleImage(
                $data['banner'],
                "images/portfolios/users/{$data['user_id']}",
                null
            );
            $portfolio = $this->portfolioRepository->create($data);
            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                Portfolio::class,
                $portfolio->id,
                "files/portfolios/{$portfolio->id}",
                "public"
            );
            return $portfolio;
        });
    }

    public function updatePortfolio(Portfolio $portfolio, array $data)
    {
        return DB::transaction(function () use ($portfolio, $data) {
            $userId = auth()->id();
            $data['banner'] = $this->mediaStorageService->updateImageIfExists(
                $data['banner'],
                $portfolio->banner,
                "images/portfolios/users/{$userId}",
                null
            );
            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                Portfolio::class,
                $portfolio->id,
                "files/portfolios/{$portfolio->id}",
                "public"
            );
            return $this->portfolioRepository->update($portfolio, $data);
        });
    }

    public function changeStatus(Portfolio $portfolio)
    {
        $portfolio->status = $portfolio->status == 1 ? 2 : 1;
        if ($portfolio->save()) {
            $message = $portfolio->status == 1
                ? 'نمونه کار در پروفایل با موفقیت فعال شد'
                : 'نمونه کار در پروفایل با موفقیت غیرفعال شد';
            return $message;
        }
        return null;
    }

    public function deletePortfolio(Portfolio $portfolio)
    {
        return DB::transaction(function () use ($portfolio) {
            foreach ($portfolio->files as $file) {
                $this->fileManagementService->deleteFile($file);
            }
            return $this->portfolioRepository->delete($portfolio);
        });
    }
}