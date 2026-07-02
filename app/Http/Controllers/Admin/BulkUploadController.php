<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BulkUploadController extends Controller
{
    public function index()
    {
        $categories = Category::active()->get();
        return view('admin.bulkupload.index', compact('categories'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'category_id' => 'required|exists:categories,id',
            'default_price' => 'required|numeric|min:0',
        ]);

        $created = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');
            $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $product = Product::create([
                'category_id' => $request->category_id,
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::random(4),
                'description' => 'Auto-created from image upload: ' . $name,
                'price' => $request->default_price,
                'stock' => $request->default_stock ?? 10,
                'images' => [$path],
                'is_active' => true,
            ]);
            $created[] = $product;
        }

        return redirect()->route('admin.bulkupload.index')
            ->with('success', count($created) . ' products created successfully from uploaded images!');
    }

    public function sync(Request $request)
    {
        $action = $request->action;
        $count = 0;

        switch ($action) {
            case 'stock':
                Product::where('stock', 0)->where('is_active', true)->each(function ($p) {
                    if ($p->stock == 0) {
                        $p->update(['is_active' => false]);
                        $count++;
                    }
                });
                return back()->with('success', "$count out-of-stock products deactivated.");
            case 'prices':
                Product::whereNotNull('sale_price')->where('sale_price', '>', 0)->each(function ($p) {
                    if ($p->sale_price >= $p->price) {
                        $p->update(['sale_price' => null]);
                        $count++;
                    }
                });
                return back()->with('success', "$count invalid sale prices corrected.");
            case 'categories':
                Product::whereDoesntHave('category')->each(function ($p) {
                    $uncategorized = Category::firstOrCreate(['name' => 'Uncategorized', 'slug' => 'uncategorized']);
                    $p->update(['category_id' => $uncategorized->id]);
                    $count++;
                });
                return back()->with('success', "$count products assigned to Uncategorized category.");
            default:
                return back()->with('error', 'Unknown sync action.');
        }
    }
}
