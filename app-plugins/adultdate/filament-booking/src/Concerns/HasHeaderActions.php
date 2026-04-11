<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use InvalidArgumentException;

trait HasHeaderActions
{
    protected array $cachedHeaderActions = [];

    public function bootedHasHeaderActions(): void
    {
        $this->cacheHeaderActions();
    }

    public function getCachedHeaderActions(): array
    {
        return $this->cachedHeaderActions;
    }

    public function getHeaderActions(): array
    {
        return [];
    }

    public function getCachedHeaderActionsComponent(): Actions
    {
        return Actions::make($this->getCachedHeaderActions())
            ->container(Schema::make($this));
    }

    protected function cacheHeaderActions(): void
    {
        /** @var Action $action */
        foreach ($this->getHeaderActions() as $action) {
            if ($action instanceof ActionGroup) {
                $action->livewire($this);

                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                $this->mergeCachedHeaderActions($flatActions);
                $this->cachedHeaderActions[] = $action;

                continue;
            }

            if (! $action instanceof Action) {
                throw new InvalidArgumentException('Header actions must be an instance of '.Action::class.', or '.ActionGroup::class.'.');
            }

            $this->cacheLivewireAction($action);
            $this->cachedHeaderActions[] = $action;
        }
    }

    protected function cacheLivewireAction(Action $action): Action
    {
        $action->livewire($this);

        return $action;
    }

    protected function mergeCachedHeaderActions(array $actions): void
    {
        // This trait doesn't maintain a global cached actions array like InteractsWithActions
        // So we don't need to do anything here
    }
}
