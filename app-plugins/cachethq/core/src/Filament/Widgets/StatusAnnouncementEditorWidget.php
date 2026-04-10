<?php

declare(strict_types=1);

namespace Cachet\Filament\Widgets;

use Cachet\Settings\AppSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class StatusAnnouncementEditorWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected string $view = 'cachet::filament.widgets.status-announcement-editor-widget';

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        //    return false;
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $settings = app(AppSettings::class);
        $this->form->fill([
            'announcement' => $settings->status_page_announcement,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Status announcement')
                    ->schema([
                        RichEditor::make('announcement')
                            ->label('Dashboard announcement')
                            ->helperText('Content entered here is rendered on the public status page before the about section.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        $announcement = data_get($state, 'announcement', '');

        if (is_array($announcement)) {
            $announcement = json_encode($announcement);
        }

        $settings = app(AppSettings::class);
        $settings->status_page_announcement = (string) $announcement;
        $settings->save();

        $this->form->fill([
            'announcement' => $settings->status_page_announcement,
        ]);

        Notification::make()
            ->success()
            ->title('Announcement saved')
            ->body('The announcement is now live on the status page.')
            ->send();
    }
}
