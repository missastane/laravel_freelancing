<?php

namespace App\Traits;

use App\Support\ApiResponse;

trait ApiResponseTrait
{
    public function success($data = null, $message = null, $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
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
        return response()->json([
            'status' => true,
            'message' => null,
            'data' => $data,
        ], 200);
    }
}
