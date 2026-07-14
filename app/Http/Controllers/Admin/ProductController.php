<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }
    public function show(Product $product)
    {
        return redirect()->route('admin.products.edit', $product);
    }
    public function create()
    {
        $categories = Category::active()->get();
        return view('admin.products.create', compact('categories'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'variant_sizes' => 'nullable|string',
            'variant_colors' => 'nullable|string',
            'variant_materials' => 'nullable|string',
        ]);
        $data = $request->except('images');
        $data['slug'] = Str::slug($request->name);
        $data['variants'] = $this->parseVariants($request);
        if (!Category::where('id', $request->category_id)->exists()) {
            return back()->withErrors(['category_id' => 'Category not found'])->withInput();
        }
        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $image) {
                $paths[] = $image->store('products', 'public');
            }
            $data['images'] = $paths;
        }
        $product = Product::create($data);
        $this->syncVariants($request, $product);
        return redirect()->route('admin.products.index')->with('success', 'Product created!');
    }
    public function edit(Product $product)
    {
        $categories = Category::active()->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'variant_sizes' => 'nullable|string',
            'variant_colors' => 'nullable|string',
            'variant_materials' => 'nullable|string',
        ]);
        $data = $request->except('images');
        $data['slug'] = Str::slug($request->name);
        $data['variants'] = $this->parseVariants($request);
        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $image) {
                $paths[] = $image->store('products', 'public');
            }
            $data['images'] = $paths;
        }
        $product->update($data);
        $this->syncVariants($request, $product);
        return redirect()->route('admin.products.index')->with('success', 'Product updated!');
    }
    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product deleted!');
    }
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Product {$status} successfully!");
    }

    private function parseVariants(Request $request): ?array
    {
        $variants = [];
        if ($request->variant_sizes) {
            $variants['size'] = array_filter(array_map('trim', explode(',', $request->variant_sizes)));
        }
        if ($request->variant_colors) {
            $variants['color'] = array_filter(array_map('trim', explode(',', $request->variant_colors)));
        }
        if ($request->variant_materials) {
            $variants['material'] = array_filter(array_map('trim', explode(',', $request->variant_materials)));
        }
        return $variants ?: null;
    }

    private function syncVariants(Request $request, Product $product): void
    {
        $rows = $request->input('variants', []);
        $data = [];
        foreach ((array) $rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $attrs = [];
            foreach (['size', 'color', 'material'] as $k) {
                if (!empty($row[$k])) {
                    $attrs[$k] = $row[$k];
                }
            }
            if (empty($attrs) && empty($row['price_override']) && empty($row['stock'])) {
                continue;
            }
            $data[] = [
                'product_id' => $product->id,
                'attributes' => $attrs ?: null,
                'sku' => $row['sku'] ?? null,
                'price_override' => ($row['price_override'] ?? '') !== '' ? $row['price_override'] : null,
                'stock' => (int) ($row['stock'] ?? 0),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $product->productVariants()->delete();
        if ($data) {
            $product->productVariants()->insert($data);
        }
    }
}
