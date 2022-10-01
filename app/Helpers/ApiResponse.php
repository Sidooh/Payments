<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse($data = [], $message = null, $code = 200): JsonResponse
    {
        $res = [
            'result' => 1,
            'data'   => $data,
        ];

        if ($message) {
            $res['message'] = $message;
        }

        return response()->json($res, $code);
    }

    protected function errorResponse($message = null, $code = 500, $errors = null): JsonResponse
    {
        $response = [
            'result' => 0,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }
        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $code);
    }
}
