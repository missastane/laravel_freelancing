<?php

namespace App\Http\Resources\ResourceCollections;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BaseCollection extends ResourceCollection
{
    protected string $itemResourceClass;

    public function __construct($resource, string $itemResourceClass, protected $message)
    {
        $this->itemResourceClass = $itemResourceClass;
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $itemResource = $this->itemResourceClass;

        // for every model in this collection is made one resource(every use will has a user resource) after conversion to array
        $items = $this->collection
            ->map(fn($model) => (new $itemResource($model))->toArray($request))
            ->all();

        // اگر اصلاً صفحه‌بندی نبود (مثلاً کلکشن معمولی بود)
        if (!($this->resource instanceof \Illuminate\Contracts\Pagination\Paginator)) {
            return [
                'status' => true,
                'message' => $this->message,
            ] + $items;
        }

        $meta = [
            'current_page' => $this->resource->currentPage(),
            'from' => $this->resource->firstItem(),
            'last_page' => $this->resource instanceof LengthAwarePaginator ? $this->resource->lastPage() : null,
            'links' => $this->resource instanceof LengthAwarePaginator ? $this->resource->linkCollection()->toArray() : [],
            // 'links' => $this->resource instanceof LengthAwarePaginator
            //     ? collect(range(1, $this->resource->lastPage()))->map(fn($page) => [
            //         'url' => $this->resource->url($page),
            //         'label' => (string) $page,
            //         'active' => $this->resource->currentPage() === $page,
            //     ])
            //     : [],
            'path' => $this->resource->path(),
            'per_page' => $this->resource->perPage(),
            'to' => $this->resource->lastItem(),
            'total' => $this->resource instanceof LengthAwarePaginator ? $this->resource->total() : null,

        ];

        $links = [
            'first' => $this->resource instanceof LengthAwarePaginator ? $this->resource->url(1) : null,
            'last' => $this->resource instanceof LengthAwarePaginator ? $this->resource->url($this->resource->lastPage()) : null,
            'prev' => $this->resource->previousPageUrl(),
            'next' => $this->resource->nextPageUrl(),
        ];

        // بخش مشترک بین paginate و simplePaginate
        $data = [
            'current_page' => $this->resource->currentPage(),
            'data' => $items,
            'first_page_url' => $this->resource->url(1),
            'from' => $this->resource->firstItem(),
            'next_page_url' => $this->resource->nextPageUrl(),
            'path' => $this->resource->path(),
            'per_page' => $this->resource->perPage(),
            'prev_page_url' => $this->resource->previousPageUrl(),
            'to' => $this->resource->lastItem(),
        ];

        // فقط برای paginate (نه simplePaginate)
        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'status' => true,
                'message' => $this->message,
                'data' => $data,
                'total' => $meta['total'],
                'last_page' => $meta['last_page'],
                // 'links' => $links,
                // 'meta' => $meta,
            ];
        }

        return [
            'status' => true,
            'message' => $this->message,
            'data' => $data
        ];
    }


}

