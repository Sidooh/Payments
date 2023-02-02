<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  Request  $request
     * @return string
     *
     * @throws \Exception
     */
    public function __invoke(Request $request): string
    {
        Artisan::call($request->command);

        return Artisan::output();
    }
}
