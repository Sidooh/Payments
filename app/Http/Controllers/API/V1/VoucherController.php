<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\Description;
use App\Enums\EventType;
use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVoucherRequest;
use App\Models\Voucher;
use App\Repositories\SidoohRepositories\VoucherRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohNotify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
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
            $voucher->load('transactions:id,voucher_id,type,amount,description,created_at')->latest()->limit(100);
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
     */
    public function credit(Request $request, Voucher $voucher): JsonResponse
    {
        $data = $request->validate([
            'amount' => 'required|integer|min:10',
        ]);

        $vT = VoucherRepository::credit($voucher->id, $data['amount'], Description::VOUCHER_CREDIT);
    }

    /**
     * @throws \Exception
     */
    public function debit(Voucher $voucher): JsonResponse
    {
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
