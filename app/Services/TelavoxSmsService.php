<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class TelavoxSmsService
{
    private readonly string $baseUrl;

    private readonly ?string $token;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = $baseUrl ?: (string) config('telavox.base_url');
        $this->token = $token ?: config('telavox.token');
    }

    /**
     * Send an SMS via Telavox API.
     *
     * @param  string  $to  E.164 or local format accepted by Telavox (e.g. 00467081234567 or 0701234567)
     * @param  string  $message  Message body
     * @param  bool  $debug  If true, returns request details without sending
     * @return array{success:bool,status:int,message:string|null,body?:mixed,request?:array}
     */
    public function send(string $to, string $message, bool $debug = false): array
    {
        if (empty($this->token)) {
            throw new RuntimeException('TELAVOX_TOKEN is not configured.');
        }

        $to = $this->normalizeNumber($to);

        // Don't urlencode the number in path - Telavox expects it as-is
        $url = mb_rtrim($this->baseUrl, '/').'/sms/'.$to;

        if ($debug) {
            return [
                'success' => false,
                'status' => 0,
                'message' => 'Debug mode',
                'request' => [
                    'url' => $url,
                    'method' => 'GET',
                    'headers' => [
                        'Authorization' => 'Bearer '.mb_substr($this->token, 0, 20).'...',
                        'Accept' => 'application/json',
                    ],
                    'query' => ['message' => $message],
                ],
            ];
        }

        $response = Http::withToken($this->token)
            ->acceptJson()
            ->get($url, [
                'message' => $message,
            ]);

        $ok = $response->status() === 200;
        $body = $response->json();
        $msg = is_array($body) && array_key_exists('message', $body) ? (string) $body['message'] : null;

        return [
            'success' => $ok && ($msg === null || Str::upper($msg) === 'OK'),
            'status' => $response->status(),
            'message' => $msg,
            'body' => $body,
        ];
    }

    private function normalizeNumber(string $number): string
    {
        // Telavox accepts local numbers (e.g., 0701234567) or international prefixed with 00 (e.g., 00467081234567).
        // Keep digits and leading plus/zeros sensibly.
        $number = mb_trim($number);
        // Remove spaces and dashes
        $number = str_replace([' ', '-'], '', $number);

        return $number;
    }
}
