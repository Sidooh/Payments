<?php

use App\Exceptions\BalanceException;
use App\Exceptions\PaymentException;
use App\Helpers\ApiResponseClass;
use App\Http\Middleware\JWTAuth;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.jwt' => JWTAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e) {
            return match (true) {
                $e instanceof MethodNotAllowedHttpException => ApiResponseClass::staticErrorResponse('The specified method for the request is invalid', 405),
                $e instanceof NotFoundHttpException         => ApiResponseClass::staticErrorResponse('The specified URL cannot be found', 404),
                $e instanceof ValidationException           => ApiResponseClass::staticErrorResponse('The request is invalid', 422, $e->errors()),
                $e instanceof ModelNotFoundException        => ApiResponseClass::staticErrorResponse('The specified resource cannot be found', 404),
                $e instanceof HttpException                 => ApiResponseClass::staticErrorResponse($e->getMessage(), $e->getStatusCode()),
                $e instanceof AuthenticationException       => ApiResponseClass::staticErrorResponse($e->getMessage(), 401),
                $e instanceof AuthorizationException        => ApiResponseClass::staticErrorResponse($e->getMessage(), 403),
                $e instanceof BalanceException, $e instanceof PaymentException => ApiResponseClass::staticErrorResponse($e->getMessage(), $e->getCode()),
                default                                     => ApiResponseClass::staticErrorResponse('Something went wrong, please contact support')
            };
        });

    })->create();
