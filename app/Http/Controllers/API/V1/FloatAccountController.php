<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FloatAccountRequest;
use App\Models\FloatAccount;
use App\Repositories\FloatAccountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FloatAccountController extends Controller
{
    public function __construct(private FloatAccountRepository $repo){}

    public function index(Request $request): JsonResponse
    {
        $floatAccounts = FloatAccount::latest()->get();

        return $this->successResponse($floatAccounts);
    }

    public function store(FloatAccountRequest $request): JsonResponse
    {
        $account = $this->repo->store(...$request->toArray());
        return $this->errorResponse($account);
    }
}
