<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Voucher;
use App\Repositories\PaymentRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @throws Throwable
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            "transactions" => ['required', 'array'],
            "data"         => ["required", "array"],
            "total_amount" => "required|numeric"
        ]);

        $data = $request->all();

        $repo = new PaymentRepository($data['transactions'], $data['total_amount'], $data['data']);

        Log::info('--- --- ---   ...[CONTROLLER - PAYMENT]: Invoke...   --- --- ---', $data);

        $data = match ($request->input('method')) {
            PaymentMethod::MPESA->name => $repo->mpesa(),
            PaymentMethod::VOUCHER->name => $repo->voucher(),
            PaymentMethod::FLOAT->name => $repo->float(),
            default => throw new Exception("Unsupported payment method!")
        };

//        PaymentCreated::dispatch($payment->toArray());

        return $this->successResponse($data, "Payment Created!");
    }

    #[ArrayShape([
        "payment" => "array",
        "voucher" => "array"
    ])] public function findDetails(int $transactionId, int $accountId): array
    {
        return [
            "payment" => Payment::wherePayableId($transactionId)->first()->toArray(),
            "voucher" => Voucher::whereAccountId($accountId)->first()->toArray()
        ];
    }
}
