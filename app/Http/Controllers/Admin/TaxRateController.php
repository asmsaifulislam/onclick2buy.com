<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function index()
    {
        $rates = TaxRate::latest()->paginate(20);
        return view('admin.tax.index', compact('rates'));
    }

    public function create()
    {
        return view('admin.tax.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        TaxRate::create($data);
        return redirect()->route('admin.tax.index')->with('success', 'Tax rate created!');
    }

    public function edit(TaxRate $tax)
    {
        return view('admin.tax.edit', compact('tax'));
    }

    public function update(Request $request, TaxRate $tax)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);
        $data['is_active'] = $request->has('is_active');
        $tax->update($data);
        return redirect()->route('admin.tax.index')->with('success', 'Tax rate updated!');
    }

    public function destroy(TaxRate $tax)
    {
        $tax->delete();
        return back()->with('success', 'Tax rate deleted!');
    }
}
