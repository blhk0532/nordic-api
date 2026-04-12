<?php

declare(strict_types=1);

namespace App\Services;

use App\Settings\TelavoxSettings;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TelavoxService
{
    public function __construct(protected TelavoxSettings $settings) {}

    public function getToken(): ?string
    {
        return $this->settings->api_token ?: config('telavox.token');
    }

    public function hasToken(): bool
    {
        return ! empty($this->getToken());
    }

    public function sendSms(string $number, string $message): Response
    {
        $token = $this->getToken();

        if (empty($token)) {
            \Log::error('TelavoxService: No API token configured in settings or config');
            throw new \RuntimeException('No Telavox API token configured');
        }

        \Log::debug('TelavoxService: Sending SMS', [
            'number' => $number,
            'message_length' => strlen($message),
            'token_prefix' => substr($token, 0, 10).'...',
        ]);

        $normalized = preg_replace('/\D+/', '', $number);

        \Log::debug('TelavoxService: Normalized number', ['normalized' => $normalized]);

        $response = Http::withToken($token)
            ->get("https://api.telavox.se/sms/{$normalized}", [
                'message' => $message,
            ]);

        \Log::debug('TelavoxService: API response', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->body(),
        ]);

        return $response;
    }

    public function sendSmsOk(string $number, string $message): bool
    {
        return $this->sendSms($number, $message)->successful();
    }
}
