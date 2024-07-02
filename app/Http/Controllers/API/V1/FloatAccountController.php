<?php

namespace App\Http\Controllers\API\V1;

use App\DTOs\PaymentDTO;
use App\Enums\Initiator;
use App\Enums\PaymentMethod;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FloatAccountRequest;
use App\Http\Requests\FloatAccountTopupRequest;
use App\Http\Resources\PaymentResource;
use App\Models\FloatAccount;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Services\SidoohAccounts;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FloatAccountController extends Controller
{
    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with', ''));

        $floatAccounts = FloatAccount::latest();

        if (in_array('transactions', $relations)) {
            $floatAccounts = $floatAccounts->with('transactions:id,float_account_id,type,amount,description,created_at')
                ->limit(10);
        }

        $floatAccounts = $floatAccounts->limit(300)->get();

        if (in_array('account', $relations)) {
            $floatAccounts = withRelation('account', $floatAccounts, 'account_id', 'id');
        }

        return $this->successResponse($floatAccounts);
    }

    public function store(FloatAccountRequest $request): JsonResponse
    {
        $initiator = $request->enum('initiator', Initiator::class);

        $account = FloatAccount::firstOrCreate([
            'floatable_type' => $initiator,
            'account_id'     => $request->account_id,
        ], [
            'floatable_id' => $request->reference,
            'description'  => $request->description,
        ]);

        return $this->successResponse($account);
    }

    /**
     * @throws \Exception
     */
    public function show(Request $request, FloatAccount $floatAccount): JsonResponse
    {
        $relations = explode(',', $request->query('with', ''));

        if (in_array('transactions', $relations)) {
            $floatAccount->load(['transactions' => function ($query) {
                $query->select('id', 'float_account_id', 'type', 'amount', 'balance', 'description', 'created_at')
                    ->orderBy('id', 'desc')
                    ->limit(100);
            }]);
        }

        if (in_array('account', $relations)) {
            $floatAccount->account = SidoohAccounts::find($floatAccount->account_id);
        }

        return $this->successResponse($floatAccount);
    }

    /**
     * Handle the incoming request.
     */
    public function credit(FloatAccountTopupRequest $request): JsonResponse
    {
        Log::info('...[CTRL - FLOAT_ACCOUNT]: Credit...', $request->all());

        try {
            [$type, $subtype] = PaymentMethod::from($request->source)->getTypeAndSubtype();

            $repo = new PaymentRepository(
                new PaymentDTO(
                    $request->account_id,
                    $request->amount,
                    $type,
                    $subtype,
                    $request->description,
                    $request->reference,
                    $request->source_account,
                    false,
                    PaymentType::SIDOOH,
                    PaymentSubtype::FLOAT,
                    ['float_account_id' => $request->float_account]
                ),
                $request->ipn
            );

            $payment = $repo->processPayment();

            return $this->successResponse(PaymentResource::make($payment->refresh()), 'Payment Requested.');
        } catch (Exception $err) {
            if ($err->getCode() === 422) {
                return $this->errorResponse($err->getMessage(), $err->getCode());
            }

            Log::error($err);
        }

        return $this->errorResponse('Failed to process credit request.');
    }
}
