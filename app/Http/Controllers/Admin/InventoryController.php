<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(15);
        return view('admin.inventory.index', compact('products'));
    }
    public function adjust(Product $product)
    {
        $transactions = $product->inventoryTransactions()->latest()->limit(20)->get();
        return view('admin.inventory.adjust', compact('product', 'transactions'));
    }
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);
        $previous = $product->stock;
        $quantity = $request->integer('quantity');
        $new = match ($request->type) {
            'add' => $previous + $quantity,
            'subtract' => max(0, $previous - $quantity),
            'set' => $quantity,
        };
        $product->update(['stock' => $new]);
        $product->inventoryTransactions()->create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'quantity' => $quantity,
            'previous_stock' => $previous,
            'new_stock' => $new,
            'notes' => $request->notes,
        ]);
        return redirect()->route('admin.inventory.index')->with('success', 'Stock updated successfully.');
    }
}
