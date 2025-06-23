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

    // Webhook для входящих сообщений от Telegram
    public function webhook(Request $request)
    {
        $data = $request->all();
        if (isset($data['message']['text'])) {
            $text = $data['message']['text'];
            $from = $data['message']['from']['id'];
            $fromName = $data['message']['from']['first_name'] ?? 'User';

            // Сохраняем сообщение в базу (пример для вашей модели Message)
            Message::create([
                'sender_id' => null, // null или специальный id для Telegram
                'receiver_id' => 1, // id админа или нужного пользователя
                'content' => "[TG:{$fromName}] $text",
                'is_read' => false,
                'external_sender' => $from, // если нужно хранить telegram_id
            ]);
        }
        return response('ok');
    }

    // Отправить сообщение в Telegram (через API)
    public function sendToTelegram(Request $request)
    {
        $request->validate([
            'text' => 'required'
        ]);
        $chatId = $request->input('chat_id', null); // если не передан — возьмётся из конфига
        $result = $this->telegram->sendMessage($chatId, $request->text);
        return response()->json($result);
    }
} 