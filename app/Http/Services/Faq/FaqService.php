<?php

namespace App\Http\Services\Faq;

use App\Models\Content\Faq;
use App\Repositories\Contracts\Content\FaqRepositoryInterface;

class FaqService
{
    public function __construct(
        protected FaqRepositoryInterface $faqRepository
    ){}

    public function getAll()
    {
        return $this->faqRepository->all();
    }


    public function showFaq(Faq $faq)
    {
        return $this->faqRepository->showWithRelations($faq);
    }


    public function storeFaq(array $data)
    {
        return $this->faqRepository->create($data);
    }

    public function updateFaq(Faq $faq, array $data)
    {
        return $this->faqRepository->update($faq,$data);
    }


    public function delete(Faq $faq)
    {
        return $this->faqRepository->delete($faq);
    }
}