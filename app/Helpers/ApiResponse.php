<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ApiResponse
{
    protected function successResponse($data = [], $message = null, $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    protected function errorResponse($message = null, $code = 500): JsonResponse
    {
        Log::error($code);
        return response()->json([
            'errors'  => [
                [
                    'message' => $message
                ]
            ]
        ], $code);
    }
}
