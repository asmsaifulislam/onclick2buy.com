<?php
namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
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

    public function index()
    {
        $cartItems = Cart::with('product')->where('session_id', session()->getId())->orWhere('user_id', Auth::id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }
        $total = $cartItems->sum(fn($item) => ($item->product->sale_price ?: $item->product->price) * $item->quantity);
        $paymentMethods = PaymentMethod::active()->get();
        return view('checkout.index', compact('cartItems', 'total', 'paymentMethods'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|min:10',
            'shipping_phone' => 'required|string|max:20',
            'shipping_email' => 'required|email|max:255',
            'payment_method' => 'required|string',
        ]);
        $cartItems = Cart::with('product')->where('session_id', session()->getId())->orWhere('user_id', Auth::id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty!');
        }
        DB::transaction(function () use ($request, $cartItems, &$order) {
            $total = $cartItems->sum(fn($item) => ($item->product->sale_price ?: $item->product->price) * $item->quantity);
            $order = Order::create([
                'user_id' => Auth::id() ?? 1,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => 'pending',
                'total' => $total,
                'shipping_address' => $request->shipping_address,
                'shipping_phone' => $request->shipping_phone,
                'shipping_email' => $request->shipping_email,
                'payment_method' => $request->payment_method,
            ]);
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'price' => $item->product->sale_price ?: $item->product->price,
                    'quantity' => $item->quantity,
                ]);
                $item->product->decrement('stock', $item->quantity);
                $item->delete();
            }

            // Track order in Mautic
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
                        'price' => $item->product->sale_price ?: $item->product->price,
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
