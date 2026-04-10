<?php

declare(strict_types=1);

namespace Cachet\Filament\Widgets;

use Cachet\Settings\AppSettings;
use Filament\Widgets\Widget;

class StatusAnnouncementWidget extends Widget
{
    protected int|string|array $columnSpan = 'full';

    protected static bool $isDiscovered = false;

    protected static ?int $sort = 0;

    protected string $view = 'cachet::filament.widgets.status-announcement-widget';

    public string $content = '';

    public static function canView(): bool
    {
        return false;

    }

    public function mount(AppSettings $appSettings): void
    {
        $this->content = (string) $appSettings->status_page_announcement;
    }

    protected function getViewData(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
