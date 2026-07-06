<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\OtpService;
use App\Services\SSLCommerzService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected SSLCommerzService $sslcommerz;
    protected OtpService $otp;

    public function __construct(SSLCommerzService $sslcommerz, OtpService $otp)
    {
        $this->sslcommerz = $sslcommerz;
        $this->otp = $otp;
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        $order->load('items');
        $method = PaymentMethod::where('code', $order->payment_method)->first();
        $payment = Payment::where('order_id', $order->id)->latest()->first();
        $phone = $order->shipping_phone ?? Auth::user()?->phone ?? '';
        $otpVerified = $this->otp->isVerified($phone);
        return view('payment.index', compact('order', 'method', 'payment', 'phone', 'otpVerified'));
    }

    public function process(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        if ($order->payment_status === 'paid') {
            return redirect()->route('home')->with('error', 'Order already paid.');
        }

        $phone = $request->phone ?? $order->shipping_phone;
        if (!$this->otp->isVerified($phone)) {
            return back()->with('error', 'Please verify your mobile number first.');
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
            $this->otp->clear($phone);
            return redirect()->away($result['url']);
        }

        return back()->with('error', 'Failed to initiate payment: ' . ($result['reason'] ?? 'Unknown error'));
    }

    public function manualPayment(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        if ($order->payment_status === 'paid') {
            return redirect()->route('home')->with('error', 'Order already paid.');
        }

        $phone = $request->phone ?? $order->shipping_phone;
        if (!$this->otp->isVerified($phone)) {
            return back()->with('error', 'Please verify your mobile number first.');
        }

        $validated = $request->validate([
            'transaction_id' => 'required|string|max:255',
            'sender_number' => 'required|string|max:20',
            'sender_name' => 'nullable|string|max:255',
        ]);

        $method = PaymentMethod::where('code', $order->payment_method)->first();

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
        $this->otp->clear($phone);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Payment info submitted! We will verify and confirm your order shortly.');
    }

    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id() && !Auth::user()?->isAdmin()) {
            abort(403);
        }
        return view('payment.success', compact('order'));
    }
}
