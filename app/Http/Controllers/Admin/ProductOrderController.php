<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use App\Mail\SupplierOrderMail;

class ProductOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductOrder::latest();
        if ($search = $request->input('search')) {
            $query->search($search);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        $orders = $query->paginate(15)->appends($request->query());
        $totalValue = (clone $query)->sum('total_price');
        $statuses = ['draft', 'pending', 'sent', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        return view('admin.product-orders.index', compact('orders', 'totalValue', 'statuses'));
    }

    public function create()
    {
        $statuses = ['draft', 'pending', 'sent', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        return view('admin.product-orders.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_email' => 'required|email|max:255',
            'supplier_phone' => 'nullable|string|max:50',
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'delivery_address' => 'nullable|string|max:1000',
            'required_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string',
        ]);

        $data = $request->only(['supplier_name', 'supplier_email', 'supplier_phone', 'product_name', 'quantity', 'unit_price', 'delivery_address', 'required_date', 'notes', 'status']);
        $data['total_price'] = $data['quantity'] * $data['unit_price'];
        $data['mail_sent'] = false;
        ProductOrder::create($data);

        return redirect()->route('admin.product-orders.index')->with('success', 'Purchase order created!');
    }

    public function edit(ProductOrder $productOrder)
    {
        $statuses = ['draft', 'pending', 'sent', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        return view('admin.product-orders.edit', ['order' => $productOrder, 'statuses' => $statuses]);
    }

    public function update(Request $request, ProductOrder $productOrder)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_email' => 'required|email|max:255',
            'supplier_phone' => 'nullable|string|max:50',
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'delivery_address' => 'nullable|string|max:1000',
            'required_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|string',
        ]);

        $data = $request->only(['supplier_name', 'supplier_email', 'supplier_phone', 'product_name', 'quantity', 'unit_price', 'delivery_address', 'required_date', 'notes', 'status']);
        $data['total_price'] = $data['quantity'] * $data['unit_price'];
        $productOrder->update($data);

        return redirect()->route('admin.product-orders.index')->with('success', 'Purchase order updated!');
    }

    public function destroy(ProductOrder $productOrder)
    {
        $productOrder->delete();
        return back()->with('success', 'Order deleted!');
    }

    public function sendMail(ProductOrder $productOrder)
    {
        if ($productOrder->supplier_email) {
            try {
                Mail::to($productOrder->supplier_email)->send(new SupplierOrderMail($productOrder));
                $productOrder->update(['mail_sent' => true, 'status' => 'sent']);
                return back()->with('success', "Purchase order email sent to {$productOrder->supplier_name} ({$productOrder->supplier_email})!");
            } catch (\Exception $e) {
                return back()->with('error', 'Failed to send email: ' . $e->getMessage());
            }
        }
        return back()->with('error', 'No email address found for this supplier.');
    }

    public function updateStatus(Request $request, ProductOrder $productOrder)
    {
        $request->validate(['status' => 'required|string']);
        $productOrder->update(['status' => $request->status]);
        return back()->with('success', 'Order status updated!');
    }

    public function export(Request $request): Response
    {
        $query = ProductOrder::orderBy('created_at', 'desc');
        if ($search = $request->input('search')) {
            $query->search($search);
        }
        $orders = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="purchase_orders_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Supplier Name', 'Email', 'Phone', 'Product', 'Qty', 'Unit Price', 'Total', 'Required Date', 'Status', 'Mail Sent', 'Delivery Address', 'Notes']);
            foreach ($orders as $o) {
                fputcsv($handle, [
                    $o->created_at->format('Y-m-d'),
                    $o->supplier_name,
                    $o->supplier_email,
                    $o->supplier_phone,
                    $o->product_name,
                    $o->quantity,
                    number_format($o->unit_price, 2),
                    number_format($o->total_price, 2),
                    $o->required_date ? $o->required_date->format('Y-m-d') : '',
                    ucfirst($o->status),
                    $o->mail_sent ? 'Yes' : 'No',
                    $o->delivery_address,
                    $o->notes,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
