<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function error(
        string $message = null,
        int $statusCode = 500,
        ?string $errorCode = null,
        array $errors = []
    ): JsonResponse {
       
        $response = [
            'status' => false,
            'message' => $message ?? __('errors.server_error'),
        ];

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        $response['errors'] = (object) $errors;

        $response['error_list'] = empty($errors)
            ? [$message]
            : array_values(array_merge(...array_values($errors)));

        return response()->json($response, $statusCode);
    }
}