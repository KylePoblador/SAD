<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    protected function storePublicUserAvatar(Request $request, User $user): ?string
    {
        if (! $request->hasFile('avatar')) {
            return null;
        }
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        return $request->file('avatar')->store('avatars/'.$user->id, 'public');
    }
}
