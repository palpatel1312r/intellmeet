<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Handle status filtering
        $status = $request->get('status', 'all');

        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->onlyTrashed(); // Use onlyTrashed() for soft deleted
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role != 'all') {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15);

        // Get statistics
        $stats = [
            'total' => User::withTrashed()->count(),
            'active' => User::whereNull('deleted_at')->count(),
            'inactive' => User::onlyTrashed()->count(),
            'admins' => User::where('role', 'admin')->whereNull('deleted_at')->count(),
            'members' => User::where('role', 'member')->whereNull('deleted_at')->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)
                ->whereNull('deleted_at')
                ->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    // Soft delete user (deactivate)
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account!');
        }

        $user->delete(); // This soft deletes

        return redirect()->route('admin.users.index')
            ->with('success', 'User deactivated successfully!');
    }

    // Restore soft-deleted user (activate)
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index')
            ->with('success', 'User activated successfully!');
    }

    // Permanently delete user
    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $user->forceDelete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User permanently deleted!');
    }

    // Bulk soft delete
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
        ]);

        $userIds = json_decode($request->user_ids);
        $count = 0;

        $users = User::whereIn('id', $userIds)
            ->where('id', '!=', auth()->id())
            ->get();

        foreach ($users as $user) {
            $user->delete();
            $count++;
        }

        return redirect()->route('admin.users.index')
            ->with('success', $count . ' users deactivated successfully!');
    }

    // Bulk restore (activate)
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
        ]);

        $userIds = json_decode($request->user_ids);
        $count = 0;

        $users = User::onlyTrashed()->whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            $user->restore();
            $count++;
        }

        return redirect()->route('admin.users.index')
            ->with('success', $count . ' users activated successfully!');
    }

    // Change user role
    public function changeRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        if ($user->id === auth()->id() && $user->role === 'admin' && $request->role === 'member') {
            $adminCount = User::where('role', 'admin')->whereNull('deleted_at')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Cannot change role. You are the only admin!');
            }
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', 'User role updated!');
    }

    // Show user details
    public function show(User $user)
    {
        $stats = [
            'total_meetings' => $user->meetings()->count(),
            'total_tasks' => $user->tasks()->count(),
            'completed_tasks' => $user->tasks()->where('status', 'done')->count(),
            'teams_count' => $user->teams()->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    // Show create form
    public function create()
    {
        return view('admin.users.create');
    }

    // Store new user
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
            'role' => 'required|in:admin,member',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => $request->has('verified') ? now() : null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    // Show edit form
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,member',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:4|confirmed']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }
}
