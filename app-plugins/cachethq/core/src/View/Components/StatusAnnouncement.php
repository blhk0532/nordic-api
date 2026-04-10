<?php

declare(strict_types=1);

namespace Cachet\View\Components;

use Cachet\Settings\AppSettings;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusAnnouncement extends Component
{
    protected static bool $isDiscovered = false;

    public function __construct(private ?string $content = null)
    {
        $this->content ??= app(AppSettings::class)->status_page_announcement;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('cachet::components.status-announcement', [
            'content' => (string) $this->content,
        ]);
    }
}
