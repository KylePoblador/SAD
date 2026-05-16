<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StudentFriendController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $friends = $user->friends;

        $pendingReceived = Friendship::with('user')
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->get();

        $pendingSent = Friendship::with('friend')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        return view('student.friends.index', compact('friends', 'pendingReceived', 'pendingSent'));
    }

    public function search(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        if (strlen($term) < 2) {
            return response()->json(['items' => []]);
        }

        $usersTable = (new User)->getTable();
        $hasUsername = Schema::hasColumn($usersTable, 'username');

        $user = auth()->user();
        
        // Exclude current user and already connected friends/pending
        $existingFriendIds = Friendship::where('user_id', $user->id)
            ->orWhere('friend_id', $user->id)
            ->get()
            ->flatMap(function ($f) use ($user) {
                return [$f->user_id, $f->friend_id];
            })->unique()->filter(fn($id) => $id != $user->id)->toArray();

        $items = User::query()
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $existingFriendIds)
            ->where('role', 'student')
            ->where(function ($q) use ($term, $hasUsername) {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('student_id', 'like', '%'.$term.'%');
                if ($hasUsername) {
                    $q->orWhere('username', 'like', '%'.$term.'%');
                }
            })
            ->select('id', 'name', 'email', 'student_id')
            ->when($hasUsername, fn ($q) => $q->addSelect('username'))
            ->limit(20)
            ->get();

        return response()->json(['items' => $items]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $user = auth()->user();
        $friendId = $validated['friend_id'];

        if ($user->id == $friendId) {
            return back()->with('error', 'You cannot add yourself.');
        }

        $exists = Friendship::where(function ($q) use ($user, $friendId) {
            $q->where('user_id', $user->id)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($user, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $user->id);
        })->exists();

        if ($exists) {
            return back()->with('error', 'Friendship or request already exists.');
        }

        Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        \App\Models\ActivityNotification::notifyUser(
            $friendId,
            \App\Models\ActivityNotification::TYPE_FRIEND_REQUEST,
            'New Friend Request',
            "{$user->name} sent you a friend request."
        );

        return back()->with('success', 'Friend request sent!');
    }

    public function accept(Friendship $friendship)
    {
        if ($friendship->friend_id !== auth()->id()) {
            abort(403);
        }

        $friendship->update(['status' => 'accepted']);

        \App\Models\ActivityNotification::notifyUser(
            $friendship->user_id,
            \App\Models\ActivityNotification::TYPE_FRIEND_ACCEPTED,
            'Friend Request Accepted',
            "{$friendship->friend->name} accepted your friend request."
        );

        return back()->with('success', 'Friend request accepted!');
    }

    public function reject(Friendship $friendship)
    {
        if ($friendship->friend_id !== auth()->id() && $friendship->user_id !== auth()->id()) {
            abort(403);
        }

        $friendship->delete();

        return back()->with('success', 'Friend request removed.');
    }

    public function removeFriend(User $friend)
    {
        $user = auth()->user();

        Friendship::where(function ($q) use ($user, $friend) {
            $q->where('user_id', $user->id)->where('friend_id', $friend->id);
        })->orWhere(function ($q) use ($user, $friend) {
            $q->where('user_id', $friend->id)->where('friend_id', $user->id);
        })->delete();

        return back()->with('success', 'Friend removed from your contacts.');
    }
}
