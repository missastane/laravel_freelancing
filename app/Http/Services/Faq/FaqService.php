<?php

namespace App\Http\Services\Faq;

use App\Models\Content\Faq;
use App\Repositories\Contracts\Content\FaqRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class FaqService
{
    public function __construct(
        protected FaqRepositoryInterface $faqRepository
    ) {
    }

    public function getAll()
    {
        $cacheKey = 'faqs';
        return Cache::rememberForever($cacheKey, fn() => $this->faqRepository->all());
    }


    public function showFaq(Faq $faq)
    {
        return $this->faqRepository->showWithRelations($faq);
    }


    public function storeFaq(array $data)
    {
        $result = $this->faqRepository->create($data);
        if ($result) {
            Cache::forget('faqs');
        }
        return $result;
    }

    public function updateFaq(Faq $faq, array $data)
    {
        $result = $this->faqRepository->update($faq, $data);
        if ($result) {
            Cache::forget('faqs');
        }
        return $result;
    }


    public function delete(Faq $faq)
    {
        $result = $this->faqRepository->delete($faq);
        if ($result) {
            Cache::forget('faqs');
        }
        return $result;
    }
}