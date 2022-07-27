<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse($data = [], $message = null, $code = 200): JsonResponse
    {
        $res["status"] = "success";

        if($data) $res["data"] = $data;
        if($message) $res["message"] = $message;

        return response()->json($res, $code);
    }

    protected function errorResponse($message = null, $code = 500): JsonResponse
    {
        return response()->json([
            "code" => $code,
            "errors"  => [
                [
                    'message' => $message
                ]
            ]
        ], $code);
    }
}
