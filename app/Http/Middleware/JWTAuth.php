<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use App\Helpers\JWT;
use Closure;
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
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $bearer = $request->bearerToken();

        if(!JWT::verify($bearer)) return $this->errorResponse("Not Authorized", 401);

        return $next($request);
    }
}
