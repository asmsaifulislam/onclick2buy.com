<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $methods = ShippingMethod::orderBy('sort_order')->paginate(20);
        return view('admin.shipping.index', compact('methods'));
    }

    public function create()
    {
        return view('admin.shipping.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'free_over' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        ShippingMethod::create($data);
        return redirect()->route('admin.shipping.index')->with('success', 'Shipping method created!');
    }

    public function edit(ShippingMethod $shipping)
    {
        return view('admin.shipping.edit', compact('shipping'));
    }

    public function update(Request $request, ShippingMethod $shipping)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'required|numeric|min:0',
            'free_over' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        $shipping->update($data);
        return redirect()->route('admin.shipping.index')->with('success', 'Shipping method updated!');
    }

    public function destroy(ShippingMethod $shipping)
    {
        $shipping->delete();
        return back()->with('success', 'Shipping method deleted!');
    }
}
