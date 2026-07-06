<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('order', 'method');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->method_code) {
            $query->where('method_code', $request->method_code);
        }

        $payments = $query->latest()->paginate(20);
        $methods = \App\Models\PaymentMethod::orderBy('name')->get();

        return view('admin.payments.index', compact('payments', 'methods'));
    }

    public function show(Payment $payment)
    {
        $payment->load('order', 'method', 'verifier');
        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $payment->update([
            'status' => 'verified',
            'admin_notes' => $request->admin_notes,
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        $payment->order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'transaction_id' => $payment->transaction_id,
            'status' => 'processing',
        ]);

        return redirect()->route('admin.payments.show', $payment)
            ->with('success', 'Payment verified. Order is now processing.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $payment->update([
            'status' => 'failed',
            'admin_notes' => $request->admin_notes,
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ]);

        return redirect()->route('admin.payments.show', $payment)
            ->with('error', 'Payment rejected.');
    }
}
