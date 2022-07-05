<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentSubtype;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class MpesaController extends Controller
{
    public function getSTKPayments(): JsonResponse
    {
        $stk = Payment::whereSubtype(PaymentSubtype::STK->name)->with([
            "provider:id,status,reference,checkout_request_id,amount,phone,created_at",
            "provider.response:id,checkout_request_id,result_desc,created_at"
        ])->get();

        return response()->json($stk);
    }

    public function getC2BPayments(): JsonResponse
    {
        $stk = Payment::whereSubtype(PaymentSubtype::C2B->name)->with([
            "provider:id,status,reference,checkout_request_id,amount,phone,created_at",
            "provider.response:id,checkout_request_id,result_desc,created_at"
        ])->get();

        return response()->json($stk);
    }

    public function getB2CPayments(): JsonResponse
    {
        $stk = Payment::whereSubtype(PaymentSubtype::B2C->name)->with([
            "provider:id,status,reference,checkout_request_id,amount,phone,created_at",
            "provider.response:id,checkout_request_id,result_desc,created_at"
        ])->get();

        return response()->json($stk);
    }
}
