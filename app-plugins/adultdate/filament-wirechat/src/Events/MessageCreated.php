<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Events;

use AdultDate\FilamentWirechat\Models\Message;
use Adultdate\Wirechat\Traits\InteractsWithPanel;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class MessageCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithPanel;
    use InteractsWithQueue;
    use InteractsWithSockets;
    use Queueable;
    use SerializesModels;

    public $message;
    // public $receiver;

    public function __construct(Message $message, ?string $panel = null)
    {
        $this->message = $message->load([]);

        $this->resolvePanel($panel);

        $panelInstance = $this->getPanel();
        if (! $panelInstance) {
            throw new RuntimeException('Panel could not be resolved for MessageCreated event');
        }
        $this->onQueue($panelInstance->getMessagesQueue());
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        $panelInstance = $this->getPanel();
        if (! $panelInstance) {
            return [];
        }

        $panelId = $panelInstance->getId();
        $channels[] = "$panelId.conversation.{$this->message->conversation_id}";

        return array_map(function ($channelName) {
            return new PrivateChannel($channelName);
        }, $channels);
    }

    public function broadcastWhen(): bool
    {
        // Check if the message is not older than 1 minutes
        $isNotExpired = Carbon::parse($this->message->created_at)->gt(Carbon::now()->subMinute());

        return $isNotExpired;
    }

    /**
     * The name of the queue on which to place the broadcasting job.
     */
    public function broadcastQueue(): string
    {
        $panelInstance = $this->getPanel();
        if (! $panelInstance) {
            return config('filament-wirechat.broadcasting.messages_queue', 'default');
        }

        return $panelInstance->getMessagesQueue();
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
            ],

        ];
    }
}
