<?php

namespace App\Http\Controllers;

use App\Models\GroupMessage;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get all users except self, with last message + unread count
     */
    public function users()
    {
        $me    = Auth::id();
        $users = User::where('id', '!=', $me)
            ->select('id', 'name', 'email', 'avatar', 'job_title')
            ->get()
            ->map(function ($user) use ($me) {
                // Last message between me and this user
                $last = Message::where(function ($q) use ($me, $user) {
                        $q->where('sender_id', $me)->where('receiver_id', $user->id);
                    })->orWhere(function ($q) use ($me, $user) {
                        $q->where('sender_id', $user->id)->where('receiver_id', $me);
                    })
                    ->latest()
                    ->first();

                // Unread count (messages FROM this user TO me that are unread)
                $unread = Message::where('sender_id', $user->id)
                    ->where('receiver_id', $me)
                    ->whereNull('read_at')
                    ->count();

                return [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'job_title'    => $user->job_title,
                    'avatar_url'   => $user->avatar_url,
                    'last_message' => $last ? [
                        'body'       => $last->body,
                        'created_at' => $last->created_at->diffForHumans(),
                        'is_mine'    => $last->sender_id === $me,
                    ] : null,
                    'unread'       => $unread,
                ];
            })
            ->sortByDesc(fn($u) => $u['last_message'] ? 1 : 0)
            ->values();

        return response()->json($users);
    }

    /**
     * Get conversation with a specific user (paginated, newest last)
     */
    public function conversation(Request $request, User $user)
    {
        $me = Auth::id();

        $messages = Message::where(function ($q) use ($me, $user) {
                $q->where('sender_id', $me)->where('receiver_id', $user->id);
            })->orWhere(function ($q) use ($me, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $me);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'is_mine'    => $m->sender_id === $me,
                'read_at'    => $m->read_at,
                'created_at' => $m->created_at->format('H:i'),
                'date'       => $m->created_at->format('d M Y'),
            ]);

        // Mark all incoming messages as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'user'     => [
                'id'        => $user->id,
                'name'      => $user->name,
                'job_title' => $user->job_title,
                'avatar_url'=> $user->avatar_url,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message
     */
    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'required|string|max:2000',
        ]);

        if ($request->receiver_id == Auth::id()) {
            return response()->json(['error' => 'Cannot message yourself.'], 422);
        }

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body,
        ]);

        $message->load('sender');

        return response()->json([
            'id'         => $message->id,
            'body'       => $message->body,
            'is_mine'    => true,
            'read_at'    => null,
            'created_at' => $message->created_at->format('H:i'),
            'date'       => $message->created_at->format('d M Y'),
        ]);
    }

    /**
     * Poll for new messages since last message id
     */
    public function poll(Request $request, User $user)
    {
        $me     = Auth::id();
        $lastId = $request->last_id ?? 0;

        $messages = Message::where('sender_id', $user->id)
            ->where('receiver_id', $me)
            ->where('id', '>', $lastId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'is_mine'    => false,
                'read_at'    => $m->read_at,
                'created_at' => $m->created_at->format('H:i'),
                'date'       => $m->created_at->format('d M Y'),
            ]);

        // Mark as read
        if ($messages->isNotEmpty()) {
            Message::where('sender_id', $user->id)
                ->where('receiver_id', $me)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        // Also return total unread count from all users for nav badge
        $totalUnread = Message::where('receiver_id', $me)->whereNull('read_at')->count();

        return response()->json([
            'messages'     => $messages,
            'total_unread' => $totalUnread,
        ]);
    }

    /**
     * Get total unread count (for nav badge)
     */
    public function unreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())->whereNull('read_at')->count();
        return response()->json(['count' => $count]);
    }

    // ─────────────────────────────────────────────
    // GROUP CHAT
    // ─────────────────────────────────────────────

    /**
     * Get last N group messages with sender info
     */
    public function groupMessages(Request $request)
    {
        $messages = GroupMessage::with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'is_mine'    => $m->user_id === Auth::id(),
                'user_id'    => $m->user_id,
                'user_name'  => $m->user->name,
                'avatar_url' => $m->user->avatar_url,
                'created_at' => $m->created_at->format('H:i'),
                'date'       => $m->created_at->format('d M Y'),
            ]);

        return response()->json($messages);
    }

    /**
     * Send a group message
     */
    public function groupSend(Request $request)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $msg = GroupMessage::create([
            'user_id' => Auth::id(),
            'body'    => $request->body,
        ]);

        $msg->load('user');

        return response()->json([
            'id'         => $msg->id,
            'body'       => $msg->body,
            'is_mine'    => true,
            'user_id'    => $msg->user_id,
            'user_name'  => $msg->user->name,
            'avatar_url' => $msg->user->avatar_url,
            'created_at' => $msg->created_at->format('H:i'),
            'date'       => $msg->created_at->format('d M Y'),
        ]);
    }

    /**
     * Poll for new group messages since last id
     */
    public function groupPoll(Request $request)
    {
        $lastId   = (int) ($request->last_id ?? 0);

        $messages = GroupMessage::with('user')
            ->where('id', '>', $lastId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'body'       => $m->body,
                'is_mine'    => $m->user_id === Auth::id(),
                'user_id'    => $m->user_id,
                'user_name'  => $m->user->name,
                'avatar_url' => $m->user->avatar_url,
                'created_at' => $m->created_at->format('H:i'),
                'date'       => $m->created_at->format('d M Y'),
            ]);

        return response()->json($messages);
    }

}