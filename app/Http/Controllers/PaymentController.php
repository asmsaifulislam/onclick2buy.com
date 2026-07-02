<?php
namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        return view('payment.index', compact('order'));
    }

    public function process(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        if ($order->payment_status === 'paid') {
            return redirect()->route('home')->with('error', 'Order already paid.');
        }

        $request->validate([
            'card_number' => 'required|string|size:16',
            'card_name' => 'required|string|max:255',
            'card_expiry' => 'required|string|size:5',
            'card_cvv' => 'required|string|size:3',
        ]);

        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => 'TXN-' . strtoupper(\Illuminate\Support\Str::random(12)),
            'status' => 'processing',
        ]);

        return redirect()->route('home')->with('success', 'Payment successful! Order is now being processed.');
    }
}
