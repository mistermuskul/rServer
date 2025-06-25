<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Models\Message;

class TelegramController extends Controller
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        if (isset($data['message']['text'])) {
            $text = $data['message']['text'];
            $from = $data['message']['from']['id'];
            $fromName = $data['message']['from']['first_name'] ?? 'User';

            
            Message::create([
                'sender_id' => null,
                'receiver_id' => 1,
                'content' => "[TG:{$fromName}] $text",
                'is_read' => false,
                'external_sender' => $from,
            ]);
        }
        return response('ok');
    }

    public function sendToTelegram(Request $request)
    {
        $request->validate([
            'text' => 'required'
        ]);
        $chatId = $request->input('chat_id', null);
        $result = $this->telegram->sendMessage($chatId, $request->text);
        return response()->json($result);
    }
} 