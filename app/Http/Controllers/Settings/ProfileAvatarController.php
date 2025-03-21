<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileAvatarController extends Controller
{
    /**
     * Update the user's profile avatar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'], // 2MB Max
        ]);

        $user = $request->user();

        // Delete old avatar if it exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store the new avatar
        $path = $request->file('avatar')->storeAs(
            'avatars',
            'avatar_' . $user->id . '_' . Str::random(10) . '.' . $request->file('avatar')->getClientOriginalExtension(),
            'public'
        );

        // Update user profile
        $user->avatar = $path;
        $user->save();

        return back()->with('status', 'avatar-updated');
    }

    /**
     * Delete the user's profile avatar.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        // Delete avatar file
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Clear the path in the database
        $user->avatar = null;
        $user->save();

        return back()->with('status', 'avatar-removed');
    }
}
