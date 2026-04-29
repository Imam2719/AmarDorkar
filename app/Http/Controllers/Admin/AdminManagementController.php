<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminManagementController extends Controller
{
    /**
     * Show all admins (super_admin only).
     */
    public function index()
    {
        // Only super admins can manage other admins
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admins can manage admins.');
        }

        $admins = Admin::orderBy('created_at')->get();
        return view('admin.admins.index', compact('admins'));
    }

    /**
     * Store a new admin.
     */
    public function store(Request $request)
    {
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => 'required|in:admin,super_admin',
        ]);

        Admin::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return back()->with('success', 'Admin added successfully.');
    }

    /**
     * Update admin details.
     */
    public function update(Request $request, Admin $admin)
    {
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email,' . $admin->id,
            'role'     => 'required|in:admin,super_admin',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);

        return back()->with('success', 'Admin updated successfully.');
    }

    /**
     * Delete an admin (cannot delete self).
     */
    public function destroy(Admin $admin)
    {
        if (!auth('admin')->user()->isSuperAdmin()) {
            abort(403);
        }

        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();
        return back()->with('success', 'Admin removed.');
    }
}
