<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\ComponentTraits;

abstract class ComponentTrait
{
    abstract public static function getId(): string;

    abstract public static function getName(): string;

    abstract public static function getLabel(): string;

    abstract public static function getType(): string;

    public static function getOrder(): int
    {
        return 100;
    }

    public static function getOptions(): array
    {
        return [];
    }

    public function toArray(): array
    {
        return [
            'id' => static::getId(),
            'name' => static::getName(),
            'label' => static::getLabel(),
            'type' => static::getType(),
            'order' => static::getOrder(),
            'options' => static::getOptions(),
        ];
    }
}