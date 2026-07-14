<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = Wishlist::with('product.category')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(12);
        return view('wishlist.index', compact('items'));
    }

    public function store(Request $request, Product $product)
    {
        Wishlist::firstOrCreate([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'added']);
        }
        return back()->with('success', 'Added to wishlist!');
    }

    public function destroy(Request $request, Product $product)
    {
        Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->delete();
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => 'removed']);
        }
        return back()->with('success', 'Removed from wishlist!');
    }
}
