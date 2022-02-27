<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [//
    ];

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
    public function register()
    {
        $this->reportable(function(Throwable $e) { });
    }

//    public function render($request, Throwable $e): \Illuminate\Http\Response|JsonResponse|Response
//    {
//        return match (true) {
//            $e instanceof MethodNotAllowedHttpException => $this->errorResponse('The specified method for the request is invalid', 405),
//            $e instanceof NotFoundHttpException => $this->errorResponse('The specified URL cannot be found', 404),
//            $e instanceof HttpException => $this->errorResponse($e->getMessage(), $e->getStatusCode()),
//            $e instanceof ValidationException => $this->errorResponse($e->getMessage(), 422),
//            default => $this->errorResponse($e->getMessage())
//        };
//    }
}
