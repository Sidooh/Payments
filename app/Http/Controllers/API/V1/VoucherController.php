<?php

namespace App\Http\Controllers\API\V1;

use App\Enums\Description;
use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherTransaction;
use App\Repositories\VoucherRepository;
use App\Services\SidoohAccounts;
use App\Services\SidoohProducts;
use Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class VoucherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $vouchers = Voucher::latest();

        if (in_array('voucher_transactions', $relations)) {
            $vouchers = $vouchers->with('voucherTransactions:id,voucher_id,type,amount,description,created_at')
                ->limit(50);
        }

        $vouchers = $vouchers->get();

        if (in_array('account', $relations)) {
            $vouchers = withRelation('account', $vouchers, 'account_id', 'id');
        }

        return $this->successResponse($vouchers);
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        $transactions = VoucherTransaction::query();

        if (in_array('voucher', $relations)) {
            $transactions = $transactions->with('voucher:id,account_id,type,balance');
        }

        if (in_array('payment', $relations)) {
            $transactions = $transactions->with('payment:id,provider_id,subtype,status');
        }

        $transactions->orderBy('id', 'desc')->limit(100);

        return $this->successResponse($transactions->get());
    }

    public function show(Request $request, Voucher $voucher): JsonResponse
    {
        $relations = explode(',', $request->query('with'));

        if (in_array('transactions', $relations)) {
            $voucher->load('voucherTransactions:id,voucher_id,type,amount,description,created_at')->latest()
                ->limit(100);
        }

        if (in_array('account', $relations)) {
            $voucher->account = SidoohAccounts::find($voucher->account_id, true);
        }

        return $this->successResponse($voucher->toArray());
    }

    public function getAccountVouchers(int $accountId): JsonResponse
    {
        $vouchers = Voucher::select(['id', 'type', 'balance'])->whereAccountId($accountId)->get();

        if ($vouchers->isEmpty()) {
            $vouchers = [Voucher::create(['account_id' => $accountId, 'type' => VoucherType::SIDOOH])];
        }

        return $this->successResponse($vouchers);
    }

    public function credit(Request $request): JsonResponse
    {
        $request->validate([
            'account_id'  => ['required'],
            'amount'      => ['required'],
            'description' => ['required', 'string'],
            'notify'      => ['required', 'boolean'],
        ]);

        $accountId = $request->input('account_id');
        $amount = $request->input('amount');
        $description = Description::from($request->input('description'));

        $response = VoucherRepository::credit($accountId, $amount, $description);

        return $this->successResponse($response);
    }

    /**
     * @throws Throwable
     */
    public function disburse(Request $request): JsonResponse
    {
        $data = $request->validate([
            'disburse_type' => 'in:LUNCH,GENERAL',
            'enterprise_id' => 'required|integer',
            'amount'        => 'numeric',
            'accounts'      => 'array',
        ], [
            'disburse_type.in' => 'invalid :attribute. allowed values are: [LUNCH, GENERAL]',
        ]);

        $enterpriseId = $data['enterprise_id'];
        $voucherType = VoucherType::tryFrom("ENTERPRISE_{$data['disburse_type']}");

        $enterprise = SidoohProducts::findEnterprise($enterpriseId);

        if ($request->isNotFilled('amount')) {
            $data['amount'] = match ($data['disburse_type']) {
                'LUNCH'   => $enterprise['max_lunch'],
                'GENERAL' => $enterprise['max_general']
            };

            if (! isset($data['amount'])) {
                return $this->errorResponse("Amount is required! default amount for {$data['disburse_type']} voucher not set");
            }
        }

        if ($request->isNotFilled('accounts')) {
            $data['accounts'] = Arr::pluck($enterprise['enterprise_accounts'], 'account_id');
        }

        $floatAccount = VoucherRepository::disburse($enterprise, $data['accounts'], $data['amount'], $voucherType);

        $message = "{$data['disburse_type']} Voucher Disburse Request Successful";

        return $this->successResponse($floatAccount, $message);
    }
}
