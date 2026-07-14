<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Category;
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::active()->with('category')->latest()->paginate(12);
        $categories = Category::active()->get();
        return view('products.index', compact('products', 'categories'));
    }
    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }
        $product->load('reviews.user');
        $avgRating = $product->avgRating();
        $userReview = auth()->check() ? $product->reviews->where('user_id', auth()->id())->first() : null;
        $related = Product::active()->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)->take(4)->get();
        return view('products.show', compact('product', 'related', 'avgRating', 'userReview'))
            ->with('productVariants', $product->productVariants);
    }
    public function category(Category $category)
    {
        $products = Product::active()->where('category_id', $category->id)->with('category')->latest()->paginate(12);
        $categories = Category::active()->get();
        return view('products.index', compact('products', 'category', 'categories'));
    }
}
