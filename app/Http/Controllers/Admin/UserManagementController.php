<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PagePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::latest();
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }
        $users = $query->paginate(15)->appends($request->query());
        $roles = ['admin', 'customer', 'guest'];
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = ['admin', 'customer', 'guest'];
        $pages = [
            'dashboard' => 'Dashboard', 'categories' => 'Categories', 'products' => 'Products',
            'orders' => 'Orders', 'inventory' => 'Inventory', 'analytics' => 'Analytics',
            'bi' => 'BI Dashboard', 'bulkupload' => 'Upload & Sync', 'settings' => 'Store Settings',
            'chat' => 'Live Chat', 'product_punches' => 'Product Punches',
            'product_orders' => 'Product Orders', 'users' => 'User Management',
        ];
        return view('admin.users.create', compact('roles', 'pages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:admin,customer,guest',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_admin' => $request->role === 'admin' ? 1 : 0,
        ]);

        // Set permissions
        $allPages = ['dashboard', 'categories', 'products', 'orders', 'inventory', 'analytics', 'bi', 'bulkupload', 'settings', 'chat', 'product_punches', 'product_orders', 'users'];
        $selectedPerms = array_keys($request->input('permissions', []));
        foreach ($allPages as $page) {
            PagePermission::create([
                'role' => $user->role,
                'page_key' => $page,
                'can_view' => in_array($page, $selectedPerms),
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        $roles = ['admin', 'customer', 'guest'];
        $pages = [
            'dashboard' => 'Dashboard',
            'categories' => 'Categories',
            'products' => 'Products',
            'orders' => 'Orders',
            'inventory' => 'Inventory',
            'analytics' => 'Analytics',
            'bi' => 'BI Dashboard',
            'bulkupload' => 'Upload & Sync',
            'settings' => 'Store Settings',
            'chat' => 'Live Chat',
            'product_punches' => 'Product Punches',
            'product_orders' => 'Product Orders',
            'users' => 'User Management',
        ];
        $permissions = PagePermission::where('role', $user->role)->pluck('can_view', 'page_key')->toArray();
        return view('admin.users.edit', compact('user', 'roles', 'pages', 'permissions'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,customer,guest',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'role']);
        $data['is_admin'] = $request->role === 'admin' ? 1 : 0;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update permissions
        if ($request->has('permissions')) {
            $pages = array_keys($request->input('permissions', []));
            PagePermission::where('role', $user->role)->delete();
            $allPages = ['dashboard', 'categories', 'products', 'orders', 'inventory', 'analytics', 'bi', 'bulkupload', 'settings', 'chat', 'product_punches', 'product_orders', 'users'];
            foreach ($allPages as $page) {
                PagePermission::create([
                    'role' => $user->role,
                    'page_key' => $page,
                    'can_view' => in_array($page, $pages),
                ]);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }
        $user->delete();
        return back()->with('success', 'User deleted!');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,customer,guest']);
        $user->update([
            'role' => $request->role,
            'is_admin' => $request->role === 'admin' ? 1 : 0,
        ]);
        return back()->with('success', 'User role updated to ' . ucfirst($request->role) . '!');
    }

    public function permissions(Request $request)
    {
        $roles = ['admin', 'customer', 'guest'];
        $pages = [
            'dashboard' => 'Dashboard',
            'categories' => 'Categories',
            'products' => 'Products',
            'orders' => 'Orders',
            'inventory' => 'Inventory',
            'analytics' => 'Analytics',
            'bi' => 'BI Dashboard',
            'bulkupload' => 'Upload & Sync',
            'settings' => 'Store Settings',
            'chat' => 'Live Chat',
            'product_punches' => 'Product Punches',
            'product_orders' => 'Product Orders',
            'users' => 'User Management',
        ];

        if ($request->isMethod('post')) {
            $allPermissions = $request->input('permissions', []);
            foreach ($roles as $role) {
                PagePermission::where('role', $role)->delete();
                foreach ($pages as $key => $label) {
                    PagePermission::create([
                        'role' => $role,
                        'page_key' => $key,
                        'can_view' => isset($allPermissions[$role]) && in_array($key, $allPermissions[$role]),
                    ]);
                }
            }
            return redirect()->route('admin.users.permissions')->with('success', 'Page permissions updated!');
        }

        $permissions = [];
        foreach ($roles as $role) {
            $permissions[$role] = PagePermission::where('role', $role)
                ->where('can_view', true)
                ->pluck('page_key')
                ->toArray();
        }

        return view('admin.users.permissions', compact('roles', 'pages', 'permissions'));
    }
}
