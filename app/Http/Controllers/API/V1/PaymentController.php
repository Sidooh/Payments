<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Jobs\TestJob;
use App\Repositories\PaymentRepository;
use Exception;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\Pure;
use Throwable;

class PaymentController extends Controller
{
    #[Pure]
    public function __construct(public PaymentRepository $repo = new PaymentRepository())
    {
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return PaymentResource
     * @throws Throwable
     */
    public function __invoke(Request $request): PaymentResource
    {
        $data = $request->all();
        $this->repo->setData($data['data']);

        $payment = match ($request->input('method')) {
            PaymentMethod::MPESA->name => $this->repo->mpesa($data['transaction_id'], $data['amount']),
            PaymentMethod::VOUCHER->name => $this->repo->voucher($data['transaction_id']),
            PaymentMethod::FLOAT->name => $this->repo->float($data['transaction_id']),
            default => throw new Exception("Unsupported payment method!")
        };

//        PaymentCreated::dispatch($payment->toArray());

        return PaymentResource::make($payment);
    }
}
