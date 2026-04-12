<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.deepseek.api_key');
        $this->model = config('services.deepseek.model', 'deepseek-chat');
        $this->baseUrl = config('services.deepseek.base_url', 'https://api.deepseek.com');
    }

    public function chat(string $message, array $history = []): string
    {
        if (empty($this->apiKey)) {
            return 'DeepSeek API-nyckel är inte konfigurerad. Vänligen sätt DEEPSEEK_API_KEY i .env-filen.';
        }

        try {
            $messages = $this->buildMessages($message, $history);

            $response = Http::timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/v1/chat/completions", [
                    'model' => $this->model,
                    'messages' => $messages,
                    'stream' => false,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['choices'][0]['message']['content'] ?? 'Inget svar mottogs';
            }

            Log::error('AI Service Error', ['response' => $response->body()]);

            return 'Tyvärr, jag stötte på ett fel. Kontrollera din DeepSeek API-nyckel.';

        } catch (\Exception $e) {
            Log::error('AI Service Exception', ['error' => $e->getMessage()]);

            return 'Tyvärr, jag kunde inte ansluta till AI-tjänsten. Kontrollera din internetanslutning och API-nyckel.';
        }
    }

    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$this->apiKey,
                ])
                ->get("{$this->baseUrl}/v1/models");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    private function buildMessages(string $message, array $history): array
    {
        $messages = [];

        $systemPrompt = [
            'role' => 'system',
            'content' => 'Du är en hjälpsam AI-assistent för ett svenskt boknings- och kundhanteringssystem. Du hjälper användare med frågor om kunder, bokningar, scheman och andra relaterade ämnen. Svara alltid på svenska om inte användaren ber om något annat. Var kortfattad och konkret i dina svar.',
        ];

        $messages[] = $systemPrompt;

        foreach ($history as $item) {
            $messages[] = [
                'role' => $item['role'] ?? 'user',
                'content' => $item['content'] ?? '',
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        return $messages;
    }
}
