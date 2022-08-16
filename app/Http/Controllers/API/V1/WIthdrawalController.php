<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Repositories\WithdrawalRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class WIthdrawalController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Validate this process, why no checks?
        $request->validate([
//            "transactions" => ['required', 'array'],
//            "data"         => ["required", "array"],
            ""
        ]);

        $data = $request->all();



        $repo = new WithdrawalRepository($data['transactions'], $data['total_amount'], $data['data']);

        Log::info('...[CONTROLLER - PAYMENT]: Invoke...', $data);

        $data = match ($request->input('method')) {
            PaymentMethod::MPESA->name => $repo->mpesa(),
//            PaymentMethod::VOUCHER->name => $repo->voucher(),
            default => throw new Exception("Unsupported payment method!")
        };

        return $this->successResponse($data, "Payment Created!");
    }
}
