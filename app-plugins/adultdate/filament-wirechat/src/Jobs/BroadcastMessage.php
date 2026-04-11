<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Jobs;

use AdultDate\FilamentWirechat\Models\Message;
use AdultDate\FilamentWirechat\Models\Participant;
use Adultdate\Wirechat\Events\MessageCreated;
use Adultdate\Wirechat\Traits\InteractsWithPanel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithPanel;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $auth;

    protected $messagesTable;

    protected $participantsTable;

    public function __construct(public Message $message, ?string $panel = null)
    {
        $this->resolvePanel($panel);
        //
        $this->onQueue($this->getPanel()->getMessagesQueue());
        $this->auth = auth()->user();

        // Get table
        $this->messagesTable = (new Message)->getTable();
        $this->participantsTable = (new Participant)->getTable();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Broadcast to the conversation channel for all participants
        event(new MessageCreated($this->message, $this->getPanel()->getId()));
    }
}
