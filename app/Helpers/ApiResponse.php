<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse($data = [], $message = null, $code = 200): JsonResponse
    {
        $res["result"] = 1;

        if($data) $res["data"] = $data;
        if($message) $res["message"] = $message;

        return response()->json($res, $code);
    }

    protected function errorResponse($message = null, $code = 500, $errors = null): JsonResponse
    {
        return response()->json([
            'result' => 0,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
