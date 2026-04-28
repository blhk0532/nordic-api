<?php

declare(strict_types=1);

namespace CybertronianKelvin\Graper\Forms\Components;

use Filament\Forms\Components\Field;

class GrapesJsField extends Field
{
    protected string $view = 'graper::fields.grapesjs';

    protected bool $loadDefaultBlocks = true;

    protected string $minHeight = '600px';

    public function loadDefaultBlocks(bool $load = true): static
    {
        $this->loadDefaultBlocks = $load;

        return $this;
    }

    public function getLoadDefaultBlocks(): bool
    {
        return $this->loadDefaultBlocks;
    }

    public function minHeight(string $height): static
    {
        $this->minHeight = $height;

        return $this;
    }

    public function getMinHeight(): string
    {
        return $this->minHeight;
    }
}
