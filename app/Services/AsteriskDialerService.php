<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Artisan;

class AsteriskDialerService
{
    public function ping(): bool
    {
        return Artisan::call('ami:action', [
            'action' => 'Ping',
        ]) === 0;
    }

    /**
     * @param  array<string, string>  $variables
     */
    public function originate(
        string $channel,
        string $extension,
        string $context = 'default',
        int $priority = 1,
        array $variables = [],
        ?string $callerId = null,
        int $timeoutMilliseconds = 30000,
        ?string $actionId = null,
    ): bool {
        $arguments = [
            'Channel' => $channel,
            'Context' => $context,
            'Exten' => $extension,
            'Priority' => (string) $priority,
            'Timeout' => (string) $timeoutMilliseconds,
            'Async' => 'true',
            'ActionID' => $actionId ?: (string) str()->uuid(),
        ];

        if ($callerId !== null && $callerId !== '') {
            $arguments['CallerID'] = $callerId;
        }

        if ($variables !== []) {
            $encodedVariables = collect($variables)
                ->map(fn (string $value, string $key): string => $key.'='.$value)
                ->implode('|');

            $arguments['Variable'] = $encodedVariables;
        }

        return Artisan::call('ami:action', [
            'action' => 'Originate',
            '--arguments' => $arguments,
        ]) === 0;
    }

    public function hangup(string $channel): bool
    {
        return Artisan::call('ami:action', [
            'action' => 'Hangup',
            '--arguments' => [
                'Channel' => $channel,
            ],
        ]) === 0;
    }
}
