<?php

namespace App\Traits;

use App\Support\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

trait ApiResponseTrait
{
    public function success(
        $data = null,
        $message = null,
        $code = 200,
        ?string $resource = null
    ) {
        $links = null;
        $meta = null;

        // 1. Detect pagination before transforming the data.
        if ($data instanceof LengthAwarePaginator || $data instanceof Paginator) {
            $paginator = $data;

            $items = $paginator->getCollection();

            // Apply Resource to paginated items.
            if ($resource) {
                $items = $items->map(
                    fn ($item) => (new $resource($item))->resolve(request())
                );
            } else {
                $items = $items->map(
                    fn ($item) => $item instanceof JsonResource
                        ? $item->resolve(request())
                        : $item
                );
            }

            $data = $items->values()->all();

            // Pagination links.
            $links = [
                'first' => $paginator instanceof LengthAwarePaginator
                    ? $paginator->url(1)
                    : null,
                'last' => $paginator instanceof LengthAwarePaginator
                    ? $paginator->url($paginator->lastPage())
                    : null,
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ];

            // Pagination metadata.
            $meta = [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator instanceof LengthAwarePaginator
                    ? $paginator->lastPage()
                    : null,
                'links' => $paginator instanceof LengthAwarePaginator
                    ? $paginator->linkCollection()->toArray()
                    : [],
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator instanceof LengthAwarePaginator
                    ? $paginator->total()
                    : null,
            ];
        }

        // 2. Handle a single Resource.
        elseif ($data instanceof JsonResource) {
            $data = $data->resolve(request());
        }

        // 3. Handle a single Model.
        elseif ($data instanceof Model) {
            $data = $resource
                ? (new $resource($data))->resolve(request())
                : $data->toArray();
        }

        // 4. Handle Collection.
        elseif ($data instanceof Collection) {
            if ($resource) {
                $data = $data->map(
                    fn ($item) => (new $resource($item))->resolve(request())
                );
            }

            $data = $data->values()->all();
        }

        // 5. Handle array.
        elseif (is_array($data) && $resource) {
            $data = array_map(
                fn ($item) => (new $resource($item))->resolve(request()),
                $data
            );
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            ...($links !== null ? ['links' => $links, 'meta' => $meta] : []),
        ], $code);
    }

    public function error(
        $message = null,
        $code = 500,
        $errorCode = null
    ) {
        return ApiResponse::error(
            $message ?? __('errors.server_error'),
            $code,
            $errorCode
        );
    }

    public function successResource($data)
    {
        return $this->success($data);
    }
}
