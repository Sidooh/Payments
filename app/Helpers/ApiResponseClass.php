<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponseClass
{
    use ApiResponse;

    public static function staticErrorResponse($message = null, $code = 500, $errors = null): JsonResponse
    {
        $instance = new self(); // Instantiate the class
        return $instance->errorResponse($message, $code, $errors);
    }
}
