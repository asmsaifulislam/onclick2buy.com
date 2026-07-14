<?php
namespace App\Http\Controllers;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with('product')->where('session_id', session()->getId())->orWhere('user_id', Auth::id())->get();
        return view('cart.index', compact('cartItems'));
    }
    public function add(Request $request, Product $product)
    {
        $request->validate(['quantity' => 'integer|min:1|max:' . ($product->stock ?: 999)]);
        $quantity = $request->quantity ?? 1;
        $cartItem = Cart::where('product_id', $product->id)
            ->where(function($q) {
                $q->where('session_id', session()->getId())->orWhere('user_id', Auth::id());
            })->first();
        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }
        if ($request->buy_now) {
            return redirect()->route('checkout.index');
        }
        return redirect()->route('cart.index')->with('success', 'Product added to cart!');
    }
    public function update(Request $request, Cart $cart)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cart->update(['quantity' => $request->quantity]);
        return back()->with('success', 'Cart updated!');
    }
    public function remove(Cart $cart)
    {
        $cart->delete();
        return back()->with('success', 'Item removed from cart.');
    }
}
