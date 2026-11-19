<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\Payment\MidtransPayments;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    /** Signature salah dijawab 403; kegagalan sementara di sisi kita dijawab 500 supaya Midtrans mengirim ulang. */
    public function __invoke(Request $request, MidtransPayments $payments): JsonResponse
    {
        $payments->handleNotification($request->json()->all());

        return response()->json(['ok' => true]);
    }
}