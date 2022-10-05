<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\Initiator;
use App\Http\Controllers\Controller;
use App\Http\Requests\FloatAccountRequest;
use App\Http\Requests\FloatRequest;
use App\Models\FloatAccount;
use App\Models\FloatAccountTransaction;
use App\Repositories\FloatAccountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FloatAccountController extends Controller
{
    public function __construct(private readonly FloatAccountRepository $repo)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $floatAccounts = FloatAccount::latest();

        if (in_array('voucher_transactions', $relations)) {
            $floatAccounts = $floatAccounts->with('floatAccountTransactions:id,float_account_id,type,amount,description,created_at')
                ->limit(50);
        }

        $floatAccounts = $floatAccounts->get();

        return $this->successResponse($floatAccounts);
    }

    /**
     * @throws \Exception
     */
    public function store(FloatAccountRequest $request): JsonResponse
    {
        $initiator = $request->enum('initiator', Initiator::class);

        $account = $this->repo->store($initiator, $request->validated('floatable_id'));

        return $this->successResponse($account);
    }

    public function show(Request $request, FloatAccount $floatAccount): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        if (in_array('transactions', $relations)) {
            $floatAccount->load('floatAccountTransactions:id,float_account_id,type,amount,description,created_at')
                ->latest()->limit(100);
        }

        return $this->successResponse($floatAccount);
    }

    /**
     * @throws \Exception
     */
    public function topUp(FloatRequest $request, FloatAccount $floatAccount): JsonResponse
    {
        $data = $request->validated();

        Log::info('...[CTRL - FLOAT ACCOUNT]: Process Float Request...', $data);

        $initiator = $request->enum('initiator', Initiator::class);
        $amount = $request->validated('amount');

        $floatAccount = $this->repo->topUp($floatAccount, $initiator, $amount);

        return $this->successResponse($floatAccount);
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $transactions = FloatAccountTransaction::query()->select([
            'id',
            'type',
            'amount',
            'description',
            'float_account_id',
            'created_at',
        ])->orderBy('id', 'desc')->limit(100);

        if (in_array('float-account', $relations)) {
            $transactions = $transactions->with('floatAccount:id,floatable_id,floatable_type,balance');
        }

        if (in_array('payment', $relations)) {
            $transactions = $transactions->with('payment:id,provider_id,subtype,status');
        }

        return $this->successResponse($transactions->get());
    }
}
