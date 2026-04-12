<?php

declare(strict_types=1);

namespace App\Services;

use WallaceMartinss\FilamentEvolution\Exceptions\EvolutionApiException;
use WallaceMartinss\FilamentEvolution\Models\WhatsappInstance;
use WallaceMartinss\FilamentEvolution\Services\EvolutionClient;

class RawWhatsappService
{
    public function __construct(
        public EvolutionClient $client,
    ) {}

    /**
     * Send a text message without formatting the phone number.
     *
     * @throws EvolutionApiException
     */
    public function sendTextRaw(string|int $instanceId, string $number, string $message): array
    {
        $instance = $this->resolveInstance($instanceId);

        return $this->client->sendText($instance->name, $number, $message);
    }

    /**
     * Resolve WhatsApp instance by ID.
     *
     * @throws EvolutionApiException
     */
    private function resolveInstance(string|int $instanceId): WhatsappInstance
    {
        $instance = WhatsappInstance::find($instanceId);

        if (! $instance) {
            throw new EvolutionApiException("WhatsApp instance not found: {$instanceId}");
        }

        if (! $instance->isConnected()) {
            throw new EvolutionApiException("WhatsApp instance is not connected: {$instance->name}");
        }

        return $instance;
    }
}
