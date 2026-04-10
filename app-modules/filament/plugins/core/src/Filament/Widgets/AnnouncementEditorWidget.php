<?php

declare(strict_types=1);

namespace Cachet\Filament\Widgets;

use App\Models\Announcement;
use App\Models\User;
use Cachet\Models\Component;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AnnouncementEditorWidget extends Widget implements HasSchemas
{
    use InteractsWithSchemas;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected string $view = 'cachet::filament.widgets.announcement-editor-widget';

    public ?int $editingId = null;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public static function canView(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $editId = request()->query('edit_announcement');
        $this->editingId = $editId ? (int) $editId : null;

        if ($this->editingId) {
            $announcement = Announcement::find($this->editingId);
            if ($announcement) {
                $this->form->fill([
                    'title' => $announcement->title,
                    'content' => $announcement->content,
                    'priority' => $announcement->priority,
                    'tekniker_id' => $announcement->tekniker_id,
                    'component_id' => $announcement->component_id,
                    'starts_at' => $announcement->starts_at?->format('Y-m-d H:i:s'),
                    'ends_at' => $announcement->ends_at?->format('Y-m-d H:i:s'),
                ]);

                return;
            }
        }

        $this->form->fill([
            'priority' => 'low',
            'starts_at' => now()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $teamId = Auth::user()?->current_team_id;

        return $schema
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->autocomplete(false),
                DateTimePicker::make('starts_at')
                    ->label('Starts At')
                    ->required()
                    ->default(now()),
                DateTimePicker::make('ends_at')
                    ->label('Ends At')
                    ->required()
                    ->default(now()->addDay()),

                RichEditor::make('content')
                    ->label('Content')
                    ->required()
                    ->columnSpanFull(),

                Select::make('priority')
                    ->label('Priority')
                    ->hidden()
                    ->default('low')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ])
                    ->required()
                    ->default('low'),

                Select::make('component_id')
                    ->hidden()
                    ->label('Component')
                    ->options(
                        Component::where('team_id', $teamId)
                            ->pluck('name', 'id')
                    )
                    ->nullable()
                    ->placeholder('None'),

                Select::make('tekniker_id')
                    ->hidden()
                    ->label('Tekniker')
                    ->options(
                        User::where('current_team_id', $teamId)
                            ->pluck('name', 'id')
                    )
                    ->nullable()
                    ->placeholder('None'),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $isUpdate = $this->editingId !== null;

        $saveData = [
            'title' => $data['title'],
            'content' => $data['content'] ?? '',
            'priority' => $data['priority'] ?? 'low',
            'starts_at' => isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : now(),
            'ends_at' => isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : now()->addDay(),
            'tekniker_id' => $data['tekniker_id'] ?? null,
            'component_id' => $data['component_id'] ?? null,
            'is_active' => true,
            'team_id' => Auth::user()?->current_team_id,
            'user_id' => Auth::id(),
        ];

        if ($isUpdate) {
            /** @var Announcement|null $announcement */
            $announcement = Announcement::find($this->editingId);

            if (! $announcement) {
                Notification::make()->danger()->title('Error')->body('Announcement not found.')->send();

                return;
            }

            $announcement->update($saveData);
            $title = 'Announcement Updated';
            $body = 'Announcement updated successfully.';
        } else {
            Announcement::create($saveData);
            $title = 'Announcement Created';
            $body = 'Announcement created successfully.';
        }

        $this->resetForm();

        Notification::make()
            ->success()
            ->title($title)
            ->body($body)
            ->send();

        $this->dispatch('refresh-announcement-widget');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->form->fill([
            'priority' => 'low',
            'starts_at' => now()->format('Y-m-d H:i:s'),
            'title' => null,
            'content' => null,
            'tekniker_id' => null,
            'component_id' => null,
            'ends_at' => null,
        ]);
    }
}
