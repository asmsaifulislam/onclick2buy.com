<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\SSLCommerzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected SSLCommerzService $sslcommerz;

    public function __construct(SSLCommerzService $sslcommerz)
    {
        $this->sslcommerz = $sslcommerz;
    }

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
            DB::transaction(function () use ($order, $method) {
                $order->update(['payment_status' => 'unpaid', 'status' => 'processing']);
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $method?->id,
                    'method_code' => 'cod',
                    'amount' => $order->total,
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);
            });
            return redirect()->route('orders.show', $order)->with('success', 'Order placed! Pay on delivery.');
        }

        $result = $this->sslcommerz->initiate($order);

        if ($result['status'] === 'SUCCESS') {
            $order->update(['payment_status' => 'pending']);
            return redirect()->away($result['url']);
        }

        return back()->with('error', 'Failed to initiate payment: ' . ($result['reason'] ?? 'Unknown error'));
    }

    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        return view('payment.success', compact('order'));
    }
}
