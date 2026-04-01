<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        // ── Ensure the avatars directory exists and is writable ──────────
        $dir = public_path('avatars');

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0775, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not create avatars directory. Please create public/avatars/ on your server and set permissions to 775.',
                ], 500);
            }
        }

        if (!is_writable($dir)) {
            return response()->json([
                'success' => false,
                'message' => 'Avatar directory is not writable. Please chmod public/avatars/ to 775 on your server.',
            ], 500);
        }

        // ── Delete old avatar file ────────────────────────────────────────
        if ($user->avatar) {
            $oldPath = $dir . DIRECTORY_SEPARATOR . $user->avatar;
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // ── Move uploaded file directly into public/avatars/ ─────────────
        // No storage symlink needed — works on all Hostinger plans
        $file     = $request->file('avatar');
        $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

        $file->move($dir, $filename);

        // Make sure file is readable by web server
        @chmod($dir . DIRECTORY_SEPARATOR . $filename, 0644);

        $user->update(['avatar' => $filename]);

        return response()->json([
            'success'    => true,
            'message'    => 'Avatar updated!',
            'avatar_url' => avatarUrl($filename) . '?t=' . time(),
        ]);
    }

    public function removeAvatar()
    {
        $user = Auth::user();

        if ($user->avatar) {
            $path = public_path('avatars' . DIRECTORY_SEPARATOR . $user->avatar);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $user->update(['avatar' => null]);

        // Return the initials avatar URL so the JS can update the img src immediately
        $user->refresh();
        return response()->json([
            'success'    => true,
            'message'    => 'Avatar removed.',
            'avatar_url' => $user->avatar_url,
        ]);
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

    /**
     * Diagnostic — checks if avatar folder is set up correctly
     * Visit /profile/avatar-check to debug 403 issues
     */
    public function avatarCheck()
    {
        $dir      = public_path('avatars');
        $url      = asset('avatars/');
        $exists   = is_dir($dir);
        $writable = $exists && is_writable($dir);
        $perms    = $exists ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';

        return response()->json([
            'dir_path'     => $dir,
            'public_url'   => $url,
            'dir_exists'   => $exists,
            'dir_writable' => $writable,
            'permissions'  => $perms,
            'php_user'     => function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown',
            'status'       => $writable ? '✅ All good' : ($exists ? '❌ Dir exists but not writable — chmod to 775' : '❌ Dir missing — create public/avatars/'),
        ]);
    }

    private function getStats(int $userId): array
    {
        $total     = \App\Models\WorkLog::where('user_id', $userId)->count();
        $dayStarts = \App\Models\WorkLog::where('user_id', $userId)->where('log_type', 'day_start')->count();
        $dayEnds   = \App\Models\WorkLog::where('user_id', $userId)->where('log_type', 'day_end')->count();
        $thisMonth = \App\Models\WorkLog::where('user_id', $userId)
            ->whereMonth('log_date', now()->month)
            ->whereYear('log_date', now()->year)
            ->count();
        $member_since = Auth::user()->created_at->format('M Y');

        return compact('total', 'dayStarts', 'dayEnds', 'thisMonth', 'member_since');
    }
}