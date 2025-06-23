<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $apiUrl;
    protected $defaultChatId;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
        $this->defaultChatId = config('services.telegram.default_chat_id');
        
        // Логируем инициализацию сервиса
        Log::info('TelegramService initialized', [
            'token' => $this->token ? substr($this->token, 0, 5) . '...' : null, // Логируем только начало токена для безопасности
            'apiUrl' => $this->apiUrl,
            'defaultChatId' => $this->defaultChatId
        ]);
    }

    public function sendMessage($chatId = null, $text)
    {
        $chatId = $chatId ?: $this->defaultChatId;
        if (!$this->token || !$chatId) {
            Log::error('Telegram config missing: token or chat_id');
            return false;
        }
        try {
            Log::info('Attempting to send Telegram message', [
                'chatId' => $chatId,
                'text' => $text
            ]);

            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML'
            ]);

            Log::info('Telegram API response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error sending Telegram message', [
                'error' => $e->getMessage(),
                'chatId' => $chatId,
                'text' => $text
            ]);
            throw $e;
        }
    }
} 