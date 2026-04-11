<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Events;

use AdultDate\FilamentWirechat\Models\Message;
use Adultdate\Wirechat\Traits\InteractsWithPanel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithPanel;
    use InteractsWithSockets;
    use SerializesModels;

    public $message;
    // public $receiver;

    public function __construct(Message $message, ?string $panel = null)
    {
        $this->message = $message->load([]);
        $this->resolvePanel($panel);

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        $panelId = $this->getPanel()->getId();
        $channels[] = "$panelId.conversation.{$this->message->conversation_id}";

        return array_map(function ($channelName) {
            return new PrivateChannel($channelName);
        }, $channels);
    }

    /**
     * The name of the queue on which to place the broadcasting job.
     */
    public function broadcastQueue(): string
    {
        return $this->getPanel()->getMessagesQueue();
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
                'participant_id' => $this->message->participant_id,
            ],
        ];
    }
}
