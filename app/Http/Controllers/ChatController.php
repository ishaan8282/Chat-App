<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    /**
     * Display the chat screen with the given user.
     */
    public function show(User $user): View
    {
        $currentUserId = Auth::id();

        // Chat list ke liye baaki users fetch kar rahe hain
        $users = User::where('id', '!=', $currentUserId)->get();

        return view('chat.show', [
            'recipient' => $user,
            'users' => $users,
        ]);
    }

    /**
     * Fetch chat history between the logged-in user and recipient (Used for Polling).
     */
    public function messages(User $user): JsonResponse
    {
        $authId = (int) Auth::id();
        $otherId = (int) $user->id;

        $messages = Message::query()
            ->where(function ($query) use ($authId, $otherId) {
                $query->where('sender_id', $authId)
                    ->where('receiver_id', $otherId);
            })
            ->orWhere(function ($query) use ($authId, $otherId) {
                $query->where('sender_id', $otherId)
                    ->where('receiver_id', $authId);
            })
            ->oldest()
            ->get(['id', 'sender_id', 'receiver_id', 'message', 'created_at']);

        return response()->json($messages);
    }

    /**
     * Save a new message sent from the chat screen.
     */
    public function sendMessage(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $user->id,
            'message' => $validated['message'],
        ]);

        broadcast(new MessageSent($message));

        return response()->json($message, 201);
    }
}
