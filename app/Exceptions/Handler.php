<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function(Throwable $e) {
        });
    }

    public function render($request, Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof MethodNotAllowedHttpException => $this->errorResponse('The specified method for the request is invalid', 405),
            $e instanceof NotFoundHttpException         => $this->errorResponse('The specified URL cannot be found', 404),
            $e instanceof ValidationException           => $this->errorResponse("The request is invalid", 422, $e->errors()),
            $e instanceof ModelNotFoundException        => $this->errorResponse('The specified resource cannot be found', 404),
            $e instanceof HttpException                 => $this->errorResponse($e->getMessage(), $e->getStatusCode()),
            $e instanceof AuthenticationException       => $this->errorResponse($e->getMessage(), 401),
            default                                     => $this->errorResponse("Something went wrong, please contact support")
        };
    }
}
