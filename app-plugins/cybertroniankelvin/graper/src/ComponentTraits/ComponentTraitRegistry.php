<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\ComponentTraits;

use Illuminate\Support\Collection;

class ComponentTraitRegistry
{
    /** @var array<class-string<ComponentTrait>, ComponentTrait> */
    protected array $traits = [];

    protected static ?self $instance = null;

    public static function make(): static
    {
        return self::$instance ??= new self();
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function register(string|ComponentTrait $trait): static
    {
        if (is_string($trait)) {
            $trait = new $trait();
        }

        $this->traits[$trait::getId()] = $trait;

        return $this;
    }

    public function get(string $id): ?ComponentTrait
    {
        return $this->traits[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->traits[$id]);
    }

    public function all(): Collection
    {
        return collect($this->traits)
            ->sortBy(fn (ComponentTrait $t) => $t::getOrder())
            ->values();
    }

    public function toArray(): array
    {
        return $this->all()
            ->map(fn (ComponentTrait $t) => $t->toArray())
            ->values()
            ->all();
    }
}