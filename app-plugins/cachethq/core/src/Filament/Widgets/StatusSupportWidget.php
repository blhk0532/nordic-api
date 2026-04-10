<?php

namespace Cachet\Filament\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Blade;

class StatusSupportWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = true;

    protected static ?int $sort = 99;

    protected string $view = 'cachet::filament.widgets.status-support';

    public function getConsiderSupportingBlock()
    {
        return preg_replace(
            '/\*(.*?)\*/',
            '<x-filament::link href="https://github.com/" target="_blank" rel="nofollow noopener">$1</x-filament::link>',
            __('cachet::cachet.support.consider_supporting')
        );
    }

    public function getKeepUpToDateBlock()
    {
        return preg_replace(
            '/\*(.*?)\*/',
            '<x-filament::link href="https://ndsth.com/blog" target="_blank" rel="nofollow noopener">$1</x-filament::link>',
            __('cachet::cachet.support.keep_up_to_date')
        );
    }

    protected function getViewData(): array
    {
        return [
            'supportingHeading' => '',
            'supportingText' => Blade::render($this->getConsiderSupportingBlock()),
            'supportingDate' => Blade::render($this->getKeepUpToDateBlock()),
        ];
    }
}
