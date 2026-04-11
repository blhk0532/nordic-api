<?php

declare(strict_types=1);

namespace Adultdate\Wirechat\Livewire\Concerns;

use Livewire\Component;

abstract class ModalComponent extends Component
{
    public bool $forceClose = false;

    public int $skipModals = 0;

    public bool $destroySkipped = false;

    /**
     * Return default modal attributes.
     *
     * NOTE: not final so individual components can override to provide
     * their own modal configuration (keeps backward compatibility).
     *
     * @return array<string, mixed>
     */
    public static function modalAttributes(): array
    {
        return [
            'closeOnEscape' => true,
            'closeOnEscapeIsForceful' => false,
            'dispatchCloseEvent' => false,
            'destroyOnClose' => false,
            'closeOnClickAway' => true,
        ];
    }

    final public function destroySkippedModals(): self
    {
        $this->destroySkipped = true;

        return $this;
    }

    final public function skipPreviousModals($count = 1, $destroy = false): self
    {
        $this->skipPreviousModal($count, $destroy);

        return $this;
    }

    final public function skipPreviousModal($count = 1, $destroy = false): self
    {
        $this->skipModals = $count;
        $this->destroySkipped = $destroy;

        return $this;
    }

    final public function forceClose(): self
    {
        $this->forceClose = true;

        return $this;
    }

    final public function closeWirechatModal(): void
    {
        $this->dispatch('closeWirechatModal', force: $this->forceClose, skipPreviousModals: $this->skipModals, destroySkipped: $this->destroySkipped);
    }

    final public function closeChatDrawer(): void
    {
        $this->dispatch('closeChatDrawer', force: $this->forceClose, skipPreviousModals: $this->skipModals, destroySkipped: $this->destroySkipped);
    }

    final public function closeModalWithEvents(array $events): void
    {
        $this->emitModalEvents($events);
        // $this->closeModal();
        $this->closeWirechatModal();
        $this->closeChatDrawer();
    }

    private function emitModalEvents(array $events): void
    {
        foreach ($events as $component => $event) {
            if (is_array($event)) {
                [$event, $params] = $event;
            }

            if (is_numeric($component)) {
                $this->dispatch($event, ...$params ?? []);
            } else {
                $this->dispatch($event, ...$params ?? [])->to($component);
            }
        }
    }
}
