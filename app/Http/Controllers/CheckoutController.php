<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\TaxRate;
use App\Services\MauticService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected $mautic;

    public function __construct(MauticService $mautic)
    {
        $this->mautic = $mautic;
    }

    private function itemPrice($item): float
    {
        $product = $item->product;
        $variant = !empty($item->options) ? $product->matchVariant($item->options) : null;
        return $product->priceForVariant($variant);
    }

    private function resolveCoupon(float $subtotal): ?Coupon
    {
        $code = session('coupon_code');
        if (!$code) {
            return null;
        }
        $coupon = Coupon::where('code', strtoupper($code))->first();
        if (!$coupon || !$coupon->isValid($subtotal)) {
            session()->forget('coupon_code');
            return null;
        }
        return $coupon;
    }

    public function index()
    {
        $cartItems = Cart::with('product')->where('session_id', session()->getId())->orWhere('user_id', Auth::id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }
        $subtotal = $cartItems->sum(fn($item) => $this->itemPrice($item) * $item->quantity);
        $coupon = $this->resolveCoupon($subtotal);
        $discount = $coupon ? $coupon->discountAmount($subtotal) : 0;
        $shippingMethods = ShippingMethod::active()->get();
        $selectedShipping = $shippingMethods->first();
        $shippingCost = $selectedShipping ? $selectedShipping->costFor($subtotal - $discount) : 0;
        $taxRate = TaxRate::activeRate();
        $tax = ($subtotal - $discount) * ($taxRate / 100);
        $total = ($subtotal - $discount) + $shippingCost + $tax;

        $paymentMethods = PaymentMethod::active()->get();
        return view('checkout.index', compact(
            'cartItems', 'subtotal', 'coupon', 'discount',
            'shippingMethods', 'selectedShipping', 'shippingCost', 'taxRate', 'tax', 'total', 'paymentMethods'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|min:10',
            'shipping_phone' => 'required|string|max:20',
            'shipping_email' => 'required|email|max:255',
            'payment_method' => 'required|string',
            'shipping_method' => 'required|exists:shipping_methods,id',
        ]);
        $cartItems = Cart::with('product')->where('session_id', session()->getId())->orWhere('user_id', Auth::id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty!');
        }
        $shippingMethod = ShippingMethod::findOrFail($request->shipping_method);

        DB::transaction(function () use ($request, $cartItems, $shippingMethod, &$order) {
            $subtotal = $cartItems->sum(fn($item) => $this->itemPrice($item) * $item->quantity);
            $coupon = $this->resolveCoupon($subtotal);
            $discount = $coupon ? $coupon->discountAmount($subtotal) : 0;
            $shippingCost = $shippingMethod->costFor($subtotal - $discount);
            $taxRate = TaxRate::activeRate();
            $tax = ($subtotal - $discount) * ($taxRate / 100);
            $total = ($subtotal - $discount) + $shippingCost + $tax;

            $order = Order::create([
                'user_id' => Auth::id() ?? 1,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_code' => $coupon?->code,
                'shipping_cost' => $shippingCost,
                'shipping_method' => $shippingMethod->name,
                'tax' => $tax,
                'tax_rate' => $taxRate,
                'total' => $total,
                'shipping_address' => $request->shipping_address,
                'shipping_phone' => $request->shipping_phone,
                'shipping_email' => $request->shipping_email,
                'payment_method' => $request->payment_method,
            ]);
            foreach ($cartItems as $item) {
                $product = $item->product;
                $variant = !empty($item->options) ? $product->matchVariant($item->options) : null;
                $price = $product->priceForVariant($variant);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'options' => $item->options,
                ]);
                if ($variant && $variant->stock > 0) {
                    $variant->decrement('stock', $item->quantity);
                } else {
                    $product->decrement('stock', $item->quantity);
                }
                $item->delete();
            }

            if ($coupon) {
                $coupon->increment('used_count');
            }
            session()->forget('coupon_code');

            $user = Auth::user();
            $this->mautic->trackOrder([
                'email' => $user->email ?? '',
                'firstname' => $user->name ?? '',
                'lastname' => '',
                'order_id' => $order->id,
                'total' => $total,
                'products' => $cartItems->map(function ($item) {
                    return [
                        'name' => $item->product->name,
                        'price' => $this->itemPrice($item),
                        'quantity' => $item->quantity,
                    ];
                })->toArray(),
            ]);
        });

        if (in_array($request->payment_method, ['bkash', 'nagad', 'rocket', 'card'])) {
            return redirect()->route('payment.show', $order);
        }

        return redirect()->route('checkout.success')->with('order_success', true);
    }

    public function success()
    {
        if (!session('order_success')) {
            return redirect()->route('home');
        }
        return view('checkout.success');
    }
}
