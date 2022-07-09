<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentMethod;
use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Voucher;
use App\Repositories\WithdrawalRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\ArrayShape;
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
            PaymentMethod::VOUCHER->name => $repo->voucher(),
            default => throw new Exception("Unsupported payment method!")
        };

        return $this->successResponse($data, "Payment Created!");
    }

    #[ArrayShape([
        "payment" => "array",
        "voucher" => "array"
    ])] public function findDetails(int $transactionId, int $accountId): array
    {
        return [
            "payment" => Payment::wherePayableId($transactionId)->first()->toArray(),
            "voucher" => Voucher::firstOrCreate(['account_id' => $accountId], [
                'type' => VoucherType::SIDOOH
            ])->toArray()
        ];
    }

    public function queryMpesaStatus(): JsonResponse
    {
        $exitCode = Artisan::call('mpesa:query_stk_status');

        return response()->json(['Status' => $exitCode]);
    }

    public function disburse(Request $request): JsonResponse
    {
        return $this->successResponse();
    }
}
