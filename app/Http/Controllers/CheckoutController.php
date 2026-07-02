<?php
namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with('product')->where('session_id', session()->getId())->orWhere('user_id', Auth::id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }
        $total = $cartItems->sum(fn($item) => ($item->product->sale_price ?: $item->product->price) * $item->quantity);
        return view('checkout.index', compact('cartItems', 'total'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|min:10',
            'payment_method' => 'required|string',
        ]);
        $cartItems = Cart::with('product')->where('session_id', session()->getId())->orWhere('user_id', Auth::id())->get();
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart is empty!');
        }
        DB::transaction(function () use ($request, $cartItems) {
            $total = $cartItems->sum(fn($item) => ($item->product->sale_price ?: $item->product->price) * $item->quantity);
            $order = Order::create([
                'user_id' => Auth::id() ?? 1,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'status' => 'pending',
                'total' => $total,
                'shipping_address' => $request->shipping_address,
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
        });
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
