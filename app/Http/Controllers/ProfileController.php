<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show()
    {
        $user = Auth::user();

        // Debug: Check if user exists
        if (!$user) {
            return redirect()->route('login');
        }

        // Get user statistics with proper error handling
        $stats = [
            'meetings_created' => $user->createdMeetings ? $user->createdMeetings()->count() : 0,
            'meetings_joined' => $user->meetings ? $user->meetings()->count() : 0,
            'teams_count' => $user->teams ? $user->teams()->count() : 0,
            'tasks_completed' => $user->tasks ? $user->tasks()->where('status', 'done')->count() : 0,
            'total_tasks' => $user->tasks ? $user->tasks()->count() : 0,
            'action_items' => $user->actionItems ? $user->actionItems()->where('status', 'pending')->count() : 0,
        ];

        // Get recent activity
        $recentMeetings = $user->meetings ? $user->meetings()->latest()->limit(5)->get() : collect();
        $recentTasks = $user->tasks ? $user->tasks()->latest()->limit(5)->get() : collect();

        return view('profile.show', compact('user', 'stats', 'recentMeetings', 'recentTasks'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'bio' => $request->bio,
            'phone' => $request->phone,
            'company' => $request->company,
            'position' => $request->position,

        ]);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
    }

    /**
     * Delete user account
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => 'required|current_password',
        ]);

        // Delete avatar if exists
        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Logout and delete
        Auth::logout();
        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted successfully.');
    }
}
