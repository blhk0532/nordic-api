<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationEvent;
use Usamamuneerchaudhary\Notifier\Models\NotificationPreference;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;

class NotificationPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Notification Preferences';

    protected static ?string $title = 'Notification Preferences';

    protected static string|null|\UnitEnum $navigationGroup = 'Notifications';

    protected static ?int $navigationSort = 6;

    protected string $view = 'notifier::filament.pages.notification-preferences';

    public ?array $data = [];

    public function getHeading(): string|Htmlable|null
    {
        return null;
    }

    public function mount(): void
    {
        $user = Auth::user();
        $events = NotificationEvent::where('is_active', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        $activeChannels = NotificationChannel::where('is_active', true)
            ->orderBy('title')
            ->get();

        $groupedEvents = $events->groupBy('group');

        $formData = [];

        foreach ($groupedEvents as $group => $groupEvents) {
            foreach ($groupEvents as $event) {
                $preference = NotificationPreference::where('user_id', $user->id)
                    ->where('notification_event_id', $event->id)
                    ->first();

                $defaultChannels = $this->getDefaultChannelsForEvent($event);

                $channelData = [];
                foreach ($activeChannels as $channel) {
                    if ($preference && isset($preference->channels[$channel->type])) {
                        $channelData[$channel->type] = (bool) $preference->channels[$channel->type];
                    } else {
                        $channelData[$channel->type] = in_array($channel->type, $defaultChannels);
                    }
                }

                $formData["event_{$event->id}"] = $channelData;
            }
        }

        $this->form->fill($formData);
    }

    protected function getFormSchema(): array
    {
        $events = NotificationEvent::where('is_active', true)
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        $activeChannels = NotificationChannel::where('is_active', true)
            ->orderBy('title')
            ->get();

        $groupedEvents = $events->groupBy('group');

        $sections = [];

        foreach ($groupedEvents as $group => $groupEvents) {
            $fields = [];

            foreach ($groupEvents as $event) {
                $channelCheckboxes = [];

                foreach ($activeChannels as $channel) {
                    $channelCheckboxes[] = Checkbox::make("event_{$event->id}.{$channel->type}")
                        ->label('')
                        ->inline(false);
                }

                $fields[] = Grid::make([
                    'default' => 1,
                    'md' => $activeChannels->count() + 1,
                ])
                    ->schema([
                        Forms\Components\Placeholder::make("event_label_{$event->id}")
                            ->label($event->name)
                            ->content($event->description ?: '')
                            ->extraAttributes([
                                'class' => 'font-medium text-gray-900',
                            ]),
                        ...$channelCheckboxes,
                    ])
                    ->extraAttributes([
                        'class' => 'border-b border-gray-200 py-4 first:pt-0 last:border-b-0 last:pb-0',
                    ]);
            }

            $sections[] = Section::make($group ?: 'General')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => $activeChannels->count() + 1,
                    ])
                        ->schema([
                            Forms\Components\Placeholder::make('header_label')
                                ->label('')
                                ->content(''),
                            ...array_map(function ($channel) {
                                return Forms\Components\Placeholder::make("header_{$channel->type}")
                                    ->label($channel->title)
                                    ->content('')
                                    ->extraAttributes([
                                        'class' => 'font-semibold text-gray-700 text-sm',
                                    ]);
                            }, $activeChannels->all()),
                        ])
                        ->extraAttributes([
                            'class' => 'border-b-2 border-gray-300 pb-3 mb-2',
                        ]),
                    ...$fields,
                ])
                ->collapsible()
                ->collapsed(false);
        }

        return $sections;
    }

    public function save(): void
    {
        $preferences = NotificationSetting::getPreferences();
        if (! ($preferences['allow_override'] ?? config('notifier.settings.preferences.allow_override', true))) {
            Notification::make()
                ->title('Preferences Disabled')
                ->body('User preference override is disabled by administrator.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $user = Auth::user();
        $activeChannels = NotificationChannel::where('is_active', true)
            ->pluck('type')
            ->toArray();

        $updated = 0;

        foreach ($data as $key => $channels) {
            if (! str_starts_with($key, 'event_')) {
                continue;
            }

            $eventId = (int) str_replace('event_', '', $key);
            $event = NotificationEvent::find($eventId);

            if (! $event) {
                continue;
            }

            $validatedChannels = [];
            foreach ($channels as $channelType => $enabled) {
                if (in_array($channelType, $activeChannels)) {
                    $validatedChannels[$channelType] = (bool) $enabled;
                }
            }

            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'notification_event_id' => $eventId,
                ],
                [
                    'channels' => $validatedChannels,
                ]
            );

            $updated++;
        }

        Notification::make()
            ->title('Preferences Saved')
            ->body("Successfully updated {$updated} notification preference(s).")
            ->success()
            ->send();
    }

    protected function getDefaultChannelsForEvent(NotificationEvent $event): array
    {
        if (isset($event->settings['channels']) && is_array($event->settings['channels'])) {
            return $event->settings['channels'];
        }

        $defaultChannels = NotificationSetting::get('preferences.default_channels', config('notifier.settings.preferences.default_channels', ['email']));

        return is_array($defaultChannels) ? $defaultChannels : ['email'];
    }
}
