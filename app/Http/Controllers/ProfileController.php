<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form (kept for legacy route compatibility).
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update name and/or profile image via AJAX.
     * Returns JSON so the sidebar can refresh without a page reload.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'profile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $user->name = $request->name;

        if ($request->hasFile('profile_image')) {
            // Delete the old image if it exists
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $path = $request->file('profile_image')->store('profile-images', 'public');
            $user->profile_image = $path;
        }

        $user->save();

        return response()->json([
            'success'           => true,
            'name'              => $user->name,
            'profile_image_url' => $user->profile_image_url,
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        \App\Models\User::destroy($user->id);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Toggle public sharing of all tasks for this user,
     * or update sharing permissions (share_can_complete / share_can_edit)
     * when the user already has an active share token.
     */
    public function toggleShare(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // If permission-only update (share already enabled)
            if ($request->has('share_can_complete') || $request->has('share_can_edit')) {
                if ($request->has('share_can_complete')) {
                    $user->share_can_complete = (bool) $request->input('share_can_complete');
                }
                if ($request->has('share_can_edit')) {
                    $user->share_can_edit = (bool) $request->input('share_can_edit');
                }
                $user->save();

                return response()->json([
                    'status'  => 'success',
                    'shared'  => (bool) $user->share_token,
                    'message' => 'Permissions updated successfully',
                ]);
            }

            // Toggle share token on/off
            if ($user->share_token) {
                $user->share_token        = null;
                $user->share_can_edit     = false;
                $user->share_can_complete = false;
                $user->save();

                return response()->json([
                    'status'  => 'success',
                    'shared'  => false,
                    'message' => 'Profile sharing disabled successfully',
                ]);
            } else {
                $user->share_token = \Illuminate\Support\Str::random(32);
                $user->save();

                return response()->json([
                    'status'    => 'success',
                    'shared'    => true,
                    'share_url' => $user->share_url,
                    'message'   => 'Profile sharing enabled successfully',
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('ProfileController::toggleShare error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update sharing settings. Please run: php artisan migrate',
                'debug'   => $e->getMessage(),
            ], 500);
        }
    }
}
