<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function cartSubtotal()
    {
        $items = Cart::with('product')->where('session_id', session()->getId())->orWhere('user_id', Auth::id())->get();
        return $items->sum(fn($item) => ($item->product->sale_price ?: $item->product->price) * $item->quantity);
    }

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
            ->where(function ($q) {
                $q->where('session_id', session()->getId())->orWhere('user_id', Auth::id());
            })->first();
        if ($cartItem) {
            $cartItem->increment('quantity', $quantity);
        } else {
            $options = [];
            if ($request->variant_size) {
                $options['size'] = $request->variant_size;
            }
            if ($request->variant_color) {
                $options['color'] = $request->variant_color;
            }
            if ($request->variant_material) {
                $options['material'] = $request->variant_material;
            }
            if ($options) {
                $variant = $product->matchVariant($options);
                if ($variant) {
                    $options['variant_id'] = $variant->id;
                }
            }
            Cart::create([
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'options' => $options ?: null,
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

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();
        $subtotal = $this->cartSubtotal();
        if (!$coupon) {
            return back()->with('error', 'Invalid coupon code.');
        }
        if (!$coupon->isValid($subtotal)) {
            return back()->with('error', 'Coupon is not valid or does not apply to your cart.');
        }
        session(['coupon_code' => $code]);
        return back()->with('success', 'Coupon applied!');
    }

    public function removeCoupon()
    {
        session()->forget('coupon_code');
        return back()->with('success', 'Coupon removed.');
    }
}
