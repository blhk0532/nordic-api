<?php

namespace Cachet\Filament\Resources\Incidents;

use App\Models\User;
use Cachet\Actions\Update\CreateUpdate as CreateIncidentUpdateAction;
use Cachet\Data\Requests\IncidentUpdate\CreateIncidentUpdateRequestData;
use Cachet\Enums\ComponentStatusEnum;
use Cachet\Enums\IncidentStatusEnum;
use Cachet\Enums\ResourceVisibilityEnum;
use Cachet\Filament\Resources\Incidents\Pages\CreateIncident;
use Cachet\Filament\Resources\Incidents\Pages\EditIncident;
use Cachet\Filament\Resources\Incidents\Pages\ListIncidents;
use Cachet\Filament\Resources\Incidents\RelationManagers\ComponentsRelationManager;
use Cachet\Filament\Resources\Updates\RelationManagers\UpdatesRelationManager;
use Cachet\Models\Component;
use Cachet\Models\Incident;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use UnitEnum;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static string|UnitEnum|null $navigationGroup = '';

    protected static bool $isScopedToTenant = false;

    protected static ?int $navigationSort = -1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('Område'))
                        ->required()
                        ->maxLength(255)
                        ->autocomplete(false),
                    ToggleButtons::make('status')
                        ->label(__('Status'))
                        ->inline()
                        ->columnSpanFull()
                        ->options(IncidentStatusEnum::class)
                        ->default(IncidentStatusEnum::unknown)
                        ->required(),
                    MarkdownEditor::make('message')
                        ->label(__('Meddelande'))
                        ->required()
                        ->columnSpanFull()
                        ->toolbarButtons([
                            ['bold', 'italic', 'strike', 'link'],
                            ['heading'],
                            ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                            ['table', 'attachFiles'],
                            ['undo', 'redo'],
                        ]),
                    Select::make('component_id')
                        ->label(__('Campaign'))
                        ->relationship('component', 'name')
                        ->default(fn () => Component::query()->orderBy('id')->value('id'))
                        ->searchable()
                        ->preload(),
                    Select::make('service_user_id')
                        ->label(__('Technician'))
                        ->default(fn () => User::query()->where('role', 'service')->orderBy('id')->value('id'))
                        ->options(fn () => User::query()->where('role', 'service')->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    DateTimePicker::make('occurred_at')
                        ->default(fn () => now())
                        ->label(__('Schemadag'))
                        ->helperText(__('')),
                    ToggleButtons::make('visible')
                        ->label(__('cachet::incident.form.visible_label'))
                        ->inline()
                        ->hidden()
                        ->options(ResourceVisibilityEnum::class)
                        ->default(ResourceVisibilityEnum::guest)
                        ->live()
                        ->required(),
                    Select::make('team_id')
                        ->label(__('Team'))
                        ->relationship('team', 'name')
                        ->default(fn (): ?int => filament()->getTenant()?->getKey() ?? Auth::user()?->current_team_id)
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get): bool => $get('visible') === ResourceVisibilityEnum::team || $get('visible') === ResourceVisibilityEnum::team->value)
                        ->nullable(),
                    Repeater::make('incidentComponents')
                        ->visibleOn('create')
                        ->relationship()
                        ->defaultItems(0)
                        ->addActionLabel(__('cachet::incident.form.add_component.action_label'))
                        ->schema([
                            Select::make('component_id')
                                ->preload()
                                ->required()
                                ->relationship('component', 'name')
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->label(__('cachet::incident.form.add_component.component_label')),
                            ToggleButtons::make('component_status')
                                ->label(__('cachet::incident.form.add_component.status_label'))
                                ->inline()
                                ->options(ComponentStatusEnum::class)
                                ->required(),
                        ])
                        ->label(__('cachet::incident.form.add_component.header')),
                ])
                    ->columnSpan(3),
                Section::make()->schema([
                    Select::make('user_id')
                        ->label(__('User'))
                        ->helperText(__('cachet::incident.form.user_helper'))
                        ->options(function (): array {
                            $userModel = config('cachet.user_model');

                            return $userModel::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                        ->default(fn (): ?int => Auth::id())
                        ->searchable()
                        ->preload(),
                    Toggle::make('notifications')
                        ->label(__('cachet::incident.form.notifications_label'))
                        ->required(),
                    Toggle::make('stickied')
                        ->label(__('cachet::incident.form.stickied_label'))
                        ->required(),
                    TextInput::make('guid')
                        ->label(__('cachet::incident.form.guid_label'))
                        ->visibleOn(['edit'])
                        ->hidden()
                        ->disabled()
                        ->readonly()
                        ->columnSpanFull(),
                ])
                    ->columnSpan(1),
            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Område'))
                    ->searchable(),
                TextColumn::make('component.name')
                    ->label(__('Tekniker'))
                    ->searchable(),
                TextColumn::make('latest_status')
                    ->label(__('cachet::incident.list.headers.status'))
                    ->sortable()
                    ->badge(),
                TextColumn::make('visible')
                    ->label(__('Synlighet'))
                    ->sortable()
                    ->color('warning')
                    ->badge(),
                IconColumn::make('stickied')
                    ->label(__('cachet::incident.list.headers.stickied'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->boolean(),
                TextColumn::make('occurred_at')
                    ->label(__('cachet::incident.list.headers.occurred_at'))
                    ->dateTime()
                    ->state(fn (Incident $record) => $record->occurred_at?->format('Y-m-d') ?? '-')
                    ->limit(12)
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                IconColumn::make('notifications')
                    ->label(__('cachet::incident.list.headers.notified_subscribers'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('cachet::incident.list.headers.created_at'))
                    ->dateTime()
                    ->hidden()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('cachet::incident.list.headers.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('cachet::incident.list.headers.deleted_at'))
                    ->dateTime()
                    ->hidden()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->label(__('cachet::incident.list.headers.status'))
                    ->options(IncidentStatusEnum::class),
            ])
            ->recordActions([
                Action::make('add-update')
                    ->disabled(fn (Incident $record) => $record->status === IncidentStatusEnum::fixed)
                    ->label(__('Update'))
                    ->color('info')
                    ->action(function (CreateIncidentUpdateAction $createIncidentUpdate, Incident $record, array $data) {
                        $createIncidentUpdate->handle($record, CreateIncidentUpdateRequestData::from($data));

                        Notification::make()
                            ->title(__('cachet::incident.record_update.success_title', ['name' => $record->name]))
                            ->body(__('cachet::incident.record_update.success_body'))
                            ->success()
                            ->send();
                    })
                    ->schema([
                        RichEditor::make('message')
                            ->label(__('cachet::incident.record_update.form.message_label'))
                            ->required()
                            ->columnSpanFull(),
                        ToggleButtons::make('status')
                            ->label(__('cachet::incident.record_update.form.status_label'))
                            ->options(IncidentStatusEnum::class)
                            ->inline()
                            ->required(),
                        Select::make('user_id')
                            ->label(__('cachet::incident.record_update.form.user_label'))
                            ->hint(__('cachet::incident.record_update.form.user_helper'))
                            ->relationship('user', 'name')
                            ->default(fn (): ?int => Auth::id())
                            ->searchable()
                            ->preload(),
                    ]),
                Action::make('view-incident')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Incident $record): string => route('cachet.status-page.incident', $record))
                    ->label(__('View')),
                EditAction::make()
                    ->color('warning'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                CreateAction::make()
                    ->label('')
                    ->icon('heroicon-c-plus-circle')
                    ->url(fn () => static::getUrl('create'))
                    ->color('success'),
            ])
            ->emptyStateHeading(__('cachet::incident.list.empty_state.heading'))
            ->emptyStateDescription(__('cachet::incident.list.empty_state.description'));
    }

    public static function getRelations(): array
    {
        return [
            ComponentsRelationManager::class,
            UpdatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncidents::route('/'),
            'create' => CreateIncident::route('/create'),
            'edit' => EditIncident::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return trans_choice('cachet::incident.resource_label', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('cachet::incident.resource_label', 2);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Incident::unresolved()
            ->get()
            ->filter(fn (Incident $incident) => in_array($incident->latest_status, IncidentStatusEnum::unresolved()))->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        if ((int) static::getNavigationBadge() > 0) {
            return 'danger';
        }

        return 'success';
    }

    public static function getNavigationGroup(): ?string
    {
        $team = filament()->getTenant()?->name;
        $name = Str::ucwords($team);

        return $name ? ' TEAM | '.$name : 'TEAM | Administration';
        // return filament()->getTenant()?->name ? filament()->getTenant()?->name : 'Administration';
    }
}
