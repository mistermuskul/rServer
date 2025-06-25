<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramService;

class MessageController extends Controller
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function getUsers()
    {
        $user = Auth::user();
        
        if ($user->id === 1) {
            return response()->json(User::where('id', '!=', 1)->get());
        }
        
        return response()->json([User::find(1)]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $otherUserId = $request->query('user_id');

        if (!$otherUserId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }

        $messages = Message::where(function ($query) use ($user, $otherUserId) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $otherUserId);
        })->orWhere(function ($query) use ($user, $otherUserId) {
            $query->where('sender_id', $otherUserId)
                ->where('receiver_id', $user->id);
        })
        ->with(['sender', 'receiver'])
        ->orderBy('created_at', 'asc')
        ->get();

        Message::where('receiver_id', $user->id)
            ->where('sender_id', $otherUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'receiver_id' => 'required|integer|exists:users,id'
        ]);

        $user = Auth::user();
        $receiverId = $request->receiver_id;

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'content' => $request->content,
            'is_read' => false
        ]);

        if ($receiverId === 1) {
            try {
                $sender = User::find($user->id);
                $telegramMessage = "📨 Новое сообщение от HR #{$user->id} ({$sender->name})\n\n{$request->content}";
                $this->telegram->sendMessage(null, $telegramMessage);
                Log::info('Message sent to Telegram', [
                    'sender' => $sender->name,
                    'content' => $request->content
                ]);
            } catch (\Exception $e) {
                Log::error('Telegram message sending failed: ' . $e->getMessage());
            }
        }

        return response()->json($message->load(['sender', 'receiver']), 201);
    }

    public function markAsDelivered($messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->is_delivered = true;
        $message->save();
        return response()->json(['success' => true]);
    }

    public function markAsRead($messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->is_read = true;
        $message->save();
        return response()->json(['success' => true]);
    }

    public function unreadCount()
    {
        $user = Auth::user();
        $count = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function unreadCountByUser()
    {
        $user = Auth::user();
        $counts = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->selectRaw('sender_id, COUNT(*) as count')
            ->groupBy('sender_id')
            ->pluck('count', 'sender_id');

        return response()->json($counts);
    }
} 