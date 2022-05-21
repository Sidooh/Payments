<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\JWT;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JWTAuth
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Exception
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $token = $request->bearerToken();

        if(!JWT::verify($token)) throw new AuthenticationException();

        return $next($request);
    }
}
