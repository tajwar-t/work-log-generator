<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user  = Auth::user();
        $stats = $this->getStats($user->id);
        return view('profile.show', compact('user', 'stats'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'job_title' => 'nullable|string|max:100',
            'timezone'  => 'nullable|string|max:100',
            'bio'       => 'nullable|string|max:500',
        ]);

        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'job_title' => $request->job_title,
            'timezone'  => $request->timezone ?? 'UTC',
            'bio'       => $request->bio,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Profile updated successfully!']);
        }

        return back()->with('success', 'Profile updated!');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar file if exists
        if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $file     = $request->file('avatar');
        $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs('avatars', $filename, 'public');

        $user->update(['avatar' => $filename]);

        return response()->json([
            'success'    => true,
            'message'    => 'Avatar updated!',
            'avatar_url' => asset('storage/avatars/' . $filename),
        ]);
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
        }

        $user->update(['avatar' => null]);

        return response()->json(['success' => true, 'message' => 'Avatar removed.']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'Password changed successfully!']);
    }

    private function getStats(int $userId): array
    {
        $logs      = \App\Models\WorkLog::where('user_id', $userId);
        $total     = $logs->count();
        $dayStarts = (clone $logs)->where('log_type', 'day_start')->count();
        $dayEnds   = (clone $logs)->where('log_type', 'day_end')->count();
        $thisMonth = (clone $logs)->whereMonth('log_date', now()->month)->whereYear('log_date', now()->year)->count();
        $member_since = Auth::user()->created_at->format('M Y');

        return compact('total', 'dayStarts', 'dayEnds', 'thisMonth', 'member_since');
    }
}