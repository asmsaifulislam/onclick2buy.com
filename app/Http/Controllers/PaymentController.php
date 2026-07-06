<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        $method = PaymentMethod::where('code', $order->payment_method)->first();
        $payment = Payment::where('order_id', $order->id)->latest()->first();
        return view('payment.index', compact('order', 'method', 'payment'));
    }

    public function process(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        if ($order->payment_status === 'paid') {
            return redirect()->route('home')->with('error', 'Order already paid.');
        }

        $method = PaymentMethod::where('code', $order->payment_method)->first();

        if ($order->payment_method === 'cod') {
            $order->update(['payment_status' => 'unpaid', 'status' => 'processing']);
            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $method?->id,
                'method_code' => 'cod',
                'amount' => $order->total,
                'status' => 'verified',
                'verified_at' => now(),
            ]);
            return redirect()->route('orders.show', $order)->with('success', 'Order placed! Pay on delivery.');
        }

        if (in_array($order->payment_method, ['bkash', 'nagad', 'rocket'])) {
            $validated = $request->validate([
                'transaction_id' => 'required|string|max:255',
                'sender_number' => 'required|string|max:20',
                'sender_name' => 'nullable|string|max:255',
            ]);

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $method?->id,
                'method_code' => $order->payment_method,
                'amount' => $order->total,
                'transaction_id' => $validated['transaction_id'],
                'sender_number' => $validated['sender_number'],
                'sender_name' => $validated['sender_name'] ?? Auth::user()?->name,
                'status' => 'pending',
            ]);

            $order->update(['payment_status' => 'pending']);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Payment info submitted! Waiting for admin verification.');
        }

        if ($order->payment_method === 'card') {
            $validated = $request->validate([
                'card_number' => 'required|string|size:16',
                'card_name' => 'required|string|max:255',
                'card_expiry' => 'required|string|size:5',
                'card_cvv' => 'required|string|size:3',
            ]);

            $txnId = 'TXN-' . strtoupper(\Illuminate\Support\Str::random(12));

            DB::transaction(function () use ($order, $method, $txnId) {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'transaction_id' => $txnId,
                    'status' => 'processing',
                ]);

                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $method?->id,
                    'method_code' => 'card',
                    'amount' => $order->total,
                    'transaction_id' => $txnId,
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);
            });

            return redirect()->route('orders.show', $order)
                ->with('success', 'Payment successful! Order is now being processed.');
        }

        return back()->with('error', 'Invalid payment method.');
    }

    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        return view('payment.success', compact('order'));
    }
}
