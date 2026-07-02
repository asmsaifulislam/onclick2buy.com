<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Category;
class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::active()->get();
        $products = Product::active()->latest()->take(8)->get();
        return view('home', compact('categories', 'products'));
    }
}
