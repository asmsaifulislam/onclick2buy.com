<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductPunch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductPunchController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductPunch::latest('date');
        if ($search = $request->input('search')) {
            $query->search($search);
        }
        $punches = $query->paginate(15)->appends($request->query());
        $totalQuantity = (clone $query)->sum('quantity');
        $totalValue = (clone $query)->sum('total_price');
        return view('admin.product-punches.index', compact('punches', 'totalQuantity', 'totalValue'));
    }

    public function create()
    {
        return view('admin.product-punches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'supplier' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'qc_remarks' => 'nullable|string|max:1000',
        ]);

        $data = $request->only(['date', 'product_name', 'supplier', 'quantity', 'unit_price', 'qc_remarks']);
        $data['total_price'] = $data['quantity'] * $data['unit_price'];
        ProductPunch::create($data);

        return redirect()->route('admin.product-punches.index')->with('success', 'Product punch entry created!');
    }

    public function edit(ProductPunch $productPunch)
    {
        return view('admin.product-punches.edit', ['punch' => $productPunch]);
    }

    public function update(Request $request, ProductPunch $productPunch)
    {
        $request->validate([
            'date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'supplier' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'qc_remarks' => 'nullable|string|max:1000',
        ]);

        $data = $request->only(['date', 'product_name', 'supplier', 'quantity', 'unit_price', 'qc_remarks']);
        $data['total_price'] = $data['quantity'] * $data['unit_price'];
        $productPunch->update($data);

        return redirect()->route('admin.product-punches.index')->with('success', 'Product punch entry updated!');
    }

    public function destroy(ProductPunch $productPunch)
    {
        $productPunch->delete();
        return back()->with('success', 'Entry deleted!');
    }

    public function export(Request $request): Response
    {
        $query = ProductPunch::orderBy('date', 'desc');
        if ($search = $request->input('search')) {
            $query->search($search);
        }
        $punches = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_punches_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($punches) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Product Name', 'Supplier', 'Quantity', 'Unit Price', 'Total Price', 'QC Remarks']);
            foreach ($punches as $p) {
                fputcsv($handle, [
                    $p->date->format('Y-m-d'),
                    $p->product_name,
                    $p->supplier,
                    $p->quantity,
                    number_format($p->unit_price, 2),
                    number_format($p->total_price, 2),
                    $p->qc_remarks,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
