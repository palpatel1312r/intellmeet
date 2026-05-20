<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        $user = Auth::user();
        $preferences = json_decode($user->preferences ?? '{}', true);

        // Initialize default notification settings if not set
        if (!isset($preferences['notifications'])) {
            $preferences['notifications'] = [
                'email_meeting_reminders' => true,
                'email_task_assigned' => true,
                'email_team_invites' => true,
                'browser_notifications' => false,
                'sound_notifications' => false,
            ];
        }

        return view('settings.index', compact('user', 'preferences'));
    }

    /**
     * Update profile settings
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'bio' => 'nullable|string|max:500',
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($request->only([
            'name',
            'email',
            'bio',
            'company',
            'position',
            'phone'
        ]));

        return redirect()->route('settings.index')->with('success', 'Profile settings updated successfully!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:4', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Password updated successfully!');
    }

    /**
     * Update notification preferences
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        $preferences = json_decode($user->preferences ?? '{}', true);

        $preferences['notifications'] = [
            'email_meeting_reminders' => $request->has('email_meeting_reminders'),
            'email_task_assigned' => $request->has('email_task_assigned'),
            'email_team_invites' => $request->has('email_team_invites'),
            'browser_notifications' => $request->has('browser_notifications'),
            'sound_notifications' => $request->has('sound_notifications'),
        ];

        $user->preferences = json_encode($preferences);
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Notification preferences updated!');
    }
}
