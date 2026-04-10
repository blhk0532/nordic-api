<?php

namespace Cachet\Filament\Resources\Schedules;

use App\Models\User;
use Cachet\Actions\Update\CreateUpdate;
use Cachet\Data\Requests\ScheduleUpdate\CreateScheduleUpdateRequestData;
use Cachet\Enums\ComponentStatusEnum;
use Cachet\Enums\ScheduleStatusEnum;
use Cachet\Filament\Resources\Schedules\Pages\CreateSchedule;
use Cachet\Filament\Resources\Schedules\Pages\EditSchedule;
use Cachet\Filament\Resources\Schedules\Pages\ListSchedules;
use Cachet\Filament\Resources\Schedules\RelationManagers\ComponentsRelationManager;
use Cachet\Filament\Resources\Updates\RelationManagers\UpdatesRelationManager;
use Cachet\Models\Schedule;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\MentionProvider;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'cachet-maintenance';

    protected static bool $isScopedToTenant = false;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = -1;

    // protected static string|UnitEnum|null $navigationGroup = '';

    protected static bool $isGloballySearchable = true;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('cachet::schedule.form.name_label'))
                        ->required(),
                    RichEditor::make('message')
                        ->toolbarButtons([
                            // Headings
                            [
                                ToolbarButtonGroup::make('Headings', [
                                    'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                                ])->icon('fi-o-heading'),
                            ],

                            // Text alignment
                            [
                                ToolbarButtonGroup::make('Alignment', [
                                    'alignStart', 'alignCenter', 'alignEnd', 'alignJustify',
                                ])->icon('heroicon-o-bars-3-bottom-left'),
                            ],

                            // Text formatting
                            [
                                ToolbarButtonGroup::make('Text Style', [
                                    'bold', 'italic', 'underline', 'strike',
                                ])->icon('heroicon-o-bold'),

                                ToolbarButtonGroup::make('Advanced Text', [
                                    'subscript', 'superscript', 'small', 'lead',
                                ]),
                            ],

                            // Colors & highlights
                            [
                                ToolbarButtonGroup::make('Colors', [
                                    'textColor', 'highlight',
                                ])->icon('heroicon-o-swatch'),
                            ],

                            // Structure
                            [
                                ToolbarButtonGroup::make('Structure', [
                                    'paragraph', 'blockquote', 'horizontalRule',
                                ])->icon('heroicon-o-document-text'),
                            ],

                            // Lists & content blocks
                            [
                                ToolbarButtonGroup::make('Lists', [
                                    'bulletList', 'orderedList',
                                ])->icon('heroicon-o-list-bullet'),

                                ToolbarButtonGroup::make('Code', [
                                    'code', 'codeBlock',
                                ])->icon('heroicon-o-code-bracket'),
                            ],

                            // Media & embeds
                            [
                                ToolbarButtonGroup::make('Media', [
                                    'link', 'table', 'attachFiles',
                                ])->icon('heroicon-o-paper-clip'),
                            ],

                            // Advanced features
                            [
                                ToolbarButtonGroup::make('Advanced', [
                                    'customBlocks', 'mergeTags', 'details', 'grid',
                                ])->icon('heroicon-o-cog-6-tooth'),
                            ],

                            // Utilities
                            [
                                ToolbarButtonGroup::make('Utilities', [
                                    'clearFormatting', 'undo', 'redo',
                                ])->icon('heroicon-o-arrow-path'),
                            ],
                        ])
                        ->mentions([
                            MentionProvider::make('@')
                                ->getSearchResultsUsing(fn (string $search): array => User::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->orderBy('name')
                                    ->limit(10)
                                    ->pluck('name', 'id')
                                    ->all())
                                ->getLabelsUsing(fn (array $ids): array => User::query()
                                    ->whereIn('id', $ids)
                                    ->pluck('name', 'id')
                                    ->all()),
                        ])
                        ->fileAttachmentsDisk('disk')
                        ->fileAttachmentsDirectory('attachments')
                        ->fileAttachmentsVisibility('public')
                        ->customTextColors()
                        ->resizableImages()
                        ->mergeTags([
                            'name',
                            'today',
                        ])
                        ->floatingToolbars([
                            'paragraph' => [
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'subscript',
                                'superscript',
                            ],
                            'heading' => [
                                'h1',
                                'h2',
                                'h3',
                            ],
                            'table' => [
                                'tableAddColumnBefore',
                                'tableAddColumnAfter',
                                'tableDeleteColumn',
                                'tableAddRowBefore',
                                'tableAddRowAfter',
                                'tableDeleteRow',
                                'tableMergeCells',
                                'tableSplitCell',
                                'tableToggleHeaderRow',
                                'tableToggleHeaderCell',
                                'tableDelete',
                            ],
                        ])
                        ->label(__('cachet::schedule.form.message_label'))
                        ->columnSpanFull(),
                    Repeater::make('scheduleComponents')
                        ->visibleOn('create')
                        ->relationship()
                        ->defaultItems(0)
                        ->addActionLabel(__('Add Tekniker'))
                        ->schema([
                            Select::make('component_id')
                                ->preload()
                                ->required()
                                ->relationship('component', 'name')
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->label(__('Tekniker')),
                            Hidden::make('component_status')
                                ->default(ComponentStatusEnum::operational->value),
                        ])
                        ->label(__('Affected Tekniker'))
                        ->columnSpanFull(),
                ])->columnSpan(3),
                Section::make()->schema([
                    DateTimePicker::make('scheduled_at')
                        ->label(__('cachet::schedule.form.scheduled_at_label'))
                        ->native(false) // Fixes #288 (Filament DateTimePicker does not display time selection on Firefox)
                        ->default(now())
                        ->required(),
                    DateTimePicker::make('completed_at')
                        ->label(__('cachet::schedule.form.completed_at_label'))
                        ->native(false) // Fixes #288 (Filament DateTimePicker does not display time selection on Firefox)
                        ->default(now()->addDay()),
                ])->columnSpan(1),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('cachet::schedule.list.headers.name'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('cachet::schedule.list.headers.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label(__('cachet::schedule.list.headers.scheduled_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->label(__('cachet::schedule.list.headers.completed_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('cachet::schedule.list.headers.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('cachet::schedule.list.headers.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('cachet::schedule.list.headers.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('add-update')
                    ->disabled(fn (Schedule $record) => $record->status === ScheduleStatusEnum::complete)
                    ->label(__('Update'))
                    ->color('info')
                    ->action(function (CreateUpdate $createUpdate, Schedule $record, array $data) {
                        $createUpdate->handle($record, CreateScheduleUpdateRequestData::from($data));

                        Notification::make()
                            ->title(__('cachet::schedule.add_update.success_title', ['name' => $record->name]))
                            ->body(__('cachet::schedule.add_update.success_body'))
                            ->success()
                            ->send();
                    })
                    ->schema([
                        TextInput::make('message')
                            ->label(__('cachet::schedule.add_update.form.message_label'))
                            ->required(),

                        DateTimePicker::make('completed_at')
                            ->label(__('cachet::schedule.add_update.form.completed_at_label')),
                    ]),
                Action::make('complete')
                    ->disabled(fn (Schedule $record): bool => $record->status === ScheduleStatusEnum::complete)
                    ->label(__('Slutför'))
                    ->color('danger')
                    ->schema([
                        DateTimePicker::make('completed_at')
                            ->label(__('Slutdatum'))
                            ->default(now())
                            ->required(),
                    ])
                    ->color('danger')
                    ->action(fn (Schedule $record, array $data) => $record->update(['completed_at' => $data['completed_at']])),
                EditAction::make(),
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
            ->emptyStateHeading(__('cachet::schedule.list.empty_state.heading'))
            ->emptyStateDescription(__('cachet::schedule.list.empty_state.description'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ComponentsRelationManager::class,
            UpdatesRelationManager::class,
        ];
    }

    public static function getLabel(): ?string
    {
        return trans_choice('cachet::schedule.resource_label', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('cachet::schedule.resource_label', 2);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Schedule::query()->count();
        //    return (string) Schedule::inTheFuture()->count();
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
