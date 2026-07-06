<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\SSLCommerzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SSLCommerzController extends Controller
{
    protected SSLCommerzService $sslcommerz;

    public function __construct(SSLCommerzService $sslcommerz)
    {
        $this->sslcommerz = $sslcommerz;
    }

    public function success(Request $request, Order $order)
    {
        $validation = $this->sslcommerz->validate($request->val_id);

        if (($validation['status'] ?? '') === 'VALID' || ($validation['status'] ?? '') === 'VALIDATED') {
            $method = PaymentMethod::where('code', 'card')->first();

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $method?->id,
                'method_code' => 'sslcommerz',
                'amount' => $validation['amount'] ?? $order->total,
                'transaction_id' => $request->tran_id ?? $order->order_number,
                'status' => 'verified',
                'verified_at' => now(),
                'notes' => json_encode($validation),
            ]);

            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'transaction_id' => $request->tran_id ?? $order->order_number,
                'status' => 'processing',
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Payment successful! Order is now being processed.');
        }

        Log::warning('SSLCommerz validation failed', [
            'order' => $order->order_number,
            'val_id' => $request->val_id,
            'response' => $validation,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('error', 'Payment verification failed. Please contact support.');
    }

    public function fail(Request $request, Order $order)
    {
        $order->update(['payment_status' => 'failed']);

        return redirect()->route('orders.show', $order)
            ->with('error', 'Payment failed. Please try again.');
    }

    public function cancel(Request $request, Order $order)
    {
        return redirect()->route('orders.show', $order)
            ->with('error', 'Payment cancelled.');
    }

    public function ipn(Request $request, Order $order)
    {
        if ($request->status === 'VALID') {
            $validation = $this->sslcommerz->validate($request->val_id);

            if (($validation['status'] ?? '') === 'VALID' || ($validation['status'] ?? '') === 'VALIDATED') {
                $existing = Payment::where('order_id', $order->id)->where('status', 'verified')->first();
                if (!$existing) {
                    $method = PaymentMethod::where('code', 'card')->first();
                    Payment::create([
                        'order_id' => $order->id,
                        'payment_method_id' => $method?->id,
                        'method_code' => 'sslcommerz',
                        'amount' => $validation['amount'] ?? $order->total,
                        'transaction_id' => $request->tran_id ?? $order->order_number,
                        'status' => 'verified',
                        'verified_at' => now(),
                        'notes' => json_encode($validation),
                    ]);

                    $order->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'transaction_id' => $request->tran_id ?? $order->order_number,
                        'status' => 'processing',
                    ]);
                }
            }
        }

        return response('OK', 200);
    }
}
