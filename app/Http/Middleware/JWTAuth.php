<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\JWT;
use Cache;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        $token = $request->bearerToken();

        if(!JWT::verify($token)) throw new AuthenticationException();

        Cache::put('auth_token', $token, JWT::expiry($token)->diffInMinutes());

        return $next($request);
    }
}
