<?php

declare(strict_types=1);

namespace Adultdate\FilamentBooking\Concerns;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use InvalidArgumentException;

trait HasFooterActions
{
    protected array $cachedFooterActions = [];

    public function bootedHasFooterActions(): void
    {
        $this->cacheFooterActions();
    }

    public function getCachedFooterActions(): array
    {
        return $this->cachedFooterActions;
    }

    public function getFooterActions(): array
    {
        return [];
    }

    public function getCachedFooterActionsComponent(): Actions
    {

        return Actions::make($this->getCachedFooterActions())
            ->container(Schema::make($this));
    }

    protected function cacheFooterActions(): void
    {
        /** @var Action $action */
        foreach ($this->getFooterActions() as $action) {

            if ($action instanceof ActionGroup) {
                $action->livewire($this);

                /** @var array<string, Action> $flatActions */
                $flatActions = $action->getFlatActions();

                $this->mergeCachedFooterActions($flatActions);
                $this->cachedFooterActions[] = $action;

                continue;
            }

            if (! $action instanceof Action) {
                throw new InvalidArgumentException('Footer actions must be an instance of '.Action::class.', or '.ActionGroup::class.'.');
            }

            $this->cacheLivewireAction($action);
            $this->cachedFooterActions[] = $action;
        }
    }

    protected function mergeCachedFooterActions(array $actions): void
    {
        // This trait doesn't maintain a global cached actions array like InteractsWithActions
        // So we don't need to do anything here
    }
}
