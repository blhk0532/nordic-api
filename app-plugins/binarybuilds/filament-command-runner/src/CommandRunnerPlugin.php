<?php

declare(strict_types=1);

namespace BinaryBuilds\CommandRunner;

use BinaryBuilds\CommandRunner\Resources\CommandRuns\CommandRunResource;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;

class CommandRunnerPlugin implements Plugin
{
    private $validationRule = null;

    private $deleteHistory = true;

    public static function make(): static
    {
        return app(self::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'command-runner';
    }

    public function validateCommand(callable $commandRule): self
    {
        $this->validationRule = $commandRule;

        return $this;
    }

    public function canDeleteCommandHistory(callable|bool $deleteHistory): self
    {
        $this->deleteHistory = $deleteHistory;

        return $this;
    }

    public function getCanDeleteHistory(): callable|bool
    {
        return $this->deleteHistory;
    }

    public function getValidationRule(): ?Closure
    {
        return $this->validationRule ?? function () {};
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            CommandRunResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
