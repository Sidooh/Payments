<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\Initiator;
use App\Http\Controllers\Controller;
use App\Http\Requests\FloatAccountRequest;
use App\Http\Requests\FloatRequest;
use App\Models\FloatAccount;
use App\Repositories\FloatAccountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FloatAccountController extends Controller
{
    public function __construct(private readonly FloatAccountRepository $repo) { }

    public function index(Request $request): JsonResponse
    {
        $floatAccounts = FloatAccount::latest()->get();

        return $this->successResponse($floatAccounts);
    }

    /**
     * @throws \Exception
     */
    public function store(FloatAccountRequest $request): JsonResponse
    {
        $initiator = $request->enum("initiator", Initiator::class);
        $accountId = $request->validated("account_id");
        $enterpriseId = $request->validated("enterprise_id");

        $account = $this->repo->store($initiator, $accountId, $enterpriseId);

        return $this->successResponse($account);
    }

    /**
     * @throws \Exception
     */
    public function topUp(FloatRequest $request): JsonResponse
    {
        $data = $request->validated();

        Log::info('...[CTRL - FLOAT ACCOUNT]: Process Float Request...', $data);

        $initiator = $request->enum("initiator", Initiator::class);
        $amount = $request->validated("amount");
        $accountId = $request->validated("account_id");
        $enterpriseId = $request->validated("enterprise_id");

        $floatAccount = $this->repo->topUp($initiator, $amount, $accountId, $enterpriseId);

        return $this->successResponse($floatAccount);
    }
}
