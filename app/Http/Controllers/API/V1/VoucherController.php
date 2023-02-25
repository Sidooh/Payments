<?php

namespace App\Http\Controllers\API\V1;

use App\DTOs\PaymentDTO;
use App\Enums\Description;
use App\Enums\EventType;
use App\Enums\PaymentSubtype;
use App\Enums\PaymentType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\Voucher;
use App\Repositories\PaymentRepositories\PaymentRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohNotify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $vouchers = Voucher::latest();

        if ($id = $request->integer('account_id')) {
            $vouchers->whereAccountId($id);
        }

        if (in_array('transactions', $relations)) {
            $vouchers->with('transactions:id,voucher_id,type,amount,description,created_at')->latest()->limit(10);
        }

        $vouchers = $vouchers->limit(1000)->get();

        if (in_array('account', $relations)) {
            $vouchers = withRelation('account', $vouchers, 'account_id', 'id');
        }

        return $this->successResponse($vouchers);
    }

    /**
     * @throws \Exception
     */
    public function show(Voucher $voucher, Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        if (in_array('transactions', $relations)) {
            $voucher->load('transactions:id,voucher_id,type,amount,description,created_at')->limit(100);
        }

        if (in_array('account', $relations)) {
            $voucher->account = SidoohAccounts::find($voucher->account_id);
        }

        return $this->successResponse($voucher->toArray());
    }

    public function store(StoreVoucherRequest $request): JsonResponse
    {
        $voucher = Voucher::firstOrCreate([
            'account_id'      => $request->account_id,
            'voucher_type_id' => $request->voucher_type_id,
        ]);

        return $this->successResponse($voucher);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function credit(Request $request, Voucher $voucher): JsonResponse
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:10',
            'reason' => 'string',
        ]);

        $repo = new PaymentRepository(
            new PaymentDTO(
                3,
                $data['amount'],
                PaymentType::SIDOOH,
                PaymentSubtype::FLOAT,
                Description::VOUCHER_CREDIT->value,
                null,
                1,
                false,
                PaymentType::SIDOOH,
                PaymentSubtype::VOUCHER,
                ['voucher_id' => $voucher->id]
            )
        );

        $payment = $repo->processPayment();

        $voucher = $voucher->refresh();

        $account = SidoohAccounts::find(3);
        $amount = 'Ksh'.number_format($payment->amount, 2);
        $balance = 'Ksh'.number_format($voucher->balance, 2);
        $date = $payment->updated_at->timezone('Africa/Nairobi')->format(config('settings.sms_date_time_format'));

        $message = "You have received $amount voucher ";
        $message .= "from Sidooh account {$account['phone']} on $date.\n";
        $message .= "New voucher balance is $balance.\n\n";
        $message .= "Dial *384*99# NOW for FREE on your Safaricom line to BUY AIRTIME or PAY BILLS & PAY USING the voucher received.\n\n";
        $message .= config('services.sidooh.tagline');

        $account = SidoohAccounts::find($voucher->account_id);
        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_CREDITED);

        return $this->successResponse($voucher, 'Payment Completed.');
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function debit(Request $request, Voucher $voucher): JsonResponse
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:10',
        ]);

        $repo = new PaymentRepository(
            new PaymentDTO(
                3,
                $data['amount'],
                PaymentType::SIDOOH,
                PaymentSubtype::VOUCHER,
                Description::VOUCHER_DEBIT->value,
                null,
                1,
                false,
                PaymentType::SIDOOH,
                PaymentSubtype::FLOAT,
                ['float_account_id' => 1]
            )
        );

        $payment = $repo->processPayment();

        $voucher = $voucher->refresh();

        $account = SidoohAccounts::find($voucher->account_id);
        $amount = 'Ksh'.number_format($payment->amount, 2);
        $balance = 'Ksh'.number_format($voucher->balance, 2);

        $message = 'Hi'.($account['user']['name'] ? " {$account['user']['name']}," : ',');
        $message .= "\nWe have deducted $amount from your voucher as a result of user over drawn. ";
        $message .= "New voucher balance is $balance.";

        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_DEBITED);

        return $this->successResponse($voucher);
    }

    /**
     * @throws \Exception
     */
    public function activate(Voucher $voucher): JsonResponse
    {
        if ($voucher->status === Status::ACTIVE) {
            return $this->successResponse($voucher, 'Voucher is already active.');
        }

        $voucher->update(['status' => Status::ACTIVE]);

        $account = SidoohAccounts::find($voucher->account_id);

        $message = 'Hi'.($account['user']['name'] ? ' '.$account['user']['name'] : '');
        $message .= ",\nYour voucher has been activated. \n\n".config('sidooh.tagline');

        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_ACTIVATED);

        return $this->successResponse($voucher);
    }

    /**
     * @throws \Exception
     */
    public function deactivate(Voucher $voucher): JsonResponse
    {
        if ($voucher->status === Status::INACTIVE) {
            return $this->successResponse($voucher, 'Voucher is already inactive.');
        }

        $voucher->update(['status' => Status::INACTIVE]);

        $account = SidoohAccounts::find($voucher->account_id);

        $message = 'Hi'.($account['user']['name'] ? ' '.$account['user']['name'] : '');
        $message .= ",\nYour voucher has temporarily been suspended. We shall notify you once it has been reactivated.\nSorry for the inconvenience.";

        SidoohNotify::notify($account['phone'], $message, EventType::VOUCHER_DEACTIVATED);

        return $this->successResponse($voucher);
    }
}
