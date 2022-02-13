<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Repositories\PaymentRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JetBrains\PhpStorm\Pure;
use Throwable;

class PaymentController extends Controller
{
    #[Pure]
    public function __construct(public PaymentRepository $repo = new PaymentRepository()) { }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function __invoke(Request $request)
    {
        $data = $request->all();
        $this->repo->setData($data['data']);

        return match ($request->input('method')) {
            PaymentMethod::MPESA->name => $this->repo->mpesa($data['transaction_id'], $data['amount']),
            PaymentMethod::VOUCHER->name => $this->repo->voucher(),
            PaymentMethod::FLOAT->name => $this->repo->float(),
            default => throw new Exception("Unsupported payment method!")
        };
    }
}
